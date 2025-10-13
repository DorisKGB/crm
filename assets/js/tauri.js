// ========== DETECTOR DE ENTORNO TAURI ==========
class TauriEnvironment {
    static isTauriApp() {
        // Usar la detección robusta si está disponible
        if (window.TauriDetection && window.TauriDetection.isDetected) {
            return window.TauriDetection.isDetected();
        }
        
        // Fallback a detección básica
        if (typeof window === 'undefined') return false;
        
        // Verificar si ya se detectó previamente
        if (window.__TAURI_DETECTED !== undefined) {
            return window.__TAURI_DETECTED;
        }
        
        // Método 1: Verificar __TAURI__ estándar
        if (window.__TAURI__ && window.__TAURI__.invoke) {
            return true;
        }
        
        // Método 2: Verificar si estamos en un contexto Tauri (aplicación embebida)
        if (window.__TAURI_METADATA__ || window.__TAURI_INTERNALS__) {
            return true;
        }
        
        // Método 3: Verificar user agent o características específicas de Tauri
        if (navigator.userAgent.includes('Tauri') || 
            navigator.userAgent.includes('tauri') ||
            window.location.protocol === 'tauri:') {
            return true;
        }
        
        // Método 4: Verificar si existe el objeto tauri en el contexto global
        if (typeof window.tauri !== 'undefined' || 
            typeof window.__tauri !== 'undefined') {
            return true;
        }
        
        // Método 5: Verificar si estamos en un iframe de Tauri
        if (window.parent !== window && 
            (window.parent.__TAURI__ || window.parent.__TAURI_METADATA__)) {
            return true;
        }
        
        return false;
    }
    
    static debugTauriDetection() {
        console.log('🔍 Debug de detección de Tauri:');
        console.log('- window.__TAURI__:', !!window.__TAURI__);
        console.log('- window.__TAURI_METADATA__:', !!window.__TAURI_METADATA__);
        console.log('- window.__TAURI_INTERNALS__:', !!window.__TAURI_INTERNALS__);
        console.log('- window.tauri:', !!window.tauri);
        console.log('- window.__tauri:', !!window.__tauri);
        console.log('- navigator.userAgent:', navigator.userAgent);
        console.log('- window.location.protocol:', window.location.protocol);
        console.log('- window.parent !== window:', window.parent !== window);
        if (window.parent !== window) {
            console.log('- window.parent.__TAURI__:', !!window.parent.__TAURI__);
            console.log('- window.parent.__TAURI_METADATA__:', !!window.parent.__TAURI_METADATA__);
        }
        console.log('- isTauriApp() result:', this.isTauriApp());
        
        // Debug específico de la API de Tauri
        if (window.__TAURI__) {
            console.log('🔧 Debug de API de Tauri:');
            console.log('- window.__TAURI__.invoke:', typeof window.__TAURI__.invoke);
            console.log('- window.__TAURI__.event:', typeof window.__TAURI__.event);
            console.log('- window.__TAURI__.window:', typeof window.__TAURI__.window);
            console.log('- window.__TAURI__.app:', typeof window.__TAURI__.app);
            console.log('- window.__TAURI__.os:', typeof window.__TAURI__.os);
            console.log('- window.__TAURI__.path:', typeof window.__TAURI__.path);
            console.log('- window.__TAURI__.fs:', typeof window.__TAURI__.fs);
            console.log('- window.__TAURI__.dialog:', typeof window.__TAURI__.dialog);
            console.log('- window.__TAURI__.notification:', typeof window.__TAURI__.notification);
        }
    }
    
