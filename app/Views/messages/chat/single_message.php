<?php
$files = unserialize($reply_info->files);
$total_files = count($files);
$download_caption = "";
if ($total_files) {
    $download_lang = app_lang('download');
    if ($total_files > 1) {
        $download_lang = sprintf(app_lang('download_files'), $total_files);
    }
    $download_caption = anchor(get_uri("messages/download_message_files/" . $reply_info->id), "<i data-feather='paperclip' class='icon-16'></i>" . $download_lang, array("class" => "", "title" => $download_lang));
}

$message_class = "m-row-" . $reply_info->id;

// Verificar si el mensaje puede editarse (solo el autor y dentro de 10 minutos)
$can_edit = false;
if ($reply_info->from_user_id === $login_user->id) {
    $created_time = strtotime($reply_info->created_at);
    $current_time = time();
    $time_diff = ($current_time - $created_time) / 60; // diferencia en minutos
    $can_edit = $time_diff <= 10;
}

if ($reply_info->from_user_id === $login_user->id) {
    ?>
    <div class="chat-me <?php echo $message_class; ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="chat-msg js-chat-msg" data-message_id="<?php echo $reply_info->id; ?>">
                    <div class="message-content">
                        <?php
                        echo nl2br(link_it(process_images_from_content($reply_info->message)));
                        if ($download_caption) {
                            echo view("includes/timeline_preview", array("files" => $files, "is_message_row" => true));
                            echo $download_caption;
                        }
                        ?>
                    </div>
                    
                    <!-- Acciones del mensaje -->
                    <div class="message-actions" style="font-size: 11px; margin-top: 5px;">
                        <?php if ($can_edit) { ?>
                            <span class="edit-message-btn" data-message-id="<?php echo $reply_info->id; ?>" 
                                  style="cursor: pointer; color: #CCC; margin-right: 10px;">
                                <i data-feather="edit-2" class="icon-12"></i> Editar
                            </span>
                        <?php } ?>
                        <?php if ($reply_info->edited_at) { ?>
                            <span class="edited-indicator" style="font-style: italic; color: #999;">Editado</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="chat-other <?php echo $message_class; ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="avatar-xs avatar mr10">
                    <?php
                    $avatar = get_avatar($reply_info->user_image);
                    if ($reply_info->user_type == "client") {
                        echo get_client_contact_profile_link($reply_info->from_user_id, " <img alt='...' src='" . $avatar . "' /> ", array("class" => "dark strong"));
                    } else {
                        echo get_team_member_profile_link($reply_info->from_user_id, " <img alt='...' src='" . $avatar . "' /> ", array("class" => "dark strong"));
                    }
                    ?>
                </div>
                <div class="chat-msg js-chat-msg" data-message_id="<?php echo $reply_info->id ?>">
                    <div class="message-content">
                        <?php
                        echo nl2br(link_it(process_images_from_content($reply_info->message)));
                        if ($download_caption) {
                            echo view("includes/timeline_preview", array("files" => $files, "is_message_row" => true));
                            echo $download_caption;
                        }
                        ?>
                    </div>
                    
                    <?php if ($reply_info->edited_at) { ?>
                        <div class="message-actions" style="font-size: 11px; color: #666; margin-top: 5px;">
                            <span class="edited-indicator" style="font-style: italic; color: #999;">Editado</span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script class="temp-script33">
    //don't show duplicate messages
    $("<?php echo '.' . $message_class; ?>:first").nextAll("<?php echo '.' . $message_class; ?>").remove();
</script>