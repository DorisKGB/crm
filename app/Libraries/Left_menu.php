<?php



namespace App\Libraries;



use App\Controllers\Security_Controller;



class Left_menu
{



  private $ci = null;



  public function __construct()
  {

    $this->ci = new Security_Controller(false);
  }



  private function _get_sidebar_menu_items($type = "")
  {

    $dashboard_menu = array("name" => "dashboard", "url" => "dashboard", "class" => "monitor");



    //Obtiene la plantilla asignada a ese usuario
    $selected_dashboard_id = get_setting("user_" . $this->ci->login_user->id . "_dashboard");

    //Este es el menu para habilitar la vista
    if ($selected_dashboard_id) {
      $dashboard_menu = array("name" => "dashboard", "url" => "dashboard/view/" . $selected_dashboard_id, "class" => "monitor", "custom_class" => "dashboard-menu");
    }



    if ($this->ci->login_user->user_type == "staff" && $type !== "client_default") {


      //Aqui se extablece el Dashboard principal establecido
      $sidebar_menu = array("dashboard" => $dashboard_menu);

      //Aqui se ontiene los permisos de usuario
      $permissions = $this->ci->login_user->permissions;

      //Aqui 
      $access_expense = get_array_value($permissions, "expense"); //Valida si ese permiso esta es expense esta en el ARRAY

      $access_invoice = get_array_value($permissions, "invoice");
      //echo $access_invoice; //Valida si invoice esta en el ARRAY

      $access_ticket = get_array_value($permissions, "ticket"); //Valida si ticket esta en el ARRAY

      $access_client = get_array_value($permissions, "client"); //Valida si client esta en el ARRAY

      $access_deposit = get_array_value($permissions, "can_access_deposit_report");

      $access_lead = get_array_value($permissions, "lead"); //Valida si lead esta en el ARRAY

      $access_timecard = get_array_value($permissions, "attendance"); //Valida si lead esta en el attendance

      $access_leave = get_array_value($permissions, "leave"); //Valida si lead esta en el attendance

      $access_estimate = get_array_value($permissions, "estimate");

      $access_contract = get_array_value($permissions, "contract");

      $access_daily = get_array_value($permissions, "daily_permission"); 

      $access_camera = get_array_value($permissions, "camera_permission"); 

      $access_clock = get_array_value($permissions, "clock_permission"); 
      
      $access_events = get_array_value($permissions, "event_permission");

      $access_task = get_array_value($permissions, "task_permission");

      $access_note = get_array_value($permissions, "note_permission");

      $access_team = get_array_value($permissions, "team_permission");

      $access_report = get_array_value($permissions, "report_permission");

      $access_file = get_array_value($permissions, "file_permission");


      $access_support = get_array_value($permissions, "support_permission");

      $access_bills_project = get_array_value($permissions, "bills_project_permission");

      $access_pending = get_array_value($permissions, "pending_permission");

      //$access_daily_report_chart = get_array_value($permissions, "daily_chart_permission");

      $acces_see_price_list = get_array_value($permissions,"can_see_price_list");

      $acces_create_price_list = get_array_value($permissions,"can_create_price_list");

      $access_proposal = get_array_value($permissions, "proposal");

      $access_order = get_array_value($permissions, "order");

      $credit_card_permission = get_array_value($permissions, "credit_card_permission");

      $excuse_permission = get_array_value($permissions, "excuse_permission");

      $stamp_permission = get_array_value($permissions, "stamp_permission");
      $stamp_permission_v1 = get_array_value($permissions, "stamp_permission_v1");

      $directory_permission = get_array_value($permissions, "directory_permission");

      $home_service_permission = get_array_value($permissions, "home_service_permission");
      
      $access_vsee = get_array_value($permissions, "vsee_permission");

      $access_items = ($this->ci->login_user->is_admin || $access_invoice || $access_estimate);  //Valida si al menos una de esas condiciones es verdadera



      $client_message_users = get_setting("client_message_users"); //Obtiene el valor de la config client_message_users

      $client_message_users_array = explode(",", $client_message_users); //obtengo el valor de configiracion 

      $access_messages = ($this->ci->login_user->is_admin || get_array_value($permissions, "message_permission") !== "no" || in_array($this->ci->login_user->id, $client_message_users_array));


      $access_file_manager = get_array_value($permissions, "file_manager"); //Obtiene si tiene el permiso habilitado o NO

      $manage_help_and_knowledge_base = ($this->ci->login_user->is_admin || get_array_value($permissions, "help_and_knowledge_base")); //asigna true o false

      $access_timeline = ($this->ci->login_user->is_admin || get_array_value($permissions, "timeline_permission") !== "no");


      
      if ($this->ci->login_user->is_admin || $access_daily) {

        $sidebar_menu["daily_report"] = array("name" => "daily_report", "url" => "daily_report", "class" => "check-circle");
     }

     if($this->ci->login_user->is_admin || $access_deposit == "view" || $access_deposit == "create" ||  $access_deposit == "all"){

      $sidebar_menu["deposit_report"] = array("name" => "deposit_report", "url" => "deposit_report", "class" => "briefcase");
      
    }

    if($this->ci->login_user->is_admin || $access_camera == "all"){

      $sidebar_menu["camera"] = array("name" => "camera", "url" => "camera", "class" => "camera");
      
    }

    if($this->ci->login_user->is_admin || $access_clock == "all"  || $access_clock == "own" || $access_clock == "clinic"){

      $sidebar_menu["clockin"] = array("name" => "clockin", "url" => "clockin", "class" => "clock");
      
    }


    if($this->ci->login_user->is_admin ||  $excuse_permission == "all" || $excuse_permission == "request" || $excuse_permission == "provider" || $stamp_permission == "all" || $stamp_permission == "provider" || $stamp_permission == "request"){

      //$sidebar_menu["excuse_permission"] = array("name" => "menu_excuse", "url" => "excuse", "class" => "book-open");

      $menu_price = array();

      if($this->ci->login_user->is_admin ||  $excuse_permission == "all" || $excuse_permission == "request" || $excuse_permission == "provider"){

        $menu_price["excuse_permission"] = array("name" => "emit", "url" => "excuse", "class" => "book-open");

      }
      if($this->ci->login_user->is_admin || $stamp_permission == "all" || $stamp_permission == "provider" || $stamp_permission == "request"){

        //$menu_price["stamp_permission"] = array("name" => "menu_stamp", "url" => "stamp", "class" => "file-text");
        
      }

      if($this->ci->login_user->is_admin || $stamp_permission_v1 == "all" || $stamp_permission_v1 == "provider" || $stamp_permission_v1 == "request"){

        $menu_price["stamp_permission_v1"] = array("name" => "menu_stamp_v1", "url" => "stamp/main", "class" => "file-text");
        
      }

      $sidebar_menu["electronic_document"] = array("name" => "electronic_document", "url" => "excuse", "class" => "copy", "submenu" => $menu_price);
      
    }

     if ($this->ci->login_user->is_admin || $access_vsee == "all") {

        //$sidebar_menu["vsee"] = array("name" => "vsee", "url" => "vsee", "class" => "video");
        $sidebar_menu["appointments"] = array("name" => "appointments", "url" => "appointments/teleconsultas", "class" => "heart");
        
     }

     if ($this->ci->login_user->is_admin || $home_service_permission == "all") {

        //$sidebar_menu["vsee"] = array("name" => "vsee", "url" => "vsee", "class" => "video");
        $sidebar_menu["route"] = array("name" => "route", "url" => "home_services", "class" => "map-pin");
        
     }

    
      if ($this->ci->login_user->is_admin || $directory_permission != "") {

        //$sidebar_menu["directory"] = array("name" => "directory", "url" => "directory", "class" => "grid");
     }


    /*if($this->ci->login_user->is_admin || $stamp_permission == "all" || $stamp_permission == "provider"){

      $sidebar_menu["stamp_permission"] = array("name" => "menu_stamp", "url" => "stamp", "class" => "file-text");
      
    }*/



     if( $acces_create_price_list ||  $this->ci->login_user->is_admin){
        $menu_price = array();

        if(get_setting("module_price") == "1" && ($this->ci->login_user->is_admin || $acces_create_price_list)){

          $menu_price["view_price"] = array("name" => "view_price", "url" => "price", "class" => "users");
          $menu_price["create_price"] = array("name" => "create_price", "url" => "price/service", "class" => "users");
        }

        if (count($menu_price) && ($acces_create_price_list || $this->ci->login_user->is_admin)) {

          $sidebar_menu["price"] = array("name" => "price", "url" => "price", "class" => "dollar-sign", "submenu" => $menu_price);
        }

     }else{
        if(get_setting("module_price") == "1" && ($this->ci->login_user->is_admin || $acces_see_price_list)){

          $sidebar_menu["view_price"] = array("name" => "view_price", "url" => "price/customer", "class" => "dollar-sign");
        }
     }
     
  
      if (get_setting("module_event") == "1" && ($access_events || $this->ci->login_user->is_admin)) {  // Si el modulo evento tiene un valor de 1 permite que el usuario tenga ese modulo habilitado
 
        $sidebar_menu["events"] = array("name" => "events", "url" => "events", "class" => "calendar"); 
        // Modulo de eventos habilitado
      }


      if ($this->ci->login_user->is_admin || $access_client) {

        $sidebar_menu["clients"] = array("name" => "clients", "url" => "clients", "class" => "briefcase");
        //$sidebar_menu["daily_report"] = array("name" => "daily_report", "url" => "daily_report", "class" => "check-circle");
      }


      
      if ($this->ci->login_user->is_admin || get_array_value($this->ci->login_user->permissions, "do_not_show_projects") == "") {

        $sidebar_menu["projects"] = array("name" => "projects", "url" => "projects/all_projects", "class" => "command");
      }


      if($this->ci->login_user->is_admin || $access_task){
          $sidebar_menu["tasks"] = array("name" => "tasks", "url" => "tasks/all_tasks_kanban", "class" => "check-circle");
      }
   



      if (get_setting("module_lead") == "1" && ($this->ci->login_user->is_admin || $access_lead)) {

        $sidebar_menu["leads"] = array("name" => "leads", "url" => "leads", "class" => "layers");
      }



      // if (get_setting("module_subscription") && ($this->ci->login_user->is_admin || $access_subscription)) {

      //   $sidebar_menu["subscriptions"] = array("name" => "subscriptions", "url" => "subscriptions", "class" => "repeat");
      // }



      $show_payments_menu = false;

      $show_expenses_menu = false;



      $sales_submenu = array();



      if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {

        $sales_submenu[] = array("name" => "invoices", "url" => "invoices", "class" => "file-text");
      }



      if (get_setting("module_order") == "1" && ($this->ci->login_user->is_admin || $access_order)) {

        $sales_submenu[] = array("name" => "orders_list", "url" => "orders", "class" => "truck");

        $sales_submenu[] = array("name" => "store", "url" => "store", "class" => "list");
      }



      if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {

        $sales_submenu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "dollar-sign");

        $show_payments_menu = true;
      }



      if ($access_items && (get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1")) {

        $sales_submenu[] = array("name" => "items", "url" => "items", "class" => "list");
      }



      if (get_setting("module_contract") && ($this->ci->login_user->is_admin || $access_contract)) {

        $sales_submenu[] = array("name" => "contracts", "url" => "contracts", "class" => "book-open");
      }

      if (get_setting("module_credit_card") && ($this->ci->login_user->is_admin || $credit_card_permission)) {

        $sales_submenu[] = array("name" => "credit_card", "url" => "credit_card", "class" => "book-open");
      }

      if (count($sales_submenu)) {

        $sidebar_menu["sales"] = array("name" => "sales", "class" => "shopping-cart", "submenu" => $sales_submenu);
      }





      // $prospects_submenu = array();
      // if (get_setting("module_estimate") && ($this->ci->login_user->is_admin || $access_estimate)) {
      //   $prospects_submenu["estimates"] = array("name" => "estimate_list", "url" => "estimates", "class" => "file");
      //   if (get_setting("module_estimate_request")) {

      //     $prospects_submenu["estimate_requests"] = array("name" => "estimate_requests", "url" => "estimate_requests", "class" => "file");

      //     $prospects_submenu["estimate_forms"] = array("name" => "estimate_forms", "url" => "estimate_requests/estimate_forms", "class" => "file");
      //   }
      // }

      // if (get_setting("module_proposal") && ($this->ci->login_user->is_admin || $access_proposal)) {

      //   $prospects_submenu["proposals"] = array("name" => "proposals", "url" => "proposals", "class" => "coffee");
      // }

      // if (count($prospects_submenu)) {

      //   $sidebar_menu["prospects"] = array("name" => "prospects", "url" => "estimates", "class" => "anchor", "submenu" => $prospects_submenu);
      // }

      if (get_setting("module_note") == "1" &&  ($access_note || $this->ci->login_user->is_admin)) {

        $sidebar_menu["notes"] = array("name" => "notes", "url" => "notes", "class" => "book");
      }



      if (get_setting("module_message") == "1" && $access_messages) {

        $sidebar_menu["messages"] = array("name" => "messages", "url" => "messages", "class" => "message-circle", "badge" => count_unread_message(), "badge_class" => "bg-primary");
      }







      $team_submenu = array();



      if (get_array_value($this->ci->login_user->permissions, "hide_team_members_list") != "1") {

        $team_submenu["team_members"] = array("name" => "team_members", "url" => "team_members", "class" => "users");
      }





      if (get_setting("module_attendance") == "1" && ($this->ci->login_user->is_admin || $access_timecard)) {

        $team_submenu["attendance"] = array("name" => "attendance", "url" => "attendance", "class" => "clock");
      } else if (get_setting("module_attendance") == "1") {

        $team_submenu["attendance"] = array("name" => "attendance", "url" => "attendance/attendance_info", "class" => "clock");
      }





      if (get_setting("module_leave") == "1" && ($this->ci->login_user->is_admin || $access_leave)) {

        $team_submenu["leaves"] = array("name" => "leaves", "url" => "leaves", "class" => "log-out");
      } else if (get_setting("module_leave") == "1") {

        $team_submenu["leaves"] = array("name" => "leaves", "url" => "leaves/leave_info", "class" => "log-out");
      }







      if (get_setting("module_timeline") == "1" && $access_timeline) {

        $team_submenu["timeline"] = array("name" => "timeline", "url" => "timeline", "class" => "send");
      }





      if (get_setting("module_announcement") == "1") {

        $team_submenu["announcements"] = array("name" => "announcements", "url" => "announcements", "class" => "bell");
      }





      if (count($team_submenu) && ($access_team || $this->ci->login_user->is_admin)) {

        $sidebar_menu["team"] = array("name" => "team", "url" => "team_members", "class" => "users", "submenu" => $team_submenu);
      }





      if (get_setting("module_ticket") == "1" && ($this->ci->login_user->is_admin || $access_ticket)) {



        $ticket_badge = 0;

        if ($this->ci->login_user->is_admin || $access_ticket === "all") {

          $ticket_badge = count_new_tickets();
        } else if ($access_ticket === "specific") {

          $specific_ticket_permission = get_array_value($permissions, "ticket_specific");

          $ticket_badge = count_new_tickets($specific_ticket_permission);
        } else if ($access_ticket === "assigned_only") {

          $ticket_badge = count_new_tickets("", $this->ci->login_user->id);
        }



        // 



        $sidebar_menu["tickets"] = array("name" => "tickets", "url" => "tickets", "class" => "life-buoy", "badge" => $ticket_badge, "badge_class" => "bg-primary");
      }





      if (get_setting("module_expense") == "1" && ($this->ci->login_user->is_admin || $access_expense)) {

        $sidebar_menu["expenses"] = array("name" => "expenses", "url" => "expenses", "class" => "arrow-right-circle");

        $show_expenses_menu = true;
      }


      if($this->ci->login_user->is_admin || $access_report){
        $sidebar_menu["reports"] = array(
          "name" => "reports",
          "url" => "reports/index",
          "class" => "pie-chart",
  
          "sub_pages" => array(
  
            "invoices/invoices_summary",
  
            "orders/orders_summary",
  
            "projects/all_timesheets",
  
            "expenses/income_vs_expenses",
  
            "invoice_payments/payments_summary",
  
            "expenses/summary",
  
            "projects/team_members_summary",
  
            "leads/converted_to_client_report",
  
            "tickets/tickets_chart_report"
  
          )
  
        );
      }
      





      $access_file_manager = true;

      if (get_setting("module_file_manager") == "1" && ($this->ci->login_user->is_admin || $access_file)) {

        $sidebar_menu["file_manager"] = array("name" => "files", "url" => "file_manager", "class" => "folder");

        $show_expenses_menu = true;
      }





      $module_help = get_setting("module_help") == "1" ? true : false;

      $module_knowledge_base = get_setting("module_knowledge_base") == "1" ? true : false;



      //prepere the help and suppor menues

      if (($access_support || $this->ci->login_user->is_admin ) && ($module_help || $module_knowledge_base)) {



        $help_knowledge_base_menues = array();

        $main_url = "help";



        if ($module_help) {

          $help_knowledge_base_menues[] = array("name" => "help", "url" => $main_url, "class" => "help-circle");
        }



        //push the help manage menu if user has access

        if ($manage_help_and_knowledge_base && $module_help) {

          $help_knowledge_base_menues[] = array("name" => "articles", "url" => "help/help_articles", "class" => "help-circle");

          $help_knowledge_base_menues[] = array("name" => "categories", "url" => "help/help_categories", "class" => "help-circle");
        }



        if ($module_knowledge_base) {

          $help_knowledge_base_menues[] = array("name" => "knowledge_base", "url" => "knowledge_base", "class" => "help-circle");
        }



        //push the knowledge_base manage menu if user has access

        if ($manage_help_and_knowledge_base && $module_knowledge_base) {

          $help_knowledge_base_menues[] = array("name" => "articles", "category" => "help", "url" => "help/knowledge_base_articles", "class" => "help-circle");

          $help_knowledge_base_menues[] = array("name" => "categories", "category" => "help", "url" => "help/knowledge_base_categories", "class" => "help-circle");
        }





        if (!$module_help) {

          $main_url = "knowledge_base";
        }



        $sidebar_menu["help_and_support"] = array(
          "name" => "help_and_support",
          "url" => $main_url,
          "class" => "help-circle",

          "submenu" => $help_knowledge_base_menues

        );
      }






      //$this->ci->login_user->is_admin || get_array_value($this->ci->login_user->permissions, "can_manage_all_kinds_of_settings")
      if ($this->ci->login_user->is_admin) {

        $sidebar_menu["settings"] = array(
          "name" => "settings",
          "url" => "settings/general",
          "class" => "settings",

          "sub_pages" => array(

            "email_templates/index",

            "left_menu/index",

            "updates/index",

            "roles/index",

            "roles/user_roles",

            "team/index",

            "dashboard/client_default_dashboard",

            "left_menus/index",

            "company/index",

            "item_categories/index",

            "payment_methods/index",

            "custom_fields/view",

            "client_groups/index",

            "expense_categories/index",

            "leave_types/index",

            "ticket_types/index",

            "lead_status/index",

            "pages/index",

            "plugins/index"

          )
        );
      }



      $sidebar_menu = app_hooks()->apply_filters('app_filter_staff_left_menu', $sidebar_menu);
    } else {

      //client menu

      //get the array of hidden menu

      $hidden_client_menus = explode(",", get_setting("hidden_client_menus"));



      //get the client contact permissions

      $users_model = $this->ci->Users_model->get_one($this->ci->login_user->id);

      $client_permissions = explode(",", $users_model->client_permissions);



      $sidebar_menu[] = $dashboard_menu;



      if ($this->ci->can_client_access("event")) {

        $sidebar_menu[] = array("name" => "events", "url" => "events", "class" => "calendar");
      }



      if ($this->ci->can_client_access("note") && get_setting("client_can_access_notes")) {

        $sidebar_menu[] = array("name" => "notes", "url" => "notes", "class" => "book");
      }



      //check message access settings for clients

      if ($this->ci->can_client_access("message") && get_setting("client_message_users")) {

        $sidebar_menu[] = array("name" => "messages", "url" => "messages", "class" => "message-circle", "badge" => count_unread_message());
      }



      if ($this->ci->can_client_access("project", false)) {

        $sidebar_menu[] = array("name" => "projects", "url" => "projects/all_projects", "class" => "command");
      }



      if ($this->ci->can_client_access("contract")) {

        $sidebar_menu[] = array("name" => "contracts", "url" => "contracts", "class" => "book-open");
      }



      if ($this->ci->can_client_access("proposal")) {

        $sidebar_menu[] = array("name" => "proposals", "url" => "proposals", "class" => "coffee");
      }



      if ($this->ci->can_client_access("estimate")) {

        $sidebar_menu[] = array("name" => "estimates", "url" => "estimates", "class" => "file");
      }



      // if ($this->ci->can_client_access("subscription")) {

      //   $sidebar_menu["subscriptions"] = array("name" => "subscriptions", "url" => "subscriptions", "class" => "repeat");
      // }



      if ($this->ci->can_client_access("invoice")) {

        if ($this->ci->can_client_access("invoice")) {

          $sidebar_menu[] = array("name" => "invoices", "url" => "invoices", "class" => "file-text");
        }

        if ($this->ci->can_client_access("payment", false)) {

          $sidebar_menu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "dollar-sign");
        }
      }



      if ($this->ci->can_client_access("store", false) && get_setting("client_can_access_store")) {

        $sidebar_menu[] = array("name" => "store", "url" => "store", "class" => "truck");

        $sidebar_menu[] = array("name" => "orders", "url" => "orders", "class" => "shopping-cart");
      }



      if ($this->ci->can_client_access("ticket")) {

        $sidebar_menu[] = array("name" => "tickets", "url" => "tickets", "class" => "life-buoy");
      }



      if ($this->ci->can_client_access("announcement")) {

        $sidebar_menu[] = array("name" => "announcements", "url" => "announcements", "class" => "bell");
      }



      $sidebar_menu[] = array("name" => "users", "url" => "clients/users", "class" => "users");



      if (get_setting("client_can_view_files")) {

        $sidebar_menu[] = array("name" => "files", "url" => "clients/files/" . $this->ci->login_user->id . "/page_view", "class" => "image");
      }



      $sidebar_menu[] = array("name" => "my_profile", "url" => "clients/contact_profile/" . $this->ci->login_user->id, "class" => "settings");



      if ($this->ci->can_client_access("knowledge_base")) {

        $sidebar_menu[] = array("name" => "knowledge_base", "url" => "knowledge_base", "class" => "help-circle");
      }



      $sidebar_menu = app_hooks()->apply_filters('app_filter_client_left_menu', $sidebar_menu);
    }

