/**
 * Configuración global de la aplicación
 * Este archivo se carga automáticamente en todas las páginas
 */

// Configuración global de la aplicación
window.AppConfig = window.AppConfig || {};

// Configuración de notificaciones de chat
window.AppConfig.chatNotifications = {
    enabled: true,
    checkInterval: 10000, // 10 segundos
    soundEnabled: true // Se inicializará dinámicamente
};

// Cargar sistema de notificaciones globales de chat
$(document).ready(function() {
    // Solo cargar si no estamos en la página del chat activo
    if (!document.querySelector('#js-chat-messages-container')) {
        // Cargar el sistema de notificaciones globales
        if (typeof window.GlobalChatNotifications !== 'undefined') {
            window.GlobalChatNotifications.init();
        } else {
            // Si no está disponible, cargar dinámicamente
            $.getScript(base_url + 'assets/js/global_chat_notifications.js', function() {
                if (window.GlobalChatNotifications) {
                    window.GlobalChatNotifications.init();
                }
            });
        }
    }
});
