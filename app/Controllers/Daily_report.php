<?php


namespace App\Controllers;

use App\Models\Daily_report_model;
use App\Models\Notifications_model;
use App\Models\Users_model;
use App\Libraries\ExcelLibrary;
use App\Libraries\Pdf;

class Daily_report extends Security_Controller
{
  protected $userModel;
  protected $dailyReportModel;
  protected $notificationsModel;

  public function __construct()
  {
    parent::__construct();
    $this->init_permission_checker("client");
    $this->dailyReportModel = new Daily_report_model();
    $this->notificationsModel = new Notifications_model();
    $this->userModel = new Users_model();
  }

  /**
   * Mostrar la página principal del módulo de reporte diario.
   */
  public function index()
  {
    $this->check_module_availability("module_note");

    if ($this->login_user->user_type == "client" && !get_setting("client_can_access_notes")) {
      app_redirect("forbidden");
    }

    $reports = $this->dailyReportModel->findAllReports();
    $clinics = $this->dailyReportModel->getClinics2($this->login_user->id);
    $clinic_options = $this->getClinicOptions2($this->login_user->id);
    $attemps = $this->dailyReportModel->getRowAttemptsDailyUser($this->login_user->id,date('Y-m-d'));
    if(!$attemps){
      $insert = $this->dailyReportModel->insertAttempt($this->login_user->id,5);
      $attemps = $this->dailyReportModel->getRowAttemptsDailyUser($this->login_user->id,date('Y-m-d'));
    }
    $log = $this->dailyReportModel->getAllLog();

    $data = [
      'reports' => $reports,
      'clinics' => $clinics,
      'clinic_options' => $clinic_options,
      'label_column' => "col-md-2",
      'field_column' => "col-md-10",
      'num_attemps' => ($attemps->attempts - $attemps->used),
      'logs' => $log
    ];

    return $this->template->rander("daily_report/index", $data);
  }

  /**
   * Mostrar el formulario modal para añadir o editar un reporte diario.
   * @param int $id
   */
  public function modal_form($id = 0)
  {
    $view_data = [
      'model_info' => $this->dailyReportModel->findReportById($id),
      'clinic_options' => $this->getClinicOptions2($this->login_user->id),
      'login_user' => $this->login_user,
      'label_column' => "col-md-2",
      'field_column' => "col-md-10"
    ];

    return $this->template->view('daily_report/modal_form', $view_data);
  }

  /**
   * Obtener las opciones de clínicas disponibles.
   */
  protected function getClinicOptions()
  {
    $clinics = $this->dailyReportModel->getClinics();
    $clinic_options = [];

    foreach ($clinics as $clinic) {
      $clinic_options[$clinic->id] = $clinic->name;
    }

    return $clinic_options;
  }

  protected function getClinicOptions2($user_id)
  {
    $clinics = $this->dailyReportModel->getClinics2($user_id);
    $clinic_options = [];

    foreach ($clinics as $clinic) {
      $clinic_options[$clinic->id] = $clinic->name;
    }

    return $clinic_options;
  }

  /**
   * Guardar un reporte diario.
   */
  public function saveReport()
  {
    $validation = \Config\Services::validation();
    $validation->setRules($this->validateInput());

    $input = $this->request->getPost(); //obtiene los datos
    log_message('debug', 'Datos recibidos: ' . print_r($input, true));

    if ($validation->run($input)) { //valida los datos
      $file = $this->request->getFile('report_file');
      $input['report_file'] = $this->handleFileUpload($file);

      $clinic = $this->dailyReportModel->getClinicById($input['clinic_id']);
      if ($clinic) {
        $input['clinic_name'] = $clinic->name;
      }

      $input['submitted_by'] = $this->login_user->first_name . " " . $this->login_user->last_name;

      //log_message('debug', 'Datos a guardar: ' . print_r($input, true));

      $k = $this->dailyReportModel->getDailyReportsCountNow($input['clinic_id'],false,$input['report_date']);
      if ($k == 0) {
        $input = clean_data($input);
        $save_id = $this->dailyReportModel->saveReport($input);

        if ($save_id) {
          //Se ha guardo el reporte "ENVIAR NOTIIFICACION"
          //log_notification("daily_report_success", array("event_id" => $save_id), $this->login_user->id);
          //log_notification("new_event_added_in_calendar", array("event_id" => $save_id),$this->login_user->id);
          $admins = $this->userModel->get_admin_ids();
          $user_account = $this->userModel->get_id_roles_for(7);
          $this->notificationsModel->create_notification_daily_reports($this->login_user->id, $save_id, $admins);
          $this->notificationsModel->create_notification_daily_reports($this->login_user->id, $save_id, $user_account);
          //log_notification("calendar_event_modified", array("event_id" => 1),$this->login_user->id);
          return $this->response->setJSON(["success" => true, "state" => "0", "data" => $input, 'id' => $save_id, 'message' => app_lang('record_saved')]);
        } else {
          log_message('error', 'Error al guardar los datos');
          return $this->response->setJSON(["success" => false, "state" => "1", 'message' => app_lang('error_occurred')]);
        }
      } else {
        //log_message('error', 'Error en la validación de datos: ' . print_r($validation->getErrors(), true));
        return $this->response->setJSON(["success" => false, "state" => "2", 'message' => 'Ya existe un reporte agregado con la misma fecha y la misma clinica.']);
      }
    } else {
      log_message('error', 'Error en la validación de datos: ' . print_r($validation->getErrors(), true));
      return $this->response->setJSON(["success" => false, "state" => "3", 'message' => $validation->getErrors()]);
    }
  }

