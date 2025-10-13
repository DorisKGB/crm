<?php

namespace App\Libraries;

class FirebaseMessaging
{
    private $access_token;
    private $project_id;
    private $fcm_url;

    public function __construct()
    {
        $this->project_id = env('FIREBASE_PROJECT_ID') ?: 'intranet-message';
        $this->fcm_url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
        $this->access_token = $this->getAccessToken();
    }

    /**
     * Obtener Access Token usando Service Account Key
     */
    private function getAccessToken()
    {
        try {
            // Ruta al archivo JSON de la service account
            $serviceAccountPath = APPPATH . 'Config/firebase-service-account.json';
            
            if (!file_exists($serviceAccountPath)) {
                throw new \Exception('Service account file not found at: ' . $serviceAccountPath);
            }

            $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
            
            if (!$serviceAccount) {
                throw new \Exception('Invalid service account JSON file');
            }

            // Crear JWT
            $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
            $now = time();
            $payload = json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600 // 1 hora
            ]);

            // Codificar en base64
            $base64Header = $this->base64UrlEncode($header);
            $base64Payload = $this->base64UrlEncode($payload);

            // Firmar con la clave privada
            $signature = '';
            $signData = $base64Header . "." . $base64Payload;
            
            if (!openssl_sign($signData, $signature, $serviceAccount['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new \Exception('Failed to sign JWT');
            }

            $base64Signature = $this->base64UrlEncode($signature);
            $jwt = $signData . "." . $base64Signature;

            // Intercambiar JWT por access token
            return $this->exchangeJwtForAccessToken($jwt);

        } catch (\Exception $e) {
            log_message('error', 'Error getting FCM access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Intercambiar JWT por Access Token
     */
    private function exchangeJwtForAccessToken($jwt)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            log_message('error', 'Failed to get access token. HTTP Code: ' . $http_code . ', Response: ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Codificación Base64 URL-safe
     */
    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Enviar notificación a un token específico
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        if (!$this->access_token) {
            return [
                'success' => false,
                'error' => 'No access token available'
            ];
        }

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
             //   'data' => $data,
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ]
                ]
            ]
        ];

        return $this->sendRequest($message);
    }

    /**
     * Enviar notificación a múltiples tokens
     */
    public function sendToMultipleTokens($tokens, $title, $body, $data = [])
    {
        if (!$this->access_token) {
            return [
                'success' => false,
                'error' => 'No access token available'
            ];
        }

        $results = [];
        $success_count = 0;
        $failure_count = 0;

        foreach ($tokens as $token) {
            $result = $this->sendToToken($token, $title, $body, $data);
            $results[] = [
                'token' => $token,
                'result' => $result
            ];

            if ($result['success']) {
                $success_count++;
            } else {
                $failure_count++;
            }
        }

        return [
            'success' => $success_count > 0,
            'total_sent' => count($tokens),
            'success_count' => $success_count,
            'failure_count' => $failure_count,
            'results' => $results
        ];
    }

    /**
     * Enviar notificación a un tópico
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        if (!$this->access_token) {
            return [
                'success' => false,
                'error' => 'No access token available'
            ];
        }

        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high'
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ]
                ]
            ]
        ];

        return $this->sendRequest($message);
    }

    /**
     * Hacer la petición HTTP a FCM
     */
    private function sendRequest($message)
    {
        $headers = [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcm_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            log_message('error', 'FCM CURL Error: ' . $curl_error);
            return [
                'success' => false,
                'error' => 'CURL Error: ' . $curl_error
            ];
        }

        $result = [
            'success' => $http_code === 200,
            'http_code' => $http_code,
            'response' => json_decode($response, true),
            'raw_response' => $response
        ];

        log_message('info', 'FCM Response: ' . $response);

        return $result;
    }
}