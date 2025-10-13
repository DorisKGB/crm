/**
 * Inicialización automática del sistema de notificaciones globales de chat
 * Este archivo se carga automáticamente en todas las páginas
 */

// Esperar a que el DOM esté listo
$(document).ready(function() {
    // Solo inicializar si no estamos en la página del chat activo
    if (!document.querySelector('#js-chat-messages-container')) {
        // Verificar si el sistema de notificaciones globales está disponible
        if (window.GlobalChatNotifications) {
            window.GlobalChatNotifications.init();
        } else {
            console.warn('Sistema de notificaciones globales de chat no disponible');
        }
    }
});
