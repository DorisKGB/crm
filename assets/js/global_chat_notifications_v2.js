/**
 * Sistema Global de Notificaciones de Chat - Versión 2
 * Maneja las notificaciones de sonido para mensajes cuando el chat no está abierto
 * Versión mejorada con mejor debugging y manejo de errores
 */

window.GlobalChatNotificationsV2 = {
    // Estado de inicialización
    initialized: false,
    
    // Configuración
    config: {
        checkInterval: 5000, // Verificar mensajes cada 5 segundos
        soundEnabled: true,
        lastCheckTime: null,
        lastNotificationTime: null,
        isChatOpen: false,
        currentUserId: null,
        apiEndpoint: null,
        csrfToken: null,
        csrfHash: null,
        baseUrl: null,
        debugMode: true // Habilitar logs detallados
    },

    // Inicialización
    init: function() {
        if (this.initialized) {
            this.log('⚠️ Sistema ya inicializado');
            return;
        }
        
        this.log('🚀 Inicializando sistema de notificaciones de chat V2...');
        
        // Inicializar configuración de sonido
        this.config.soundEnabled = localStorage.getItem('globalChatSoundEnabled') !== 'false';
        this.log('🔊 Sonido habilitado:', this.config.soundEnabled);

        // Obtener configuración desde el DOM
        this.loadConfigFromDOM();
        
        // Configurar event listeners
        this.setupEventListeners();
        
        // Iniciar verificación de mensajes
        setTimeout(() => {
            this.log('⏰ Iniciando verificación de mensajes...');
            this.startMessageChecking();
        }, 2000);
        
        // Detectar si el chat está abierto
        this.detectChatState();
        
        this.initialized = true;
        this.log('✅ Sistema de notificaciones V2 inicializado correctamente');
    },

    // Función de logging
    log: function(message, data) {
        if (this.config.debugMode) {
            if (data !== undefined) {
                console.log(message, data);
            } else {
                console.log(message);
            }
        }
    },

    // Cargar configuración desde el DOM
    loadConfigFromDOM: function() {
        this.log('🔧 Cargando configuración desde el DOM...');
        
        // Buscar configuración en el DOM
        var configScript = document.querySelector('script[data-chat-config]');
        if (configScript) {
            try {
                var config = JSON.parse(configScript.textContent);
                this.config.currentUserId = config.userId;
                this.config.apiEndpoint = config.apiEndpoint;
                this.config.csrfToken = config.csrfToken;
                this.config.csrfHash = config.csrfHash;
                this.config.baseUrl = config.baseUrl;
                this.log('✅ Configuración cargada desde DOM:', config);
            } catch (e) {
                this.log('❌ Error parseando configuración del DOM:', e);
            }
        } else {
            this.log('⚠️ No se encontró script de configuración en el DOM');
        }
        
        // Valores por defecto si no hay configuración
        if (!this.config.currentUserId) {
            this.config.currentUserId = window.loginUserId || null;
        }
        if (!this.config.apiEndpoint) {
            this.config.apiEndpoint = (window.baseUrl || '') + 'messages/check_new_messages';
        }
        if (!this.config.csrfToken) {
            this.config.csrfToken = window.csrfToken || 'csrf_test_name';
        }
        if (!this.config.csrfHash) {
            this.config.csrfHash = window.csrfHash || '';
        }
        if (!this.config.baseUrl) {
            this.config.baseUrl = window.baseUrl || '';
        }
        
        this.log('🔧 Configuración final:', {
            userId: this.config.currentUserId,
            apiEndpoint: this.config.apiEndpoint,
            csrfToken: this.config.csrfToken,
            baseUrl: this.config.baseUrl
        });
    },

    // Configurar event listeners
    setupEventListeners: function() {
        var self = this;
        
        // Detectar cuando la ventana gana/pierde foco
        window.addEventListener('focus', function() {
            self.config.isChatOpen = self.isChatCurrentlyOpen();
            self.log('🔄 Ventana enfocada, chat abierto:', self.config.isChatOpen);
        });
        
        window.addEventListener('blur', function() {
            self.config.isChatOpen = self.isChatCurrentlyOpen();
            self.log('🔄 Ventana desenfocada, chat abierto:', self.config.isChatOpen);
        });
        
        // Detectar cambios en el DOM para saber si el chat se abrió/cerró
        var observer = new MutationObserver(function(mutations) {
            var wasOpen = self.config.isChatOpen;
            self.config.isChatOpen = self.isChatCurrentlyOpen();
            if (wasOpen !== self.config.isChatOpen) {
                self.log('🔄 Estado del chat cambió:', self.config.isChatOpen);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Escuchar eventos de mensajes desde el chat activo
        document.addEventListener('newChatMessage', function(event) {
            if (event.detail && event.detail.isOtherUser) {
                self.log('📨 Evento de mensaje nuevo recibido');
                self.playNotificationSound();
            }
        });
    },

    // Detectar si el chat está actualmente abierto
    isChatCurrentlyOpen: function() {
        var chatElements = [
            '#js-chat-messages-container',
            '.rise-chat-wrapper',
            '.modal[data-bs-target*="chat"]',
            '.chat-panel',
            '.active-chat',
            '.chat-window'
        ];
        
        for (var i = 0; i < chatElements.length; i++) {
            var element = document.querySelector(chatElements[i]);
            if (element) {
                var rect = element.getBoundingClientRect();
                var style = window.getComputedStyle(element);
                var isVisible = rect.width > 0 && rect.height > 0 && 
                               style.display !== 'none' && 
                               style.visibility !== 'hidden';
                if (isVisible) {
                    return true;
                }
            }
        }
        
        return false;
    },

    // Detectar estado del chat
    detectChatState: function() {
        this.config.isChatOpen = this.isChatCurrentlyOpen();
        this.log('🔍 Estado del chat detectado:', this.config.isChatOpen);
    },

    // Iniciar verificación de mensajes
    startMessageChecking: function() {
        var self = this;
        
        // Verificar mensajes cada X segundos
        setInterval(function() {
            self.detectChatState();
            
            if (self.config.soundEnabled) {
                self.checkForNewMessages();
            } else {
                self.log('⏭️ Saltando verificación - sonido deshabilitado');
            }
        }, this.config.checkInterval);
        
        // Verificar al cambiar de pestaña
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && self.config.soundEnabled) {
                self.log('🔄 Cambio de pestaña detectado');
                self.detectChatState();
                self.checkForNewMessages();
            }
        });
        
        // Verificar al enfocar la ventana
        window.addEventListener('focus', function() {
            if (self.config.soundEnabled) {
                self.log('🔄 Ventana enfocada');
                self.detectChatState();
                self.checkForNewMessages();
            }
        });
    },

    // Verificar si hay mensajes nuevos
    checkForNewMessages: function() {
        if (!this.config.currentUserId || !this.config.apiEndpoint) {
            this.log('⚠️ No se puede verificar mensajes - falta configuración:', {
                userId: this.config.currentUserId,
                apiEndpoint: this.config.apiEndpoint
            });
            return;
        }

        var self = this;
        var currentTime = new Date().toISOString();
        var lastCheckTime = this.config.lastCheckTime || new Date(Date.now() - 60000).toISOString(); // 1 minuto atrás

        this.log('🔍 Verificando mensajes nuevos...', {
            userId: this.config.currentUserId,
            lastCheck: lastCheckTime,
            currentTime: currentTime
        });

        $.ajax({
            url: this.config.apiEndpoint,
            type: 'POST',
            dataType: 'json',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            data: {
                user_id: this.config.currentUserId,
                last_check: lastCheckTime,
                [this.config.csrfToken]: this.config.csrfHash
            },
            success: function(response) {
                self.log('📨 Respuesta del servidor:', response);
                
                var parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                    } catch (e) {
                        self.log('❌ Error parseando respuesta JSON:', e);
                        return;
                    }
                }

                var hasNewMessages = false;
                if (parsedResponse && parsedResponse.hasNewMessages !== undefined) {
                    hasNewMessages = parsedResponse.hasNewMessages === true || 
                                   parsedResponse.hasNewMessages === 'true' || 
                                   parsedResponse.hasNewMessages === 1 ||
                                   parsedResponse.hasNewMessages === '1';
                }

                self.log('📊 Análisis de respuesta:', {
                    success: parsedResponse && parsedResponse.success,
                    hasNewMessages: hasNewMessages,
                    count: parsedResponse ? parsedResponse.count : 'N/A'
                });
                
                if (parsedResponse && parsedResponse.success && hasNewMessages) {
                    var messageId = parsedResponse.count || 'unknown';
                    var now = Date.now();
                    
                    // Prevenir notificaciones duplicadas
                    if (self.config.lastNotificationTime && (now - self.config.lastNotificationTime) < 10000) {
                        self.log('⏭️ Mensaje ya procesado recientemente, saltando...');
                        return;
                    }
                    
                    self.log('🔔 ¡Mensaje nuevo detectado! Reproduciendo sonido...');
                    self.playNotificationSound();
                    
                    // Actualizar timestamp
                    self.config.lastCheckTime = currentTime;
                    self.config.lastNotificationTime = now;
                } else {
                    self.log('📭 No hay mensajes nuevos');
                }
                
                // Actualizar lastCheckTime
                if (parsedResponse && parsedResponse.success) {
                    self.config.lastCheckTime = currentTime;
                }
            },
            error: function(xhr, status, error) {
                self.log('❌ Error verificando mensajes nuevos:', error);
                self.log('Respuesta del servidor:', xhr.responseText);
            }
        });
    },

    // Reproducir sonido de notificación
    playNotificationSound: function() {
        this.log('🔊 Intentando reproducir sonido de notificación...');
        
        if (!this.config.soundEnabled) {
            this.log('🔇 Sonido deshabilitado, no se reproduce');
            return;
        }
        
        // Prevenir sonidos duplicados
        var now = Date.now();
        if (this.config.lastNotificationTime && (now - this.config.lastNotificationTime) < 5000) {
            this.log('⏭️ Sonido muy reciente, saltando...');
            return;
        }

        try {
            var audioUrl = this.config.baseUrl + 'assets/sounds/mensaje.mp3';
            this.log('🎵 URL del audio:', audioUrl);

            var audio = new Audio(audioUrl);
            audio.volume = 0.7;
            
            this.log('▶️ Reproduciendo audio...');
            audio.play().then(function() {
                this.log('✅ Sonido reproducido exitosamente');
            }.bind(this)).catch(function(error) {
                this.log('❌ No se pudo reproducir el sonido:', error);
                this.log('❌ Detalles del error:', {
                    name: error.name,
                    message: error.message,
                    code: error.code
                });
            }.bind(this));
            
            this.config.lastNotificationTime = now;
            this.log('⏰ Timestamp actualizado:', now);
        } catch (error) {
            this.log('❌ Error al reproducir sonido:', error);
        }
    },

    // Activar/desactivar sonido
    toggleSound: function() {
        this.config.soundEnabled = !this.config.soundEnabled;
        localStorage.setItem('globalChatSoundEnabled', this.config.soundEnabled);
        this.log('🔊 Sonido ' + (this.config.soundEnabled ? 'habilitado' : 'deshabilitado'));
        return this.config.soundEnabled;
    },

    // Obtener estado del sonido
    isSoundEnabled: function() {
        return this.config.soundEnabled;
    },

    // Función de prueba
    testSound: function() {
        this.log('🧪 Probando sonido...');
        this.playNotificationSound();
    }
};

// Función global para activar/desactivar sonido
window.toggleGlobalChatSoundV2 = function() {
    return window.GlobalChatNotificationsV2.toggleSound();
};

// Función global para verificar si el sonido está habilitado
window.isGlobalChatSoundEnabledV2 = function() {
    return window.GlobalChatNotificationsV2.isSoundEnabled();
};

// Función global para probar sonido
window.testGlobalChatSoundV2 = function() {
    return window.GlobalChatNotificationsV2.testSound();
};