  /**
   * Validar la entrada del formulario.
   */
  protected function validateInput()
  {
    return [
      "clinic_id" => "required|numeric",
      "report_file" => "uploaded[report_file]|max_size[report_file,10240]|ext_in[report_file,png,jpg,jpeg,pdf]",
      "report_date" => "required",
      "sales_cash" => "numeric",
      "sales_card" => "numeric",
      "sales_other" => "numeric",
      "new_patients_total" => "numeric",
      "followup_patients_total" => "numeric",
      "referral_google" => "numeric",
      "referral_referred" => "numeric",
      "referral_mail" => "numeric",
      "referral_walkby" => "numeric",
      "referral_facebook" => "numeric",
      "referral_events" => "numeric",
      "referral_instagram" => "numeric",
      "referral_youtube" => "numeric",
      "referral_tiktok" => "numeric",
      "referral_radio" => "numeric",
      "referral_newspaper" => "numeric",
      "referral_tv" => "numeric",
      "uninsured_patients" => "numeric",
      "insured_patients" => "numeric",
      "boxed_samples" => "numeric",
      "added_to_square_ecw" => "numeric",
    ];
  }

  /**
   * Manejar la carga de archivos.
   */
  protected function handleFileUpload($file)
  {
    if ($file && $file->isValid()) {
      $fileName = $file->getRandomName();
      $file->move(WRITEPATH . 'uploads', $fileName);
      return $fileName;
    } else {
      return null;
    }
  }

  /**
   * Obtener datos filtrados de los reportes diarios.
   */
  public function list_data()
  {
    $request = \Config\Services::request();
    $start = $request->getPost('start');
    $length = $request->getPost('length');
    $search = $request->getPost('search')['value'] ?? '';
    $order = $request->getPost('order') ?? [];
    $columnIndex = $order[0]['column'] ?? 0;
    $columns = $request->getPost('columns') ?? [];
    $columnName = $columns[$columnIndex]['data'] ?? 'id';
    $columnSortOrder = $order[0]['dir'] ?? 'asc';

    $startDate = $request->getPost('startDate') ?? null;
    $endDate = $request->getPost('endDate') ?? null;
    $clinicId = $request->getPost('clinicId') ?? null;

    $reports = $this->dailyReportModel->getFilteredReports2($start, $length, $search, $columnName, $columnSortOrder, $startDate, $endDate, $clinicId, $this->login_user->id);
    $totalRecords = $this->dailyReportModel->getTotalReportsCount();
    $filteredRecords = $this->dailyReportModel->getFilteredReportsCount2($search, $startDate, $endDate, $clinicId, $this->login_user->id);

    $data = [];
    foreach ($reports as $report) {
      $data[] = [
        'submitted_by' => $report['submitted_by'],
        'id' => $report['id'],
        'clinic_id' => $report['clinic_id'],
        'clinic_name' => $report['clinic_name'],
        'report_date' => $report['report_date'],
        'report_file' => (strpos($report['report_file'], 'https://drive.google.com') === 0)
          ? $report['report_file']
          : base_url('index.php/getViewReport/' . esc($report['report_file'])),
        'sales_cash' => $report['sales_cash'],
        'sales_card' => $report['sales_card'],
        'sales_other' => $report['sales_other'],
        'new_patients_total' => $report['new_patients_total'],
        'followup_patients_total' => $report['followup_patients_total'],
        'referral_google' => $report['referral_google'],
        'referral_referred' => $report['referral_referred'],
        'referral_mail' => $report['referral_mail'],
        'referral_walkby' => $report['referral_walkby'],
        'referral_facebook' => $report['referral_facebook'],
        'referral_events' => $report['referral_events'],
        'referral_instagram' => $report['referral_instagram'],
        'referral_youtube' => $report['referral_youtube'],
        'referral_tiktok' => $report['referral_tiktok'],
        'referral_radio' => $report['referral_radio'],
        'referral_newspaper' => $report['referral_newspaper'],
        'referral_tv' => $report['referral_tv'],
        'uninsured_patients' => $report['uninsured_patients'],
        'insured_patients' => $report['insured_patients'],
        'actions' => ''
      ];
    }

    $headers = [
      app_lang("reported_by"),
      app_lang("report_id"),
      app_lang("clinic_id"),
      app_lang("clinic_name"),
      app_lang("report_date"),
      app_lang("file"),
      app_lang("cash_sales"),
      app_lang("card_sales"),
      app_lang("other_sales"),
      app_lang("new_patients"),
      app_lang("followup_patients"),
      app_lang("referral_google"),
      app_lang("referral_referred"),
      app_lang("referral_mail"),
      app_lang("referral_walkby"),
      app_lang("referral_facebook"),
      app_lang("referral_events"),
      app_lang("referral_instagram"),
      app_lang("referral_youtube"),
      app_lang("referral_tiktok"),
      app_lang("referral_radio"),
      app_lang("referral_newspaper"),
      app_lang("referral_tv"),
      app_lang("uninsured_patients"),
      app_lang("insured_patients"),
    ];

    $totalDate = $this->dailyReportModel->getTotalsData($clinicId, $startDate, $endDate, $this->login_user->id);
    return $this->response->setJSON([
      'totalData' =>  $totalDate,
      'draw' => intval($request->getPost('draw')),
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $filteredRecords,
      'data' => $data,
      'headers' => $headers
    ]);
  }

