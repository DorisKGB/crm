<?php
/**
 * Inclusión automática del sistema de notificaciones de escritorio
 * Este archivo debe incluirse en el layout principal de la aplicación
 */

// Solo incluir si el usuario está logueado y tiene permisos de mensajes
if (isset($login_user) && $login_user && get_array_value($login_user->permissions, "message_permission") !== "no") {
    echo view('includes/desktop_notifications_config');
}
?>
