<?php
/**
 * Configuración global para el sistema de notificaciones de excusas médicas
 * Este archivo debe incluirse en el layout principal de la aplicación
 */

// Solo cargar si el usuario tiene permisos de excusas médicas
if (isset($login_user) && $login_user) {
    $excuse_permission = get_array_value($login_user->permissions, "excuse_permission");
    $shouldLoad = $excuse_permission === "provider" || 
                  $excuse_permission === "all" || 
                  $login_user->is_admin;
    
    if ($shouldLoad) {
        // Cargar el JavaScript de notificaciones de excusas de forma asíncrona
        echo '<script>
        // Cargar helper de zona horaria primero
        setTimeout(function() {
            if (typeof $ !== "undefined") {
                $.getScript("' . base_url('assets/js/timezone_helper.js') . '", function() {
                    console.log("✅ Helper de zona horaria cargado");
                    
                    // Luego cargar controlador global de sonidos
                    $.getScript("' . base_url('assets/js/global_sound_controller.js') . '", function() {
                        console.log("✅ Controlador global de sonidos cargado");
                        
                        // Finalmente cargar notificaciones de excusas
                        $.getScript("' . base_url('assets/js/global_excuse_notifications.js') . '", function() {
                            if (window.GlobalExcuseNotifications) {
                                window.GlobalExcuseNotifications.init();
                            }
                        });
                    });
                });
            }
        }, 3000); // Delay de 3 segundos para no interferir con otros sistemas
        </script>';
        
        // Configuración JSON
        echo '<script type="application/json" data-excuse-config>
        {
            "userId": ' . json_encode($login_user->id ?? null) . ',
            "apiEndpoint": ' . json_encode(get_uri('excuse/check_new_excuses')) . ',
            "csrfToken": ' . json_encode(csrf_token()) . ',
            "csrfHash": ' . json_encode(csrf_hash()) . ',
            "baseUrl": ' . json_encode(get_uri()) . ',
            "userRole": ' . json_encode($excuse_permission) . ',
            "isAdmin": ' . json_encode($login_user->is_admin ?? false) . ',
            "serverTimezone": ' . json_encode(app_timezone()) . '
        }
        </script>';
    }
}
?>