  public function updateDailyReport()
  {
    try {
      $request = \Config\Services::request();
      $report_id = $request->getPost('id');
      $column = $request->getPost('column');
      $value = $request->getPost('value');
      $user_id = $request->getPost('user_id');

      // Verificar si los valores son válidos
      if (empty($report_id) || empty($column)) {
        return $this->response->setJSON([
          'status' => 'error',
          'message' => 'ID del reporte y columna son obligatorios'
        ]);
      }
      $report = $this->dailyReportModel->getReportById($report_id);
      $attemps = $this->dailyReportModel->getRowAttemptsDailyUser($user_id,date('Y-m-d'));
      $user = $this->userModel->get_user_by_id($user_id);
      $num_row_exits = 0;
      if($column == "report_date"){
        $num_row_exits = $this->dailyReportModel->getDailyReportsCountNow($report->clinic_id,$value);
      }
      
      if($num_row_exits == 0){
        if($user["is_admin"] == "1" || $attemps->used < $attemps->attempts){
          // Llamar al modelo para actualizar el dato
         $success = $this->dailyReportModel->updateDailyReport($report_id, $column, $value);
         $exitEdicionNow = $this->dailyReportModel->checkLogByUserAndToday($user_id,$report_id);
         if ($success) {
           if(!$exitEdicionNow){
             $response2 = $this->dailyReportModel->incrementUsedAttempt($user_id,date('Y-m-d'));
           }else{
             $response2 = true;
           }
           if($response2 || $user["is_admin"]){
             
             $comment = $user['first_name']." ".$user['last_name']. " ha editado correctamente el reporte con ID ".$report_id." de la clinica ".$report->clinic_name;
             $comment .= ". El campo editado fué ". $column. " y el nuevo valor es ".$value;
             $result = $this->dailyReportModel->insertLogEntry($report_id,$user_id,$comment);
             if(!$exitEdicionNow){
               $attemps_available = ($attemps->attempts - ($attemps->used + 1));
             }else{
               $attemps_available = ($attemps->attempts - $attemps->used);
             }
             return $this->response->setJSON([
               'status' => 'success',
               'attempt_available' => $attemps_available,
               'message' => 'Reporte actualizado correctamente.'
             ]);
           }else{
             return $this->response->setJSON([
               'status' => 'warning',
               'attempt_available' => ($attemps->attempts - $attemps->used),
               'message' => 'No hizo el aumento.'
             ]);
           }
         } else {
           return $this->response->setJSON([
             'status' => 'warning',
             'attempt_available' => ($attemps->attempts - $attemps->used),
             'message' => 'No se actualizó.'
           ]);
         }
       }
       return $this->response->setJSON([
         'status' => 'warning',
         'attempt_available' => ($attemps->attempts - $attemps->used),
         'message' => 'No tienes intentos disponibles para editar.'
       ]);
      }
      return $this->response->setJSON([
        'status' => 'warning',
        'attempt_available' => ($attemps->attempts - $attemps->used),
        'message' => 'Ya existe un reporte en esa fecha para esa clinica.'
      ]);
    } catch (\Exception $e) {
      return $this->response->setJSON([
        'status' => 'error',
        'attempt_available' => '0',
        'message' => 'Error al actualizar el reporte: ' . $e->getMessage()
      ]);
    }
  }



  public function getTotalsData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->dailyReportModel->getTotalsData($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }

  /**
   * Obtener datos de pacientes.
   */

  public function getTotalPatientsData1() //Nuevo metodo
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');

