// ========== DETECTOR ROBUSTO DE TAURI ==========
// Este script se ejecuta temprano para detectar Tauri de manera más confiable

(function() {
    'use strict';
    
    // Función para detectar Tauri de manera robusta
    function detectTauriEnvironment() {
        console.log('🔍 Iniciando detección robusta de Tauri...');
        
        // Verificar si ya existe una detección previa
        if (window.__TAURI_DETECTED !== undefined) {
            console.log('✅ Tauri ya detectado previamente:', window.__TAURI_DETECTED);
            return window.__TAURI_DETECTED;
        }
        
        let isTauri = false;
        let detectionMethod = '';
        
        // Método 1: Verificar __TAURI__ estándar
        if (window.__TAURI__ && window.__TAURI__.invoke) {
            isTauri = true;
            detectionMethod = '__TAURI__ estándar';
        }
        // Método 2: Verificar metadatos de Tauri
        else if (window.__TAURI_METADATA__ || window.__TAURI_INTERNALS__) {
            isTauri = true;
            detectionMethod = 'metadatos Tauri';
        }
        // Método 3: Verificar user agent
        else if (navigator.userAgent.includes('Tauri') || 
                 navigator.userAgent.includes('tauri') ||
                 window.location.protocol === 'tauri:') {
            isTauri = true;
            detectionMethod = 'user agent/protocolo';
        }
        // Método 4: Verificar objetos alternativos
        else if (typeof window.tauri !== 'undefined' || 
                 typeof window.__tauri !== 'undefined') {
            isTauri = true;
            detectionMethod = 'objetos alternativos';
        }
        // Método 5: Verificar contexto padre (iframe)
        else if (window.parent !== window && 
                 (window.parent.__TAURI__ || window.parent.__TAURI_METADATA__)) {
            isTauri = true;
            detectionMethod = 'contexto padre (iframe)';
        }
        // Método 6: Verificar características específicas del entorno
        else if (window.location.href.includes('tauri://') ||
                 window.location.origin.includes('tauri') ||
                 document.title.includes('Tauri')) {
            isTauri = true;
            detectionMethod = 'características del entorno';
        }
        // Método 7: Verificar si estamos en un WebView de Tauri
        else if (window.chrome && window.chrome.runtime && 
                 window.chrome.runtime.id && 
                 window.chrome.runtime.id.includes('tauri')) {
            isTauri = true;
            detectionMethod = 'WebView Tauri';
        }
        
        // Almacenar resultado globalmente
        window.__TAURI_DETECTED = isTauri;
        window.__TAURI_DETECTION_METHOD = detectionMethod;
        
        console.log(`🎯 Detección de Tauri: ${isTauri ? 'SÍ' : 'NO'} (método: ${detectionMethod})`);
        
        // Log detallado para debug
        console.log('📊 Detalles de detección:');
        console.log('- window.__TAURI__:', !!window.__TAURI__);
        console.log('- window.__TAURI_METADATA__:', !!window.__TAURI_METADATA__);
        console.log('- window.__TAURI_INTERNALS__:', !!window.__TAURI_INTERNALS__);
        console.log('- window.tauri:', !!window.tauri);
        console.log('- window.__tauri:', !!window.__tauri);
        console.log('- navigator.userAgent:', navigator.userAgent);
        console.log('- window.location.protocol:', window.location.protocol);
        console.log('- window.location.href:', window.location.href);
        console.log('- window.parent !== window:', window.parent !== window);
        console.log('- document.title:', document.title);
        
        return isTauri;
    }
    
    // Función para esperar a que Tauri esté disponible
    function waitForTauri(maxAttempts = 50, interval = 100) {
        return new Promise((resolve) => {
            let attempts = 0;
            
            const checkTauri = () => {
                attempts++;
                
                if (detectTauriEnvironment()) {
                    console.log(`✅ Tauri detectado después de ${attempts} intentos`);
                    resolve(true);
                    return;
                }
                
                if (attempts >= maxAttempts) {
                    console.log(`⏰ Timeout: Tauri no detectado después de ${attempts} intentos`);
                    resolve(false);
                    return;
                }
                
                setTimeout(checkTauri, interval);
            };
            
            checkTauri();
        });
    }
    
    // Función para inicializar la detección
    function initTauriDetection() {
        console.log('🚀 Inicializando detección de Tauri...');
        
        // Detección inmediata
        const immediateDetection = detectTauriEnvironment();
        
        if (immediateDetection) {
            console.log('✅ Tauri detectado inmediatamente');
            return;
        }
        
        // Si no se detectó inmediatamente, esperar
        console.log('⏳ Tauri no detectado inmediatamente, esperando...');
        waitForTauri().then((detected) => {
            if (detected) {
                console.log('✅ Tauri detectado después de esperar');
                // Disparar evento personalizado para notificar a otros scripts
                window.dispatchEvent(new CustomEvent('tauri-detected', {
                    detail: { 
                        detected: true, 
                        method: window.__TAURI_DETECTION_METHOD 
                    }
                }));
            } else {
                console.log('❌ Tauri no detectado después de esperar');
                // Disparar evento personalizado para notificar a otros scripts
                window.dispatchEvent(new CustomEvent('tauri-detected', {
                    detail: { 
                        detected: false, 
                        method: 'none' 
                    }
                }));
            }
        });
    }
    
    // Ejecutar detección cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTauriDetection);
    } else {
        initTauriDetection();
    }
    
    // También ejecutar inmediatamente para casos donde el script se carga tarde
    initTauriDetection();
    
    // Exponer funciones globalmente para uso en otros scripts
    window.TauriDetection = {
        detect: detectTauriEnvironment,
        waitFor: waitForTauri,
        isDetected: () => window.__TAURI_DETECTED || false,
        getDetectionMethod: () => window.__TAURI_DETECTION_METHOD || 'unknown'
    };
    
})();
