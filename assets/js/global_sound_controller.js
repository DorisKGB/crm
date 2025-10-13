/**
 * Controlador Global de Sonidos
 * Componente reutilizable para manejar sonidos de notificaciones en toda la aplicación
 * Centraliza la lógica de reproducción de sonidos y evita duplicados
 */

window.GlobalSoundController = {
    // Estado global
    initialized: false,
    soundQueue: [],
    isPlaying: false,
    
    // Configuración
    config: {
        baseUrl: null,
        defaultSound: 'campana.mp3',
        maxVolume: 0.8,
        minInterval: 5000, // Mínimo 5 segundos entre sonidos
        lastPlayTime: null
    },

    // Inicialización
    init: function() {
        if (this.initialized) {
            console.log('⚠️ Controlador de sonidos ya inicializado');
            return;
        }
        
        console.log('🔊 Inicializando controlador global de sonidos...');
        
        // Cargar configuración desde el DOM
        this.loadConfigFromDOM();
        
        this.initialized = true;
        console.log('✅ Controlador global de sonidos inicializado');
    },

    // Cargar configuración desde el DOM
    loadConfigFromDOM: function() {
        var configScript = document.querySelector('script[data-sound-config]');
        if (configScript) {
            try {
                var config = JSON.parse(configScript.textContent);
                this.config.baseUrl = config.baseUrl || window.baseUrl || '';
                this.config.defaultSound = config.defaultSound || 'campana.mp3';
                console.log('✅ Configuración de sonidos cargada desde el DOM');
            } catch (e) {
                console.warn('No se pudo cargar configuración de sonidos desde el DOM:', e);
            }
        }
        
        if (!this.config.baseUrl) {
            this.config.baseUrl = window.baseUrl || '';
        }
    },

    // Reproducir sonido con control de duplicados
    playSound: function(soundFile, options = {}) {
        if (!this.initialized) {
            this.init();
        }
        
        var sound = soundFile || this.config.defaultSound;
        var volume = options.volume || this.config.maxVolume;
        var force = options.force || false;
        var context = options.context || 'general';
        
        // Verificar si podemos reproducir (control de duplicados)
        if (!force && !this.canPlaySound()) {
            console.log('⏭️ Saltando sonido - muy reciente o en cola');
            return false;
        }
        
        // Agregar a la cola si ya hay un sonido reproduciéndose
        if (this.isPlaying) {
            console.log('📋 Agregando sonido a la cola:', sound);
            this.soundQueue.push({ sound, volume, context });
            return true;
        }
        
        // Reproducir inmediatamente
        return this._playSoundImmediate(sound, volume, context);
    },

    // Verificar si se puede reproducir un sonido
    canPlaySound: function() {
        var now = Date.now();
        if (this.config.lastPlayTime && (now - this.config.lastPlayTime) < this.config.minInterval) {
            return false;
        }
        return true;
    },

    // Reproducir sonido inmediatamente
    _playSoundImmediate: function(sound, volume, context) {
        try {
            // Corregir la URL base para assets (remover index.php si está presente)
            var baseUrl = this.config.baseUrl || '';
            if (baseUrl.includes('/index.php/')) {
                baseUrl = baseUrl.replace('/index.php/', '/');
            } else if (baseUrl.endsWith('/index.php')) {
                baseUrl = baseUrl.replace('/index.php', '');
            }
            
            var audioUrl = baseUrl + 'assets/sounds/' + sound;
            console.log('🎵 Reproduciendo sonido:', { sound, volume, context, url: audioUrl, originalBaseUrl: this.config.baseUrl, correctedBaseUrl: baseUrl });
            
            var audio = new Audio(audioUrl);
            audio.volume = Math.min(volume, 1.0); // Asegurar que no exceda 1.0
            this.isPlaying = true;
            
            audio.play().then(() => {
                console.log('✅ Sonido reproducido exitosamente:', sound);
                this.config.lastPlayTime = Date.now();
                this.isPlaying = false;
                
                // Procesar siguiente sonido en la cola
                this._processQueue();
            }).catch((error) => {
                console.log('❌ Error reproduciendo sonido:', error);
                this.isPlaying = false;
                this._processQueue();
            });
            
            return true;
        } catch (error) {
            console.log('❌ Error al reproducir sonido:', error);
            this.isPlaying = false;
            this._processQueue();
            return false;
        }
    },

    // Procesar cola de sonidos
    _processQueue: function() {
        if (this.soundQueue.length > 0 && !this.isPlaying) {
            var nextSound = this.soundQueue.shift();
            console.log('📋 Procesando siguiente sonido de la cola:', nextSound);
            this._playSoundImmediate(nextSound.sound, nextSound.volume, nextSound.context);
        }
    },

    // Limpiar cola de sonidos
    clearQueue: function() {
        console.log('🧹 Limpiando cola de sonidos');
        this.soundQueue = [];
    },

    // Reproducir sonido de excusa médica
    playExcuseSound: function(options = {}) {
        // Usar el mismo método que los mensajes - elemento audio con ID
        var audioElement = document.getElementById('notif-sound');
        if (audioElement) {
            console.log('🎵 Usando elemento audio existente para excusa (notif-sound)');
            var volume = options.volume || 0.8;
            audioElement.volume = Math.min(volume, 1.0);
            return audioElement.play().then(() => {
                console.log('✅ Sonido de excusa reproducido exitosamente');
                this.config.lastPlayTime = Date.now();
                return true;
            }).catch((error) => {
                console.log('❌ Error reproduciendo sonido de excusa:', error);
                return false;
            });
        } else {
            // Fallback al método original
            return this.playSound('campana.mp3', {
                volume: 0.8,
                context: 'excuse',
                ...options
            });
        }
    },

    // Reproducir sonido de mensaje
    playMessageSound: function(options = {}) {
        return this.playSound('campana.mp3', {
            volume: 0.7,
            context: 'message',
            ...options
        });
    },

    // Reproducir sonido de llamada
    playCallSound: function(options = {}) {
        return this.playSound('campana.mp3', {
            volume: 0.9,
            context: 'call',
            ...options
        });
    },

    // Forzar reproducción (ignora controles de duplicados)
    forcePlay: function(soundFile, options = {}) {
        return this.playSound(soundFile, { ...options, force: true });
    },

    // Obtener estado del controlador
    getStatus: function() {
        return {
            initialized: this.initialized,
            isPlaying: this.isPlaying,
            queueLength: this.soundQueue.length,
            lastPlayTime: this.config.lastPlayTime,
            canPlay: this.canPlaySound()
        };
    }
};

// Inicialización automática
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.GlobalSoundController.init();
    });
} else {
    window.GlobalSoundController.init();
}

// Funciones globales para compatibilidad
window.playGlobalSound = function(soundFile, options) {
    return window.GlobalSoundController.playSound(soundFile, options);
};

window.playExcuseSound = function(options) {
    return window.GlobalSoundController.playExcuseSound(options);
};

window.playMessageSound = function(options) {
    return window.GlobalSoundController.playMessageSound(options);
};

window.playCallSound = function(options) {
    return window.GlobalSoundController.playCallSound(options);
};

