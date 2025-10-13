<div id="page-content" class="page-wrapper clearfix">
    <?php
    load_css([
        'assets/css/vsee.css',
    ]);
    ?>
    <style>
        #page-content {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        @keyframes fadeZoom {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>


    <!-- ANIMACIÓN DE CARGA PROFESIONAL -->
    <div id="vsee-loader" style="position:absolute;top:0;left:0;width:100%;height:100%;background:#ffffff;z-index:9999;display:flex;align-items:center;justify-content:center;flex-direction:column;">
        <img src="<?= base_url("assets/images/vsee_logo.png") ?>" alt="VSee Logo" style="width:100px;height:auto;animation:fadeZoom 2s ease-in-out infinite;">
        <div style="margin-top:20px;width:60px;height:60px;border:6px solid #a5d6a7;border-top:6px solid #388e3c;border-radius:50%;animation:spin 1s linear infinite;"></div>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                const loader = document.getElementById('vsee-loader');
                if (loader) loader.style.display = 'none';
            }, 1800);
        });
    </script>


    <div class="">

        <div class="vsee-replic-window-frame">
            <div class="vsee-replic-titlebar">
                <div class="vsee-replic-logo">
                    <img src="<?= base_url("assets/images/vsee_logo.png"); ?>" alt="VSee Logo">
                </div>
                <div class="vsee-replic-title">Chats</div>
                <div class="vsee-replic-controls">
                    <div class="vsee-replic-btn">▁</div>
                    <div class="vsee-replic-btn">☐</div>
                    <div class="vsee-replic-btn vsee-replic-close">✕</div>
                </div>
            </div>
        </div>
        <div class="vsee-replic-container">

            <aside class="vsee-replic-sidebar">
                <div class="vsee-replic-sidebar-top">
                    <div class="vsee-replic-user-container">
                        <div class="vsee-replic-user-icon">TP</div>
                        <span class="vsee-replic-status-badge"></span>
                    </div>
                </div>

                <div class="vsee-replic-menu-icon vsee-replic-active"><i class="fas fa-comment-dots"></i></div>
                <div class="vsee-replic-menu-icon"><i class="fas fa-video"></i></div>
                <div class="vsee-replic-menu-icon"><i class="fas fa-users"></i></div>
                <div class="vsee-replic-menu-icon"><i class="fas fa-cog"></i></div>
                <div class="vsee-replic-menu-icon"><i class="fas fa-ellipsis-h"></i></div>
            </aside>

            <div class="vsee-replic-content">
                <div class="vsee-replic-chatlist">
                    <div class="vsee-replic-chatlist-header">
                        <i class="fas fa-bars vsee-replic-header-icon"></i>
                        <div class="vsee-replic-chatlist-title">Chats</div>
                        <i class="fas fa-pen-to-square vsee-replic-header-icon"></i>
                    </div>

                    <div class="vsee-replic-search-bar">
                        <i class="fas fa-search vsee-replic-search-icon"></i>
                        <input type="text" placeholder="Buscar...">
                    </div>
                    <?php foreach ($chat_list as $chat): ?>
                        <div class="vsee-replic-chat-item" onclick="openChat('<?= esc($chat->receiver_key) ?>')">
                            <div class="vsee-replic-status vsee-replic-online"></div>
                            <div class="vsee-replic-chat-info">
                                <strong><?= esc($chat->name ?? get_chat_name($chat->receiver_key)) ?></strong>
                                <span class="vsee-replic-smallChat"><?= esc(substr($chat->last_message, 0, 40)) ?>...</span>
                            </div>
                            <!-- Aquí podrías mostrar la burbuja si hay mensajes no leídos -->
                            <!-- <span class="vsee-replic-notif-badge">1</span> -->
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="vsee-replic-chatpanel">

                    <div class="vsee-replic-chat-topbar">
                        <div class="vsee-replic-chat-user">
                            <span class="vsee-replic-status-dot"></span>
                            <div>
                                <strong>Rubymed Katy</strong><br>
                                <small>katy@rubymed.org</small>
                            </div>
                        </div>
                        <div class="vsee-replic-chat-actions">
                            <i class="fas fa-video vsee-replic-icon"></i>
                            <i class="fas fa-ellipsis-h vsee-replic-icon"></i>
                        </div>
                    </div>


                    <div class="vsee-replic-messages" style="position: relative;">

                        <!-- Loader solo para esta área -->
                        <div id="chat-loading-spinner" class="floating-loader d-none">
                            <div class="lds-dual-ring"></div>
                        </div>
                        <!--<div class="vsee-replic-date">julio 17</div>

                        <div class="vsee-replic-msg vsee-replic-recv">
                            <p><strong>Boris Marrero DOB 10.25.87</strong><br>
                                Paciente masculino de 37 años...<br>
                                Se envía tratamiento...</p>
                            <span class="vsee-replic-time">11:03 a. m.</span>
                        </div>

                        <div class="vsee-replic-msg vsee-replic-sent">
                            <p>done colegas</p>
                            <span class="vsee-replic-time">11:09 a. m. ✓</span>
                        </div>-->

                        <div id="chat-messages-container"></div> <!-- NUEVO CONTENEDOR -->
                    </div>

                    <div class="vsee-replic-input">
                        <i class="fas fa-plus-circle vsee-replic-input-icon"></i>
                        <input type="text" name="message" placeholder="Escribe aquí tus mensajes">
                    </div>


                </div>

            </div>

            <div class="vsee-replic-content d-none">
                <div class="vsee-replic-contacts">
                    <div class="vsee-replic-contacts-header">
                        <input type="text" class="vsee-replic-contacts-search" placeholder="Buscar...">
                        <i class="fas fa-plus vsee-replic-contacts-add"></i>
                    </div>

                    <div class="vsee-replic-contacts-list">
                        <div class="vsee-replic-contact-item online">
                            <span class="status-dot green"></span>
                            Rubymed Memphis
                        </div>
                        <div class="vsee-replic-contact-item online">
                            <span class="status-dot green"></span>
                            Rubymed Mesquite
                        </div>
                        <div class="vsee-replic-contact-item offline">
                            <span class="status-dot gray"></span>
                            Yulasis Napoles
                        </div>
                        <div class="vsee-replic-contact-item busy">
                            <span class="status-dot orange"></span>
                            Rubymed Magnolia Park
                        </div>
                        <!-- Puedes repetir el bloque anterior con tus contactos reales -->
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>


