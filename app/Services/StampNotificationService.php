<?php

namespace App\Services;

use App\Models\Stamp_model;
use App\Models\Users_model;

/**
 * Servicio para manejar notificaciones de timbres
 * Centraliza la lógica de negocio para las notificaciones
 */
class StampNotificationService
{
    protected $stampModel;
    protected $userModel;

    public function __construct()
    {
        $this->stampModel = new Stamp_model();
        $this->userModel = new Users_model();
    }

    /**
     * Verifica si hay timbres nuevos para notificaciones
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado de la verificación
     */
    public function checkNewStamps($user_id, $permission, $is_admin, $last_check)
    {
        try {
            // Validar permisos
            if (!$this->hasNotificationPermission($permission, $is_admin)) {
                return [
                    'success' => false,
                    'message' => 'Sin permisos para verificar timbres',
                    'hasNewStamps' => false
                ];
            }

            // Debug: Log del last_check recibido
            log_message('info', sprintf(
                'DEBUG STAMP - last_check recibido: "%s" (tipo: %s)',
                $last_check,
                gettype($last_check)
            ));

            // Convertir last_check a formato MySQL si es necesario
            $formatted_last_check = $this->formatLastCheck($last_check);
            log_message('info', sprintf(
                'DEBUG STAMP - last_check formateado: "%s"',
                $formatted_last_check
            ));

            // Obtener timbres según el tipo de usuario
            $stamps = $this->getStampsByUserType($user_id, $permission, $is_admin, $formatted_last_check);
            
            if (empty($stamps)) {
                return [
                    'success' => true,
                    'hasNewStamps' => false,
                    'message' => 'No hay timbres nuevos',
                    'debug' => [
                        'user_id' => $user_id,
                        'permission' => $permission,
                        'is_admin' => $is_admin,
                        'lastCheck' => $last_check,
                        'formattedLastCheck' => $formatted_last_check,
                        'serverTime' => date('Y-m-d H:i:s'),
                        'timezone' => app_timezone()
                    ]
                ];
            }

            // Obtener el timbre más reciente para la notificación
            $latestStamp = $stamps[0];
            
            return [
                'success' => true,
                'hasNewStamps' => true,
                'stampData' => [
                    'stampId' => $latestStamp->id,
                    'clinicName' => $latestStamp->clinic_name ?? 'Clínica',
                    'createdAt' => $latestStamp->created_at,
                    'state' => $latestStamp->state ?? 'pending'
                ],
                'count' => count($stamps),
                'debug' => [
                    'user_id' => $user_id,
                    'permission' => $permission,
                    'is_admin' => $is_admin,
                    'lastCheck' => $last_check,
                    'formattedLastCheck' => $formatted_last_check,
                    'serverTime' => date('Y-m-d H:i:s'),
                    'timezone' => app_timezone(),
                    'foundStamps' => count($stamps),
                    'latestStampId' => $latestStamp->id,
                    'latestStampCreated' => $latestStamp->created_at
                ]
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en StampNotificationService: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'hasNewStamps' => false
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
               $permission === "request" || 
               $permission === "all" || 
               $is_admin === true;
    }

    /**
     * Obtiene timbres según el tipo de usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con timbres encontrados
     */
    private function getStampsByUserType($user_id, $permission, $is_admin, $last_check)
    {
        switch ($permission) {
            case 'provider':
                return $this->stampModel->get_pending_stamps_for_provider($user_id, $last_check);
            
            case 'all':
                return $this->stampModel->get_pending_stamps_for_all_permission($last_check);
            
            case 'request':
                return $this->stampModel->get_pending_stamps_for_request_permission($user_id, $last_check);
            
            default:
                if ($is_admin) {
                    return $this->stampModel->get_pending_stamps_for_admin($last_check);
                }
                
                // Fallback: usar el método general
                return $this->stampModel->get_new_stamps_for_notifications($user_id, $permission, $is_admin, $last_check);
        }
    }

    /**
     * Formatea el last_check para uso en consultas MySQL
     * Maneja automáticamente las diferencias de zona horaria entre cliente y servidor
     * 
     * @param string $last_check Timestamp de la última verificación
     * @return string Timestamp formateado
     */
    private function formatLastCheck($last_check)
    {
        try {
            // Si ya es un timestamp MySQL, devolverlo tal como está
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $last_check)) {
                return $last_check;
            }
            
            // Si es ISO 8601 (formato del cliente), convertir considerando zona horaria
            if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $last_check)) {
                // Crear DateTime en UTC (como viene del cliente)
                $clientDate = new \DateTime($last_check, new \DateTimeZone('UTC'));
                
                // Convertir a la zona horaria del servidor
                $serverTimezone = new \DateTimeZone(app_timezone());
                $clientDate->setTimezone($serverTimezone);
                
                // Restar un pequeño margen para asegurar que no perdamos timbres por diferencias de milisegundos
                $clientDate->modify('-30 seconds');
                
                $formatted = $clientDate->format('Y-m-d H:i:s');
                
                log_message('info', sprintf(
                    'STAMP TIMEZONE: Cliente UTC "%s" -> Servidor "%s" (zona: %s)',
                    $last_check,
                    $formatted,
                    app_timezone()
                ));
                
                return $formatted;
            }
            
            // Si es un timestamp Unix, convertir
            if (is_numeric($last_check)) {
                return date('Y-m-d H:i:s', $last_check);
            }
            
            // Fallback: usar el valor tal como está
            return $last_check;
            
        } catch (\Exception $e) {
            log_message('error', 'Error formateando last_check: ' . $e->getMessage());
            // Fallback: usar hace 5 minutos
            return date('Y-m-d H:i:s', strtotime('-5 minutes'));
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
        try {
            $last_24h = date('Y-m-d H:i:s', strtotime('-24 hours'));
            $stamps = $this->getStampsByUserType($user_id, $permission, $is_admin, $last_24h);
            
            return [
                'totalStamps24h' => count($stamps),
                'lastCheck' => date('Y-m-d H:i:s'),
                'userPermission' => $permission,
                'isAdmin' => $is_admin
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo estadísticas de timbres: ' . $e->getMessage());
            return [
                'totalStamps24h' => 0,
                'lastCheck' => date('Y-m-d H:i:s'),
                'userPermission' => $permission,
                'isAdmin' => $is_admin,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida la configuración de notificaciones
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return array Configuración validada
     */
    public function validateNotificationConfig($user_id, $permission, $is_admin)
    {
        return [
            'userId' => $user_id,
            'permission' => $permission,
            'isAdmin' => $is_admin,
            'hasPermission' => $this->hasNotificationPermission($permission, $is_admin),
            'serverTime' => date('Y-m-d H:i:s'),
            'timezone' => app_timezone()
        ];
    }
}
