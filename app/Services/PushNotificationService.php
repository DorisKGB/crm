<?php

namespace App\Services;

use App\Models\Notification_Token_Model;
use App\Libraries\FirebaseMessaging;

/**
 * Servicio genérico para el envío de notificaciones push
 * Centraliza toda la lógica de notificaciones push para reutilización
 */
class PushNotificationService
{
    protected $notificationTokenModel;
    protected $fcm;

    public function __construct()
    {
        $this->notificationTokenModel = new Notification_Token_Model();
        $this->fcm = new FirebaseMessaging();
    }

    /**
     * Envía notificaciones push a múltiples usuarios
     * 
     * @param array $recipients Array de IDs de usuarios
     * @param string $title Título de la notificación
     * @param string $message Mensaje de la notificación
     * @param array $data Datos adicionales para la notificación
     * @param string $notificationType Tipo de notificación para logging
     * @return array Resultado del envío con estadísticas
     */
    public function sendToMultipleUsers($recipients, $title, $message, $data = [], $notificationType = 'general')
    {
        try {
            $success_count = 0;
            $failure_count = 0;
            $no_token_count = 0;
            $errors = [];

            foreach ($recipients as $user_id) {
                $result = $this->sendToUser($user_id, $title, $message, $data);
                
                if ($result['success']) {
                    $success_count++;
                } else {
                    $failure_count++;
                    if ($result['reason'] === 'no_token') {
                        $no_token_count++;
                    } else {
                        $errors[] = "Usuario {$user_id}: " . $result['error'];
                    }
                }
            }

            $total_recipients = count($recipients);
            $result = [
                'success' => true,
                'total_recipients' => $total_recipients,
                'success_count' => $success_count,
                'failure_count' => $failure_count,
                'no_token_count' => $no_token_count,
                'errors' => $errors,
                'notification_type' => $notificationType
            ];

            // Log del resultado
            log_message('info', sprintf(
                'Push notifications [%s]: %d enviadas, %d fallidas, %d sin token de %d destinatarios',
                $notificationType,
                $success_count,
                $failure_count,
                $no_token_count,
                $total_recipients
            ));

            return $result;

        } catch (\Exception $e) {
            log_message('error', 'Error en PushNotificationService::sendToMultipleUsers: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'notification_type' => $notificationType
            ];
        }
    }

    /**
     * Envía notificación push a un usuario específico
     * 
     * @param int $user_id ID del usuario
     * @param string $title Título de la notificación
     * @param string $message Mensaje de la notificación
     * @param array $data Datos adicionales
     * @return array Resultado del envío
     */
    public function sendToUser($user_id, $title, $message, $data = [])
    {
        try {
            // Obtener el token FCM del usuario
            $token = $this->notificationTokenModel->getUserToken($user_id);
            
            if (!$token) {
                log_message('debug', "No FCM token found for user {$user_id}");
                return [
                    'success' => false,
                    'reason' => 'no_token',
                    'error' => 'Usuario no tiene token FCM registrado'
                ];
            }

            // Enviar notificación push
            $result = $this->fcm->sendToToken($token, $title, $message, $data);
            
            if ($result['success']) {
                log_message('info', "Push notification sent to user {$user_id}");
                return [
                    'success' => true,
                    'message' => 'Notificación enviada correctamente'
                ];
            } else {
                log_message('error', "Failed to send push notification to user {$user_id}: " . json_encode($result));
                return [
                    'success' => false,
                    'reason' => 'fcm_error',
                    'error' => $result['error'] ?? 'Error desconocido en FCM'
                ];
            }

        } catch (\Exception $e) {
            log_message('error', "Error sending push notification to user {$user_id}: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'exception',
                'error' => $e->getMessage()
            ];
        }
    }


    /**
     * Envía notificación de cambio de estado
     * 
     * @param array $recipients Array de IDs de usuarios
     * @param string $entity_type Tipo de entidad (excuse, stamp, etc.)
     * @param int $entity_id ID de la entidad
     * @param string $old_status Estado anterior
     * @param string $new_status Nuevo estado
     * @param string $clinic_name Nombre de la clínica
     * @return array Resultado del envío
     */
    public function sendStatusChangeNotification($recipients, $entity_type, $entity_id, $old_status, $new_status, $clinic_name = '')
    {
        $title = "Cambio de Estado";
        $message = "El {$entity_type} #{$entity_id} ha cambiado de {$old_status} a {$new_status}";
        if ($clinic_name) {
            $message .= " desde {$clinic_name}";
        }
        
        $data = [
            'type' => 'status_change',
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'clinic_name' => $clinic_name,
            'url' => site_url($entity_type)
        ];

        return $this->sendToMultipleUsers($recipients, $title, $message, $data, 'status_change');
    }


    /**
     * Obtiene estadísticas de tokens FCM
     * 
     * @return array Estadísticas de tokens
     */
    public function getTokenStats()
    {
        try {
            $total_users = $this->notificationTokenModel->getTotalUsers();
            $users_with_tokens = $this->notificationTokenModel->getUsersWithTokens();
            $users_without_tokens = $total_users - $users_with_tokens;

            return [
                'success' => true,
                'total_users' => $total_users,
                'users_with_tokens' => $users_with_tokens,
                'users_without_tokens' => $users_without_tokens,
                'coverage_percentage' => $total_users > 0 ? round(($users_with_tokens / $total_users) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting token stats: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envía notificación push de mensaje de chat
     * 
     * @param int $to_user_id ID del usuario destinatario
     * @param int $from_user_id ID del usuario remitente
     * @param string $message Contenido del mensaje
     * @param int $message_id ID del mensaje
     * @param string $sender_name Nombre del remitente
     * @return array Resultado del envío
     */
    public function sendChatNotification($to_user_id, $from_user_id, $message, $message_id, $sender_name = '')
    {
        try {            
            // Verificar si el usuario destinatario tiene token FCM
            $validation = $this->validateUserForNotifications($to_user_id);
            if (!$validation['success'] || !$validation['can_receive_notifications']) {
                log_message('debug', "User {$to_user_id} cannot receive push notifications");
                return [
                    'success' => false,
                    'reason' => 'no_token',
                    'error' => 'Usuario no puede recibir notificaciones push'
                ];
            }            
            // Preparar título y mensaje de la notificación
            $title = $sender_name ? "Nuevo mensaje de {$sender_name}" : "Nuevo mensaje";
            $notification_message = strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;

            // Datos adicionales para la notificación
            $data = [
                'type' => 'chat_message',
                'message_id' => $message_id,
                'from_user_id' => $from_user_id,
                'to_user_id' => $to_user_id,
                'sender_name' => $sender_name,
                'message_preview' => $notification_message,
                'url' => site_url("messages/view/{$message_id}"),
                'timestamp' => time()
            ];            
            $token = $validation['token'];
            $result = $this->fcm->sendToToken($token, $title, $notification_message, $data);            
            return  $result;

        } catch (\Exception $e) {
            log_message('error', "Error sending chat notification: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'exception',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida si un usuario puede recibir notificaciones push
     * 
     * @param int $user_id ID del usuario
     * @return array Estado de la validación
     */
    public function validateUserForNotifications($user_id)
    {
        try {
            $token = $this->notificationTokenModel->getUserToken($user_id);
            
            return [
                'success' => true,
                'user_id' => $user_id,
                'has_token' => !empty($token),
                'can_receive_notifications' => !empty($token),
                'token' => $token
            ];
        } catch (\Exception $e) {
            log_message('error', "Error validating user {$user_id} for notifications: " . $e->getMessage());
            return [
                'success' => false,
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ];
        }
    }
}
