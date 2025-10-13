<?php
/**
 * Configuración del sistema de notificaciones de escritorio
 * Incluye scripts y configuración necesaria
 */

// Obtener configuración del usuario
$desktop_notifications_config = array(
    'userId' => $login_user->id,
    'baseUrl' => base_url(),
    'csrfToken' => csrf_token(),
    'csrfHash' => csrf_hash()
);
?>

<!-- Configuración de notificaciones de escritorio -->
<script type="text/javascript">
    // Configuración global para notificaciones de escritorio
    window.DesktopNotificationsConfig = <?php echo json_encode($desktop_notifications_config); ?>;
</script>

<!-- Script de inicialización automática -->
<script src="<?php base_url('assets/js/desktop_notifications_init.js'); ?>"></script>