    /*if(!$this->ci->login_user->is_admin){
        if($this->ci->login_user->role_id == 1 || $this->ci->login_user->role_id == 5 || $this->ci->login_user->role_id == 7 ){
          $sidebar_menu = array("dashboard" => $dashboard_menu);
        }
        if($this->ci->login_user->role_id == 5 || $this->ci->login_user->role_id == 7){
          $sidebar_menu["daily_report"] = array("name" => "daily_report", "url" => "daily_report", "class" => "check-circle");
        }
        if($this->ci->login_user->role_id == 1 || $this->ci->login_user->role_id == 5 || $this->ci->login_user->role_id == 7 ){
          $sidebar_menu["tasks"] = array("name" => "tasks", "url" => "tasks/all_tasks_kanban", "class" => "book-open");
        }
        if($this->ci->login_user->role_id == 1 || $this->ci->login_user->role_id == 5 || $this->ci->login_user->role_id == 7){
          $sidebar_menu["projects"] = array("name" => "projects", "url" => "projects/all_projects", "class" => "command");
        }
    }*/


    //var_dump($sidebar_menu);
      


    return $this->position_items_for_default_left_menu($sidebar_menu);
  }



  function _get_active_menu($sidebar_menu = array())
  {

    $router = service('router');

    $controller_name = strtolower(get_actual_controller_name($router));

    $uri_string = uri_string();

    $current_url = get_uri($uri_string);

    $method_name = $router->methodName();



    $found_url_active_key = null;



    foreach ($sidebar_menu as $key => $menu) {

      if (isset($menu["name"])) {

        $menu_name = get_array_value($menu, "name");

        $menu_url = get_array_value($menu, "url");



        if ($controller_name == $menu_url) {

          $found_url_active_key = $key;
        }



        //compare with current url

        if ($menu_url && ($menu_url === $current_url || get_uri($menu_url) === $current_url)) {

          $found_url_active_key = $key;
        }



        //compare with controller name

        if ($menu_name === $controller_name) {

          $found_url_active_key = $key;
        }



        //check in submenu values

        $submenu = get_array_value($menu, "submenu");

        if ($submenu && count($submenu)) {

          foreach ($submenu as $sub_menu) {

            if (isset($sub_menu['name'])) {



              $sub_menu_url = get_array_value($sub_menu, "url");



              if ($controller_name == $sub_menu_url) {

                $found_url_active_key = $key;
              }



              //compare with current url

              if ($sub_menu_url === $current_url || get_uri($sub_menu_url) === $current_url) {

                $found_url_active_key = $key;
              }



              //compare with controller name

              if (get_array_value($sub_menu, "name") === $controller_name) {

                $found_url_active_key = $key;
              } else if (get_array_value($sub_menu, "category") === $controller_name) {

                $found_url_active_key = $key;
              }
            }
          }
        }





        $sub_pages = get_array_value($menu, "sub_pages");

        if ($sub_pages) {

          foreach ($sub_pages as $sub_page_ur) {

            if ($sub_page_ur == $controller_name . "/" . $method_name) {

              $found_url_active_key = $key;
            }
          }
        }
      }
    }



    if (!is_null($found_url_active_key)) {

      $sidebar_menu[$found_url_active_key]["is_active_menu"] = 1;
    }





    return $sidebar_menu;
  }



  function get_available_items($type = "default")
  {

    $items_array = $this->_prepare_sidebar_menu_items($type);



    //remove used items

    $default_left_menu_items = $this->_get_left_menu_from_setting($type);



    foreach ($default_left_menu_items as $default_item) {

      unset($items_array[get_array_value($default_item, "name")]);
    }



    //since all menu items will be added to the customization area when there is no item, don't show anything here

    if (!$default_left_menu_items) {

      $items_array = array();
    }



    $items = "";

    foreach ($items_array as $item) {

      $items .= $this->_get_item_data($item, true);
    }



    return $items ? $items : "<span class='text-off empty-area-text'>" . app_lang('no_more_items_available') . "</span>";
  }



  private function _prepare_sidebar_menu_items($type = "", $return_sub_menu_data = false)
  {

    $final_items_array = array();

    $items_array = $this->_get_sidebar_menu_items($type);



    foreach ($items_array as $item) {

      $main_menu_name = get_array_value($item, "name");



      if (isset($item["submenu"])) {

        //first add this menu removing the submenus

        $main_menu = $item;

        unset($main_menu["submenu"]);

        $final_items_array[$main_menu_name] = $main_menu;



        $submenu = get_array_value($item, "submenu");

        foreach ($submenu as $key => $s_menu) {

          //prepare help items differently

          if ($main_menu_name == "help_and_support") {

            $s_menu = $this->_make_customized_sub_menu_for_help_and_support($key, $s_menu);
          }



          if ($return_sub_menu_data) {

            $s_menu["is_sub_menu"] = true;
          }



          if (get_array_value($s_menu, "class")) {

            $final_items_array[get_array_value($s_menu, "name")] = $s_menu;
          }
        }
      } else {

        $final_items_array[$main_menu_name] = $item;
      }
    }



    //add todo

    $final_items_array["todo"] = array("name" => "todo", "url" => "todo", "class" => "check-square");



    return $final_items_array;
  }



  private function _make_customized_sub_menu_for_help_and_support($key, $s_menu)
  {

    if ($key == 1) {

      $s_menu["name"] = "help_articles";
    } else if ($key == 2) {

      $s_menu["name"] = "help_categories";
    } else if ($key == 4) {

      $s_menu["name"] = "knowledge_base_articles";
    } else if ($key == 5) {

      $s_menu["name"] = "knowledge_base_categories";
    }



    return $s_menu;
  }



  private function _get_left_menu_from_setting_for_rander($is_preview = false, $type = "default")
  {

    $user_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");

    $default_left_menu = ($type == "client_default" || $this->ci->login_user->user_type == "client") ? get_setting("default_client_left_menu") : get_setting("default_left_menu");

    $custom_left_menu = "";



    //for preview, show the edit type preview

    if ($is_preview) {

      $custom_left_menu = $default_left_menu; //default preview

      if ($type == "user") {

        $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //user level preview

      }
    } else {

      $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //page rander

    }



    return $custom_left_menu ? json_decode(json_encode(@unserialize($custom_left_menu)), true) : array();
  }



  private function _get_left_menu_from_setting($type)
  {

    if ($type == "client_default") {

      $default_left_menu = get_setting("default_client_left_menu");
    } else if ($type == "user") {

      $default_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");
    } else {

      $default_left_menu = get_setting("default_left_menu");
    }



    return $default_left_menu ? json_decode(json_encode(@unserialize($default_left_menu)), true) : array();
  }



  public function _get_item_data($item, $is_default_item = false)
  {

    $name = get_array_value($item, "name");

    $language_key = get_array_value($item, "language_key");

    $url = get_array_value($item, "url");

    $is_sub_menu = get_array_value($item, "is_sub_menu");

    $open_in_new_tab = get_array_value($item, "open_in_new_tab");

    $icon = get_array_value($item, "icon");



    if ($name) {

      $sub_menu_class = "";

      $clickable_menu_class = "make-sub-menu";

      $clickable_icon = "<i data-feather='corner-right-down' class='icon-14'></i>";

      if ($is_sub_menu) {

        $sub_menu_class = "ml20";

        $clickable_menu_class = "make-root-menu";

        $clickable_icon = "<i data-feather='corner-up-left' class='icon-14'></i>";
      }



      $extra_attr = "";

      $edit_button = "";

      $name_lang = "";

      if ($is_default_item || !$url) {

        $name_lang = app_lang($name);
      } else {

        if ($language_key) {

          $name_lang = app_lang($language_key);
        } else {

          $name_lang = $name;
        }



        //custom menu item

        $extra_attr = "data-url='$url' data-icon='$icon' data-custom_menu_item_id='" . rand(2000, 400000000) . "' data-open_in_new_tab='$open_in_new_tab' data-language_key='$language_key'";

        $edit_button = modal_anchor(get_uri("left_menus/add_menu_item_modal_form"), "<i data-feather='edit' class='icon-14'></i> ", array("title" => app_lang('edit'), "class" => "custom-menu-edit-button", "data-post-title" => $name, "data-post-url" => $url, "data-post-is_sub_menu" => $is_sub_menu, "data-post-icon" => $icon, "data-post-open_in_new_tab" => $open_in_new_tab, "data-post-language_key" => $language_key));
      }



      return "<div data-value='" . $name . "' $extra_attr class='left-menu-item mb5 widget clearfix p10 bg-white $sub_menu_class'>

                        <span class='float-start text-start'><i data-feather='move' class='icon-16 text-off mr5'></i> " . $name_lang . "</span>

                        <span class='float-end invisible'>

                            <span class='clickable $clickable_menu_class toggle-menu-icon' title='" . app_lang("make_previous_items_sub_menu") . "'>$clickable_icon</span>

                            $edit_button

                            <span class='clickable delete-left-menu-item' title=" . app_lang("delete") . "><i data-feather='x' class='icon-14 text-danger'></i></span>

                        </span>

                    </div>";
    }
  }



  function get_sortable_items($type = "default")
  {

    $items = "<div id='menu-item-list-2' class='js-left-menu-scrollbar add-column-drop text-center p15 menu-item-list sortable-items-container'>";



    $default_left_menu_items = $this->_get_left_menu_from_setting($type);

    if (count($default_left_menu_items)) {

      foreach ($default_left_menu_items as $item) {

        $items .= $this->_get_item_data($item);
      }
    } else {

      //if there has no item in the customization panel, show the default items

      $items_array = $this->_prepare_sidebar_menu_items($type, true);
      //var_dump($item_array);

      foreach ($items_array as $item) {

        $items .= $this->_get_item_data($item, true);
      }
    }



    $items .= "</div>";



    return $items;
  }



  function rander_left_menu($is_preview = false, $type = "default")
  {

    $final_left_menu_items = array();

    $custom_left_menu_items = $this->_get_left_menu_from_setting_for_rander($is_preview, $type);



    if ($custom_left_menu_items) {

      $left_menu_items = $this->_prepare_sidebar_menu_items($type);

      $last_final_menu_item = ""; //store the last menu item of final left menu to add submenu to this item



      foreach ($custom_left_menu_items as $custom_left_menu_item) {

        $item_value_array = $this->_get_item_array_value($custom_left_menu_item, $left_menu_items);

        $is_sub_menu = get_array_value($custom_left_menu_item, "is_sub_menu");



        if ($is_sub_menu) {

          //this is a sub menu, move it to it's parent item

          $final_left_menu_items[$last_final_menu_item]["submenu"][] = $item_value_array;
        } else {

          $final_left_menu_items[] = $item_value_array;

          $last_final_menu_item = end($final_left_menu_items);

          $last_final_menu_item = key($final_left_menu_items);
        }
      }
    }



    if (count($final_left_menu_items)) {

      $view_data["sidebar_menu"] = $final_left_menu_items;
    } else {

      $view_data["sidebar_menu"] = $this->_get_sidebar_menu_items($type);
    }



    if (!$is_preview) {

      $view_data["sidebar_menu"] = $this->_get_active_menu($view_data["sidebar_menu"]);
    }



    $view_data["is_preview"] = $is_preview;

    $view_data["login_user"] = $this->ci->login_user;

    return view("includes/left_menu", $view_data);
  }



  private function _get_item_array_value($data_array, $left_menu_items)
  {

    $name = get_array_value($data_array, "name");

    $language_key = get_array_value($data_array, "language_key");

    $url = get_array_value($data_array, "url");

    $icon = get_array_value($data_array, "icon");

    $open_in_new_tab = get_array_value($data_array, "open_in_new_tab");

    $item_value_array = array();



    if ($url) { //custom menu item

      $item_value_array = array("name" => $name, "language_key" => $language_key, "url" => $url, "is_custom_menu_item" => true, "class" => "$icon", "open_in_new_tab" => $open_in_new_tab);
    } else if (array_key_exists($name, $left_menu_items)) { //default menu items

      $item_value_array = get_array_value($left_menu_items, $name);
    }



    return $item_value_array;
  }



  //position items for plugins

  private function position_items_for_default_left_menu($sidebar_menu = array())
  {

    foreach ($sidebar_menu as $key => $menu) {
      
      $position = get_array_value($menu, "position");
      
      if ($position) {

        $position = $position - 1;

        $sidebar_menu = array_slice($sidebar_menu, 0, $position, true) +

          array($key => $menu) +

          array_slice($sidebar_menu, $position, NULL, true);
      }
    }


    //var_dump($sidebar_menu);
    return $sidebar_menu;
  }
}
