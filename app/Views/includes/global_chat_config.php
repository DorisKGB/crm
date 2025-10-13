<?php
/**
 * Configuración global para el sistema de notificaciones de chat
 * Este archivo debe incluirse en el layout principal de la aplicación
 */

// Solo cargar si el usuario tiene permisos de mensajes
if (isset($login_user) && $login_user && get_array_value($login_user->permissions, "message_permission") !== "no") {
    // Cargar el JavaScript de notificaciones globales de forma asíncrona
    echo '<script>
    // Cargar notificaciones de chat de forma asíncrona para no interferir
    setTimeout(function() {
        if (typeof $ !== "undefined") {
            console.log("🔄 Cargando sistema de notificaciones de chat V2...");
            $.getScript("' . base_url('assets/js/global_chat_notifications_v2.js') . '", function() {
                console.log("📦 Script V2 cargado, inicializando...");
                if (window.GlobalChatNotificationsV2) {
                    window.GlobalChatNotificationsV2.init();
                } else {
                    console.error("❌ GlobalChatNotificationsV2 no disponible después de cargar el script");
                }
            }).fail(function() {
                console.error("❌ Error cargando script de notificaciones de chat V2");
            });
        } else {
            console.error("❌ jQuery no disponible para cargar notificaciones de chat");
        }
    }, 1000); // Reducido a 1 segundo para mejor debugging
    </script>';
    
    // Configuración JSON
    echo '<script type="application/json" data-chat-config>
    {
        "userId": ' . json_encode($login_user->id ?? null) . ',
        "apiEndpoint": ' . json_encode(get_uri('messages/check_new_messages')) . ',
        "csrfToken": ' . json_encode(csrf_token()) . ',
        "csrfHash": ' . json_encode(csrf_hash()) . ',
        "baseUrl": ' . json_encode(base_url()) . '
    }
    </script>';
}
?>