    static async testTauriCommands() {
        console.log('🧪 Probando comandos de Tauri...');
        
        // Probar APIs nativas primero
        if (window.__TAURI__) {
            console.log('🔍 Probando APIs nativas de Tauri:');
            
            // Probar API de ventana
            if (window.__TAURI__.window) {
                try {
                    const currentWindow = window.__TAURI__.window.getCurrent();
                    console.log('✅ window.getCurrent():', typeof currentWindow);
                    
                    // Probar métodos de ventana
                    const methods = ['show', 'setFocus', 'setAlwaysOnTop', 'minimize', 'maximize'];
                    for (const method of methods) {
                        if (typeof currentWindow[method] === 'function') {
                            console.log(`✅ window.${method}:`, 'disponible');
                        } else {
                            console.log(`❌ window.${method}:`, 'no disponible');
                        }
                    }
                } catch (error) {
                    console.log('❌ Error probando API de ventana:', error.message);
                }
            }
            
            // Probar API de notificaciones
            if (window.__TAURI__.notification) {
                try {
                    console.log('✅ notification.sendNotification:', typeof window.__TAURI__.notification.sendNotification);
                } catch (error) {
                    console.log('❌ Error probando API de notificaciones:', error.message);
                }
            }
        }
        
        // Probar comandos personalizados
        const testCommands = [
            'handle_incoming_call',
            'show_notification',
            'show_window',
            'set_focus',
            'bring_to_front'
        ];
        
        console.log('🔍 Probando comandos personalizados:');
        for (const command of testCommands) {
            try {
                console.log(`🔍 Probando comando: ${command}`);
                const result = await this.invokeTauriCommand(command, { test: true });
                console.log(`✅ ${command}:`, result);
            } catch (error) {
                console.log(`❌ ${command}:`, error.message);
            }
        }
    }
    
    static async invokeTauriCommand(command, args = {}) {
        if (this.isTauriApp()) {
            try {
                // Verificar si la API de Tauri está realmente disponible
                if (!window.__TAURI__ || typeof window.__TAURI__.invoke !== 'function') {
                    console.warn(`⚠️ API de Tauri no disponible para comando ${command} - Simulando`);
                    return Promise.resolve({ 
                        success: true, 
                        message: `Comando ${command} simulado - API no disponible` 
                    });
                }
                
                // Verificar si el comando específico está disponible
                const availableCommands = [
                    'handle_incoming_call',
                    'handle_call_ended', 
                    'show_notification',
                    'show_window',
                    'set_focus',
                    'bring_to_front',
                    'minimize_window',
                    'maximize_window',
                    'is_minimized',
                    'unminimize'
                ];
                
                if (!availableCommands.includes(command)) {
                    console.warn(`⚠️ Comando ${command} no implementado en el backend de Tauri - Simulando`);
                    return Promise.resolve({ 
                        success: true, 
                        message: `Comando ${command} simulado - No implementado` 
                    });
                }
                
                console.log(`🔧 Ejecutando comando Tauri ${command} con argumentos:`, args);
                const result = await window.__TAURI__.invoke(command, args);
                console.log(`✅ Comando ${command} ejecutado exitosamente:`, result);
                return result;
                
            } catch (error) {
                console.error(`❌ Error ejecutando comando Tauri ${command}:`, error);
                
                // Si es un error de comando no encontrado, simular
                if (error.message && error.message.includes('command not found')) {
                    console.warn(`⚠️ Comando ${command} no encontrado en el backend - Simulando`);
                    return Promise.resolve({ 
                        success: true, 
                        message: `Comando ${command} simulado - No encontrado en backend` 
                    });
                }
                
                // Para otros errores, también simular para evitar que la app falle
                console.warn(`⚠️ Simulando comando Tauri ${command} debido a error: ${error.message}`);
                return Promise.resolve({ 
                    success: true, 
                    message: `Comando ${command} simulado debido a error: ${error.message}` 
                });
            }
        } else {
            console.warn(`Simulando comando Tauri ${command} en navegador web`);
            return Promise.resolve({ 
                success: true, 
                message: `Comando ${command} simulado en web` 
            });
        }
    }
    
    static async notifyIncomingCall(callData) {
        const command = 'handle_incoming_call';
        const args = {
            caller_name: callData.caller_name || 'Usuario desconocido',
            caller_image: callData.caller_image || '',
            call_id: callData.call_id || '',
            timestamp: Date.now()
        };
        
        console.log('📞 Notificando llamada entrante a Tauri:', args);
        
        try {
            const response = await this.invokeTauriCommand(command, args);
            console.log('✅ Respuesta de Tauri:', response);
            return response;
        } catch (error) {
            console.error('❌ Error notificando a Tauri:', error);
            return { success: false, error: error.message };
        }
    }
    