    $data = $this->dailyReportModel->getPatientsData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }


  public function getTotalPatientsData()
  {
    $clinicId = $this->request->getGet('clinic_id');

    $data = $this->dailyReportModel->getPatientsData($clinicId);
    return $this->response->setJSON($data);
  }

  /**
   * Obtener datos de ingresos.
   */

  public function getTotalIncomeData1()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->dailyReportModel->getIncomeData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }


  public function getTotalIncomeData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getIncomeData($clinicId);
    return $this->response->setJSON($data);
  }

  /**
   * Obtener datos de plataformas.
   */

  public function getPlatformsData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getPlatformsData($clinicId);
    return $this->response->setJSON($data);
  }

  public function getPlatformsData1()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->dailyReportModel->getPlatformsData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }

  public function getPlatformsDataEspecific()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->dailyReportModel->getPlatformsData1($clinicId, $start_date, $end_date, $this->login_user->id,true);
    return $this->response->setJSON($data);
  }


  public function getInsurancePrevalenceData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getInsurancePrevalenceData($clinicId);
    return $this->response->setJSON($data);
  }

  public function getInsurancePrevalenceData1()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->dailyReportModel->getInsurancePrevalenceData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }

  public function getIndicatePerformance()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');

    $rang_two_1 = $this->request->getGet('rang_two_1');
    $rang_two_2 = $this->request->getGet('rang_two_2');

    $clinic_id = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
    return $this->response->setJSON($data);
  }


  public function generarPdf()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');
    $rang_two_1 = $this->request->getGet('rang_two_1');
    $rang_two_2 = $this->request->getGet('rang_two_2');
    $clinic_id = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
    $clinic = $this->dailyReportModel->getClinicById($clinic_id);

    // Crear una instancia de la clase Pdf
    $pdf = new Pdf();

    // Establecer la orientación a "landscape" (horizontal)
    $pdf->setPageOrientation('L'); // 'L' para horizontal, 'P' para vertical

    // Convertir las fechas al formato MM/DD/YYYY
    $rang_one_1 = date('m/d/Y', strtotime($rang_one_1));
    $rang_one_2 = date('m/d/Y', strtotime($rang_one_2));
    $rang_two_1 = date('m/d/Y', strtotime($rang_two_1));
    $rang_two_2 = date('m/d/Y', strtotime($rang_two_2));

    // Agregar los indicadores como encabezados de columna
    $indicators = [
      'new_patients_total' => 'Pacientes Nuevos',
      'followup_patients_total' => 'Pacientes De Seguimiento',
      'sales_total' => 'Ventas',
      'referral_total' => 'Referidos',
      'google_website_total' => 'Google & Website',
      'email_total' => 'Tarjetas & Correo Postal',
      'walkby_total' => 'Nos vio al pasar',
      'facebook_total' => 'Facebook',
      'instagram_total' => 'Instagram',
      'events_total' => 'Eventos',
      'youtube_total' => 'Youtube',
      'tiktok_total' => 'TikTok',
      'radio_total' => 'Radio',
      'newspaper_total' => 'Periodico',
      'tv_total' => 'Televisión',
      'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
      'insured_patients' => '# de pacientes nuevos CON seguro medico',
    ];

    // Contenido HTML para el PDF con la tabla horizontal
    $htmlContent = '
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
        <thead>
          <tr style="background-color:#f9a3a6; font-weight: 700; font-size: 16px;">
              <th colspan="' . count($indicators) + 1 . '">Comparación de Rendimiento <br /> ' . $clinic->name . ' <br /> <span style="font-size:12px !important;"> ( Rango de Fecha 1 => <span style="font-weight:600;">' . $rang_one_1 . ' - ' . $rang_one_2 . '</span></span>)<span style="font-size:12px !important;"> ( Rango de Fecha 2 => <span style="font-weight:600;">' . $rang_two_1 . ' - ' . $rang_two_2 . '</span></span>)</th>
          </tr>
            <tr>
            
                <th style="background-color:#b3e5f6; font-weight: 600;">Indicadores</th>';


 
    // Imprimir los encabezados de los indicadores
    foreach ($indicators as $key => $label) {
      $htmlContent .= '<th  style="background-color:#b3e5f6; font-weight: 600;">' . $label . '</th>';
    }


    $htmlContent .= '</tr>
        </thead>
        <tbody>';

    // Ahora agregamos las filas para cada rango de fechas y variación
    $htmlContent .= '<tr ><td><strong>' . $rang_one_1 . ' - ' . $rang_one_2 . '</strong></td>';
    foreach ($indicators as $key => $label) {
      $range1 = number_format($data['range_1'][$key], 2, ',', '.');
      $htmlContent .= '<td>' . $range1 . '</td>';
    }
    $htmlContent .= '</tr>';

    $htmlContent .= '<tr><td><strong>' . $rang_two_1 . ' - ' . $rang_two_2 . '</strong></td>';
    foreach ($indicators as $key => $label) {
      $range2 = number_format($data['range_2'][$key], 2, ',', '.');
      $htmlContent .= '<td>' . $range2 . '</td>';
    }
    $htmlContent .= '</tr>';

    $htmlContent .= '<tr><td><strong>Variación</strong></td>';
    foreach ($indicators as $key => $label) {
      $variation = $data['range_2'][$key] - $data['range_1'][$key];
      $variation = number_format($variation, 2, ',', '.');

      // Definir el color según la variación
      $color = $variation > 0 ? 'green' : ($variation < 0 ? 'red' : 'black');
      $htmlContent .= '<td style="color: ' . $color . ';">' . $variation . '</td>';
    }
    $htmlContent .= '</tr>';

    $htmlContent .= '</tbody>
    </table>';

    // Llamar al método generatePdf de la clase Pdf y forzar la descarga
    $pdf->generatePdf($htmlContent, 'ComparacionRendimiento.pdf');
  }





  /*public function generarPdf()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');
    $rang_two_1 = $this->request->getGet('rang_two_1');
    $rang_two_2 = $this->request->getGet('rang_two_2');
    $clinic_id = $this->request->getGet('clinic_id');
    $data = $this->dailyReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
    // Crear una instancia de la clase Pdf
    $pdf = new Pdf();  // Puedes pasar 'invoice' si quieres el tipo 'invoice'

    // Contenido HTML para el PDF

    // Convertir las fechas al formato MM/DD/YYYY
    $rang_one_1 = date('m/d/Y', strtotime($rang_one_1));
    $rang_one_2 = date('m/d/Y', strtotime($rang_one_2));
    $rang_two_1 = date('m/d/Y', strtotime($rang_two_1));
    $rang_two_2 = date('m/d/Y', strtotime($rang_two_2));


    $htmlContent = '
     <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
         <thead>
             <tr>
                 <th style="background-color:#b3e5f6; font-weight: 600;">INDICADOR DE RENDIMIENTO</th>
                 <th style="background-color:#b3e5f6; font-weight: 600;">Rango de Fecha 1 <span style="font-weight:600;">' . $rang_one_1 . ' - ' . $rang_one_2 . '</span></th>
                 <th style="background-color:#b3e5f6; font-weight: 600;">Rango de Fecha 2 <span style="font-weight:600;">' . $rang_two_1 . ' - ' . $rang_two_2 . '</span></th>
                 <th style="background-color:#b3e5f6; font-weight: 600;">VARIACIÓN</th>
             </tr>
         </thead>
         <tbody>';

    $indicators = [
      'new_patients_total' => 'Pacientes Nuevos',
      'followup_patients_total' => 'Pacientes De Seguimiento',
      'sales_total' => 'Ventas',
      'referral_total' => 'Referidos',
      'google_website_total' => 'Google & Website',
      'email_total' => 'Tarjetas & Correo Postal',
      'walkby_total' => 'Nos vio al pasar',
      'facebook_total' => 'Facebook',
      'instagram_total' => 'Instagram',
      'events_total' => 'Eventos',
      'youtube_total' => 'Youtube',
      'tiktok_total' => 'TikTok',
      'radio_total' => 'Radio',
      'newspaper_total' => 'Periodico',
      'tv_total' => 'Televisión',
      'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
      'insured_patients' => '# de pacientes nuevos CON seguro medico',
    ];

    foreach ($indicators as $key => $label) {
      $range1 = number_format($data['range_1'][$key], 2, ',', '.');
      $range2 = number_format($data['range_2'][$key], 2, ',', '.');
      $variation = $data['range_2'][$key] - $data['range_1'][$key];

      // Definir el color según el valor de la variación
      $color = $variation > 0 ? 'green' : ($variation < 0 ? 'red' : 'black');

      // Si la variación es mayor que 0, agregar el signo '+'
      if ($variation > 0) {
        $variation = '+' . number_format($variation, 2, ',', '.');
      } elseif ($variation < 0) {
        $variation = number_format($variation, 2, ',', '.');
      } else {
        $variation = number_format($variation, 2, ',', '.');
      }

      // Si es 'sales_total', agregar signo de peso
      if ($key == 'sales_total') {
        $range1 = '$' . $range1;
        $range2 = '$' . $range2;
        $variation = '$' . $variation;
      }

      // Construir la fila de la tabla
      $htmlContent .= '
          <tr>
              <td>' . $label . '</td>
              <td>' . $range1 . '</td>
              <td>' . $range2 . '</td>
              <td style="color: ' . $color . ';">' . $variation . '</td>
          </tr>';
    }

    $htmlContent .= '
          </tbody>
      </table>';

    // Llamar al método generatePdf de la clase Pdf y forzar la descarga
    $pdf->generatePdf($htmlContent, 'ComparacionRendimiento.pdf');
  }*/

  public function generatePDFRangeClinic()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');
    $clinic_id = $this->request->getGet('clinic_id');
    $clinic = $this->dailyReportModel->getClinicById($clinic_id);
    $data = $this->dailyReportModel->getFilteredReportsPdf($rang_one_1, $rang_one_2, $clinic_id);

    // Crear una instancia de la clase Pdf
    $pdf = new Pdf();  // Puedes pasar 'invoice' si quieres el tipo 'invoice'

    // Contenido HTML para el PDF

    // Convertir las fechas al formato MM/DD/YYYY
    $rang_one_1 = date('m/d/Y', strtotime($rang_one_1));
    $rang_one_2 = date('m/d/Y', strtotime($rang_one_2));

    $indicators = [
      'report_date' => 'Fecha',
      'sales_cash' => 'Ventas en Efectivo',
      'sales_card' => 'Ventas en Tarjetas',
      'sales_other' => 'Ventas Otras',
      'new_patients_total' => 'Pacientes Nuevos',
      'followup_patients_total' => 'Pacientes De Seguimiento',
      'referral_google' => 'Referidos Google',
      'referral_referred' => 'Referido Referido',
      'referral_mail' => 'Referidos Correo',
      'referral_walkby' => 'Nos vio al pasar',
      'referral_facebook' => 'Facebook',
      'referral_instagram' => 'Instagram',
      'referral_events' => 'Eventos',
      'referral_youtube' => 'Youtube',
      'referral_tiktok' => 'TikTok',
      'referral_radio' => 'Radio',
      'referral_newspaper' => 'Periodico',
      'referral_tv' => 'Televisión',
      'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
      'insured_patients' => '# de pacientes nuevos CON seguro medico',
    ];

    // Obtener los reportes
    $reports = $data['range_1'];  // Los datos obtenidos desde la base de datos
    $sumValues = array_fill_keys(array_keys($indicators), 0);
    //var_dump($reports);
    //exit;
    $htmlContent = '
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
    <thead>
        <tr style="background-color:#f9a3a6; font-weight: 700; font-size: 16px;">
            <th colspan="' . count($indicators) . '">' . $clinic->name . ' <br /> <span style="font-size:12px !important;"> Rango de Fecha <span style="font-weight:600;">' . $rang_one_1 . ' - ' . $rang_one_2 . '</span></span></th>
        </tr>
        <tr style="background-color:#b3e5f6; font-weight: 600;">
