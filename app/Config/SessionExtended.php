<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuración extendida de sesiones para manejo de timeouts
 * y problemas de conexión después de inactividad
 */
class SessionExtended extends BaseConfig
{
    /**
     * Timeout de sesión en segundos (4 horas)
     */
    public int $sessionTimeout = 14400;
    
    /**
     * Intervalo de heartbeat en segundos (30 segundos)
     */
    public int $heartbeatInterval = 30;
    
    /**
     * Tiempo de gracia antes de expirar la sesión (5 minutos)
     */
    public int $gracePeriod = 300;
    
    /**
     * Habilitar renovación automática de sesión
     */
    public bool $autoRenewal = true;
    
    /**
     * Verificar conectividad antes de operaciones críticas
     */
    public bool $checkConnectivity = true;
    
    /**
     * Timeout para operaciones de base de datos (30 segundos)
     */
    public int $dbTimeout = 30;
    
    /**
     * Timeout para operaciones de red (10 segundos)
     */
    public int $networkTimeout = 10;
    
    /**
     * Máximo número de reintentos para operaciones fallidas
     */
    public int $maxRetries = 3;
    
    /**
     * Delay entre reintentos en segundos
     */
    public int $retryDelay = 2;
    
    /**
     * Habilitar logging de problemas de sesión
     */
    public bool $enableLogging = true;
    
    /**
     * Nivel de log para problemas de sesión
     */
    public string $logLevel = 'warning';
    
    /**
     * Configuración de notificaciones al usuario
     */
    public array $notifications = [
        'sessionExpiring' => [
            'enabled' => true,
            'warningTime' => 300, // 5 minutos antes de expirar
            'message' => 'Tu sesión expirará pronto. ¿Deseas continuar?'
        ],
        'sessionExpired' => [
            'enabled' => true,
            'message' => 'Tu sesión ha expirado. Recargando la página...'
        ],
        'connectionLost' => [
            'enabled' => true,
            'message' => 'Se perdió la conexión. Reintentando...'
        ]
    ];
    
    /**
     * Configuración de servicios externos
     */
    public array $externalServices = [
        'vsee' => [
            'timeout' => 30,
            'retries' => 3,
            'checkInterval' => 60
        ],
        'pusher' => [
            'timeout' => 10,
            'retries' => 2,
            'checkInterval' => 30
        ],
        'database' => [
            'timeout' => 30,
            'retries' => 3,
            'checkInterval' => 60
        ]
    ];
    
    /**
     * Configuración de polling para sistemas en tiempo real
     */
    public array $polling = [
        'chat' => [
            'interval' => 3000, // 3 segundos
            'maxFailures' => 5,
            'backoffMultiplier' => 1.5
        ],
        'calls' => [
            'interval' => 2000, // 2 segundos
            'maxFailures' => 3,
            'backoffMultiplier' => 2.0
        ],
        'notifications' => [
            'interval' => 60000, // 1 minuto
            'maxFailures' => 2,
            'backoffMultiplier' => 1.2
        ]
    ];
    
    /**
     * Configuración de limpieza automática
     */
    public array $cleanup = [
        'enabled' => true,
        'interval' => 3600, // 1 hora
        'maxAge' => 86400, // 24 horas
        'cleanupSessions' => true,
        'cleanupLogs' => true,
        'cleanupTempFiles' => true
    ];
}
