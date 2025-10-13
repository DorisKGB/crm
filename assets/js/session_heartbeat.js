/**
 * Sistema de Heartbeat para Renovación de Sesión
 * Mantiene la sesión activa y maneja reconexiones automáticas
 */

class SessionHeartbeat {
    constructor() {
        this.heartbeatInterval = null;
        this.heartbeatUrl = null;
        this.csrfToken = null;
        this.csrfHash = null;
        this.isOnline = true;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.heartbeatIntervalMs = 30000; // 30 segundos
        this.retryDelayMs = 5000; // 5 segundos
        
        this.init();
    }

    init() {
        // Obtener configuración desde el DOM
        this.heartbeatUrl = window.heartbeatConfig?.url || '/heartbeat';
        this.csrfToken = window.heartbeatConfig?.csrfName || 'csrf_test_name';
        this.csrfHash = window.heartbeatConfig?.csrfHash || '';
        
        // Detectar cambios de conectividad
        this.setupConnectivityDetection();
        
        // Iniciar heartbeat
        this.startHeartbeat();
        
        // Manejar visibilidad de la página
        this.setupVisibilityHandling();
        
        console.log('🔄 SessionHeartbeat inicializado');
    }

    setupConnectivityDetection() {
        // Detectar cuando se pierde la conexión
        window.addEventListener('online', () => {
            console.log('🌐 Conexión restaurada');
            this.isOnline = true;
            this.retryCount = 0;
            this.startHeartbeat();
        });

        window.addEventListener('offline', () => {
            console.log('🌐 Conexión perdida');
            this.isOnline = false;
            this.stopHeartbeat();
        });
    }

    setupVisibilityHandling() {
        // Pausar heartbeat cuando la pestaña no está visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('👁️ Pestaña oculta - pausando heartbeat');
                this.stopHeartbeat();
            } else {
                console.log('👁️ Pestaña visible - reanudando heartbeat');
                this.startHeartbeat();
            }
        });
    }

    startHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }

        if (!this.isOnline) {
            return;
        }

        // Ejecutar inmediatamente
        this.performHeartbeat();

        // Programar ejecución periódica
        this.heartbeatInterval = setInterval(() => {
            this.performHeartbeat();
        }, this.heartbeatIntervalMs);

        console.log('💓 Heartbeat iniciado');
    }

    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
            console.log('💓 Heartbeat detenido');
        }
    }

    async performHeartbeat() {
        try {
            const response = await fetch(this.heartbeatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    [this.csrfToken]: this.csrfHash,
                    timestamp: Date.now()
                }),
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar tokens CSRF si se proporcionan
                    if (data.csrf_hash) {
                        this.csrfHash = data.csrf_hash;
                        this.updateCsrfTokens(data.csrf_hash);
                    }
                    
                    this.retryCount = 0;
                    console.log('💓 Heartbeat exitoso');
                    
                    // Notificar a otros sistemas que la sesión está activa
                    this.notifySessionActive();
                } else {
                    throw new Error(data.message || 'Error en heartbeat');
                }
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.warn('💓 Error en heartbeat:', error.message);
            this.handleHeartbeatError();
        }
    }

    handleHeartbeatError() {
        this.retryCount++;
        
        if (this.retryCount >= this.maxRetries) {
            console.error('💓 Máximo de reintentos alcanzado - sesión expirada');
            this.handleSessionExpired();
        } else {
            console.log(`💓 Reintentando en ${this.retryDelayMs}ms (${this.retryCount}/${this.maxRetries})`);
            setTimeout(() => {
                this.performHeartbeat();
            }, this.retryDelayMs);
        }
    }

    handleSessionExpired() {
        this.stopHeartbeat();
        
        // Mostrar notificación al usuario
        this.showSessionExpiredNotification();
        
        // Intentar recargar la página para renovar la sesión
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }

    showSessionExpiredNotification() {
        // Crear notificación de sesión expirada
        const notification = document.createElement('div');
        notification.className = 'session-expired-notification';
        notification.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ef4444;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                max-width: 300px;
            ">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 18px;"></i>
                    <div>
                        <strong>Sesión Expirada</strong><br>
                        <small>Recargando la página...</small>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    updateCsrfTokens(newHash) {
        // Actualizar tokens CSRF en formularios existentes
        document.querySelectorAll(`input[name="${this.csrfToken}"]`).forEach(input => {
            input.value = newHash;
        });
        
        // Actualizar en sistemas de llamadas si existe
        if (window.CallsBubbleSystem && window.CallsBubbleSystem.config) {
            window.CallsBubbleSystem.config.csrf.hash = newHash;
        }
        
        // Actualizar en configuración global
        if (window.heartbeatConfig) {
            window.heartbeatConfig.csrfHash = newHash;
        }
        
        console.log('🔄 Tokens CSRF actualizados');
    }

    notifySessionActive() {
        // Disparar evento personalizado para que otros sistemas sepan que la sesión está activa
        window.dispatchEvent(new CustomEvent('sessionActive', {
            detail: { timestamp: Date.now() }
        }));
    }

    // Método público para verificar si la sesión está activa
    isSessionActive() {
        return this.isOnline && this.retryCount < this.maxRetries;
    }

    // Método público para forzar un heartbeat
    forceHeartbeat() {
        this.performHeartbeat();
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.sessionHeartbeat = new SessionHeartbeat();
});

// Exportar para uso global
window.SessionHeartbeat = SessionHeartbeat;
