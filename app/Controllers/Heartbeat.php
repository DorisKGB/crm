<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Heartbeat extends Security_Controller
{
    protected $Sessions_model;

    function __construct()
    {
        parent::__construct();
        $this->Sessions_model = model("App\Models\Sessions_model");
    }
    /**
     * Endpoint para mantener la sesión activa
     */
    public function index()
    {
        try {
            // Verificar que el usuario esté logueado
            if (!$this->login_user || !$this->login_user->id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                    'requires_login' => true
                ]);
            }

            // Actualizar timestamp de última actividad usando el modelo
            $this->Users_model->update_last_activity($this->login_user->id);
            
            // Actualizar timestamp de la sesión
            $this->updateSessionTimestamp();

            // Regenerar token CSRF para mayor seguridad
            $new_csrf_hash = csrf_hash();

            // Respuesta exitosa
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sesión activa',
                'timestamp' => time(),
                'user_id' => $this->login_user->id,
                'csrf_hash' => $new_csrf_hash,
                'session_lifetime' => $this->getSessionLifetime()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en heartbeat: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor',
                'requires_login' => false
            ]);
        }
    }

    /**
     * Verificar estado de la sesión sin actualizarla
     */
    public function check()
    {
        try {
            if (!$this->login_user || !$this->login_user->id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Sesión expirada',
                    'requires_login' => true
                ]);
            }

            // Verificar si la sesión está activa usando el modelo
            $session_id = session_id();
            $is_session_active = $this->Sessions_model->is_session_active($session_id);
            
            if (!$is_session_active) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Sesión expirada',
                    'requires_login' => true
                ]);
            }

            // Verificar si el usuario está online
            $is_user_online = $this->Users_model->is_user_online($this->login_user->id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sesión válida',
                'timestamp' => time(),
                'user_id' => $this->login_user->id,
                'is_online' => $is_user_online,
                'session_lifetime' => $this->getSessionLifetime()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error verificando sesión: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error verificando sesión',
                'requires_login' => true
            ]);
        }
    }

    /**
     * Actualizar timestamp de la sesión usando el modelo
     */
    private function updateSessionTimestamp()
    {
        try {
            $session_id = session_id();
            if ($session_id) {
                $this->Sessions_model->update_session_timestamp($session_id);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error actualizando timestamp de sesión: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tiempo de vida restante de la sesión
     */
    private function getSessionLifetime()
    {
        $config = new \Config\Session();
        $expiration = $config->expiration;
        
        if ($expiration > 0) {
            $session_start = $_SESSION['__ci_last_regenerate'] ?? time();
            $elapsed = time() - $session_start;
            return max(0, $expiration - $elapsed);
        }
        
        return 0; // Sesión no expira
    }

    /**
     * Endpoint para verificar conectividad de servicios externos
     */
    public function check_services()
    {
        try {
            $services = [
                'database' => $this->checkDatabase(),
                'vsee_api' => $this->checkVseeApi(),
                'pusher' => $this->checkPusher()
            ];

            $all_healthy = !in_array(false, $services);

            return $this->response->setJSON([
                'success' => $all_healthy,
                'message' => $all_healthy ? 'Todos los servicios operativos' : 'Algunos servicios no disponibles',
                'services' => $services,
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error verificando servicios',
                'services' => [],
                'timestamp' => time()
            ]);
        }
    }

    /**
     * Verificar conectividad de la base de datos
     */
    private function checkDatabase()
    {
        try {
            // Usar el modelo Users para verificar conectividad
            $test_user = $this->Users_model->get_one(1);
            return $test_user !== false;
        } catch (\Exception $e) {
            log_message('error', 'Error verificando base de datos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar conectividad de la API de VSee
     */
    private function checkVseeApi()
    {
        try {
            // Verificar si las credenciales VSee están configuradas
            $vsee_username = get_setting('vsee_username');
            $vsee_password = get_setting('vsee_password');
            
            return !empty($vsee_username) && !empty($vsee_password);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar conectividad de Pusher
     */
    private function checkPusher()
    {
        try {
            $pusher_app_id = get_setting('pusher_app_id');
            $pusher_key = get_setting('pusher_key');
            $pusher_secret = get_setting('pusher_secret');
            
            return !empty($pusher_app_id) && !empty($pusher_key) && !empty($pusher_secret);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Limpiar sesiones expiradas (endpoint de mantenimiento)
     */
    public function cleanup()
    {
        try {
            // Solo permitir a administradores
            if (!$this->login_user || $this->login_user->is_admin != 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Acceso denegado'
                ]);
            }

            $cleaned = $this->Sessions_model->clean_expired_sessions();
            
            return $this->response->setJSON([
                'success' => $cleaned,
                'message' => $cleaned ? 'Sesiones expiradas limpiadas' : 'Error limpiando sesiones',
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error limpiando sesiones: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error limpiando sesiones'
            ]);
        }
    }
}