    static async notifyCallEnded(callData) {
        const command = 'handle_call_ended';
        const args = {
            call_id: callData.call_id || '',
            duration: callData.duration || 0,
            status: callData.status || 'unknown',
            timestamp: Date.now()
        };
        
        console.log('📞 Notificando fin de llamada a Tauri:', args);
        
        try {
            const response = await this.invokeTauriCommand(command, args);
            console.log('✅ Llamada finalizada notificada a Tauri:', response);
            return response;
        } catch (error) {
            console.error('❌ Error notificando fin de llamada a Tauri:', error);
            return { success: false, error: error.message };
        }
    }
    
    static async requestWindowFocus() {
        if (this.isTauriApp()) {
            try {
                // Primero intentar mostrar la ventana si está minimizada
                await this.invokeTauriCommand('show_window');
                // Luego enfocar la ventana
                await this.invokeTauriCommand('set_focus');
                // Finalmente traer la ventana al frente
                await this.invokeTauriCommand('bring_to_front');
                console.log('🔍 Ventana activada y enfocada por Tauri');
            } catch (error) {
                console.error('❌ Error enfocando ventana:', error);
                // Fallback: intentar solo con el comando básico
                try {
                    await this.invokeTauriCommand('focus_window');
                } catch (fallbackError) {
                    console.error('❌ Error en fallback de enfoque:', fallbackError);
                }
            }
        }
    }
    
