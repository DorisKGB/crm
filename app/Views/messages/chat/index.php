<?php
//load chat ui if chat module is enabled

$can_chat = can_access_messages_module();

if (get_setting("module_chat") && $can_chat) {
    ?>
    <div id="js-init-chat-icon" class="init-chat-icon">
        <!-- data-type= open/close/unread -->
        <span id="js-chat-min-icon" data-type="open" class="chat-min-icon"><i data-feather="message-circle" class="icon-18"></i></span>
    </div>

    <div id="js-rise-chat-wrapper" class="rise-chat-wrapper hide"></div>

    <script type="text/javascript">
        
        window._chatNotify = window._chatNotify || {
          lastTotal: 0,      // último total de no leídos que ya notificamos
          lastPlayAt: 0,     // última vez que sonó (ms)
          minGapMs: 4000     // separador mínimo entre sonidos (4s)
        };

        $(document).ready(function () {
            
            document.addEventListener('click', function() {
                // Primer click permite audio automático
            }, { once: true });

            chatIconContent = {
                "open": "<i data-feather='message-circle' class='icon-18'></i>",
                "close": "<span class='chat-close'>&times;</span>",
                "unread": ""
            };

            //we'll wait for 15 sec after clicking on the unread icon to see more notifications again.

            setChatIcon = function (type, count) {

                //don't show count if the data-prevent-notification-count is 1
                if ($("#js-chat-min-icon").attr("data-prevent-notification-count") === "1" && type === "unread") {
                    return false;
                }


                $("#js-chat-min-icon").attr("data-type", type).html(count ? count : chatIconContent[type]);

                if (type === "open") {
                    $("#js-rise-chat-wrapper").addClass("hide"); //hide chat box
                    $("#js-init-chat-icon").removeClass("has-message");
                } else if (type === "close") {
                    $("#js-rise-chat-wrapper").removeClass("hide"); //show chat box
                    $("#js-init-chat-icon").removeClass("has-message");
                } else if (type === "unread") {
                    $("#js-init-chat-icon").addClass("has-message");
                }

            };

            changeChatIconPosition = function (type) {
                if (type === "close") {
                    $("#js-init-chat-icon").addClass("move-chat-icon");
                } else if (type === "open") {
                    $("#js-init-chat-icon").removeClass("move-chat-icon");
                }
            };

            //is there any active chat? open the popup
            //otherwise show the chat icon only
            var activeChatId = getCookie("active_chat_id"),
                    isChatBoxOpen = getCookie("chatbox_open"),
                    $chatIcon = $("#js-init-chat-icon");


            $chatIcon.click(function () {
                $("#js-rise-chat-wrapper").html("");

                window.updateLastMessageCheckingStatus();

                var $chatIcon = $("#js-chat-min-icon");

                if ($chatIcon.attr("data-type") === "unread") {
                    $chatIcon.attr("data-prevent-notification-count", "1");

                    //after clicking on the unread icon, we'll wait 11 sec to show more notifications again.
                    setTimeout(function () {
                        $chatIcon.attr("data-prevent-notification-count", "0");
                    }, 11000);
                }

                var windowSize = window.matchMedia("(max-width: 767px)");

                if ($chatIcon.attr("data-type") !== "close") {
                    //have to reload
                    setTimeout(function () {
                        loadChatTabs();
                        updateChatUnreadBadges(); // Agregar aquí
                    }, 200);
                    setChatIcon("close"); //show close icon
                    setCookie("chatbox_open", "1");
                    if (windowSize.matches) {
                        changeChatIconPosition("close");
                    }
                } else {
                    //have to close the chat box
                    setChatIcon("open"); //show open icon
                    setCookie("chatbox_open", "");
                    setCookie("active_chat_id", "");
                    if (windowSize.matches) {
                        changeChatIconPosition("open");
                    }
                }

                if (window.activeChatChecker) {
                    window.clearInterval(window.activeChatChecker);
                }

                if (typeof window.placeCartBox === "function") {
                    window.placeCartBox();
                }
                
                feather.replace();

            });

            //open chat box
            if (isChatBoxOpen) {

                if (activeChatId) {
                    getActiveChat(activeChatId);
                } else {
                    loadChatTabs();
                }
            }

            var windowSize = window.matchMedia("(max-width: 767px)");
            if (windowSize.matches) {
                if (isChatBoxOpen) {
                    $("#js-init-chat-icon").addClass("move-chat-icon");
                }
            }




            $('body #js-rise-chat-wrapper').on('click', '.js-message-row', function () {
                getActiveChat($(this).attr("data-id"));
            });

            $('body #js-rise-chat-wrapper').on('click', '.js-message-row-of-team-members-tab', function () {
                getChatlistOfUser($(this).attr("data-id"), "team_members");
            });

            $('body #js-rise-chat-wrapper').on('click', '.js-message-row-of-clients-tab', function () {
                getChatlistOfUser($(this).attr("data-id"), "clients");
            });


        });

        function getChatlistOfUser(user_id, tab_type) {

            setChatIcon("close"); //show close icon

            appLoader.show({container: "#js-rise-chat-wrapper", css: "bottom: 40%; right: 35%;"});
            $.ajax({
                url: "<?php echo get_uri("messages/get_chatlist_of_user"); ?>",
                type: "POST",
                data: {user_id: user_id, tab_type: tab_type},
                success: function (response) {
                    $("#js-rise-chat-wrapper").html(response);
                    appLoader.hide();
                }
            });
        }

        function loadChatTabs(trigger_from_user_chat) {

            setChatIcon("close"); //show close icon

            setCookie("active_chat_id", "");
            appLoader.show({container: "#js-rise-chat-wrapper", css: "bottom: 40%; right: 35%;"});
            $.ajax({
                url: "<?php echo get_uri("messages/chat_list"); ?>",
                data: {
                    type: "inbox"
                },
                success: function (response) {
                    $("#js-rise-chat-wrapper").html(response);

                    if (!trigger_from_user_chat) {
                        $("#chat-inbox-tab-button a").trigger("click");
                    } else if (trigger_from_user_chat === "team_members") {
                        $("#chat-users-tab-button").find("a").trigger("click");
                    } else if (trigger_from_user_chat === "clients") {
                        $("#chat-clients-tab-button").find("a").trigger("click");
                    }
                    
                    // Actualizar badges después de cargar
                    setTimeout(function() {
                        window.updateChatUnreadBadges();
                    }, 1000);
            
                    appLoader.hide();
                }
            });

        }
        
        /*function updateChatUnreadBadges() {
            $.ajax({
                url: "<?php echo get_uri('messages/get_unread_counts'); ?>",
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        // Actualizar badges en la lista
                        $.each(response.unread_counts, function(userId, count) {
                            var $badge = $('.js-message-row[data-id="' + userId + '"] .chat-unread-badge');
                            if (count > 0) {
                                if ($badge.length) {
                                    $badge.text(count);
                                } else {
                                    $('.js-message-row[data-id="' + userId + '"] .avatar').append(
                                        '<span class="chat-unread-badge">' + count + '</span>'
                                    );
                                }
                            } else {
                                $badge.remove();
                            }
                        });
                    }
                }
            });
        }*/
        
        function playNotificationSound() {
            var audio = new Audio('<?= base_url("assets/sounds/chat.mp3") ?>');
            audio.volume = 0.3;
            // Asegura que no está en loop (por si el mp3 tuviera metadata rara)
            audio.loop = false;
            audio.play().catch(function(error) {
                console.log('No se pudo reproducir el sonido:', error);
            });
        }


        function getActiveChat(message_id) {
            setChatIcon("close"); //show close icon

            appLoader.show({container: "#js-rise-chat-wrapper", css: "bottom: 40%; right: 35%;"});
            $.ajax({
                url: "<?php echo get_uri('messages/get_active_chat'); ?>",
                type: "POST",
                data: {
                    message_id: message_id
                },
                success: function (response) {
                    $("#js-rise-chat-wrapper").html(response);
                    appLoader.hide();
                    setCookie("active_chat_id", message_id);
                    $("#js-chat-message-textarea").focus();
                    // Actualizar badges después de abrir el chat
                    setTimeout(function() {
                        window.updateChatUnreadBadges();
                    }, 1000);
                }
            });
        }

        /*window.prepareUnreadMessageChatBox = function (totalMessages) {
            setChatIcon("unread", totalMessages);
            
            // Reproducir sonido de notificación
            playNotificationSound();
            
            // Actualizar badges si el chat está abierto
            if ($("#js-rise-chat-wrapper").is(":visible")) {
                setTimeout(function() {
                    window.updateChatUnreadBadges();
                }, 500);
            }
        };*/
        
        window.prepareUnreadMessageChatBox = function (totalMessages) {
            setChatIcon("unread", totalMessages);
        
            // Solo sonar si subió el total y respetando un cooldown
            var now = Date.now();
            if (typeof totalMessages === "number" &&
                totalMessages > (window._chatNotify.lastTotal || 0) &&
                (now - window._chatNotify.lastPlayAt) > window._chatNotify.minGapMs) {
        
                playNotificationSound();
                window._chatNotify.lastPlayAt = now;
            }
        
            window._chatNotify.lastTotal = totalMessages;
        
            // SIEMPRE actualizar badges cuando llegan notificaciones
            if ($("#js-rise-chat-wrapper").is(":visible")) {
                setTimeout(function() {
                    window.updateChatUnreadBadges();
                }, 200); // Más rápido
            }
        };



        window.triggerActiveChat = function (message_id) {
            getActiveChat(message_id);
        }
        
        /*window.updateChatUnreadBadges = function() {
            if (!$("#js-rise-chat-wrapper").is(":visible")) return;
            
            $.ajax({
                url: "<?php echo get_uri('messages/get_unread_counts'); ?>",
                type: "GET",
                success: function(response) {
                    if (response.success && response.unread_counts) {
                        $('.js-message-row').each(function() {
                            var $row = $(this);
                            var $badge = $row.find('.chat-unread-badge');
                            var $avatar = $row.find('.avatar');
                            var messageId = $row.attr('data-id');
                            
                            // Buscar si hay mensajes no leídos para este chat
                            var hasUnread = false;
                            var unreadCount = 0;
                            
                            $.each(response.unread_counts, function(fromUserId, count) {
                                if (count > 0) {
                                    hasUnread = true;
                                    unreadCount = count;
                                    return false;
                                }
                            });
                            
                            if (unreadCount > 0) {
                                if ($badge.length) {
                                    $badge.text(unreadCount);
                                } else {
                                    $avatar.append('<span class="chat-unread-badge">' + unreadCount + '</span>');
                                }
                            } else {
                                $badge.remove();
                            }
                        });
                    }
                }
            });
        };*/
        
        /*window.updateChatUnreadBadges = function() {
            if (!$("#js-rise-chat-wrapper").is(":visible")) return;
            
            $.ajax({
                url: "<?php echo get_uri('messages/get_unread_counts'); ?>",
                type: "GET",
                success: function(response) {
                    if (response.success && response.unread_counts) {
                        // Actualizar cada fila individualmente
                        $('.js-message-row').each(function() {
                            var $row = $(this);
                            var $badge = $row.find('.chat-unread-badge');
                            var $avatar = $row.find('.avatar');
                            var messageId = $row.attr('data-id');
                            var fromUserId = $row.attr('data-from-user-id');
                            
                            // Buscar el conteo específico para este usuario
                            var unreadCount = 0;
                            if (response.unread_counts[fromUserId]) {
                                unreadCount = response.unread_counts[fromUserId];
                            }
                            
                            // Actualizar o crear badge
                            if (unreadCount > 0) {
                                if ($badge.length) {
                                    $badge.text(unreadCount);
                                } else {
                                    $avatar.append('<span class="chat-unread-badge">' + unreadCount + '</span>');
                                }
                            } else {
                                $badge.remove();
                            }
                        });
                    }
                }
            });
        };*/
        
       window.updateChatUnreadBadges = function () {
          var $wrap = $("#chat-inbox-tab");
          if (!$wrap.length) return;
        
          // Recolecta los message_id visibles
          var messageIds = [];
          $wrap.find('.js-message-row').each(function () {
            var mid = $(this).data('id'); // message_id en tu HTML
            if (mid !== undefined && mid !== null) messageIds.push(mid);
          });
          if (!messageIds.length) return;
        
          $.ajax({
            url: "<?= get_uri('messages/get_unread_counts_by_threads_api'); ?>", // tu endpoint por thread
            type: "POST",
            dataType: "json",
            data: { message_ids: messageIds },
            success: function (res) {
              if (!res || !res.success) return;
              var counts = res.unread_counts || {};
        
              $wrap.find('.js-message-row').each(function () {
                var $row = $(this);
                var threadId = String($row.data('id') || "");
                var count = parseInt(counts[threadId] || 0, 10);
        
                // ----- BADGE EN AVATAR -----
                var $target = $row.find('.flex-shrink-0 .avatar');
                if (!$target.length) $target = $row;
                var $badge = $row.find('.chat-unread-badge');
        
                if (count > 0) {
                  if ($badge.length) {
                    $badge.text(count);
                  } else {
                    $target.append('<span class="chat-unread-badge">' + count + '</span>');
                  }
                } else {
                  $badge.remove();
                }
        
                // ----- TEXTO INLINE JUNTO AL MENSAJE -----
                // contenedor donde está el snippet (tu HTML lo tiene en .w-100.ps-2)
                var $snippetBox = $row.find('.w-100.ps-2');
                var $inline = $snippetBox.find('.unread-inline');
                if (count > 0) {
                  var label = (count === 1) ? '1 mensaje no leído' : (count + ' mensajes no leídos');
                  if ($inline.length) {
                    $inline.text(label);
                  } else {
                    // se agrega al final del snippet
                    $snippetBox.append(' <span class="unread-inline">' + label + '</span>');
                  }
                } else {
                  $inline.remove();
                }
              });
            }
          });
        };


            
       setInterval(function() {
            if ($("#js-rise-chat-wrapper").is(":visible")) {
                window.updateChatUnreadBadges();
            }
        }, 1000); // Cada 1 segundo
        
                setTimeout(function() {
            $.ajax({
                url: "<?php echo get_uri('messages/get_unread_counts'); ?>",
                type: "GET", 
                success: function(response) {
                    if (response.success) {
                        var totalUnread = 0;
                        $.each(response.unread_counts, function(userId, count) {
                            totalUnread += count;
                        });
                        if (totalUnread > 0) {
                            setChatIcon("unread", totalUnread);
                        }
                    }
                }
            });
        }, 2000);


    </script>  


<?php } ?>