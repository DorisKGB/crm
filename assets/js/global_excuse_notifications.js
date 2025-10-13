/**
 * Sistema Global de Notificaciones de Excusas Médicas
 * Maneja las notificaciones de sonido para excusas médicas cuando llegan a los proveedores
 * Reutiliza componentes del sistema de notificaciones globales existente
 */

window.GlobalExcuseNotifications = {
    // Estado de inicialización
    initialized: false,
    
    // Configuración
    config: {
        checkInterval: 5000, // Verificar excusas cada 5 segundos
        soundEnabled: true,
        lastCheckTime: null,
        lastNotificationTime: null,
        lastProcessedExcuseId: null,
        processedExcuseIds: new Set(), // Set para rastrear excusas ya procesadas
        currentUserId: null,
        apiEndpoint: null,
        csrfToken: null,
        csrfHash: null,
        baseUrl: null,
        userRole: null, // 'provider', 'admin', 'all', etc.
        isAdmin: false
    },

    // Inicialización
    init: function() {
        // Verificar si ya está inicializado
        if (this.initialized) {
            console.log('⚠️ Sistema de notificaciones de excusas ya inicializado, omitiendo...');
            return;
        }
        
        console.log('🏥 Inicializando notificaciones globales de excusas médicas...');
        
        // Inicializar configuración de sonido
        this.config.soundEnabled = localStorage.getItem('globalExcuseSoundEnabled') !== 'false';
        console.log('🔊 Sonido de excusas habilitado:', this.config.soundEnabled);
        
        // Obtener configuración desde el DOM
        this.loadConfigFromDOM();
        
        // Verificar si el usuario debe recibir notificaciones de excusas
        if (!this.shouldReceiveExcuseNotifications()) {
            console.log('⏭️ Usuario no debe recibir notificaciones de excusas, omitiendo...');
            return;
        }
        
        // Configurar event listeners
        this.setupEventListeners();
        
        // Iniciar verificación de excusas con delay
        setTimeout(() => {
            this.startExcuseChecking();
        }, 3000); // Delay de 3 segundos
        
        this.initialized = true;
        console.log('✅ Notificaciones globales de excusas médicas inicializadas');
        console.log('📊 Configuración actual:', this.config);
    },

    // Cargar configuración desde el DOM
    loadConfigFromDOM: function() {
        // Buscar configuración en el DOM
        var configScript = document.querySelector('script[data-excuse-config]');
        if (configScript) {
            try {
                var config = JSON.parse(configScript.textContent);
                this.config.currentUserId = config.userId;
                this.config.apiEndpoint = config.apiEndpoint;
                this.config.csrfToken = config.csrfToken;
                this.config.csrfHash = config.csrfHash;
                this.config.baseUrl = config.baseUrl;
                this.config.userRole = config.userRole;
                this.config.isAdmin = config.isAdmin;
                console.log('✅ Configuración de excusas cargada desde el DOM:', config);
            } catch (e) {
                console.warn('No se pudo cargar configuración de excusas desde el DOM:', e);
            }
        }
        
        // Si no hay configuración, usar valores por defecto
        if (!this.config.currentUserId) {
            this.config.currentUserId = window.loginUserId || null;
        }
        if (!this.config.apiEndpoint) {
            this.config.apiEndpoint = (window.baseUrl || '') + 'excuse/check_new_excuses';
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
        if (!this.config.userRole) {
            this.config.userRole = window.excusePermission || 'no';
        }
        if (this.config.isAdmin === undefined) {
            this.config.isAdmin = window.isAdmin || false;
        }
    },

    // Verificar si el usuario debe recibir notificaciones de excusas
    shouldReceiveExcuseNotifications: function() {
        // Verificar permisos de excusas desde la configuración del DOM
        var excusePermission = this.config.userRole || window.excusePermission || 'no';
        var isAdmin = this.config.isAdmin || window.isAdmin || false;
        
        console.log('🔍 Verificando permisos de excusas:', {
            excusePermission: excusePermission,
            isAdmin: isAdmin,
            userRole: this.config.userRole,
            configIsAdmin: this.config.isAdmin,
            windowExcusePermission: window.excusePermission,
            windowIsAdmin: window.isAdmin
        });
        
        // Solo notificar a proveedores, admins o usuarios con permiso 'all'
        var shouldReceive = excusePermission === 'provider' || 
                           excusePermission === 'all' || 
                           isAdmin === true;
        
        console.log('🔍 ¿Debe recibir notificaciones?', shouldReceive);
        
        return shouldReceive;
    },

    // Configurar event listeners
    setupEventListeners: function() {
        var self = this;
        
        // Detectar cuando la ventana gana/pierde foco
        window.addEventListener('focus', function() {
            if (self.config.soundEnabled) {
                self.checkForNewExcuses();
            }
        });
        
        // Verificar al cambiar de pestaña
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && self.config.soundEnabled) {
                console.log('👁️ Pestaña visible - verificando excusas inmediatamente');
                self.checkForNewExcuses();
            }
        });
        
        // Escuchar eventos de excusas desde el sistema activo
        document.addEventListener('newExcuseCreated', function(event) {
            if (event.detail && event.detail.isForCurrentUser) {
                self.playNotificationSound();
            }
        });
    },

    // Iniciar verificación de excusas
    startExcuseChecking: function() {
        var self = this;
        
        // Verificar excusas cada X segundos
        setInterval(function() {
            console.log('⏰ Verificación programada de excusas:', {
                soundEnabled: self.config.soundEnabled,
                shouldCheck: self.config.soundEnabled,
                message: 'VERIFICANDO EXCUSAS NUEVAS'
            });
            
            // Limpiar excusas procesadas antiguas cada 5 minutos (probabilidad del 20%)
            if (Math.random() < 0.2) {
                self.cleanupProcessedExcuses();
            }
            
            if (self.config.soundEnabled) {
                self.checkForNewExcuses();
            } else {
                console.log('⏭️ Saltando verificación - sonido deshabilitado');
            }
        }, this.config.checkInterval);
    },

        // Verificar si hay excusas nuevas
        checkForNewExcuses: function() {
            if (!this.config.currentUserId || !this.config.apiEndpoint) {
                console.log('❌ No se puede verificar excusas: userId o apiEndpoint faltante');
                return;
            }

            var self = this;
            
            // Usar el helper de zona horaria para obtener timestamps correctos
            var currentTime = TimezoneHelper ? TimezoneHelper.now() : new Date().toISOString();
            
            // Usar un timestamp más reciente si no hay lastCheckTime
            var lastCheckTime = this.config.lastCheckTime || (TimezoneHelper ? TimezoneHelper.minutesAgo(1) : new Date(Date.now() - 60000).toISOString()); // 1 minuto atrás
        
        console.log('🔍 Verificando excusas nuevas...', {
            userId: this.config.currentUserId,
            lastCheck: lastCheckTime,
            apiEndpoint: this.config.apiEndpoint
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
                console.log('📨 Respuesta del servidor (excusas):', response);
                
                // Verificar si la respuesta es un string que necesita parsing
                var parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                        console.log('📨 Respuesta parseada (excusas):', parsedResponse);
                    } catch (e) {
                        console.error('❌ Error parseando JSON:', e);
                        return;
                    }
                }
                
                // Mostrar información de debug si está disponible
                if (parsedResponse && parsedResponse.debug) {
                    console.log('🔍 Debug del servidor:', parsedResponse.debug);
                }
                
                // Convertir hasNewExcuses a boolean si es string
                var hasNewExcuses = false;
                if (parsedResponse && parsedResponse.hasNewExcuses !== undefined) {
                    hasNewExcuses = parsedResponse.hasNewExcuses === true || 
                                   parsedResponse.hasNewExcuses === 'true' || 
                                   parsedResponse.hasNewExcuses === 1 ||
                                   parsedResponse.hasNewExcuses === '1';
                }
                
                console.log('🔍 Evaluación de excusas:', {
                    success: parsedResponse ? parsedResponse.success : false,
                    hasNewExcuses: hasNewExcuses,
                    count: parsedResponse ? parsedResponse.count : 0,
                    excuseData: parsedResponse ? parsedResponse.excuseData : null
                });
                
                if (parsedResponse && parsedResponse.success && hasNewExcuses) {
                    // Verificar si ya procesamos esta excusa específica
                    var excuseId = parsedResponse.excuseData ? parsedResponse.excuseData.excuseId : 'unknown';
                    var now = Date.now();
                    
                    // Control principal: verificar si ya procesamos esta excusa específica
                    if (self.config.processedExcuseIds.has(excuseId)) {
                        console.log('⏭️ Saltando excusa duplicada (ID ya procesado):', excuseId);
                        return;
                    }
                    
                    // Control adicional: verificar tiempo (máximo 1 notificación cada 10 segundos)
                    if (self.config.lastNotificationTime && (now - self.config.lastNotificationTime) < 10000) {
                        console.log('⏭️ Saltando excusa duplicada (muy reciente - último hace ' + Math.round((now - self.config.lastNotificationTime) / 1000) + 's)');
                        return;
                    }
                    
                    console.log('🏥 ¡Excusa médica nueva detectada! Reproduciendo sonido...');
                    console.log('📊 Detalles:', {
                        excuseId: excuseId,
                        patientName: parsedResponse.excuseData ? parsedResponse.excuseData.patientName : 'N/A',
                        excuseType: parsedResponse.excuseData ? parsedResponse.excuseData.excuseType : 'N/A',
                        clinic: parsedResponse.excuseData ? parsedResponse.excuseData.clinic : 'N/A'
                    });
                    
                    // Marcar esta excusa como procesada ANTES de reproducir el sonido
                    self.config.processedExcuseIds.add(excuseId);
                    self.config.lastProcessedExcuseId = excuseId;
                    self.config.lastNotificationTime = now;
                    
                    self.playNotificationSound();
                    
                    // Actualizar lastCheckTime INMEDIATAMENTE después de detectar excusa nueva
                    self.config.lastCheckTime = currentTime;
                    console.log('⏰ lastCheckTime actualizado a:', currentTime);
                    console.log('📝 Excusa marcada como procesada:', excuseId);
                    console.log('📊 Total excusas procesadas:', self.config.processedExcuseIds.size);
                } else {
                    console.log('📭 No hay excusas nuevas', {
                        success: parsedResponse ? parsedResponse.success : false,
                        hasNewExcuses: hasNewExcuses,
                        message: parsedResponse ? parsedResponse.message : 'Sin respuesta'
                    });
                }
                
                // Actualizar lastCheckTime solo si la verificación fue exitosa (para casos sin excusas nuevas)
                if (parsedResponse && parsedResponse.success && !hasNewExcuses) {
                    self.config.lastCheckTime = currentTime;
                }
            },
            error: function(xhr, status, error) {
                console.log('❌ Error verificando excusas nuevas:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        });
    },

    // Reproducir sonido de notificación
    playNotificationSound: function() {
        console.log('🔊 Intentando reproducir sonido de notificación de excusa...');
        console.log('🔊 Sonido habilitado:', this.config.soundEnabled);
        
        if (!this.config.soundEnabled) {
            console.log('🔇 Sonido deshabilitado, no se reproduce');
            return;
        }
        
        // No hay control de duplicados aquí - ya se maneja en checkForNewExcuses()

        // Usar el controlador global de sonidos si está disponible
        if (window.GlobalSoundController) {
            console.log('🎵 Usando controlador global de sonidos');
            var success = window.GlobalSoundController.playExcuseSound({
                volume: 0.8,
                context: 'excuse_notification'
            });
            
            if (success) {
                console.log('✅ Sonido de excusa reproducido exitosamente (controlador global)');
            }
        } else {
            // Fallback al método original si no hay controlador global
            console.log('🎵 Usando método de sonido directo (fallback)');
            try {
                // Corregir la URL base para assets (remover index.php si está presente)
                var baseUrl = this.config.baseUrl || '';
                if (baseUrl.includes('/index.php/')) {
                    baseUrl = baseUrl.replace('/index.php/', '/');
                } else if (baseUrl.endsWith('/index.php')) {
                    baseUrl = baseUrl.replace('/index.php', '');
                }
                
                // Usar el mismo método que los mensajes - elemento audio con ID
                var audioElement = document.getElementById('notif-sound');
                if (audioElement) {
                    console.log('🎵 Usando elemento audio existente (notif-sound)');
                    audioElement.volume = 0.8;
                    audioElement.play().then(function() {
                        console.log('✅ Sonido de excusa reproducido exitosamente');
                    }).catch(function(error) {
                        console.log('❌ No se pudo reproducir el sonido de notificación de excusa:', error);
                    });
                } else {
                    // Fallback: crear elemento audio dinámicamente
                    var audioUrl = baseUrl + 'assets/sounds/campana.mp3';
                    console.log('🎵 URL del audio (fallback):', audioUrl, 'originalBaseUrl:', this.config.baseUrl, 'correctedBaseUrl:', baseUrl);
                    
                    var audio = new Audio(audioUrl);
                    audio.volume = 0.8;
                    audio.play().then(function() {
                        console.log('✅ Sonido de excusa reproducido exitosamente (fallback)');
                    }).catch(function(error) {
                        console.log('❌ No se pudo reproducir el sonido de notificación de excusa (fallback):', error);
                    });
                }
            } catch (error) {
                console.log('❌ Error al reproducir sonido de notificación de excusa:', error);
            }
        }
    },

    // Activar/desactivar sonido
    toggleSound: function() {
        this.config.soundEnabled = !this.config.soundEnabled;
        localStorage.setItem('globalExcuseSoundEnabled', this.config.soundEnabled);
        return this.config.soundEnabled;
    },

    // Obtener estado del sonido
    isSoundEnabled: function() {
        return this.config.soundEnabled;
    },

    // Método para notificar manualmente (para uso desde otros scripts)
    notifyNewExcuse: function(excuseData) {
        console.log('🔔 Notificación manual de excusa nueva:', excuseData);
        this.playNotificationSound();
    },

    // Limpiar excusas procesadas antiguas (más de 30 minutos)
    cleanupProcessedExcuses: function() {
        var now = Date.now();
        var thirtyMinutesAgo = now - (30 * 60 * 1000); // 30 minutos en milisegundos
        
        // Si no hay lastNotificationTime o es muy antiguo, limpiar todo
        if (!this.config.lastNotificationTime || (now - this.config.lastNotificationTime) > thirtyMinutesAgo) {
            console.log('🧹 Limpiando excusas procesadas antiguas (más de 30 min)');
            console.log('📊 Excusas antes de limpiar:', this.config.processedExcuseIds.size);
            this.config.processedExcuseIds.clear();
            this.config.lastProcessedExcuseId = null;
            console.log('✅ Limpieza completada');
        }
    },

    // Obtener estadísticas de excusas procesadas
    getProcessedExcusesStats: function() {
        return {
            totalProcessed: this.config.processedExcuseIds.size,
            lastProcessedId: this.config.lastProcessedExcuseId,
            lastNotificationTime: this.config.lastNotificationTime
        };
    }

};

// Función global para activar/desactivar sonido desde otros scripts
window.toggleGlobalExcuseSound = function() {
    return window.GlobalExcuseNotifications.toggleSound();
};

// Función global para verificar si el sonido está habilitado
window.isGlobalExcuseSoundEnabled = function() {
    return window.GlobalExcuseNotifications.isSoundEnabled();
};

// Función global para notificar manualmente
window.notifyNewExcuse = function(excuseData) {
    if (window.GlobalExcuseNotifications) {
        window.GlobalExcuseNotifications.notifyNewExcuse(excuseData);
    }
};
