/**
 * Sistema Global de Notificaciones de Timbres
 * Maneja las notificaciones de sonido para timbres cuando llegan nuevos
 * Reutiliza componentes del sistema de notificaciones globales existente
 */

window.GlobalStampNotifications = {
    // Estado de inicialización
    initialized: false,
    
    // Configuración
    config: {
        checkInterval: 10000, // Verificar timbres cada 10 segundos
        soundEnabled: true,
        lastCheckTime: null,
        lastNotificationTime: null,
        lastProcessedStampId: null,
        processedStampIds: new Set(), // Set para rastrear timbres ya procesados
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
            console.log('⚠️ Sistema de notificaciones de timbres ya inicializado, omitiendo...');
            return;
        }
        
        console.log('🔧 Inicializando sistema de notificaciones de timbres...');
        
        // Inicializar configuración de sonido
        this.config.soundEnabled = localStorage.getItem('globalStampSoundEnabled') !== 'false';

        // Obtener configuración desde el DOM
        this.loadConfigFromDOM();
        
        // Cargar estado persistente
        this.loadPersistentState();
        
        console.log('📋 Configuración cargada:', {
            userId: this.config.currentUserId,
            userRole: this.config.userRole,
            isAdmin: this.config.isAdmin,
            apiEndpoint: this.config.apiEndpoint,
            soundEnabled: this.config.soundEnabled,
            lastCheckTime: this.config.lastCheckTime,
            processedStampIds: this.config.processedStampIds.size
        });
        
        // Verificar si el usuario debe recibir notificaciones de timbres
        if (!this.shouldReceiveStampNotifications()) {
            console.log('❌ Usuario no debe recibir notificaciones de timbres');
            return;
        }
        
        console.log('✅ Usuario autorizado para recibir notificaciones de timbres');
        
        // Configurar event listeners
        this.setupEventListeners();
        
        // Iniciar verificación de timbres con delay
        setTimeout(() => {
            console.log('⏰ Iniciando verificación periódica de timbres...');
            this.startStampChecking();
        }, 5000); // Delay de 5 segundos
        
        this.initialized = true;
        console.log('🎉 Sistema de notificaciones de timbres inicializado completamente');
    },

    // Cargar configuración desde el DOM
    loadConfigFromDOM: function() {
        // Buscar configuración en el DOM
        var configScript = document.querySelector('script[data-stamp-config]');
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
            } catch (e) {
                console.warn('No se pudo cargar configuración de timbres desde el DOM:', e);
            }
        }
        
        // Si no hay configuración, usar valores por defecto
        if (!this.config.currentUserId) {
            this.config.currentUserId = window.loginUserId || null;
        }
        if (!this.config.apiEndpoint) {
            this.config.apiEndpoint = (window.baseUrl || '') + 'stamp/check_new_stamps';
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
            this.config.userRole = window.stampPermission || 'no';
        }
        if (this.config.isAdmin === undefined) {
            this.config.isAdmin = window.isAdmin || false;
        }
    },

    // Cargar estado persistente desde localStorage
    loadPersistentState: function() {
        try {
            var userId = this.config.currentUserId;
            if (!userId) return;
            
            var storageKey = 'globalStampState_' + userId;
            var savedState = localStorage.getItem(storageKey);
            
            if (savedState) {
                var state = JSON.parse(savedState);
                
                // Cargar lastCheckTime
                if (state.lastCheckTime) {
                    this.config.lastCheckTime = state.lastCheckTime;
                    console.log('📅 Estado cargado - Última verificación:', state.lastCheckTime);
                }
                
                // Cargar processedStampIds
                if (state.processedStampIds && Array.isArray(state.processedStampIds)) {
                    this.config.processedStampIds = new Set(state.processedStampIds);
                    console.log('📋 Estado cargado - Timbres procesados:', state.processedStampIds.length);
                }
                
                // Cargar lastProcessedStampId
                if (state.lastProcessedStampId) {
                    this.config.lastProcessedStampId = state.lastProcessedStampId;
                }
                
                console.log('💾 Estado persistente cargado exitosamente');
            } else {
                console.log('🆕 No hay estado persistente previo, iniciando desde cero');
                // Establecer un tiempo inicial más reciente para evitar notificaciones de timbres antiguos
                this.config.lastCheckTime = TimezoneHelper ? TimezoneHelper.now() : new Date().toISOString();
            }
        } catch (error) {
            console.warn('⚠️ Error cargando estado persistente:', error);
            // En caso de error, establecer tiempo inicial
            this.config.lastCheckTime = TimezoneHelper ? TimezoneHelper.now() : new Date().toISOString();
        }
    },

    // Guardar estado persistente en localStorage
    savePersistentState: function() {
        try {
            var userId = this.config.currentUserId;
            if (!userId) return;
            
            var storageKey = 'globalStampState_' + userId;
            var state = {
                lastCheckTime: this.config.lastCheckTime,
                processedStampIds: Array.from(this.config.processedStampIds),
                lastProcessedStampId: this.config.lastProcessedStampId,
                lastNotificationTime: this.config.lastNotificationTime,
                timestamp: Date.now()
            };
            
            localStorage.setItem(storageKey, JSON.stringify(state));
            console.log('💾 Estado persistente guardado');
        } catch (error) {
            console.warn('⚠️ Error guardando estado persistente:', error);
        }
    },

    // Verificar si el usuario debe recibir notificaciones de timbres
    shouldReceiveStampNotifications: function() {
        // Verificar permisos de timbres desde la configuración del DOM
        var stampPermission = this.config.userRole || window.stampPermission || 'no';
        var isAdmin = this.config.isAdmin || window.isAdmin || false;
        
        // Solo notificar a usuarios con permisos de timbres o admins
        var shouldReceive = stampPermission === 'provider' || 
                           stampPermission === 'request' || 
                           stampPermission === 'all' || 
                           isAdmin === true;
        
        return shouldReceive;
    },

    // Configurar event listeners
    setupEventListeners: function() {
        var self = this;
        
        // Detectar cuando la ventana gana/pierde foco
        window.addEventListener('focus', function() {
            if (self.config.soundEnabled) {
                self.checkForNewStamps();
            }
        });
        
        // Verificar al cambiar de pestaña
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && self.config.soundEnabled) {
                self.checkForNewStamps();
            }
        });
        
        // Escuchar eventos de timbres desde el sistema activo
        document.addEventListener('newStampCreated', function(event) {
            if (event.detail && event.detail.isForCurrentUser) {
                self.playNotificationSound();
            }
        });
    },

    // Iniciar verificación de timbres
    startStampChecking: function() {
        var self = this;
        
        // Verificar timbres cada X segundos
        setInterval(function() {
            // Limpiar timbres procesados antiguos cada 5 minutos (probabilidad del 20%)
            if (Math.random() < 0.2) {
                self.cleanupProcessedStamps();
            }
            
            if (self.config.soundEnabled) {
                self.checkForNewStamps();
            }
        }, this.config.checkInterval);
    },

    // Verificar si hay timbres nuevos
    checkForNewStamps: function() {
        if (!this.config.currentUserId || !this.config.apiEndpoint) {
            console.log('⚠️ No se puede verificar timbres: falta userId o apiEndpoint');
            return;
        }

        var self = this;
        
        // Usar el helper de zona horaria para obtener timestamps correctos
        var currentTime = TimezoneHelper ? TimezoneHelper.now() : new Date().toISOString();
        
        // Usar un timestamp más reciente si no hay lastCheckTime
        var lastCheckTime = this.config.lastCheckTime || (TimezoneHelper ? TimezoneHelper.minutesAgo(5) : new Date(Date.now() - 300000).toISOString()); // 5 minutos atrás para ser más permisivo
        
        console.log('🔍 Verificando timbres nuevos...', {
            userId: this.config.currentUserId,
            lastCheck: lastCheckTime,
            endpoint: this.config.apiEndpoint
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
                // Verificar si la respuesta es un string que necesita parsing
                var parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                    } catch (e) {
                        console.log('❌ Error parseando respuesta JSON:', e);
                        return;
                    }
                }
                
                // Mostrar información de debug si está disponible
                if (parsedResponse && parsedResponse.debug) {
                    console.log('🔍 Debug del servidor (timbres):', parsedResponse.debug);
                }
                
                // Convertir hasNewStamps a boolean si es string
                var hasNewStamps = false;
                if (parsedResponse && parsedResponse.hasNewStamps !== undefined) {
                    hasNewStamps = parsedResponse.hasNewStamps === true || 
                                   parsedResponse.hasNewStamps === 'true' || 
                                   parsedResponse.hasNewStamps === 1 ||
                                   parsedResponse.hasNewStamps === '1';
                }
                
                if (parsedResponse && parsedResponse.success && hasNewStamps) {
                    // Verificar si ya procesamos este timbre específico
                    var stampId = parsedResponse.stampData ? parsedResponse.stampData.stampId : 'unknown';
                    var now = Date.now();
                    
                    console.log('🔔 Timbre nuevo detectado:', {
                        stampId: stampId,
                        clinicName: parsedResponse.stampData ? parsedResponse.stampData.clinicName : 'N/A',
                        alreadyProcessed: self.config.processedStampIds.has(stampId)
                    });
                    
                    // Control principal: verificar si ya procesamos este timbre específico
                    if (self.config.processedStampIds.has(stampId)) {
                        console.log('⏭️ Timbre ya procesado, saltando notificación');
                        return;
                    }
                    
                    // Control adicional: verificar tiempo (máximo 1 notificación cada 10 segundos)
                    if (self.config.lastNotificationTime && (now - self.config.lastNotificationTime) < 10000) {
                        console.log('⏭️ Notificación muy reciente, saltando');
                        return;
                    }
                    
                    // Marcar este timbre como procesado ANTES de reproducir el sonido
                    self.config.processedStampIds.add(stampId);
                    self.config.lastProcessedStampId = stampId;
                    self.config.lastNotificationTime = now;
                    
                    console.log('🔔 Reproduciendo sonido y mostrando notificación...');
                    self.playNotificationSound();
                    
                    // Mostrar toast si está disponible
                    if (parsedResponse.stampData) {
                        self.showStampToast(parsedResponse.stampData);
                    }
                    
                    // Disparar evento personalizado para otros sistemas
                    var event = new CustomEvent('newStampCreated', {
                        detail: {
                            stampData: parsedResponse.stampData,
                            isForCurrentUser: true,
                            timestamp: now
                        }
                    });
                    document.dispatchEvent(event);
                    
                    // Actualizar lastCheckTime INMEDIATAMENTE después de detectar timbre nuevo
                    self.config.lastCheckTime = currentTime;
                    // Guardar estado persistente
                    self.savePersistentState();
                } else {
                    // Actualizar lastCheckTime solo si la verificación fue exitosa (para casos sin timbres nuevos)
                    if (parsedResponse && parsedResponse.success && !hasNewStamps) {
                        self.config.lastCheckTime = currentTime;
                        // Guardar estado persistente
                        self.savePersistentState();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('❌ Error verificando timbres nuevos:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
                console.log('Status:', status);
                console.log('XHR:', xhr);
            }
        });
    },

    // Mostrar toast de notificación de timbre
    showStampToast: function(stampData) {
        // Buscar el toast existente o crear uno nuevo
        var toastElement = document.getElementById('newStampToast');
        if (!toastElement) {
            // Crear el toast si no existe
            var toastContainer = document.querySelector('.position-fixed.bottom-0.end-0.p-3');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '1055';
                document.body.appendChild(toastContainer);
            }
            
            toastElement = document.createElement('div');
            toastElement.id = 'newStampToast';
            toastElement.className = 'toast';
            toastElement.setAttribute('role', 'alert');
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            toastElement.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Nuevo Timbre</strong>
                    <small class="text-muted">Ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body">
                    Tienes un nuevo timbre <span id="toast-stamp-id"></span> en <span id="toast-stamp-clinic"></span>.
                </div>
            `;
            toastContainer.appendChild(toastElement);
        }
        
        // Actualizar contenido del toast
        var stampIdElement = document.getElementById('toast-stamp-id');
        var stampClinicElement = document.getElementById('toast-stamp-clinic');
        
        if (stampIdElement) {
            stampIdElement.textContent = `#${stampData.stampId || 'N/A'}`;
        }
        if (stampClinicElement) {
            stampClinicElement.textContent = stampData.clinicName || 'Clínica';
        }
        
        // Mostrar el toast
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            var toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    },

    // Reproducir sonido de notificación
    playNotificationSound: function() {
        if (!this.config.soundEnabled) {
            console.log('🔇 Sonido deshabilitado para timbres');
            return;
        }
        
        console.log('🔔 Reproduciendo sonido de timbre (campana.mp3)...');
        
        // Usar directamente campana.mp3 para timbres
        var baseUrl = this.config.baseUrl || '';
        if (baseUrl.includes('/index.php/')) {
            baseUrl = baseUrl.replace('/index.php/', '/');
        } else if (baseUrl.endsWith('/index.php')) {
            baseUrl = baseUrl.replace('/index.php', '');
        }
        
        var audioUrl = baseUrl + 'assets/sounds/campana.mp3';
        console.log('🎵 URL del sonido de timbre:', audioUrl);
        
        var audio = new Audio(audioUrl);
        audio.volume = 0.8;
        audio.play().then(function() {
            console.log('✅ Sonido de timbre (campana.mp3) reproducido exitosamente');
        }).catch(function(error) {
            console.log('❌ Error reproduciendo sonido de timbre (campana.mp3):', error);
            
            // Fallback: intentar con el elemento stamp-notif-sound si existe
            var audioElement = document.getElementById('stamp-notif-sound');
            if (audioElement) {
                console.log('🔄 Fallback: usando elemento stamp-notif-sound');
                audioElement.volume = 0.8;
                audioElement.play().catch(function(fallbackError) {
                    console.log('❌ Error en fallback también:', fallbackError);
                });
            }
        });
    },

    // Activar/desactivar sonido
    toggleSound: function() {
        this.config.soundEnabled = !this.config.soundEnabled;
        localStorage.setItem('globalStampSoundEnabled', this.config.soundEnabled);
        return this.config.soundEnabled;
    },

    // Obtener estado del sonido
    isSoundEnabled: function() {
        return this.config.soundEnabled;
    },

    // Método para notificar manualmente (para uso desde otros scripts)
    notifyNewStamp: function(stampData) {
        console.log('🔔 Notificación manual de timbre nuevo:', stampData);
        this.playNotificationSound();
        if (stampData) {
            this.showStampToast(stampData);
        }
    },

    // Limpiar timbres procesados antiguos (más de 30 minutos)
    cleanupProcessedStamps: function() {
        var now = Date.now();
        var thirtyMinutesAgo = now - (30 * 60 * 1000); // 30 minutos en milisegundos
        
        // Si no hay lastNotificationTime o es muy antiguo, limpiar todo
        if (!this.config.lastNotificationTime || (now - this.config.lastNotificationTime) > thirtyMinutesAgo) {
            this.config.processedStampIds.clear();
            this.config.lastProcessedStampId = null;
            // Guardar estado después de limpiar
            this.savePersistentState();
            console.log('🧹 Timbres procesados antiguos limpiados');
        }
    },

    // Obtener estadísticas de timbres procesados
    getProcessedStampsStats: function() {
        return {
            totalProcessed: this.config.processedStampIds.size,
            lastProcessedId: this.config.lastProcessedStampId,
            lastNotificationTime: this.config.lastNotificationTime
        };
    }
};

// Función global para activar/desactivar sonido desde otros scripts
window.toggleGlobalStampSound = function() {
    return window.GlobalStampNotifications.toggleSound();
};

// Función global para verificar si el sonido está habilitado
window.isGlobalStampSoundEnabled = function() {
    return window.GlobalStampNotifications.isSoundEnabled();
};

// Función global para notificar manualmente
window.notifyNewStamp = function(stampData) {
    if (window.GlobalStampNotifications) {
        window.GlobalStampNotifications.notifyNewStamp(stampData);
    }
};
