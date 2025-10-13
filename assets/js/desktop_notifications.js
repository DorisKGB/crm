/**
 * Sistema de Notificaciones de Escritorio
 * Maneja las notificaciones para la aplicación de escritorio de manera robusta
 */

window.DesktopNotifications = {
    // Estado de inicialización
    initialized: false,
    
    // Configuración
    config: {
        desktopAppUrl: 'http://127.0.0.1:8080',
        retryAttempts: 3,
        retryDelay: 1000,
        timeout: 5000,
        isDesktopAppAvailable: false,
        lastCheckTime: null
    },

    // Inicialización
    init: function() {
        if (this.initialized) {
            return;
        }

        // Cargar configuración desde el DOM
        this.loadConfigFromDOM();
        
        // Verificar disponibilidad de la aplicación de escritorio
        this.checkDesktopAppAvailability();
        
        // Configurar event listeners
        this.setupEventListeners();
        
        this.initialized = true;
        console.log('✅ DesktopNotifications inicializado');
    },

    // Cargar configuración desde el DOM
    loadConfigFromDOM: function() {
        // Usar configuración global si está disponible
        if (window.DesktopNotificationsConfig) {
            this.config.currentUserId = window.DesktopNotificationsConfig.userId;
            this.config.baseUrl = window.DesktopNotificationsConfig.baseUrl;
            this.config.csrfToken = window.DesktopNotificationsConfig.csrfToken;
            this.config.csrfHash = window.DesktopNotificationsConfig.csrfHash;
        } else {
            // Valores por defecto si no hay configuración
            this.config.currentUserId = window.loginUserId || null;
            this.config.baseUrl = window.baseUrl || '';
            this.config.csrfToken = window.csrfToken || 'csrf_test_name';
            this.config.csrfHash = window.csrfHash || '';
        }
    },

    // Configurar event listeners
    setupEventListeners: function() {
        var self = this;
        
        // Escuchar eventos de mensajes nuevos
        document.addEventListener('newMessageSent', function(event) {
            if (event.detail) {
                self.sendDesktopNotification(event.detail);
            }
        });
        
        // Escuchar eventos de respuestas de mensajes
        document.addEventListener('newMessageReply', function(event) {
            if (event.detail) {
                self.sendDesktopNotification(event.detail);
            }
        });
    },

    // Verificar si la aplicación de escritorio está disponible
    checkDesktopAppAvailability: function() {
        var self = this;
        
        // Hacer una petición de prueba a la aplicación de escritorio
        fetch(this.config.desktopAppUrl + '/ping', {
            method: 'GET',
            timeout: 2000
        })
        .then(function(response) {
            if (response.ok) {
                self.config.isDesktopAppAvailable = true;
                console.log('✅ Aplicación de escritorio disponible');
            } else {
                self.config.isDesktopAppAvailable = false;
                console.log('⚠️ Aplicación de escritorio no disponible');
            }
        })
        .catch(function(error) {
            self.config.isDesktopAppAvailable = false;
            console.log('⚠️ Aplicación de escritorio no disponible:', error.message);
        });
    },

    // Enviar notificación a la aplicación de escritorio
    sendDesktopNotification: function(messageData) {
        if (!this.config.isDesktopAppAvailable) {
            console.log('⚠️ Aplicación de escritorio no disponible, saltando notificación');
            return;
        }

        var self = this;
        var attempts = 0;
        
        function attemptSend() {
            attempts++;
            
            var notificationData = {
                sender_name: messageData.sender_name || 'Usuario',
                message_content: messageData.message_content || '',
                message_id: messageData.message_id || 0,
                sender_image: messageData.sender_image || '',
                timestamp: new Date().toISOString(),
                type: messageData.type || 'message'
            };

            fetch(self.config.desktopAppUrl + '/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(notificationData),
                timeout: self.config.timeout
            })
            .then(function(response) {
                if (response.ok) {
                    console.log('✅ Notificación de escritorio enviada:', notificationData.sender_name);
                    self.config.lastCheckTime = new Date().toISOString();
                } else {
                    throw new Error('Error en respuesta del servidor: ' + response.status);
                }
            })
            .catch(function(error) {
                console.log('❌ Error enviando notificación de escritorio (intento ' + attempts + '):', error.message);
                
                if (attempts < self.config.retryAttempts) {
                    setTimeout(function() {
                        attemptSend();
                    }, self.config.retryDelay * attempts);
                } else {
                    console.log('❌ Falló después de ' + self.config.retryAttempts + ' intentos');
                    // Marcar como no disponible temporalmente
                    self.config.isDesktopAppAvailable = false;
                    // Reintentar verificación en 30 segundos
                    setTimeout(function() {
                        self.checkDesktopAppAvailability();
                    }, 30000);
                }
            });
        }
        
        attemptSend();
    },

    // Método público para enviar notificación manualmente
    notify: function(senderName, messageContent, messageId, senderImage, type) {
        var messageData = {
            sender_name: senderName,
            message_content: messageContent,
            message_id: messageId,
            sender_image: senderImage || '',
            type: type || 'message'
        };
        
        this.sendDesktopNotification(messageData);
    },

    // Verificar estado de la aplicación de escritorio
    isAvailable: function() {
        return this.config.isDesktopAppAvailable;
    },

    // Forzar verificación de disponibilidad
    checkAvailability: function() {
        this.checkDesktopAppAvailability();
    }
};

// Inicialización automática
document.addEventListener('DOMContentLoaded', function() {
    window.DesktopNotifications.init();
});

// Función global para uso desde otros scripts
window.sendDesktopNotification = function(senderName, messageContent, messageId, senderImage, type) {
    return window.DesktopNotifications.notify(senderName, messageContent, messageId, senderImage, type);
};
