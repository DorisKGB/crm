<?php

namespace App\Libraries;

use Config\Services;
use Exception;

class VseeClient
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected $tokenUrl;
    protected $cache;

    protected $apiToken;
    protected $apiKey;
    protected $apiSecret;
    protected $accountCode;


    public function __construct()
    {
        $this->clientId     = env('VSEE_CLIENT_ID');
        $this->clientSecret = env('VSEE_CLIENT_SECRET');
        $this->baseUrl      = env('VSEE_BASE_URL');
        $this->tokenUrl     = env('VSEE_TOKEN_URL');
        $this->cache        = Services::cache();

        // Headers personalizados
        $this->apiToken     = env('VSEE_API_TOKEN');
        $this->apiKey       = env('VSEE_API_KEY');
        $this->apiSecret    = env('VSEE_API_SECRET');
        $this->accountCode  = env('VSEE_ACCOUNT_CODE');
    }

    /**
     * Obtiene y cachea el token de acceso OAuth2
     */
    protected function getAccessToken(): string
    {
        if ($token = $this->cache->get('vsee_token')) {
            return $token;
        }

        $client = Services::curlrequest();
        $response = $client->post($this->tokenUrl, [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $body    = json_decode($response->getBody(), true);
        $token   = $body['access_token'];
        $expires = $body['expires_in'] ?? 3600;

        $this->cache->save('vsee_token', $token, $expires - 60);
        return $token;
    }

    /**
     * Hace una petición HTTP autenticada
     */
    protected function request(string $method, string $uri, array $options = [])
    {
        $client = Services::curlrequest([
            'base_uri' => $this->baseUrl,
            'headers'  => array_merge([
                'Content-Type'   => 'application/json',
                'X-ApiToken'     => $this->apiToken,
                'X-ApiKey'       => $this->apiKey,
                'X-ApiSecret'    => $this->apiSecret,
                'X-AccountCode'  => $this->accountCode,
                'Accept'         => 'application/json',
            ], $options['headers'] ?? [])
        ]);

        unset($options['headers']);

        $response = $client->request($method, $uri, $options);
        return json_decode($response->getBody(), true);
    }



    /**
     * ✅ Crea un usuario vía SSO con retorno de ID, username y token
     */
    public function createUserSSO(array $userData)
    {
        return $this->request('POST', '/vc/next/api_v3/users/sso?fields=vsee', [
            'json' => $userData
        ]);
    }

    // ------------------------------------------
    // Opcional: otros métodos que ya tenías
    // ------------------------------------------

    public function createAppointment(array $data)
    {
        return $this->request('POST', '/v1/appointments', ['json' => $data]);
    }

    public function createIntake(array $data)
    {
        return $this->request('POST', '/vc/stable/api_v3/intakes', ['json' => $data]);
    }

    public function add_walkin(array $data)
    {
        try {
            $multipart = [];
            foreach ($data as $key => $value) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value
                ];
            }

            $client = \Config\Services::curlrequest([
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'X-ApiToken'    => $this->apiToken,
                    'X-AccountCode' => $this->accountCode,
                    // No pongas Content-Type
                ]
            ]);

            $response = $client->post('/vc/next/api_v3/visits/add_walkin', [
                'multipart' => $multipart
            ]);

            $body = json_decode($response->getBody(), true);
            log_message('error', 'Respuesta de add_walkin: ' . print_r($body, true));
            return $body;
        } catch (\Throwable $e) {
            log_message('error', 'add_walkin error: ' . $e->getMessage());

            // NUEVO: intentar mostrar contenido del cuerpo aunque no sea una excepción Guzzle específica
            if (method_exists($e, 'getResponse')) {
                $body = (string) $e->getResponse()->getBody();
                log_message('error', 'Cuerpo de error (getResponse): ' . $body);
            } elseif (property_exists($e, 'response')) {
                log_message('error', 'Cuerpo de error (propiedad response): ' . print_r($e->response, true));
            } else {
                log_message('error', 'Excepción sin cuerpo: ' . get_class($e));
            }

            return null;
        }
    }

    /**
     * ✅ Crear Sala
     */
    public function createRoom(array $Data)
    {
        return $this->request('POST', '/vc/next/api_v3/rooms', [
            'json' => $Data
        ]);
    }



    public function listAppointments(array $queryParams = [])
    {
        return $this->request('GET', '/v1/appointments', ['query' => $queryParams]);
    }

    public function getAppointment(string $appointmentId)
    {
        return $this->request('GET', "/v1/appointments/{$appointmentId}");
    }

    public function updateAppointment(string $appointmentId, array $data)
    {
        return $this->request('PATCH', "/v1/appointments/{$appointmentId}", ['json' => $data]);
    }

    public function cancelAppointment(string $appointmentId)
    {
        return $this->request('DELETE', "/v1/appointments/{$appointmentId}");
    }

    public function listUsers()
    {
        return $this->request('GET', '/v1/users');
    }

    public function getUser(string $userId)
    {
        return $this->request('GET', "/v1/users/{$userId}");
    }

    public function sendMessage(string $roomId, string $message)
    {
        return $this->request('POST', "/v1/chat/{$roomId}/message", [
            'json' => ['text' => $message],
        ]);
    }

    public function sendFile(string $roomId, string $fileUrl)
    {
        return $this->request('POST', "/v1/chat/{$roomId}/file", [
            'json' => ['fileUrl' => $fileUrl],
        ]);
    }

    public function getChatHistory(string $roomId, array $queryParams = [])
    {
        return $this->request('GET', "/v1/chat/{$roomId}/history", ['query' => $queryParams]);
    }

    public function joinMeeting(string $meetingId)
    {
        $res = $this->request('GET', "/v1/meetings/{$meetingId}/join");
        return $res['joinUrl'] ?? $res;
    }

    public function getMeetingHistory(string $meetingId, array $queryParams = [])
    {
        return $this->request('GET', "/v1/meetings/{$meetingId}/history", ['query' => $queryParams]);
    }
}