    static async showSystemNotification(title, body, icon = null) {
        if (this.isTauriApp()) {
            try {
                // Método 1: Notificaciones nativas del navegador (más confiable)
                if ('Notification' in window) {
                    try {
                        if (Notification.permission === 'granted') {
                            const notification = new Notification(title, { 
                                body, 
                                icon: icon || undefined,
                                requireInteraction: true,
                                tag: 'tauri-call-notification'
                            });
                            
                            // Auto-cerrar después de 5 segundos
                            setTimeout(() => notification.close(), 5000);
                            
                            // Al hacer clic en la notificación, enfocar la ventana
                            notification.onclick = () => {
                                window.focus();
                                notification.close();
                            };
                            
                            console.log('🔔 Notificación mostrada con API nativa del navegador');
                            return;
                        } else if (Notification.permission === 'default') {
                            // Solicitar permiso
                            const permission = await Notification.requestPermission();
                            if (permission === 'granted') {
                                this.showSystemNotification(title, body, icon);
                                return;
                            }
                        }
                    } catch (browserError) {
                        console.warn('⚠️ Error con notificaciones del navegador:', browserError);
                    }
                }
                
                // Método 2: Usar API nativa de notificaciones de Tauri
                if (window.__TAURI__ && window.__TAURI__.notification) {
                    try {
                        await window.__TAURI__.notification.sendNotification({
                            title,
                            body,
                            icon: icon || undefined
                        });
                        console.log('🔔 Notificación mostrada con API de Tauri');
                        return;
                    } catch (nativeError) {
                        console.warn('⚠️ Error con API de Tauri:', nativeError);
                    }
                }
                
                // Método 3: Usar comando personalizado (fallback)
                await this.invokeTauriCommand('show_notification', {
                    title,
                    body,
                    icon
                });
                console.log('🔔 Notificación del sistema mostrada');
            } catch (error) {
                console.error('❌ Error mostrando notificación:', error);
            }
        } else {
            // Fallback para navegador web
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, { body, icon });
            }
        }
    }
    
    static async minimizeWindow() {
        if (this.isTauriApp()) {
            try {
                await this.invokeTauriCommand('minimize_window');
                console.log('📦 Ventana minimizada');
            } catch (error) {
                console.error('❌ Error minimizando ventana:', error);
            }
        }
    }
    
    static async maximizeWindow() {
        if (this.isTauriApp()) {
            try {
                await this.invokeTauriCommand('maximize_window');
                console.log('📈 Ventana maximizada');
            } catch (error) {
                console.error('❌ Error maximizando ventana:', error);
            }
        }
    }
    
    static async isWindowMinimized() {
        if (this.isTauriApp()) {
            try {
                const result = await this.invokeTauriCommand('is_minimized');
                return result.is_minimized || false;
            } catch (error) {
                console.error('❌ Error verificando estado de ventana:', error);
                return false;
            }
        }
        return false;
    }
    
    static async activateAndFocusWindow() {
        if (this.isTauriApp()) {
            try {
                console.log('🎯 Activando ventana con APIs nativas del navegador...');
                
                // Método 1: APIs nativas del navegador (más confiable)
                try {
                    // Enfocar la ventana actual
                    window.focus();
                    console.log('✅ Ventana enfocada con window.focus()');
                    
                    // Mover la ventana a una posición visible
                    window.moveTo(0, 0);
                    console.log('✅ Ventana movida a posición visible');
                    
                    // Redimensionar para asegurar que sea visible
                    const screenWidth = screen.width || 1920;
                    const screenHeight = screen.height || 1080;
                    window.resizeTo(Math.min(screenWidth, 1200), Math.min(screenHeight, 800));
                    console.log('✅ Ventana redimensionada');
                    
                    // Intentar traer al frente con múltiples métodos
                    window.blur();
                    window.focus();
                    console.log('✅ Ventana traída al frente');
                    
                    // Scroll hacia arriba para asegurar visibilidad
                    window.scrollTo(0, 0);
                    console.log('✅ Página scrolleada al inicio');
                    
                    return { success: true, method: 'browser_native' };
                    
                } catch (browserError) {
                    console.warn('⚠️ Error con APIs nativas del navegador:', browserError);
                }
                
                // Método 2: Intentar con Tauri si está disponible
                if (window.__TAURI__ && window.__TAURI__.window) {
                    try {
                        const currentWindow = window.__TAURI__.window.getCurrent();
                        await currentWindow.show();
                        await currentWindow.setFocus();
                        console.log('✅ Ventana activada con API de Tauri');
                        return { success: true, method: 'tauri_api' };
                    } catch (tauriError) {
                        console.warn('⚠️ Error con API de Tauri:', tauriError);
                    }
                }
                
                // Método 3: Comandos personalizados (fallback)
                await this.invokeTauriCommand('set_focus');
                await this.invokeTauriCommand('bring_to_front');
                
                console.log('🎯 Ventana activada con métodos de fallback');
                return { success: true, method: 'fallback_commands' };
                
            } catch (error) {
                console.error('❌ Error activando ventana:', error);
                return { success: false, error: error.message };
            }
        }
        return { success: false, error: 'No es una aplicación Tauri' };
    }
    
    static async requestNotificationPermission() {
        if ('Notification' in window) {
            try {
                if (Notification.permission === 'default') {
                    const permission = await Notification.requestPermission();
                    console.log('🔔 Permiso de notificaciones:', permission);
                    return permission === 'granted';
                }
                return Notification.permission === 'granted';
            } catch (error) {
                console.error('❌ Error solicitando permiso de notificaciones:', error);
                return false;
            }
        }
        return false;
    }
    
    static async forceWindowActivation() {
        console.log('🚀 Forzando activación de ventana con todos los métodos disponibles...');
        
        try {
            // Método 1: APIs nativas del navegador
            window.focus();
            window.moveTo(0, 0);
            window.resizeTo(1200, 800);
            window.scrollTo(0, 0);
            
            // Método 2: Intentar abrir una ventana pequeña y cerrarla (truco para activar)
            const popup = window.open('', '_blank', 'width=1,height=1,left=0,top=0');
            if (popup) {
                popup.close();
            }
            
            // Método 3: Disparar eventos
            window.dispatchEvent(new Event('focus'));
            window.dispatchEvent(new Event('click'));
            
            console.log('✅ Activación forzada completada');
            return { success: true };
        } catch (error) {
            console.error('❌ Error en activación forzada:', error);
            return { success: false, error: error.message };
        }
    }
}