';

    // Generar los encabezados de la tabla con los indicadores
    foreach ($indicators as $key => $label) {
      $htmlContent .= '<th>' . $label . '</th>';
    }

    $htmlContent .= '</tr></thead><tbody>';

    // Recorrer los datos de los reportes

    foreach ($reports as $report) {
      $htmlContent .= '<tr>';
      $i = 0;
      foreach ($indicators as $key => $label) {
        // Verificar si el dato está disponible en el reporte
        if (isset($report[$key])) {
          //$name = number_format($report[$key], 2, ',', '.');
          if ($key !== 'report_date' && isset($report[$key])) {
            $sumValues[$key] += $report[$key]; // Sumar los valores
            if ($i == 1  || $i == 2 || $i == 3) {
              $htmlContent .= '<td>$' . number_format($report[$key], 2, ',', '.') . '</td>';
            } else {
              $htmlContent .= '<td>' . $report[$key] . '</td>';
            }
          } else {
            $fecha_formateada = date('m/d/Y', strtotime($report[$key]));
            $htmlContent .= '<td>' . $fecha_formateada . '</td>';
          }
        } else {
          $htmlContent .= '<td></td>';  // Si el dato no está presente
        }
        $i++;
      }

      $htmlContent .= '</tr>';
    }

    // Agregar la fila de suma al final
    $htmlContent .= '<tr style="background-color:#d9d9d9; font-weight: 700;">
  ';

    $k = 0;
    foreach ($sumValues as $value) {
      // Mostrar los valores sumados, formateados si es necesario
      if ($k != 0) { // No mostrar la suma de la fecha
        if ($k == 1  || $k == 2 || $k == 3) {
          $htmlContent .= '<td>$' . number_format($value, 2, ',', '.') . '</td>';
        } else {
          $htmlContent .= '<td>' . $value . '</td>';
        }
        //$htmlContent .= '<td>' . number_format($value, 2, ',', '.') . '</td>';
      } else {
        $htmlContent .= '<td><strong>Total</strong></td>';
      }
      $k++;
    }
    $htmlContent .= '</tr>';

    $htmlContent .= '</tbody></table>';


    /*$htmlContent = '

     <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; text-align: center;">
         <thead>
         <tr>
            <th colspan="2" style="background-color:#f9a3a6; font-weight: 700; font-size: 16px; padding: 10px;">
                '.$clinic->name.'
            </th>
        </tr>
             <tr>
                 <th style="background-color:#b3e5f6; font-weight: 600;">INDICADOR</th>
                 <th style="background-color:#b3e5f6; font-weight: 600;">Rango de Fecha <span style="font-weight:600;">' . $rang_one_1 . ' - ' . $rang_one_2 . '</span></th>
             </tr>
         </thead>
         <tbody>';

    $indicators = [
      'new_patients_total' => 'Pacientes Nuevos',
      'followup_patients_total' => 'Pacientes De Seguimiento',
      'sales_cash' => 'Ventas en Efectivo',
      'sales_card' => 'Ventas en Tarjetas',
      'sales_other' => 'Ventas Otras',
      'referral_total' => 'Referidos',
      'google_website_total' => 'Google & Website',
      'email_total' => 'Tarjetas & Correo Postal',
      'walkby_total' => 'Nos vio al pasar',
      'facebook_total' => 'Facebook',
      'instagram_total' => 'Instagram',
      'events_total' => 'Eventos',
      'youtube_total' => 'Youtube',
      'tiktok_total' => 'TikTok',
      'radio_total' => 'Radio',
      'newspaper_total' => 'Periodico',
      'tv_total' => 'Televisión',
      'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
      'insured_patients' => '# de pacientes nuevos CON seguro medico',
    ];

    foreach ($indicators as $key => $label) {
      $range1 = number_format($data['range_1'][$key], 2, ',', '.');
    
      // Si es 'sales_total', agregar signo de peso
      if ($key == 'sales_total') {
        $range1 = '$' . $range1;
      }

      $htmlContent .= '
          <tr>
              <td>' . $label . '</td>
              <td>' . $range1 . '</td>
          </tr>';
    }

    $htmlContent .= '
          </tbody>
      </table>';*/

    // Llamar al método generatePdf de la clase Pdf y forzar la descarga
    $pdf->generatePdf($htmlContent, 'DailyReportMonth.pdf');
  }

  public function getViewReport($filename)
  {
    $session = session();

    // Verificar si el usuario está autenticado
    if (!$session->get('isLoggedIn')) {
      // Si el usuario no está logueado, redirigir al login
      return redirect()->to('/signin')->with('error', 'Acceso no autorizado');
    }

    // Ruta al archivo dentro de writable/uploads
    $filePath = WRITEPATH . 'uploads/' . basename($filename); // Usamos WRITEPATH para asegurarnos de que estamos en el directorio correcto

    // Verificar si el archivo existe
    if (!file_exists($filePath)) {
      return redirect()->back()->with('error', 'Archivo no encontrado');
    }

    // Detectar el tipo de archivo (en este caso, para PDFs, imágenes, etc.)
    $fileMime = mime_content_type($filePath);

    // Enviar el archivo con el encabezado adecuado para abrirlo en una nueva pestaña
    return $this->response->setHeader('Content-Type', $fileMime)
      ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
      ->setHeader('Content-Length', filesize($filePath))
      ->setBody(file_get_contents($filePath));
  }

  

  public function exportExcelPerformanceComparison()
  {
      $rang_one_1 = $this->request->getGet('rang_one_1');
      $rang_one_2 = $this->request->getGet('rang_one_2');
      $rang_two_1 = $this->request->getGet('rang_two_1');
      $rang_two_2 = $this->request->getGet('rang_two_2');
      $clinic_id = $this->request->getGet('clinic_id');
      $clinic = $this->dailyReportModel->getClinicById($clinic_id);
      $data = $this->dailyReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
      //var_dump($data);
      //exit();
  
      // Convertir las fechas al formato MM/DD/YYYY
      $rang_one_1 = date('m/d/Y', strtotime($rang_one_1));
      $rang_one_2 = date('m/d/Y', strtotime($rang_one_2));
      $rang_two_1 = date('m/d/Y', strtotime($rang_two_1));
      $rang_two_2 = date('m/d/Y', strtotime($rang_two_2));
  
      // Definir los indicadores
      $indicators = [
          'new_patients_total' => 'Pacientes Nuevos',
          'followup_patients_total' => 'Pacientes De Seguimiento',
          'sales_total' => 'Ventas',
          'referral_total' => 'Referidos',
          'google_website_total' => 'Google & Website',
          'email_total' => 'Tarjetas & Correo Postal',
          'walkby_total' => 'Nos vio al pasar',
          'facebook_total' => 'Facebook',
          'instagram_total' => 'Instagram',
          'events_total' => 'Eventos',
          'youtube_total' => 'Youtube',
          'tiktok_total' => 'TikTok',
          'radio_total' => 'Radio',
          'newspaper_total' => 'Periodico',
          'tv_total' => 'Televisión',
          'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
          'insured_patients' => '# de pacientes nuevos CON seguro medico',
      ];
  
      // Crear una instancia de PhpSpreadsheet
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
  
      // Título de la hoja
      $sheet->setCellValue('A1', $clinic->name);
      $sheet->setCellValue('A2', '(Rango de Fecha 1 => ' . $rang_one_1 . ' - ' . $rang_one_2. ') ( Rango de Fecha 2 =>' . $rang_two_1 . ' - ' . $rang_two_2);

      
  
      // Generar los encabezados de la tabla
      $col = 'B'; // Comienza desde la columna B
      $sheet->setCellValue('A3', 'Indicadores'); // Cabecera para indicadores
      foreach ($indicators as $label) {
          $sheet->setCellValue($col.'3', $label); // Establecer los encabezados de indicadores
          $col++; // Avanzamos a la siguiente columna
      }
  
      // Imprimir las filas con los datos
$row = 4;
$sheet->setCellValue('A' . $row, $rang_one_1 . ' - ' . $rang_one_2); // Rango de la primera fecha
$col = 'B'; // Comenzamos en la columna B
foreach ($indicators as $key => $label) {
    $value = $data['range_1'][$key]; // Obtener el valor directo (sin formatear)
    $sheet->setCellValueExplicit($col.$row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC); // Especificar que es numérico
    $col++; // Avanzar a la siguiente columna
}

$row++;
$sheet->setCellValue('A' . $row, $rang_two_1 . ' - ' . $rang_two_2); // Rango de la segunda fecha
$col = 'B';
foreach ($indicators as $key => $label) {
    $value = $data['range_2'][$key]; // Obtener el valor directo (sin formatear)
    $sheet->setCellValueExplicit($col.$row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC); // Especificar que es numérico
    $col++;
}

$row++;
$sheet->setCellValue('A' . $row, 'Variación'); // Variación
$col = 'B';
foreach ($indicators as $key => $label) {
    $variation = $data['range_2'][$key] - $data['range_1'][$key]; // Calcular la variación
    $sheet->setCellValueExplicit($col.$row, $variation, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC); // Especificar que es numérico

    // Definir el color de la celda dependiendo del valor
    $color = ($variation > 0) ? '00FF00' : (($variation < 0) ? 'FF0000' : '000000');
    $sheet->getStyle($col.$row)->getFont()->getColor()->setRGB($color);
    $col++;
}
  
      // Generar el archivo Excel
      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      header('Content-Disposition: attachment;filename="ComparacionRendimiento_' . $clinic_id . '.xlsx"');
      header('Cache-Control: max-age=0');
  
      $writer->save('php://output');
  }
  



  public function exportExcelRangeClinic()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');
    $clinic_id = $this->request->getGet('clinic_id');
    $clinic = $this->dailyReportModel->getClinicById($clinic_id);
    $data = $this->dailyReportModel->getFilteredReportsPdf($rang_one_1, $rang_one_2, $clinic_id);

    // Convertir las fechas al formato MM/DD/YYYY
    $rang_one_1 = date('m/d/Y', strtotime($rang_one_1));
    $rang_one_2 = date('m/d/Y', strtotime($rang_one_2));

    $indicators = [
      'report_date' => 'Fecha',
      'sales_cash' => 'Ventas en Efectivo',
      'sales_card' => 'Ventas en Tarjetas',
      'sales_other' => 'Ventas Otras',
      'new_patients_total' => 'Pacientes Nuevos',
      'followup_patients_total' => 'Pacientes De Seguimiento',
      'referral_google' => 'Referidos Google',
      'referral_referred' => 'Referido Referido',
      'referral_mail' => 'Referidos Correo',
      'referral_walkby' => 'Nos vio al pasar',
      'referral_facebook' => 'Facebook',
      'referral_instagram' => 'Instagram',
      'referral_events' => 'Eventos',
      'referral_youtube' => 'Youtube',
      'referral_tiktok' => 'TikTok',
      'referral_radio' => 'Radio',
      'referral_newspaper' => 'Periodico',
      'referral_tv' => 'Televisión',
      'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
      'insured_patients' => '# de pacientes nuevos CON seguro medico',
    ];

    // Obtener los reportes
    $reports = $data['range_1']; // Los datos obtenidos desde la base de datos
    $sumValues = array_fill_keys(array_keys($indicators), 0);

    // Crear una instancia de PhpSpreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Título de la hoja
    $sheet->setCellValue('A1', $clinic->name);
    $sheet->setCellValue('A2', 'Rango de Fecha: ' . $rang_one_1 . ' - ' . $rang_one_2);

    // Generar los encabezados de la tabla
    $col = 1;
    foreach ($indicators as $label) {
      $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
      $sheet->setCellValue($columnLetter . '3', $label); // Usando setCellValue con la notación de columna
      $col++;
    }

    // Recorrer los datos de los reportes y agregarlos a la hoja
    $row = 4; // Empezamos en la fila 4 después de los encabezados
    foreach ($reports as $report) {
      $col = 1;
      foreach ($indicators as $key => $label) {
        // Verificar si el dato está disponible en el reporte
        if (isset($report[$key])) {
          if ($key !== 'report_date') {
            // Si es una cantidad monetaria, dar formato
            if ($key == 'sales_cash' || $key == 'sales_card' || $key == 'sales_other') {
              $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
              //$sheet->setCellValue($columnLetter . $row, '$' . number_format($report[$key], 2, ',', '.'));
              //$sheet->setCellValue($columnLetter . $row, number_format($report[$key], 2, '.', ','));
              $sheet->setCellValue($columnLetter . $row, (float)$report[$key]);
              
              $sheet->getStyle($columnLetter . $row)->getNumberFormat()->setFormatCode('$#,##0.00');

            } else {
              $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
              $sheet->setCellValue($columnLetter . $row, $report[$key]);
            }
            $sumValues[$key] += $report[$key]; // Sumar los valores
          } else {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($columnLetter . $row, date('m/d/Y', strtotime($report[$key])));
          }
        } else {
          $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
          $sheet->setCellValue($columnLetter . $row, ''); // Si el dato no está presente
        }
        $col++;
      }
      $row++;
    }


    $sheet->setCellValue('A' . $row, 'Total');
    $col = 2;
    $k = 0;
    foreach ($sumValues as $value) {
      if( $k > 0){
        if ($col == 2 || $col == 3 || $col == 4) {
          $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
          //$sheet->setCellValue($columnLetter . $row, '$' . number_format($value, 2, ',', '.'));
          $sheet->setCellValue($columnLetter . $row, (float)$value);
          //$sheet->setCellValue($columnLetter . $row, number_format($value, 2, '.', ','));
          $sheet->getStyle($columnLetter . $row)->getNumberFormat()->setFormatCode('$#,##0.00');

        } else {
          $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
          $sheet->setCellValue($columnLetter . $row, $value);
        }
        $col++;
      }
      

      $k++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Disposition: attachment;filename="ClinicReport_' . $clinic_id . '.xlsx"');
    header('Cache-Control: max-age=0');


    $writer->save('php://output');
  }

    /**
     * Mostrar modal de confirmación para eliminar reporte diario
     * @param int $id
     */
    public function delete_confirmation_modal($id = 0)
    {
        if (!$id) {
            show_404();
        }
    
        $model_info = $this->dailyReportModel->getReportById($id);
        
        if (!$model_info) {
            show_404();
        }
    
        // Verificar permisos
        $user = $this->userModel->get_user_by_id($this->login_user->id);
        $user_clinics = $this->dailyReportModel->getClinics2($this->login_user->id);
        $has_access = false;
        
        foreach ($user_clinics as $clinic) {
            if ($clinic->id == $model_info->clinic_id) {
                $has_access = true;
                break;
            }
        }
        
        if (!$has_access && !$user["is_admin"]) {
            show_404();
        }
    
        $view_data['model_info'] = $model_info;
        
        return $this->template->view('daily_report/delete_confirmation_modal', $view_data);
    }
    
    /**
     * Eliminar reporte diario
     */
    public function delete()
    {
        $request = \Config\Services::request();
        $report_id = $request->getPost('id');
        
        if (!$report_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('invalid_request')
            ]);
        }
    
        $report = $this->dailyReportModel->getReportById($report_id);
        
        if (!$report) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('record_not_found')
            ]);
        }
    
        // Verificar permisos
        $user = $this->userModel->get_user_by_id($this->login_user->id);
        $user_clinics = $this->dailyReportModel->getClinics2($this->login_user->id);
        $has_access = false;
        
        foreach ($user_clinics as $clinic) {
            if ($clinic->id == $report->clinic_id) {
                $has_access = true;
                break;
            }
        }
        
        if (!$has_access && !$user["is_admin"]) {
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('access_denied')
            ]);
        }
    
        try {
            // Eliminar archivos asociados si los hay
            if ($report->report_file && !filter_var($report->report_file, FILTER_VALIDATE_URL)) {
                $file_path = WRITEPATH . 'uploads/' . $report->report_file;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Eliminar logs relacionados con este reporte
            $this->dailyReportModel->deleteLogsByReportId($report_id);
            
            // Eliminar el reporte
            $deleted = $this->dailyReportModel->deleteReport($report_id);
            
            if ($deleted) {
                // Log de la acción de eliminación
                $comment = $user['first_name'] . " " . $user['last_name'] . " ha eliminado el reporte con ID " . $report_id . " de la clínica " . $report->clinic_name;
                $comment .= ". Fecha del reporte: " . $report->report_date;
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => app_lang('record_deleted')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => app_lang('record_cannot_be_deleted')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', "Error al eliminar reporte diario: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => app_lang('error_occurred')
            ]);
        }
    }

    
    /**
     * Obtener meses disponibles para una clínica
     */
    public function get_available_months()
    {
        // Solo administradores pueden acceder
        if (!$this->login_user->is_admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado. Solo administradores pueden acceder a esta función.'
            ]);
        }

        $clinic_id = $this->request->getGet('clinic_id');
        
        if (!$clinic_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de clínica requerido'
            ]);
        }

        $result = $this->dailyReportModel->getAvailableMonthsForClinic($clinic_id);
        return $this->response->setJSON($result);
    }

    /**
     * Obtener estadísticas mensuales de rendimiento económico
     */
    public function get_monthly_economic_stats()
    {
        // Solo administradores pueden acceder
        if (!$this->login_user->is_admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado. Solo administradores pueden acceder a esta función.'
            ]);
        }

        $clinic_id = $this->request->getPost('clinic_id');
        $selected_months = $this->request->getPost('selected_months');
        
        if (!$clinic_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de clínica requerido'
            ]);
        }

        // Si selected_months es un string JSON, convertirlo a array
        if (is_string($selected_months)) {
            $selected_months = json_decode($selected_months, true);
        }

        $result = $this->dailyReportModel->getMonthlyEconomicStats($clinic_id, $selected_months);
        return $this->response->setJSON($result);
    }
}
