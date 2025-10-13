<?php
/**
 * Inclusión automática del sistema de notificaciones de excusas médicas
 * Este archivo debe incluirse en el layout principal de la aplicación
 */

// Solo incluir si el usuario está logueado y tiene permisos de excusas
if (isset($login_user) && $login_user) {
    $excuse_permission = get_array_value($login_user->permissions, "excuse_permission");
    $shouldLoad = $excuse_permission === "provider" || 
                  $excuse_permission === "all" || 
                  $login_user->is_admin;
    
    if ($shouldLoad) {
        echo view('includes/global_excuse_config');
    }
}
?>
