<?php

namespace App\Controllers;

class Messages extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("message_permission");
    }

    private function is_my_message($message_info) {
        if ($message_info->from_user_id == $this->login_user->id || $message_info->to_user_id == $this->login_user->id) {
            return true;
        }
    }

    private function check_message_user_permission() {
        if (!$this->check_access_on_messages_for_this_user()) {
            app_redirect("forbidden");
        }
    }

    private function check_validate_sending_message($to_user_id) {
        if (!$this->validate_sending_message($to_user_id)) {
            echo json_encode(array("success" => false, 'message' => app_lang("message_sending_error_message")));
            exit;
        }
    }

    function index() {
        $this->check_message_user_permission();
        app_redirect("messages/inbox");
    }

    /* show new message modal */

    function modal_form($user_id = 0) {
        validate_numeric_value($user_id);
        $this->check_message_user_permission();
        $view_data['users_dropdown'] = array("" => "-");

        if ($user_id) {
            $view_data['message_user_info'] = $this->Users_model->get_one($user_id);
        } else {
            $users = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query())->getResult();

            foreach ($users as $user) {
                $user_name = $user->first_name . " " . $user->last_name;

                if ($user->user_type === "client" && $user->company_name) { //user is a client contact
                    if ($this->login_user->user_type == "staff") {
                        $user_name .= " - " . app_lang("client") . ": " . $user->company_name . "";
                    } else {
                        $user_name = app_lang("contact") . ": " . $user_name;
                    }
                }

                $view_data['users_dropdown'][$user->id] = $user_name;
            }
        }

        return $this->template->view('messages/modal_form', $view_data);
    }

    /* show inbox */

    function inbox($auto_select_index = "") {
        $this->check_message_user_permission();
        $this->check_module_availability("module_message");

        $view_data['mode'] = "inbox";
        $view_data['auto_select_index'] = clean_data($auto_select_index);
        return $this->template->rander("messages/index", $view_data);
    }

    /* show sent items */

    function sent_items($auto_select_index = "") {
        $this->check_message_user_permission();
        $this->check_module_availability("module_message");

        $view_data['mode'] = "sent_items";
        $view_data['auto_select_index'] = clean_data($auto_select_index);
        return $this->template->rander("messages/index", $view_data);
    }

    /* list of messages, prepared for datatable  */

    function list_data($mode = "inbox") {
        $this->check_message_user_permission();
        if ($mode !== "inbox") {
            $mode = "sent_items";
        }

        $options = array("user_id" => $this->login_user->id, "mode" => $mode, "user_ids" => $this->get_allowed_user_ids());
        $list_data = $this->Messages_model->get_list($options)->getResult();

        $result = array();

        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $mode);
        }

        echo json_encode(array("data" => $result));
    }

    /* return a message details */

    function view($message_id = 0, $mode = "", $reply = 0) {
        validate_numeric_value($message_id);
        $this->check_message_user_permission();

        $message_mode = $mode;
        if ($reply == 1 && $mode == "inbox") {
            $message_mode = "sent_items";
        } else if ($reply == 1 && $mode == "sent_items") {
            $message_mode = "inbox";
        }

        $options = array("id" => $message_id, "user_id" => $this->login_user->id, "mode" => $message_mode);
        $view_data["message_info"] = $this->Messages_model->get_details($options)->row;

        if (!$this->is_my_message($view_data["message_info"])) {
            app_redirect("forbidden");
        }

        //change message status to read
        $this->Messages_model->set_message_status_as_read($view_data["message_info"]->id, $this->login_user->id);

        $replies_options = array("message_id" => $message_id, "user_id" => $this->login_user->id, "limit" => 4);
        $messages = $this->Messages_model->get_details($replies_options);

        $view_data["replies"] = $messages->result;
        $view_data["found_rows"] = $messages->found_rows;

        $view_data["mode"] = clean_data($mode);
        $view_data["is_reply"] = clean_data($reply);
        echo json_encode(array("success" => true, "data" => $this->template->view("messages/view", $view_data), "message_id" => $message_id));
    }

    /* prepare a row of message list table */

    private function _make_row($data, $mode = "", $return_only_message = false, $online_status = false) {
        $image_url = get_avatar($data->user_image);
        $created_at = format_to_relative_time($data->created_at);
        $message_id = $data->main_message_id;
        $label = "";
        $reply = "";
        $status = "";
        $attachment_icon = "";
        $subject = $data->subject;
        if ($mode == "inbox") {
            $status = $data->status;
        }

        if ($data->reply_subject) {
            $label = " <label class='badge bg-success d-inline-block'>" . app_lang('reply') . "</label>";
            $reply = "1";
            $subject = $data->reply_subject;
        }

        if ($data->files && is_array(unserialize($data->files)) && count(unserialize($data->files))) {
            $attachment_icon = "<i data-feather='paperclip' class='icon-14 mr15'></i>";
        }


        //prepare online status
        $online = "";
        if ($online_status && is_online_user($data->last_online)) {
            $online = "<i class='online'></i>";
        }

        $message = "<div class='message-row $status' data-id='$message_id' data-index='$data->main_message_id' data-reply='$reply'><div class='d-flex'><div class='flex-shrink-0'>
                        <span class='avatar avatar-xs'>
                            <img src='$image_url' />
                                $online
                        </span>
                    </div>
                    <div class='w-100 ps-3'>
                        <div class='mb5'>
                            <strong> $data->user_name</strong>
                                  <span class='text-off float-end time'>$attachment_icon $created_at</span>
                        </div>
                        $label $subject
                    </div></div></div>
                  
                ";
        if ($return_only_message) {
            return $message;
        } else {
            return array(
                $message,
                $data->created_at,
                $status
            );
        }
    }
    
    function get_unread_counts() {
        //error_log("=== get_unread_counts called for user: " . $this->login_user->id);
        log_message('error', '=== get_unread_counts called for user:  ' . $this->login_user->id);
        
        $unread_counts = $this->Messages_model->get_unread_counts_by_user($this->login_user->id);
        
        log_message('error',"=== Final result: " . json_encode($unread_counts));
        //error_log("=== Final result: " . json_encode($unread_counts));
        
        echo json_encode(array("success" => true, "unread_counts" => $unread_counts));
    }
    
    public function get_unread_counts_by_threads_api()
    {
        $user_id = $this->login_user->id;
        $ids = $this->request->getPost('message_ids'); // [39,103,37,...]
        if (!is_array($ids)) $ids = [];
    
        $model = new \App\Models\Messages_model();
        $map = $model->get_unread_counts_by_threads($user_id, $ids);
    
        return $this->response->setJSON([
            'success' => true,
            'unread_counts' => $map
        ]);
    }

    /* send new message */

    function send_message() {
        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "message" => "required",
            "to_user_id" => "required|numeric"
        ));

        $to_user_id = $this->request->getPost('to_user_id');

        //team member can send message to any team member
        //client can send messages to only allowed members

        $this->check_validate_sending_message($to_user_id);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

        $message_data = array(
            "from_user_id" => $this->login_user->id,
            "to_user_id" => $to_user_id,
            "subject" => $this->request->getPost('subject'),
            "message" => $this->request->getPost('message'),
            "created_at" => get_current_utc_time(),
            "deleted_by_users" => "",
        );

        $message_data = clean_data($message_data);

        $message_data["files"] = $files_data; //don't clean serilized data

        $save_id = $this->Messages_model->ci_save($message_data);

        if ($save_id) {
            $this->sendFirebaseChatNotification($to_user_id, $this->request->getPost('message'), $save_id);

            // Notificar a la aplicación de escritorio
            //$this->notifyDesktopMessage($to_user_id, $this->request->getPost('message'), $save_id,'message');

            log_notification("new_message_sent", array("actual_message_id" => $save_id));
            echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), "id" => $save_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

     /**
     * Método helper privado para enviar notificaciones push de Firebase
     * 
     * @param int $to_user_id ID del usuario destinatario
     * @param string $message Contenido del mensaje
     * @param int $save_id ID del mensaje guardado
     */
    private function sendFirebaseChatNotification($to_user_id, $message, $save_id) {
        
        try {
            $to_user_id = (int) $to_user_id;
            
            $pushService = new \App\Services\PushNotificationService();
            $sender_info = $this->Users_model->get_one($this->login_user->id);
            $sender_name = $sender_info->first_name . " " . $sender_info->last_name;            
            return $pushService->sendChatNotification(
                $to_user_id, 
                $this->login_user->id, 
                $message, 
                $save_id, 
                $sender_name
            );
        } catch (\Exception $e) {
            log_message('error', 'Error sending chat push notification: ' . $e->getMessage());
        }
    }    

    /* reply to an existing message */

    function reply($is_chat = 0) {
        $this->check_message_user_permission();
        $message_id = $this->request->getPost('message_id');

        $this->validate_submitted_data(array(
            "reply_message" => "required",
            "message_id" => "required|numeric"
        ));

        $message_info = $this->Messages_model->get_one($message_id);

        if (!$this->is_my_message($message_info)) {
            app_redirect("forbidden");
        }


        if ($message_info->id) {
            //check, where we have to send this message
            $to_user_id = 0;
            if ($message_info->from_user_id === $this->login_user->id) {
                $to_user_id = $message_info->to_user_id;
            } else {
                $to_user_id = $message_info->from_user_id;
            }

            $this->check_validate_sending_message($to_user_id);

            $target_path = get_setting("timeline_file_path");
            $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "message");

            $message = $this->request->getPost('reply_message');

            $message_data = array(
                "from_user_id" => $this->login_user->id,
                "to_user_id" => $to_user_id,
                "message_id" => $message_id,
                "subject" => "",
                "message" => $message,
                "created_at" => get_current_utc_time(),
                "deleted_by_users" => "",
            );

            $message_data = clean_data($message_data);
            $message_data["files"] = $files_data; //don't clean serilized data


            $save_id = $this->Messages_model->ci_save($message_data);

            if ($save_id) {
                $this->sendFirebaseChatNotification($to_user_id, $message, $save_id);

                //$this->notifyDesktopMessage($to_user_id, $message, $save_id,'message');

                if (!$is_chat) {
                        // Disparar evento para actualizar badges si hay WebSockets
                        log_notification("chat_badge_update", array(
                            "to_user_id" => $to_user_id,
                            "from_user_id" => $this->login_user->id
                        ));
                    }

                //if chat via pusher is enabled, then send message data to pusher
                if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                    send_message_via_pusher($to_user_id, $message_data, $message_id);
                }

                //we'll not send notification, if the user is online

                if ($this->request->getPost("is_user_online") !== "1") {
                    log_notification("message_reply_sent", array("actual_message_id" => $save_id, "parent_message_id" => $message_id));
                }

                //clear the delete status, if the mail deleted
                $this->Messages_model->clear_deleted_status($message_id);

                if ($is_chat) {
                    echo json_encode(array("success" => true, 'data' => $this->_load_messages($message_id, $this->request->getPost("last_message_id"), 0, $to_user_id)));
                } else {
                    $options = array("id" => $save_id, "user_id" => $this->login_user->id);
                    $view_data['reply_info'] = $this->Messages_model->get_details($options)->row;
                    echo json_encode(array("success" => true, 'message' => app_lang('message_sent'), 'data' => $this->template->view("messages/reply_row", $view_data)));
                }

                return;
            }
        }
        echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
    }

    //load messages right panel when clicking load more button
    function view_messages() {

        $this->check_message_user_permission();
        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "last_message_id" => "numeric",
            "top_message_id" => "numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        echo $this->_load_more_messages($message_id, $this->request->getPost("last_message_id"), $this->request->getPost("top_message_id"));
    }

    //prepare the chat box messages 
    private function _load_more_messages($message_id, $last_message_id, $top_message_id) {

        $replies_options = array("message_id" => $message_id, "last_message_id" => $last_message_id, "top_message_id" => $top_message_id, "user_id" => $this->login_user->id, "limit" => 10);

        $view_data["replies"] = $this->Messages_model->get_details($replies_options)->result;
        $view_data["message_id"] = $message_id;

        $this->Messages_model->set_message_status_as_read($message_id, $this->login_user->id);

        return $this->template->view("messages/reply_rows", $view_data);
    }

    function count_notifications() {
        $this->validate_submitted_data(array(
            "active_message_id" => "numeric"
        ));

        $notifiations = $this->Messages_model->count_notifications($this->login_user->id, $this->login_user->message_checked_at, $this->request->getPost("active_message_id"), $this->get_allowed_user_ids());
        echo json_encode(array("success" => true, "active_message_id" => $this->request->getPost("active_message_id"), 'total_notifications' => $notifiations));
    }

    /* prepare notifications */

    function get_notifications() {
        $options = array("user_id" => $this->login_user->id, "mode" => "inbox", "user_ids" => $this->get_allowed_user_ids(), "is_notification" => true);
        $view_data['notifications'] = $this->Messages_model->get_list($options)->getResult();
        echo json_encode(array("success" => true, 'notification_list' => $this->template->view("messages/notifications", $view_data)));
    }

    function update_notification_checking_status() {
        $now = get_current_utc_time();
        $user_data = array("message_checked_at" => $now);
        $this->Users_model->ci_save($user_data, $this->login_user->id);
    }


    /* download files by zip */

    function download_message_files($message_id = "") {
        validate_numeric_value($message_id);
        $model_info = $this->Messages_model->get_one($message_id);
        if (!$this->is_my_message($model_info)) {
            app_redirect("forbidden");
        }

        $files = $model_info->files;

        $timeline_file_path = get_setting("timeline_file_path");
        return $this->download_app_files($timeline_file_path, $files);
    }

    function delete_my_messages($id = 0) {

        if (!$id) {
            exit();
        }

        validate_numeric_value($id);

        //delete messages for current user.
        $this->Messages_model->delete_messages_for_user($id, $this->login_user->id);
    }

    //prepare chat inbox list
    function chat_list() {
        $this->check_message_user_permission();

        $view_data['show_users_list'] = false;
        $view_data['show_clients_list'] = false;

        $client_message_users = get_setting("client_message_users");
        if ($this->login_user->user_type === "staff") {
            //user is team member
            $client_message_users_array = explode(",", $client_message_users);
            if (in_array($this->login_user->id, $client_message_users_array)) {
                //user can send message to clients
                $view_data['show_clients_list'] = true;
            }

            if (get_array_value($this->login_user->permissions, "message_permission") !== "no") {
                //user can send message to team members
                $view_data['show_users_list'] = true;
            }
        } else {
            //user is a client contact and can send messages
            if ($client_message_users) {
                $view_data['show_users_list'] = true;
            }

            //user can send message to own client contacts
            if (get_setting("client_message_own_contacts")) {
                $view_data['show_clients_list'] = true;
            }
        }

        $options = array("login_user_id" => $this->login_user->id, "user_ids" => $this->get_allowed_user_ids());

        $view_data['messages'] = $this->Messages_model->get_chat_list($options)->getResult();

        /*AGREGADOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO*/
        if (!empty($view_data['messages'])) {
        try {
            // Recopilar todos los user_ids únicos para hacer consultas en lote
            $user_ids = [];
            foreach ($view_data['messages'] as $message) {
                $other_user_id = $this->_get_other_user_id_from_message_optimized($message);
                $user_ids[] = $other_user_id;
                $message->other_user_id = $other_user_id; // Guardar para uso posterior
            }
            $user_ids = array_unique($user_ids);
            
            // Obtener datos de providers y clínicas en lote
            $providers_data = $this->_get_providers_batch($user_ids);
            $clinics_data = $this->_get_clinics_batch($user_ids);
            
            // Asignar datos a cada mensaje
            foreach ($view_data['messages'] as $message) {
                $user_id = $message->other_user_id;
                $message->is_provider = isset($providers_data[$user_id]);
                $message->user_clinics = $clinics_data[$user_id] ?? [];
                unset($message->other_user_id); // Limpiar campo temporal
            }
        } catch (\Exception $e) {
            // En caso de error, continuar sin badges para evitar que el chat se cuelgue
            log_message('error', 'Error loading clinic/provider data in chat_list: ' . $e->getMessage());
            foreach ($view_data['messages'] as $message) {
                $message->is_provider = false;
                $message->user_clinics = [];
            }
        }
    }

        return $this->template->view("messages/chat/tabs", $view_data);
    }

    /*function users_list($type) {
        $view_data["users"] = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query($type))->getResult();

        $page_type = "";
        if ($type === "staff") {
            $page_type = "team-members-tab";
        } else {
            $page_type = "clients-tab";
        }

        $view_data["page_type"] = $page_type;

        return $this->template->view("messages/chat/team_members", $view_data);
    }*/
    
    function users_list($type) {
        $users = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query($type))->getResult();
        /*AGREGADOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO*/
        // Obtener información de clínicas y providers de forma optimizada
        if (!empty($users)) {
            try {
                $user_ids = array_column($users, 'id');
                
                // Obtener datos de providers y clínicas en lote
                $providers_data = $this->_get_providers_batch($user_ids);
                $clinics_data = $this->_get_clinics_batch($user_ids);
                
                // Asignar datos a cada usuario
                foreach ($users as $user) {
                    $user->is_provider = isset($providers_data[$user->id]);
                    $user->user_clinics = $clinics_data[$user->id] ?? [];
                }
            } catch (\Exception $e) {
                // En caso de error, continuar sin badges para evitar que el chat se cuelgue
                log_message('error', 'Error loading clinic/provider data in users_list: ' . $e->getMessage());
                foreach ($users as $user) {
                    $user->is_provider = false;
                    $user->user_clinics = [];
                }
            }
        }
        
        // Agregar unread_count a cada usuario
        foreach ($users as $user) {
            $user->unread_count = $this->Messages_model->count_unread_messages_from_user($user->id, $this->login_user->id);
        }
        
        $view_data["users"] = $users;
    
        $page_type = "";
        if ($type === "staff") {
            $page_type = "team-members-tab";
        } else {
            $page_type = "clients-tab";
        }
    
        $view_data["page_type"] = $page_type;
    
        return $this->template->view("messages/chat/team_members", $view_data);
    }

    //load messages in chat view
    function view_chat() {

        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "last_message_id" => "numeric",
            "top_message_id" => "numeric",
            "another_user_id" => "numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        $another_user_id = $this->request->getPost("another_user_id");

        if ($this->request->getPost("is_first_load") == "1") {
            $view_data["first_message"] = $this->Messages_model->get_details(array("id" => $message_id, "user_id" => $this->login_user->id))->row;
            echo $this->template->view("messages/chat/message_title", $view_data);
        }

        echo $this->_load_messages($message_id, $this->request->getPost("last_message_id"), $this->request->getPost("top_message_id"), $another_user_id);
    }

    //prepare the chat box messages 
    private function _load_messages($message_id, $last_message_id, $top_message_id, $another_user_id = "") {

        $replies_options = array("message_id" => $message_id, "last_message_id" => $last_message_id, "top_message_id" => $top_message_id, "user_id" => $this->login_user->id);

        $view_data["replies"] = $this->Messages_model->get_details($replies_options)->result;
        $view_data["message_id"] = $message_id;

        $this->Messages_model->set_message_status_as_read($message_id, $this->login_user->id);

        $is_online = false;
        if ($another_user_id) {
            $last_online = $this->Users_model->get_one($another_user_id)->last_online;
            if ($last_online) {
                $is_online = is_online_user($last_online);
            }
        }

        $view_data['is_online'] = $is_online;

        return $this->template->view("messages/chat/message_items", $view_data);
    }

    function get_active_chat() {

        $this->validate_submitted_data(array(
            "message_id" => "required|numeric"
        ));

        $message_id = $this->request->getPost("message_id");

        $options = array("id" => $message_id, "user_id" => $this->login_user->id);
        $view_data["message_info"] = $this->Messages_model->get_details($options)->row;

        if (!$this->is_my_message($view_data["message_info"])) {
            app_redirect("forbidden");
        }

        //$this->Messages_model->set_message_status_as_read($view_data["message_info"]->id, $this->login_user->id);

        $view_data["message_id"] = $message_id;
        return $this->template->view("messages/chat/active_chat", $view_data);
    }

    function get_chatlist_of_user() {

        $this->check_message_user_permission();

        $this->validate_submitted_data(array(
            "user_id" => "required|numeric"
        ));

        $user_id = $this->request->getPost("user_id");

        $options = array("user_id" => $user_id, "login_user_id" => $this->login_user->id);
        $view_data["messages"] = $this->Messages_model->get_chat_list($options)->getResult();

        $user_info = $this->Users_model->get_one_where(array("id" => $user_id, "status" => "active", "deleted" => "0"));
        $view_data["user_name"] = $user_info->first_name . " " . $user_info->last_name;

        $view_data["user_id"] = $user_id;
        $view_data["tab_type"] = $this->request->getPost("tab_type");

        return $this->template->view("messages/chat/get_chatlist_of_user", $view_data);
    }

    // Verificar si hay mensajes nuevos para notificaciones globales
    /*function check_new_messages() {
        $this->check_message_user_permission();
        
        $this->validate_submitted_data(array(
            "user_id" => "required|numeric",
            "last_check" => "required"
        ));
        
        $user_id = $this->request->getPost('user_id');
        $last_check = $this->request->getPost('last_check');
        
        // Verificar si hay mensajes nuevos usando count_notifications
        $user_ids = $this->get_allowed_user_ids();
        $new_messages_count = $this->Messages_model->count_notifications(
            $this->login_user->id, 
            $last_check, 
            0, // active_message_id = 0 para verificar todos los mensajes
            $user_ids
        );
        
        $has_new_messages = $new_messages_count > 0;
        
        // Log para debugging
        log_message('debug', 'Chat Notifications Debug: user_id=' . $this->login_user->id . 
                   ', last_check=' . $last_check . 
                   ', count=' . $new_messages_count . 
                   ', has_new=' . ($has_new_messages ? 'true' : 'false'));
        
        echo json_encode(array(
            "success" => true,
            "hasNewMessages" => $has_new_messages,
            "count" => $new_messages_count
        ));
    }*/

     function check_new_messages() {
        try {
            $this->check_message_user_permission();
            
            $this->validate_submitted_data(array(
                "user_id" => "required|numeric",
                "last_check" => "required"
            ));
            
            $user_id = $this->request->getPost('user_id');
            $last_check = $this->request->getPost('last_check');
            
            // Validar que last_check sea una fecha válida
            if (!strtotime($last_check)) {
                throw new \Exception('Fecha de última verificación inválida');
            }
            
            // Verificar si hay mensajes nuevos usando count_notifications
            $user_ids = $this->get_allowed_user_ids();
            
            // Si no hay user_ids permitidos, retornar sin mensajes nuevos
            if (empty($user_ids)) {
                echo json_encode(array(
                    "success" => true,
                    "hasNewMessages" => false,
                    "count" => 0
                ));
                return;
            }
            
            $new_messages_count = $this->Messages_model->count_notifications(
                $this->login_user->id, 
                $last_check, 
                0, // active_message_id = 0 para verificar todos los mensajes
                $user_ids
            );
            
            // Asegurar que count_notifications retorne un número
            if ($new_messages_count === null) {
                $new_messages_count = 0;
            }
            
            $has_new_messages = $new_messages_count > 0;
            
            // Log para debugging
            log_message('debug', 'Chat Notifications Debug: user_id=' . $this->login_user->id . 
                       ', last_check=' . $last_check . 
                       ', count=' . $new_messages_count . 
                       ', has_new=' . ($has_new_messages ? 'true' : 'false') .
                       ', user_ids=' . $user_ids);
            
            // Log adicional para debugging
            error_log('=== CHAT NOTIFICATIONS DEBUG ===');
            error_log('User ID: ' . $this->login_user->id);
            error_log('Last Check: ' . $last_check);
            error_log('New Messages Count: ' . $new_messages_count);
            error_log('Has New Messages: ' . ($has_new_messages ? 'true' : 'false'));
            error_log('Allowed User IDs: ' . $user_ids);
            error_log('===============================');
            
            echo json_encode(array(
                "success" => true,
                "hasNewMessages" => $has_new_messages,
                "count" => $new_messages_count
            ));
            
        } catch (\Exception $e) {
            // Log del error
            log_message('error', 'Error en check_new_messages: ' . $e->getMessage());
            error_log('ERROR en check_new_messages: ' . $e->getMessage());
            
            // Retornar respuesta de error
            echo json_encode(array(
                "success" => false,
                "error" => "Error interno del servidor",
                "hasNewMessages" => false,
                "count" => 0
            ));
        }
    }



    function send_typing_indicator_to_pusher() {
        $message_id = $this->request->getPost("message_id");
        if (!$message_id) {
            show_404();
        }

        $message_info = $this->Messages_model->get_one($message_id);
        if (!$this->is_my_message($message_info)) {
            app_redirect("forbidden");
        }

        if ($message_info->id) {
            //check, where we have to send this message
            $to_user_id = 0;
            if ($message_info->from_user_id === $this->login_user->id) {
                $to_user_id = $message_info->to_user_id;
            } else {
                $to_user_id = $message_info->from_user_id;
            }

            $this->check_validate_sending_message($to_user_id);

            if (get_setting('enable_chat_via_pusher') && get_setting("enable_push_notification")) {
                send_message_via_pusher($to_user_id, "", $message_id, "typing");
            }
        } else {
            show_404();
        }
    }
    
    function edit_message() {
        $this->check_message_user_permission();
        
        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "new_message" => "required"
        ));
        
        $message_id = $this->request->getPost('message_id');
        $new_message = $this->request->getPost('new_message');
        
        // Verificaciones existentes...
        $message_info = $this->Messages_model->get_one($message_id);
        if ($message_info->from_user_id != $this->login_user->id) {
            echo json_encode(array("success" => false, "message" => "No puedes editar este mensaje"));
            return;
        }
        
        $created_time = strtotime($message_info->created_at);
        $current_time = time();
        $time_diff = ($current_time - $created_time) / 60;
        
        if ($time_diff > 10 && $time_diff > 0) {
            echo json_encode(array("success" => false, "message" => "Ya no puedes editar este mensaje (tiempo límite: 10 minutos)"));
            return;
        }
        
        // Actualizar mensaje
        $update_data = array(
            "message" => $new_message,
            "edited_at" => get_current_utc_time()
        );
        
        $result = $this->Messages_model->ci_save($update_data, $message_id);
        
        if ($result) {
            // NO USAR PUSHER - Solo responder success
            echo json_encode(array(
                "success" => true, 
                "message" => "Mensaje editado correctamente",
                "edited_message_id" => $message_id,
                "new_content" => nl2br(link_it($new_message))
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "Error al editar el mensaje"));
        }
    }
    
    function check_edited_messages() {
        $this->check_message_user_permission();
        
        $this->validate_submitted_data(array(
            "message_id" => "required|numeric",
            "last_edit_check" => "required"
        ));
        
        $message_id = $this->request->getPost("message_id");
        $last_edit_check = $this->request->getPost("last_edit_check");
        $last_edit_datetime = date('Y-m-d H:i:s', $last_edit_check / 1000);
        
        // Buscar mensajes editados después del último check
        $edited_messages = $this->Messages_model->get_recently_edited_messages($message_id, $last_edit_datetime, $this->login_user->id);
        
        $result = array();
        foreach ($edited_messages as $msg) {
            $result[] = array(
                'message_id' => $msg->id,
                'new_content' => nl2br(link_it(process_images_from_content($msg->message)))
            );
        }
        
        echo json_encode(array(
            "success" => true,
            "edited_messages" => $result
        ));
    }

        // Método auxiliar para obtener el ID del otro usuario en un mensaje (optimizado)
    private function _get_other_user_id_from_message_optimized($message) {
        // El SQL de get_chat_list ya filtra para que from_user_id sea diferente del login_user
        // cuando es necesario, así que podemos usar from_user_id directamente
        return $message->from_user_id;
    }

    // Método optimizado para obtener múltiples providers en una sola consulta
    /*private function _get_providers_batch($user_ids) {
        if (empty($user_ids)) return [];
        
        try {
            $db = \Config\Database::connect();
            $vsee_users_table = $db->prefixTable('vsee_users');
            
            $builder = $db->table($vsee_users_table);
            $builder->select('user_id');
            $builder->whereIn('user_id', $user_ids);
            $builder->where('action', 'provider');
            $builder->where('deleted', 0);
            
            $result = $builder->get()->getResult();
            
            // Convertir a array asociativo para búsqueda rápida
            $providers = [];
            foreach ($result as $row) {
                $providers[$row->user_id] = true;
            }
            
            return $providers;
        } catch (\Exception $e) {
            log_message('error', 'Error in _get_providers_batch: ' . $e->getMessage());
            return [];
        }
    }*/

    private function _get_providers_batch($user_ids) {
        if (empty($user_ids)) return [];
        
        try {
            $db = \Config\Database::connect();
            $crm_providers_table = $db->prefixTable('providers');
            
            $builder = $db->table($crm_providers_table);
            $builder->select('user_id');
            $builder->whereIn('user_id', $user_ids);
            $builder->where('deleted', 0);
            
            $result = $builder->get()->getResult();
            
            // Convertir a array asociativo para búsqueda rápida
            $providers = [];
            foreach ($result as $row) {
                $providers[$row->user_id] = true;
            }
            
            return $providers;
        } catch (\Exception $e) {
            log_message('error', 'Error in _get_providers_batch: ' . $e->getMessage());
            return [];
        }
    }

    // Método optimizado para obtener múltiples clínicas en una sola consulta
    private function _get_clinics_batch($user_ids) {
        if (empty($user_ids)) return [];
        
        try {
            helper('clinics_helper');
            
            $db = \Config\Database::connect();
            $branch_table = $db->prefixTable('branch');
            $clinic_table = $db->prefixTable('clinic_directory');
            
            $builder = $db->table($branch_table);
            $builder->select("
                {$branch_table}.id_user,
                {$clinic_table}.id,
                {$clinic_table}.name,
                {$clinic_table}.address,
                {$clinic_table}.phone
            ");
            $builder->join($clinic_table, "{$branch_table}.id_clinic = {$clinic_table}.id");
            $builder->whereIn("{$branch_table}.id_user", $user_ids);
            
            $result = $builder->get()->getResult();
            
            // Agrupar clínicas por usuario
            $clinics_by_user = [];
            foreach ($result as $row) {
                if (!isset($clinics_by_user[$row->id_user])) {
                    $clinics_by_user[$row->id_user] = [];
                }
                $clinics_by_user[$row->id_user][] = $row;
            }
            
            return $clinics_by_user;
        } catch (\Exception $e) {
            log_message('error', 'Error in _get_clinics_batch: ' . $e->getMessage());
            return [];
        }
    }

   /**
     * Método helper privado para enviar notificaciones de escritorio
     * 
     * @param int $to_user_id ID del usuario destinatario
     * @param string $message_content Contenido del mensaje
     * @param int $message_id ID del mensaje
     */
    private function notifyDesktopMessage($to_user_id, $message_content, $message_id, $type = 'message') {
        try {
            // Obtener información del remitente
            $sender_info = $this->Users_model->get_one($this->login_user->id);
            $sender_name = $sender_info->first_name . " " . $sender_info->last_name;
            $sender_image = get_avatar($sender_info->image);
            
            // Preparar datos para la notificación
            $notification_data = array(
                'sender_name' => $sender_name,
                'message_content' => $message_content,
                'message_id' => $message_id,
                'sender_image' => $sender_image,
                'type' => $type,
                'timestamp' => get_current_utc_time()
            );
            
            // Generar JavaScript para disparar evento personalizado
            $js_code = "
            <script>
            // Disparar evento personalizado para notificación de escritorio
            try {
                const messageData = " . json_encode($notification_data) . ";
                
                // Disparar evento que será capturado por DesktopNotifications
                const event = new CustomEvent('newMessageSent', {
                    detail: messageData
                });
                document.dispatchEvent(event);
                
                console.log('📤 Evento de notificación de escritorio disparado:', messageData.sender_name);
            } catch (error) {
                console.log('❌ Error disparando evento de notificación:', error);
            }
            </script>";
            
            // Inyectar el JavaScript en la respuesta
            echo $js_code;
            
        } catch (\Exception $e) {
            log_message('error', 'Error sending desktop message notification: ' . $e->getMessage());
        }
    }
    

}

/* End of file messages.php */
    /* Location: ./app/controllers/messages.php */    