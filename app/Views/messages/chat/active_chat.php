<div class="rise-chat-header box">
  <div class="box-content chat-back" id="js-back-to-chat-tabs">
    <i data-feather="chevron-left" class="icon-16"></i>
  </div>

  <div class="box-content chat-title">
    <?php
      $hide_online_icon = is_online_user($message_info->another_user_last_online) ? "" : " hide";

      $user_id = ($message_info->from_user_id == $login_user->id)
        ? $message_info->to_user_id
        : $message_info->from_user_id;

      // puntico online + user_id
      echo "<i id='js-active-chat-online-icon' class='online $hide_online_icon' data-user_id='$user_id'></i>";
    ?>
    <span class="chat-name">
      <?php echo ($message_info->another_user_id === $login_user->id)
        ? $message_info->user_name
        : $message_info->another_user_name; ?>
    </span>
  </div>

  <div class="box-content chat-call">
    <button id="js-call-btn" class="btn btn-sm btn-ghost-primary" style="border-radius:50%;" title="Iniciar llamada">
      <i data-feather="phone"></i>
    </button>
  </div>
</div>


<div class="rise-chat-body clearfix">
    <div id="js-chat-messages-container" class="clearfix"></div>
    <div id="js-chat-reply-indicator"></div>
</div>

<div class="rise-chat-footer">
    <div id="chat-reply-form-dropzone" class="post-dropzone">
        <?php echo form_open(get_uri("messages/reply/1"), array("id" => "chat-message-reply-form", "class" => "general-form", "role" => "form")); ?>


        <?php echo view("includes/dropzone_preview"); ?>    


        <input type="hidden" id="is_user_online" name="is_user_online" value="<?php echo is_online_user($message_info->another_user_last_online) ? 1 : 0; ?>">
        <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
        <input type="hidden" name="last_message_id" value="">
        <div class="chat-file-upload-icon">
            <?php
            echo view("includes/upload_button", array("upload_button_text" => ""));
            ?>   
        </div>
        <?php
        echo form_textarea(array(
            "id" => "js-chat-message-textarea",
            "name" => "reply_message",
            "data-rule-required" => true,
            "autofocus" => true,
            "data-msg-required" => "",
            "placeholder" => app_lang('write_a_message')
        ));
        ?>
        <span class="message-send-button"><i data-feather="send" class="icon-16"></i></span>

        <?php echo form_close(); ?>
    </div>
</div>
<style>
.rise-chat-header{
  display:flex;
  align-items:center;
  gap:8px;
}

/* Back y botón: fijos */
.chat-back, .chat-call { flex:0 0 auto; }

/* Título ocupa el espacio intermedio */
.chat-title{
  flex:1 1 auto;
  min-width:0;
  display:flex;               /* <- clave */
  align-items:center;         /* <- alinea el punto con el texto */
  gap:8px;
  font-weight:600;
}

/* Punto verde; por si tu CSS no lo hace ya */
.chat-title .online{
  display:inline-block;
  width:10px;
  height:10px;
  border-radius:50%;
  /* si ya tienes color en tu theme, puedes omitir esto */
  background:#28a745;
  vertical-align:middle;
}