<script>
    //const audio = new Audio('/assets/sounds/notification.mp3');
    let currentReceiverKey = '<?= $receiver_key ?>';
    let currentUserId = <?= $login_user->id ?>;
    let lastMessageId = 0;

    function loadMessages(withSpinner = false) {
        if (withSpinner) {
            $('#chat-loading-spinner').fadeIn(100);
        }

        const start = Date.now();

        $.get("<?= get_uri('chat/get_messages') ?>/" + currentReceiverKey, function(html) {
            // Guarda el loader temporalmente
            const loader = $('#chat-loading-spinner').detach();

            // Reemplaza mensajes
            $('#chat-messages-container').html(html);

            // Vuelve a insertar el loader al final
            $('.vsee-replic-messages').append(loader);

            // Marcar como leídos
            $.post("<?= get_uri('chat/mark_seen') ?>", {
                receiver_key: currentReceiverKey,
                user_id: currentUserId
            });

            // ACTUALIZA EL ID MÁS RECIENTE
            const ids = $('#chat-messages-container .last-id');
            if (ids.length) {
                lastMessageId = Math.max(...ids.map((_,el) => +$(el).data('id')).get());
            }

            // Ocultar loader
            // Espera al menos 300ms antes de ocultar el loader
            const elapsed = Date.now() - start;
            const delay = Math.max(300 - elapsed, 0);

            setTimeout(() => {
                if (withSpinner) {
                    $('#chat-loading-spinner').fadeOut(150);
                }
            }, delay);
        });
    }


    function loadNewMessages() {
        console.log(currentReceiverKey);
        if (!currentReceiverKey || !$('#chat-messages-container').length) return;

        $.get("<?= get_uri('chat/get_new_messages') ?>/" + currentReceiverKey, {
            last_id: lastMessageId
        }, function(html) {
            if (html.trim()) {
                 console.log(html);
                $('#chat-messages-container').append(html);
                scrollToBottom(true);

                // Actualiza el último ID
                const ids = $('#chat-messages-container .last-id');
                if (ids.length) {
                    lastMessageId = Math.max(...ids.map((_,el) => +$(el).data('id')).get());
                }


            }
        });
    }


    function sendMessage() {
        const msg = $('input[name=message]').val();
        if (!msg.trim()) return;

        $.post("<?= get_uri('chat/send') ?>", {
            sender_id: currentUserId,
            receiver_key: currentReceiverKey,
            message: msg
        }, function(response) {
            if (response.success) {
                $('input[name=message]').val('');

                const now = new Date();
                const time = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                $('#chat-messages-container').append(`
                    <div class="vsee-replic-msg vsee-replic-sent last-id" data-id="${response.id}">
                        <p>${$('<div>').text(msg).html()}</p>
                        <span class="vsee-replic-time">${time}</span>
                    </div>
                `);

                scrollToBottom(true);

                lastMessageId = response.id;
                loadNewMessages();
                loadChatList();
            }
        });
    }

    function checkNewMessages() {
        $.get("<?= get_uri('chat/check_new_messages') ?>", {
            receiver_key: currentReceiverKey,
            user_id: currentUserId
        }, function(res) {
            if (res.has_new) {
                //audio.play();
                // Puedes mostrar un toast aquí también
            }
        });
    }

    /*function openChat(receiverKey) {
        currentReceiverKey = receiverKey;

        // Cargar mensajes
        loadMessages();

        // Buscar elemento seleccionado
        const selected = $('.vsee-replic-chat-item')
            .filter(function() {
                return $(this).attr('onclick')?.includes(receiverKey);
            })
            .first();

        const name = selected.find('strong').text()?.trim() || 'Chat';
        const email = receiverKey.replace('clinic_', '').replace('user_', '') + '@rubymed.org'; // Ajusta si es necesario

        $('.vsee-replic-chat-user strong').text(name);
        $('.vsee-replic-chat-user small').text(email);
    }*/

    function openChat(receiverKey) {
        currentReceiverKey = receiverKey;
        console.log(receiverKey);

        $('.vsee-replic-chat-item').removeClass('vsee-replic-active');
        $(`.vsee-replic-chat-item[onclick*="${receiverKey}"]`).addClass('vsee-replic-active');

        $('#chat-loading-spinner').fadeIn(100);

        $.get("<?= get_uri('chat/chat_info') ?>", {
            receiver_key: receiverKey
        }, function(res) {
            $('.vsee-replic-chat-user strong').text(res.name);
            $('.vsee-replic-chat-user small').text(res.email);
        });

        // ✅ AQUÍ VIENE LA CORRECCIÓN
        $.get("<?= get_uri('chat/get_messages') ?>/" + receiverKey, function(html) {
            console.log(html);
            $('#chat-messages-container').html(html); // ✅ Solo reemplaza los mensajes, no todo el contenedor
            scrollToBottom(true);
            // Establecer el último ID para el polling
            const last = $('.last-id').last().data('id');
            lastMessageId = last ?? 0;
            $('#chat-loading-spinner').fadeOut(150);
        });
    }



    function scrollToBottom(force = true) {
        const messages = document.querySelector('.vsee-replic-messages');
        if (!messages) return;

        if (force) {
            messages.scrollTop = messages.scrollHeight;
        } else {
            const shouldScroll = messages.scrollHeight - messages.scrollTop - messages.clientHeight < 50;
            if (shouldScroll) {
                messages.scrollTop = messages.scrollHeight;
            }
        }
    }


    function loadChatList() {
        $.get("<?= get_uri('chat/chat_list') ?>", function(html) {
            $('.vsee-replic-chatlist').html(html);

            // Restaurar clase activa
            $(`.vsee-replic-chat-item[onclick*="${currentReceiverKey}"]`).addClass('vsee-replic-active');
        });
    }


    setInterval(() => {
        loadNewMessages();
        checkNewMessages();
    }, 3000);

    $(document).ready(function() {
        const firstChat = $('.vsee-replic-chat-item').first();
        if (firstChat.length) {
            const receiverKey = firstChat.attr('onclick').match(/openChat\('([^']+)'\)/)[1];
            openChat(receiverKey);
            firstChat.addClass('vsee-replic-active');
        }
    });

    $('input[name=message]').keypress(function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });

    
</script>