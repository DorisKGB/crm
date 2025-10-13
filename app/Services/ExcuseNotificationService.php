<?php

namespace App\Services;

use App\Models\Excuse_model;
use App\Models\Users_model;

/**
 * Servicio para manejar notificaciones de excusas médicas
 * Centraliza la lógica de negocio para las notificaciones
 */
class ExcuseNotificationService
{
    protected $excuseModel;
    protected $userModel;

    public function __construct()
    {
        $this->excuseModel = new Excuse_model();
        $this->userModel = new Users_model();
    }

    /**
     * Verifica si hay excusas nuevas para notificaciones
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado de la verificación
     */
    public function checkNewExcuses($user_id, $permission, $is_admin, $last_check)
    {
        try {
            // Validar permisos
            if (!$this->hasNotificationPermission($permission, $is_admin)) {
                return [
                    'success' => false,
                    'message' => 'Sin permisos para verificar excusas',
                    'hasNewExcuses' => false
                ];
            }

            // Debug: Log del last_check recibido
            log_message('info', sprintf(
                'DEBUG - last_check recibido: "%s" (tipo: %s)',
                $last_check,
                gettype($last_check)
            ));

            // Convertir last_check a formato MySQL si es necesario
            $formatted_last_check = $this->formatLastCheck($last_check);
            log_message('info', sprintf(
                'DEBUG - last_check formateado: "%s"',
                $formatted_last_check
            ));

            // Obtener excusas según el tipo de usuario
            $result = $this->getExcusesByUserType($user_id, $permission, $is_admin, $formatted_last_check);
            
            // Formatear respuesta
            $response = [
                'success' => true,
                'hasNewExcuses' => $result['has_new'],
                'count' => $result['count'],
                'excuseData' => null,
                'debug' => [
                    'last_check_original' => $last_check,
                    'last_check_formatted' => $formatted_last_check,
                    'user_id' => $user_id,
                    'permission' => $permission,
                    'is_admin' => $is_admin
                ]
            ];

            // Si hay excusas, formatear los datos
            if ($result['has_new'] && !empty($result['excuses'])) {
                $response['excuseData'] = $this->excuseModel->format_excuse_for_notification($result['excuses'][0]);
            }

            // Log para debugging
            log_message('info', sprintf(
                'Verificando excusas para usuario %d con permiso %s. Encontradas: %d. Last check: %s',
                $user_id,
                $permission,
                $result['count'],
                $formatted_last_check
            ));

            return $response;

        } catch (\Exception $e) {
            log_message('error', 'Error en ExcuseNotificationService: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage(),
                'hasNewExcuses' => false
            ];
        }
    }

    /**
     * Verifica si el usuario tiene permisos para recibir notificaciones
     * 
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return bool
     */
    private function hasNotificationPermission($permission, $is_admin)
    {
        return $permission === "provider" || 
               $permission === "all" || 
               $is_admin === true;
    }

    /**
     * Obtiene excusas según el tipo de usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    private function getExcusesByUserType($user_id, $permission, $is_admin, $last_check)
    {
        switch ($permission) {
            case 'provider':
                return $this->excuseModel->get_pending_excuses_for_provider($user_id, $last_check);
            
            case 'all':
                return $this->excuseModel->get_pending_excuses_for_all_permission($last_check);
            
            case 'request':
                return $this->excuseModel->get_pending_excuses_for_request_permission($user_id, $last_check);
            
            default:
                if ($is_admin) {
                    return $this->excuseModel->get_pending_excuses_for_admin($last_check);
                }
                
                // Fallback: usar el método general
                return $this->excuseModel->get_new_excuses_for_notifications($user_id, $permission, $is_admin, $last_check);
        }
    }

    /**
     * Obtiene estadísticas de notificaciones para un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return array Estadísticas
     */
    public function getNotificationStats($user_id, $permission, $is_admin)
    {
        $last_hour = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $last_day = date('Y-m-d H:i:s', strtotime('-1 day'));
        
        $hourly = $this->getExcusesByUserType($user_id, $permission, $is_admin, $last_hour);
        $daily = $this->getExcusesByUserType($user_id, $permission, $is_admin, $last_day);
        
        return [
            'last_hour' => $hourly['count'],
            'last_day' => $daily['count'],
            'has_permission' => $this->hasNotificationPermission($permission, $is_admin)
        ];
    }

    /**
     * Valida la configuración de notificaciones para un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return array Estado de la configuración
     */
    public function validateNotificationConfig($user_id, $permission, $is_admin)
    {
        $has_permission = $this->hasNotificationPermission($permission, $is_admin);
        
        return [
            'user_id' => $user_id,
            'permission' => $permission,
            'is_admin' => $is_admin,
            'has_permission' => $has_permission,
            'can_receive_notifications' => $has_permission,
            'notification_types' => $this->getAvailableNotificationTypes($permission, $is_admin)
        ];
    }

    /**
     * Obtiene los tipos de notificaciones disponibles para un usuario
     * 
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return array Tipos de notificaciones
     */
    private function getAvailableNotificationTypes($permission, $is_admin)
    {
        $types = [];
        
        if ($permission === 'provider' || $is_admin) {
            $types[] = 'new_excuse_created';
            $types[] = 'excuse_pending_approval';
        }
        
        if ($permission === 'all' || $is_admin) {
            $types[] = 'all_excuse_notifications';
        }
        
        if ($permission === 'request') {
            $types[] = 'my_excuse_status_changed';
        }
        
        return $types;
    }

    /**
     * Formatea el last_check para que sea compatible con MySQL
     * 
     * @param string $last_check Timestamp en cualquier formato
     * @return string Timestamp formateado para MySQL
     */
    private function formatLastCheck($last_check)
    {
        // Cargar helper de fecha y hora
        helper('date');
        
        // Si está vacío o es null, usar hace 1 hora
        if (empty($last_check)) {
            return date('Y-m-d H:i:s', strtotime('-1 hour'));
        }

        // Si ya es un timestamp de MySQL, devolverlo tal como está
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $last_check)) {
            return $last_check;
        }

        // Si es un timestamp ISO (con T y Z), convertirlo correctamente
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $last_check)) {
            try {
                // Crear un objeto DateTime para manejar correctamente las zonas horarias
                $date = new \DateTime($last_check);
                
                // Si la base de datos guarda en hora local del servidor,
                // necesitamos convertir el UTC a la zona horaria del servidor
                $serverTimezone = date_default_timezone_get();
                $date->setTimezone(new \DateTimeZone($serverTimezone));
                
                $formatted = $date->format('Y-m-d H:i:s');
                
                // Log para debugging
                log_message('info', sprintf(
                    'DEBUG - Conversión de zona horaria: %s -> %s (zona: %s)',
                    $last_check,
                    $formatted,
                    $serverTimezone
                ));
                
                return $formatted;
            } catch (\Exception $e) {
                log_message('warning', 'Error parseando timestamp ISO: ' . $e->getMessage());
                // Fallback: usar strtotime
                $timestamp = strtotime($last_check);
                if ($timestamp !== false) {
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        // Si es un timestamp Unix, convertirlo
        if (is_numeric($last_check)) {
            return date('Y-m-d H:i:s', $last_check);
        }

        // Intentar parsear como fecha en cualquier formato usando strtotime
        $timestamp = strtotime($last_check);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        // Si todo falla, usar hace 1 hora
        log_message('warning', 'No se pudo parsear last_check: ' . $last_check . '. Usando hace 1 hora.');
        return date('Y-m-d H:i:s', strtotime('-1 hour'));
    }
}

