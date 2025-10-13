<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\SessionExtended;

/**
 * Middleware para renovación automática de sesiones
 * Previene problemas de timeout después de inactividad
 */
class SessionRenewal implements FilterInterface
{
    protected $sessionConfig;
    protected $extendedConfig;

    public function __construct()
    {
        $this->sessionConfig = new \Config\Session();
        $this->extendedConfig = new SessionExtended();
    }

    /**
     * Ejecutar antes de la petición
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Solo procesar para peticiones AJAX y API
        if (!$this->isAjaxRequest($request) && !$this->isApiRequest($request)) {
            return;
        }

        // Verificar si la sesión está próxima a expirar
        if ($this->isSessionNearExpiry()) {
            $this->renewSession();
        }

        // Verificar conectividad de servicios críticos
        if ($this->extendedConfig->checkConnectivity) {
            $this->checkCriticalServices();
        }
    }

    /**
     * Ejecutar después de la respuesta
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Agregar headers de sesión a la respuesta
        $this->addSessionHeaders($response);
        
        // Log de actividad si está habilitado
        if ($this->extendedConfig->enableLogging) {
            $this->logActivity($request);
        }
    }

    /**
     * Verificar si es una petición AJAX
     */
    private function isAjaxRequest(RequestInterface $request): bool
    {
        return $request->getHeader('X-Requested-With') && 
               $request->getHeader('X-Requested-With')->getValue() === 'XMLHttpRequest';
    }

    /**
     * Verificar si es una petición API
     */
    private function isApiRequest(RequestInterface $request): bool
    {
        $uri = $request->getUri();
        return strpos($uri->getPath(), '/api/') === 0 || 
               strpos($uri->getPath(), '/heartbeat') === 0 ||
               strpos($uri->getPath(), '/calls_system') === 0 ||
               strpos($uri->getPath(), '/chat') === 0;
    }

    /**
     * Verificar si la sesión está próxima a expirar
     */
    private function isSessionNearExpiry(): bool
    {
        if (!session_id()) {
            return false;
        }

        $lastActivity = $_SESSION['__ci_last_regenerate'] ?? time();
        $elapsed = time() - $lastActivity;
        $remaining = $this->sessionConfig->expiration - $elapsed;

        return $remaining <= $this->extendedConfig->gracePeriod;
    }

    /**
     * Renovar la sesión
     */
    private function renewSession(): void
    {
        try {
            // Regenerar ID de sesión
            session_regenerate_id(true);
            
            // Actualizar timestamp de última actividad
            $_SESSION['__ci_last_regenerate'] = time();
            
            // Actualizar en base de datos si es necesario
            $this->updateSessionInDatabase();
            
            if ($this->extendedConfig->enableLogging) {
                log_message('info', 'Sesión renovada automáticamente para usuario: ' . (session('user_id') ?? 'unknown'));
            }
        } catch (\Exception $e) {
            if ($this->extendedConfig->enableLogging) {
                log_message('error', 'Error renovando sesión: ' . $e->getMessage());
            }
        }
    }

    /**
     * Actualizar sesión en base de datos
     */
    private function updateSessionInDatabase(): void
    {
        try {
            $db = \Config\Database::connect();
            $sessionId = session_id();
            $timestamp = time();
            
            $db->table('ci_sessions')
               ->where('id', $sessionId)
               ->update(['timestamp' => $timestamp]);
               
        } catch (\Exception $e) {
            if ($this->extendedConfig->enableLogging) {
                log_message('error', 'Error actualizando sesión en BD: ' . $e->getMessage());
            }
        }
    }

    /**
     * Verificar servicios críticos
     */
    private function checkCriticalServices(): void
    {
        $services = $this->extendedConfig->externalServices;
        
        foreach ($services as $service => $config) {
            if (!$this->isServiceHealthy($service, $config)) {
                $this->handleServiceFailure($service);
            }
        }
    }

    /**
     * Verificar si un servicio está saludable
     */
    private function isServiceHealthy(string $service, array $config): bool
    {
        try {
            switch ($service) {
                case 'database':
                    $db = \Config\Database::connect();
                    $db->query('SELECT 1');
                    return true;
                    
                case 'vsee':
                    // Verificar configuración VSee
                    return !empty(get_setting('vsee_username')) && 
                           !empty(get_setting('vsee_password'));
                           
                case 'pusher':
                    // Verificar configuración Pusher
                    return !empty(get_setting('pusher_app_id')) && 
                           !empty(get_setting('pusher_key'));
                           
                default:
                    return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Manejar fallo de servicio
     */
    private function handleServiceFailure(string $service): void
    {
        if ($this->extendedConfig->enableLogging) {
            log_message('warning', "Servicio crítico no disponible: {$service}");
        }
        
        // Aquí podrías implementar lógica adicional como:
        // - Notificar al administrador
        // - Activar modo degradado
        // - Reintentar después de un tiempo
    }

    /**
     * Agregar headers de sesión a la respuesta
     */
    private function addSessionHeaders(ResponseInterface $response): void
    {
        $response->setHeader('X-Session-Status', 'active');
        $response->setHeader('X-Session-Timeout', (string)$this->sessionConfig->expiration);
        $response->setHeader('X-Heartbeat-Interval', (string)$this->extendedConfig->heartbeatInterval);
    }

    /**
     * Registrar actividad del usuario
     */
    private function logActivity(RequestInterface $request): void
    {
        try {
            $userId = session('user_id');
            if (!$userId) return;

            $db = \Config\Database::connect();
            $db->table('users')
               ->where('id', $userId)
               ->update(['last_online' => date('Y-m-d H:i:s')]);
               
        } catch (\Exception $e) {
            // Silently fail - no queremos que el logging rompa la aplicación
        }
    }
}
