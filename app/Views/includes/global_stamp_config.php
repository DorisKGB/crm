<?php
/**
 * Configuración global para el sistema de notificaciones de timbres
 * Este archivo debe incluirse en el layout principal de la aplicación
 */

// Solo cargar si el usuario tiene permisos de timbres
if (isset($login_user) && $login_user) {
    // Obtener permisos de timbres
    $permissions = $login_user->permissions;
    $stamp_permission = get_array_value($permissions, "stamp_permission_v1");
    
    // Verificar si el usuario debe recibir notificaciones de timbres
    $shouldReceiveNotifications = false;
    if ($stamp_permission === 'provider' || 
        $stamp_permission === 'request' || 
        $stamp_permission === 'all' || 
        $login_user->is_admin) {
        $shouldReceiveNotifications = true;
    }
    
    if ($shouldReceiveNotifications) {
        // Cargar el JavaScript de notificaciones globales de forma asíncrona
        echo '<script>
        // Cargar notificaciones de timbres de forma asíncrona para no interferir
        setTimeout(function() {
            if (typeof $ !== "undefined") {
                $.getScript("' . base_url('assets/js/global_stamp_notifications.js') . '", function() {
                    if (window.GlobalStampNotifications) {
                        window.GlobalStampNotifications.init();
                    }
                });
            }
        }, 5000); // Delay de 5 segundos para no interferir con otros sistemas
        </script>';
        
        // Configuración JSON
        echo '<script type="application/json" data-stamp-config>
        {
            "userId": ' . json_encode($login_user->id ?? null) . ',
            "apiEndpoint": ' . json_encode(get_uri('stamp/check_new_stamps')) . ',
            "csrfToken": ' . json_encode(csrf_token()) . ',
            "csrfHash": ' . json_encode(csrf_hash()) . ',
            "baseUrl": ' . json_encode(base_url()) . ',
            "userRole": ' . json_encode($stamp_permission ?? 'no') . ',
            "isAdmin": ' . json_encode($login_user->is_admin ?? false) . '
        }
        </script>';
        
        // Elemento de audio específico para timbres
        echo '<audio id="stamp-notif-sound" src="' . base_url('assets/sounds/campana.mp3') . '" preload="auto" style="display: none;"></audio>';
    }
}
?>
