/**
 * Inicialización automática del sistema de notificaciones de escritorio
 * Este archivo se carga automáticamente en todas las páginas
 */

(function() {
    'use strict';
    
    // Verificar si ya se cargó el script
    if (window.DesktopNotificationsLoaded) {
        return;
    }
    
    // Función para cargar el script de notificaciones de escritorio
    function loadDesktopNotifications() {
        // Verificar si el script ya está cargado
        if (document.querySelector('script[src*="desktop_notifications.js"]')) {
            return;
        }
        
        // Crear elemento script
        var script = document.createElement('script');
        script.src = window.baseUrl + 'assets/js/desktop_notifications.js';
        script.async = true;
        script.onload = function() {
            console.log('✅ Sistema de notificaciones de escritorio cargado');
        };
        script.onerror = function() {
            console.log('❌ Error cargando sistema de notificaciones de escritorio');
        };
        
        // Agregar al head
        document.head.appendChild(script);
    }
    
    // Cargar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadDesktopNotifications);
    } else {
        loadDesktopNotifications();
    }
    
    // Marcar como cargado
    window.DesktopNotificationsLoaded = true;
})();