/* Nombre con elipsis */
.chat-name{
  flex:1 1 auto;
  min-width:0;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.chat-call button{ padding:4px 8px; }

..edit-message-btn{
    color:#CCCCCC !important;
}
.edit-message-btn:hover {
    text-decoration: underline;
}

.edited-indicator {
    font-style: italic;
    color: #999;
}

.message-actions {
    margin-top: 5px;
    transition: opacity 0.2s;
}

.chat-msg:hover .message-actions {
    opacity: 1;
}
</style>



<script type="text/javascript">
    var isEditingSaving = false;
    var lastEditCheck = Date.now();
    
    $(document).ready(function () {
    $("#js-call-btn").on("click", function() {
        var userId   = $("#js-active-chat-online-icon").data("user_id");
        var userName = "<?php echo $message_info->another_user_name; ?>";
        var userAvatar = "<?php echo base_url("assets/images/avatar.jpg"); ?>";

        CallsBubbleSystem.initiateCall(userId, userName, userAvatar);
    });
});

    $(document).ready(function () {
        
        console.log("Pusher Config:", {
            enabled: "<?php echo get_setting('enable_chat_via_pusher'); ?>",
            notifications: "<?php echo get_setting('enable_push_notification'); ?>",
            key: "<?php echo get_setting('pusher_key'); ?>",
            cluster: "<?php echo get_setting('pusher_cluster'); ?>"
        });

        var textarea = document.querySelector('.rise-chat-footer textarea');
        textarea.addEventListener('keydown', autosizeRISEChatBox);
        function autosizeRISEChatBox() {
            var el = this;
            setTimeout(function () {
                if (el.scrollHeight < 110) {
                    $(".rise-chat-body").height(400 - el.scrollHeight);
                    el.style.cssText = 'height:' + el.scrollHeight + 'px';
                }
            });
        }




        loadMessages(1);
        $('.rise-chat-header').mousedown(handle_mousedown);
        $("#js-chat-message-textarea").keypress(function (e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                $("#chat-message-reply-form").submit();
                $(this).attr("style", "")
                return false;
            }
        });

        $("#chat-message-reply-form").appForm({
            isModal: false,
            showLoader: false,
            beforeAjaxSubmit: function (data) {

                //send the last message id
                $.each(data, function (index, obj) {
                    if (obj.name === "last_message_id") {
                        data[index]["value"] = $(".chat-msg").last().attr("data-message_id");
                    }
                });
                //clear message input box
                $("#js-chat-message-textarea").val("");
                $("#chat-message-reply-form").append('<div id="fast-loader" class="fast-line"></div>');
            },
            onSuccess: function (response) {
                if (window.formDropzone) {
                    window.formDropzone['chat-reply-form-dropzone'].removeAllFiles();
                }
                if (response.success) {
                    renderMessages(response.data);
                    $("#fast-loader").remove();
                }

            }
        });


        //set focus

        setTimeout(function () {
            $("#js-chat-message-textarea").focus();
        }, 200);

        $("#js-back-to-chat-tabs").click(function () {
            loadChatTabs();
        
            // Reset ambos timers
            if (window.activeChatChecker) {
                window.clearInterval(window.activeChatChecker);
            }
            if (window.editChecker) {
                window.clearInterval(window.editChecker);
            }
        });
        //bind scroll with chat messages and load more messages when scrolling on top
        var fatchNewData = true,
                topMessageId = 0;
        $("#js-chat-messages-container").scroll(function () {
            if ($(this).scrollTop() < 50 && fatchNewData) {
                fatchNewData = false;
                loadMoreMessages(function () {
                    fatchNewData = true; //reset the status so that it can call again
                });
            }
        });

        if ("<?php echo get_setting('enable_chat_via_pusher') ?>" && "<?php echo get_setting('enable_push_notification') ?>") {
            var pusherKey = "<?php echo get_setting("pusher_key"); ?>";
            var pusherCluster = "<?php echo get_setting("pusher_cluster"); ?>";

            var pusher = new Pusher(pusherKey, {
                cluster: pusherCluster,
                encrypted: true
            });

            var channel = pusher.subscribe("user_" + "<?php echo $login_user->id; ?>" + "_message_id_" + "<?php echo $message_id ?>" + "_channel");

            /*channel.bind('rise-chat-event',
                    function (data) {
                        $.ajax({
                            url: "<?php echo get_uri('messages/view_chat'); ?>",
                            type: "POST",
                            data: {
                                message_id: "<?php echo $message_id; ?>",
                            },
                            success: function (response) {
                                if (response) {
                                    $("#js-chat-messages-container").append(response);
                                    $("#js-chat-reply-indicator").html(" ");
                                    chatScrollToBottom();
                                }
                            }
                        });
                    });*/
                    
            channel.bind('rise-chat-event', function (data) {
                if (data.message_html) {
                    renderMessages(data.message_html);
                }
                
                // Actualizar badges inmediatamente cuando llega un mensaje
                if (typeof window.updateChatUnreadBadges === 'function') {
                    window.updateChatUnreadBadges();
                }
            });       

            channel.bind('rise-chat-typing-event',
                    function (data) {
                        $("#js-chat-reply-indicator").html(data);
                        chatScrollToBottom();

                        setTimeout(function () {
                            $("#js-chat-reply-indicator").html(" ");
                        }, 8000);
                    });
                    
            channel.bind('message-edited-event', function (data) {
                console.log("📝 Mensaje editado recibido:", data);
                var messageId = data.message_id;
                var newMessage = data.new_message;
                
                // Buscar y actualizar el mensaje
                var $messageElement = $('.js-chat-msg[data-message_id="' + messageId + '"] .message-content');
                if ($messageElement.length) {
                    // Actualizar contenido
                    $messageElement.html(newMessage);
                    
                    // Agregar indicador "Editado"
                    var $messageDiv = $messageElement.closest('.chat-msg');
                    var $actions = $messageDiv.find('.message-actions');
                    
                    if (!$actions.find('.edited-indicator').length) {
                        if ($actions.length) {
                            $actions.append('<span class="edited-indicator" style="font-style: italic; color: #999; margin-left: 10px;">Editado</span>');
                        } else {
                            $messageDiv.append('<div class="message-actions" style="font-size: 11px; margin-top: 5px;"><span class="edited-indicator" style="font-style: italic; color: #999;">Editado</span></div>');
                        }
                    }
                    
                    // Efecto visual
                    $messageElement.fadeOut(200).fadeIn(200);
                }
            });
        }

        $(".message-send-button").click(function () {
            $(this).trigger("submit");
        });

    });
    function handle_mousedown(e) {
        var dragging = {};
        dragging.pageX0 = e.pageX;
        dragging.pageY0 = e.pageY;
        dragging.offset0 = $(this).offset();
        function handleDragging(e) {
            var left = dragging.offset0.left + (e.pageX - dragging.pageX0);
            var top = dragging.offset0.top + (e.pageY - dragging.pageY0);
            $(".rise-chat-wrapper").offset({top: top, left: left});
        }

        function handleMouseup(e) {
            $('body').off('mousemove', handleDragging).off('mouseup', handleMouseup);
        }
        $('body').on('mouseup', handleMouseup).on('mousemove', handleDragging);
    }

    /*function chatScrollToBottom() {
        //scroll to bottom only if the foucs on textarea
        var $focused = $(':focus');
        if ($focused && $focused.is("textarea")) {
            $(".rise-chat-body").animate({scrollTop: 10000000}, 100);
        }
    }*/

    
    
    function chatScrollToBottom() {
        var chatBody = $(".rise-chat-body");
        chatBody.animate({scrollTop: chatBody[0].scrollHeight}, 300);
    }

    function loadMessages(firstLoad) {
        checkNewMessagesAutomatically();
        var message_id = "<?php echo $message_id; ?>";
        $.ajax({
            url: "<?php echo get_uri('messages/view_chat'); ?>",
            type: "POST",
            cache: false, // ← Deshabilitar cache
            data: {
                message_id: message_id,
                last_message_id: $(".js-chat-msg").last().attr("data-message_id"),
                is_first_load: firstLoad,
                another_user_id: $("#js-active-chat-online-icon").attr("data-user_id"),
                _t: Date.now() // ← Cache-buster
            },
            success: function (response) {
                if (response) {
                    renderMessages(response);
                    window.updateChatUnreadBadges();
                }
            }
        });
    }

    function loadMoreMessages(callback) {
        if ($("#js-chat-old-messages").attr("no-messages") === "1")
            return false; //there is no messages to show.

        var message_id = "<?php echo $message_id; ?>";

        $("#js-chat-old-messages").prepend("<div id='loading-more-chat-messages-" + message_id + "' class='inline-loader' >....<br></br></div>");

        $.ajax({
            url: "<?php echo get_uri('messages/view_chat'); ?>",
            type: "POST",
            data: {
                message_id: "<?php echo $message_id; ?>",
                top_message_id: $(".js-chat-msg").first().attr("data-message_id"),
                another_user_id: $("#js-active-chat-online-icon").attr("data-user_id")
            },
            success: function (response) {
                if (response) {
                    $("#js-chat-old-messages").prepend(response);
                    if (callback) {
                        callback(); //has more data?
                    }
                }

                //if we got empty message, then we'll add a flag to stop finding new messages for next calls.
                if (!$(response).find("#temp-script").remove().text()) {
                    $("#js-chat-old-messages").attr("no-messages", "1");
                }



                $('#loading-more-chat-messages-' + message_id).remove();

            }
        });
    }
    
    

    /*function renderMessages(html) {
        $("#js-chat-messages-container").append(html);
        chatScrollToBottom();
    }*/
    
       function renderMessages(html) {
        var $container = $("#js-chat-messages-container");
        var $newMessages = $(html);
        var hasNewContent = false;
        
        $newMessages.each(function() {
            var $msg = $(this).find('.js-chat-msg');
            var messageId = $msg.attr('data-message_id');
            
            if (messageId) {
                var $existing = $container.find('.js-chat-msg[data-message_id="' + messageId + '"]');
                if (!$existing.length) {
                    // Solo agregar mensajes realmente nuevos
                    $container.append(this);
                    hasNewContent = true;
                }
            }
        });
        
        // Solo hacer scroll si hay contenido nuevo
        if (hasNewContent) {
            chatScrollToBottom();
        }
    }


    //reset existing timmer and check new message after a certain time
    function checkNewMessagesAutomatically() {
        // Reset timers existentes
        if (window.activeChatChecker) {
            window.clearInterval(window.activeChatChecker);
        }
        if (window.editChecker) {
            window.clearInterval(window.editChecker);
        }
    
        // Polling para mensajes nuevos (cada 3 segundos)
        window.activeChatChecker = window.setInterval(function () {
            loadMessages();
        }, 3000);
        
        // Polling solo para ediciones (cada 1 segundo, más ligero)
        window.editChecker = window.setInterval(function () {
            checkForEditedMessages();
        }, 1000);
    }
    
    function checkForEditedMessages() {
        $.ajax({
            url: "<?php echo get_uri('messages/check_edited_messages'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                message_id: "<?php echo $message_id; ?>",
                last_edit_check: lastEditCheck
            },
            success: function(response) {
                if (response.success && response.edited_messages) {
                    response.edited_messages.forEach(function(edit) {
                        updateEditedMessage(edit.message_id, edit.new_content);
                    });
                    lastEditCheck = Date.now();
                }
            }
        });
    }
    
    function updateEditedMessage(messageId, newContent) {
        var $messageElement = $('.js-chat-msg[data-message_id="' + messageId + '"] .message-content');
        if ($messageElement.length) {
            // Solo actualizar si el contenido cambió
            var currentContent = $messageElement.html().trim();
            if (currentContent !== newContent.trim()) {
                
                // Actualizar sin efecto de parpadeo
                $messageElement.html(newContent);
                
                // Agregar indicador "Editado" solo si no existe
                var $messageDiv = $messageElement.closest('.chat-msg');
                var $actions = $messageDiv.find('.message-actions');
                if (!$actions.find('.edited-indicator').length) {
                    if ($actions.length) {
                        $actions.append('<span class="edited-indicator" style="font-style: italic; color: #999; margin-left: 10px;">Editado</span>');
                    } else {
                        $messageDiv.append('<div class="message-actions" style="font-size: 11px; margin-top: 5px;"><span class="edited-indicator" style="font-style: italic; color: #999;">Editado</span></div>');
                    }
                }
                
            }
        }
    }


    //send typing status to pusher
    if ("<?php echo get_setting('enable_chat_via_pusher') ?>" && "<?php echo get_setting('enable_push_notification') ?>") {
        addKeyup();
        function addKeyup() {
            $("#chat-message-reply-form").one('keyup', function (e) {
                $.ajax({
                    url: '<?php echo get_uri("messages/send_typing_indicator_to_pusher"); ?>',
                    type: "POST",
                    data: {message_id: "<?php echo $message_id ?>"}
                });

                setTimeout(addKeyup, 10000);
            });
        }
    }
    
    // CSS para las acciones de mensaje
    $('<style>')
        .text(`
            .chat-msg:hover .message-actions { opacity: 1 !important; }
            .edit-message-btn:hover { text-decoration: underline; }
            .edit-message-modal { 
                position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                background: rgba(0,0,0,0.5); z-index: 9999; 
                display: flex; align-items: center; justify-content: center; 
            }
        `)
        .appendTo('head');
    
    // Manejar click en botón editar
    $('body').on('click', '.edit-message-btn', function(e) {
        e.preventDefault();
        // Prevenir si ya hay un modal abierto
        if ($('.edit-message-modal').length > 0) return;
    
        var messageId = $(this).data('message-id');
        var $messageContent = $(this).closest('.chat-msg').find('.message-content');
        var currentText = $messageContent.text().trim();
        
        var editHtml = `
            <div class="edit-message-modal">
                <div style="background: white; padding: 20px; border-radius: 5px; width: 400px; max-width: 90%;">
                    <h5>Editar mensaje</h5>
                    <textarea id="edit-message-text" style="width: 100%; height: 100px; border: 1px solid #ccc; 
                             padding: 10px; border-radius: 3px; resize: vertical;">${currentText}</textarea>
                    <div style="margin-top: 10px; text-align: right;">
                        <button id="cancel-edit-btn" class="btn btn-secondary btn-sm" style="margin-right: 5px;">Cancelar</button>
                        <button id="save-edit-btn" class="btn btn-primary btn-sm" data-message-id="${messageId}">Guardar</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(editHtml);
        $('#edit-message-text').focus();
    });
    
    // Guardar edición
   $('body').on('click', '#save-edit-btn', function() {
        if (isEditingSaving) return;
        
        var $btn = $(this);
        var messageId = $btn.data('message-id');
        var newText = $('#edit-message-text').val().trim();
        
        if (!newText) {
            alert('El mensaje no puede estar vacío');
            return;
        }
        
        isEditingSaving = true;
        $btn.prop('disabled', true).text('Guardando...');
        
        $.ajax({
            url: "<?php echo get_uri('messages/edit_message'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                message_id: messageId,
                new_message: newText
            },
            success: function(response) {
                if (response.success) {
                    $('.edit-message-modal').remove();
                    
                    // Actualizar inmediatamente para quien edita
                    var $messageContent = $('.js-chat-msg[data-message_id="' + messageId + '"] .message-content');
                    if ($messageContent.length) {
                        $messageContent.html(newText.replace(/\n/g, '<br>'));
                        
                        var $messageDiv = $messageContent.closest('.chat-msg');
                        var $actions = $messageDiv.find('.message-actions');
                        if (!$actions.find('.edited-indicator').length) {
                            if ($actions.length) {
                                $actions.append('<span class="edited-indicator" style="font-style: italic; color: #999; margin-left: 10px;">Editado</span>');
                            } else {
                                $messageDiv.append('<div class="message-actions" style="font-size: 11px; margin-top: 5px;"><span class="edited-indicator" style="font-style: italic; color: #999;">Editado</span></div>');
                            }
                        }
                    }
                    
                } else {
                    alert(response.message || 'Error al editar mensaje');
                }
            },
            error: function() {
                alert('Error de conexión');
            },
            complete: function() {
                isEditingSaving = false;
                $btn.prop('disabled', false).text('Guardar');
            }
        });
    });
    
    // Cancelar edición
    $('body').on('click', '#cancel-edit-btn', function() {
        $('.edit-message-modal').remove();
    });

</script>  
