<?php
if ($messages) {

    foreach ($messages as $message) {
        $online = "";
        if ($message->last_online && is_online_user($message->last_online)) {
            $online = "<i class='online'></i>";
        }

        $status = "";
        $last_message_from = $message->from_user_id;
        if ($message->last_from_user_id) {
            $last_message_from = $message->last_from_user_id;
        }
        
        if ($message->status === "unread" && $last_message_from != $login_user->id) {
            $status = "unread";
        }
        
        // Usar unread_count que viene directamente de la consulta del modelo
        $unread_count = isset($message->unread_count) ? $message->unread_count : 0;
        ?>
        
        <div class='js-message-row message-row <?php echo $status; ?>' data-id='<?php echo $message->id; ?>' data-index='<?php echo $message->id; ?>' data-from-user-id='<?php echo $message->from_user_id; ?>'>
            <div class="d-flex">
                <div class='flex-shrink-0'>
                    <span class='avatar avatar-xs' style="position: relative;">
                        <img src='<?php echo get_avatar($message->user_image); ?>' />
                        <?php echo $online; ?>
                        <?php 
                        $unread_count = isset($message->unread_count) ? $message->unread_count : 0;
                        if ($unread_count > 0) { 
                        ?>
                            <span class="chat-unread-badge"><?php echo $unread_count; ?></span>
                        <?php } ?>
                    </span>
                </div>
                <div class='w-100 ps-2'>
                    <div class='mb5'>
                        <strong><?php echo $message->user_name; ?></strong>
                        <span class='text-off float-end time'><?php echo format_to_relative_time($message->message_time); ?></span>
                    </div>

                    <!-- Badges para Provider o Clínicas -->
                    <div class="mb-1 clinic-badges">
                        <?php if (isset($message->is_provider) && $message->is_provider): ?>
                            <span class="badge bg-success badge-sm">
                                <i class="fa fa-user-md me-1"></i>Provider
                            </span>
                        <?php elseif (isset($message->user_clinics) && is_array($message->user_clinics) && !empty($message->user_clinics)): ?>
                            <?php 
                            try {
                                $colors = ['bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                                $clinics_to_show = array_slice($message->user_clinics, 0, 3);
                                $has_more = count($message->user_clinics) > 3;
                                ?>
                                <?php foreach ($clinics_to_show as $index => $clinic): ?>
                                    <?php if (isset($clinic->name)): ?>
                                        <span class="badge <?php echo $colors[$index % count($colors)]; ?> badge-sm me-1">
                                            <?php echo esc($clinic->name); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if ($has_more): ?>
                                    <span class="badge bg-dark badge-sm" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="<?php echo implode(', ', array_map(function($c) { return isset($c->name) ? $c->name : 'N/A'; }, $message->user_clinics)); ?>">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </span>
                                <?php endif; ?>
                            <?php 
                            } catch (\Exception $e) {
                                // En caso de error en la vista, no mostrar badges
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                    <?php echo $message->subject; ?>
                </div>
            </div>
        </div>

        <?php
    }
} else {
    ?>

    <div class="chat-no-messages text-off text-center">
        <i data-feather="message-circle" height="4rem" width="4rem"></i><br />
        <?php echo app_lang("no_messages_text"); ?>
    </div>

<?php } ?>

<script>
    
    console.log("=== DATOS DE MENSAJES ===");
<?php foreach ($messages as $message) { ?>
console.log("Mensaje ID: <?php echo $message->id; ?>, From User ID: <?php echo $message->from_user_id; ?>, Unread Count: <?php echo isset($message->unread_count) ? $message->unread_count : 'UNDEFINED'; ?>");
<?php } ?>


$(document).ready(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
    //trigger the users/clients list tab if there is no messages
<?php if (!$messages) { ?>
    setTimeout(function () {
        if ($("#chat-users-tab-button").length) {
            $("#chat-users-tab-button a").trigger("click");
        } else {
            $("#chat-clients-tab-button a").trigger("click");
        }
    }, 500);
<?php } ?>
    
    // Ejecutar siempre, no solo cuando no hay mensajes
    setTimeout(function() {
        if (typeof updateChatUnreadBadges === 'function') {
            window.updateChatUnreadBadges();
        }
    }, 1000);
});


</script>

<style>
.chat-unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    color:#fff !important;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    z-index: 10;
    min-width: 18px;
}
.avatar {
    position: relative;
}
.unread-inline {
  display: inline-block;
  margin-left: 8px;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 11px;
  line-height: 1;
  font-weight: 600;
  background: #eef3ff;   /* suave */
  color: red !important;        /* azul discreto */
  vertical-align: baseline;
}
</style>