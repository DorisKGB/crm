<?php


namespace App\Controllers;

use App\Models\Electronic_consecutive_model;
use App\Models\Deposit_report_model;
use App\Models\Notifications_model;
use App\Models\Users_model;
use App\Libraries\Pdf;

class Deposit_report extends Security_Controller
{
  protected $userModel;
  protected $depositReportModel;
  protected $notificationsModel;
  protected $consecutiveModel;
  protected $clinicModel;

  public function __construct()
  {
    parent::__construct();
    $this->init_permission_checker("can_access_deposit_report");
    $this->depositReportModel = new Deposit_report_model();
    $this->notificationsModel = new Notifications_model();
    $this->userModel = new Users_model();
    $this->consecutiveModel = new Electronic_consecutive_model();
  }

  /**
   * Mostrar la página principal del módulo de reporte diario.
   */
  public function index()
  {
    $this->check_module_availability("module_deposit");

    /*if ($this->login_user->user_type == "client" && !get_setting("client_can_access_notes")) {
      app_redirect("forbidden");
    }*/

    $reports = $this->depositReportModel->findAllReports();
    $clinics = $this->depositReportModel->getClinics2($this->login_user->id);
    $clinic_options = $this->getClinicOptions2($this->login_user->id);

    $data = [
      'reports' => $reports,
      'clinics' => $clinics,
      'clinic_options' => $clinic_options,
      'label_column' => "col-md-2",
      'field_column' => "col-md-10"
    ];

    return $this->template->rander("deposit_report/index", $data);
  }

  /**
   * Mostrar el formulario modal para añadir o editar un reporte diario.
   * @param int $id
   */
  public function modal_form($id = 0)
  {
    $signature_electronic = $this->generate_electronic_signature();
    
    $view_data = [
      'model_info' => $this->depositReportModel->findReportById($id),
      'clinic_options' => $this->getClinicOptions2($this->login_user->id),
      'login_user' => $this->login_user,
      'label_column' => "col-md-2",
      'field_column' => "col-md-10",
      'signature_electronic' => $signature_electronic
    ];

    return $this->template->view('deposit_report/modal_form', $view_data);
  }

  public function generate_electronic_signature(){
    $ip  = $this->generate_ip();
    $name = $this->login_user->first_name . " " .  $this->login_user->last_name;
    $fecha_hora_firma = date('m-d-Y H:i:s');
    $consecutive = $this->consecutiveModel->generate_next_consecutive();
    return $name.$fecha_hora_firma.$ip.$consecutive;
  }


  public function generate_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    // Si es IPv6 localhost (::1), lo convertimos a IPv4
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }
    return $ip;
  }

  /**
   * Obtener las opciones de clínicas disponibles.
   */
  protected function getClinicOptions()
  {
    $clinics = $this->depositReportModel->getClinics();
    $clinic_options = [];

    foreach ($clinics as $clinic) {
      $clinic_options[$clinic->id] = $clinic->name;
    }

    return $clinic_options;
  }

  protected function getClinicOptions2($user_id)
  {
    $clinics = $this->depositReportModel->getClinics2($user_id);
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
    //$validation = \Config\Services::validation();
    //$validation->setRules($this->validateInput());

    $input = $this->request->getPost(); //obtiene los datos
    log_message('debug', 'Datos recibidos: ' . print_r($input, true));
    //AQUI RECIBE TODOS LOS DATOS

    $file = $this->request->getFile('deposit_receipt_scan');
    $input['deposit_receipt_scan'] = $this->handleFileUpload($file);
    $input['depositor'] = $this->login_user->id;
    $input['signature_electronic'] = $this->generate_electronic_signature();
    $input['clinic_name'] = ($this->depositReportModel->getClinicNameById($input['clinic_id']))->name;

    //return $this->response->setJSON(["success" => false, "state" => "3",'message' => $input]);


    if ($input['deposit_amount'] != "" && strtotime($input['deposit_end_date']) >= strtotime($input['deposit_start_date']) && $input['signature_electronic'] != null) { //valida los datos

      $next_consecutive = $this->consecutiveModel->insert_consecutive();
        $input = clean_data($input);
        $save_id = $this->depositReportModel->saveReport($input);
        if ($save_id) {
          //Se ha guardo el reporte "ENVIAR NOTIIFICACION"
          //log_notification("daily_report_success", array("event_id" => $save_id), $this->login_user->id);
          //log_notification("new_event_added_in_calendar", array("event_id" => $save_id),$this->login_user->id);
          $admins = $this->userModel->get_admin_ids();
          $user_account = $this->userModel->get_id_roles_for(7);
          //$this->notificationsModel->create_notification_daily_reports($this->login_user->id, $save_id, $admins);
          //$this->notificationsModel->create_notification_daily_reports($this->login_user->id, $save_id, $user_account);
          //log_notification("calendar_event_modified", array("event_id" => 1),$this->login_user->id);
          return $this->response->setJSON(["success" => true, "state" => "0", "data" => $input, 'id' => $save_id, 'message' => app_lang('record_saved')]);
        } else {
          log_message('error', 'Error al guardar los datos');
          return $this->response->setJSON(["success" => false, "state" => "1" , 'message' => app_lang('error_occurred')]);
        }
      
    } else {
      //log_message('error', "Error en la validación de datos: Complete todos los campos!", true));
      return $this->response->setJSON(["success" => false, "state" => "3",'message' => "Complete todos los campos."]);
    }
  }

  /**
   * Validar la entrada del formulario.
   */
  protected function validateInput()
  {
    return [
      "clinic_id" => "required|numeric",
      "report_file" => "uploaded[report_file]|max_size[report_file,2048]|ext_in[report_file,png,jpg,jpeg,pdf]",
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
    $start = (is_null($start)) ? 0 : (int)$start;   // Asignar 0 si es null
    $length = (is_null($length)) ? 10 : (int)$length;
    $search = $request->getPost('search')['value'] ?? '';
    $order = $request->getPost('order') ?? [];
    $columnIndex = $order[0]['column'] ?? 0;
    $columns = $request->getPost('columns') ?? [];
    $columnName = $columns[$columnIndex]['data'] ?? 'id';
    $columnSortOrder = $order[0]['dir'] ?? 'asc';


    $startDate = $request->getPost('startDate') ?? null;
    $endDate = $request->getPost('endDate') ?? null;
    $clinicId = $request->getPost('clinicId') ?? null;

    
    $reports = $this->depositReportModel->getFilteredReports2($start, $length, $search, $columnName, $columnSortOrder, $startDate, $endDate, $clinicId, $this->login_user->id);
   
   
    $totalRecords = $this->depositReportModel->getTotalReportsCount();

    $filteredRecords = $this->depositReportModel->getFilteredReportsCount2($search, $startDate, $endDate, $clinicId, $this->login_user->id);

    $data = [];
  

    foreach ($reports as $report) {
      $userModel = $this->userModel->get_user_by_id($report['depositor']);
      $data[] = [
        'report_id' => $report['id'],
        'deposit_datetime' => $report['deposit_datetime'],
        'clinic_id' => $report['clinic_id'],
        'clinic_name' => $report['clinic_name'],
        'depositor' => $userModel['first_name']. " ".$userModel['last_name'],
        'deposit_amount' => $report['deposit_amount'],
        'deposit_start_date' => $report['deposit_start_date'],
        'deposit_end_date' => $report['deposit_end_date'],
        'signature_electronic' => $report['signature_electronic'],
        'deposit_receipt_scan' => (strpos($report['deposit_receipt_scan'], 'https://drive.google.com') === 0)
          ? $report['deposit_receipt_scan']
          : base_url('index.php/getViewReport/' . esc($report['deposit_receipt_scan'])),
      ];
    }


    $headers = [
      app_lang("report_id"),
      app_lang("report_date"),
      app_lang("clinic_id"),
      app_lang("clinic_name"),
      app_lang("depositor"),
      app_lang("deposit_amount"),
      app_lang("deposit_start_date"),
      app_lang("deposit_end_date"),
      app_lang("signature_electronic"),
      app_lang("deposit_receipt_scan")
    ];

    
    $totalDate = $this->depositReportModel->getTotalsData($clinicId, $startDate, $endDate, $this->login_user->id);
    return $this->response->setJSON([
      'totalData' =>  $totalDate,
      'draw' => intval($request->getPost('draw')),
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $filteredRecords,
      'data' => $data,
      'headers' => $headers
    ]);
  }

  public function updateReport()
  {
    try {
      $request = \Config\Services::request();
      $report_id = $request->getPost('id');
      $column = $request->getPost('column');
      $value = $request->getPost('value');

      // Verificar si los valores son válidos
      if (empty($report_id) || empty($column)) {
        return $this->response->setJSON([
          'status' => 'error',
          'message' => 'ID del reporte y columna son obligatorios'
        ]);
      }

      // Llamar al modelo para actualizar el dato
      $success = $this->depositReportModel->updateReport($report_id, $column, $value);

      if ($success) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'Reporte actualizado correctamente'
        ]);
      } else {
        return $this->response->setJSON([
          'status' => 'warning',
          'message' => 'No se realizaron cambios en la base de datos'
        ]);
      }
    } catch (\Exception $e) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Error al actualizar el reporte: ' . $e->getMessage()
      ]);
    }
  }



  public function getTotalsData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->depositReportModel->getTotalsData($clinicId, $start_date, $end_date, $this->login_user->id);
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
    $data = $this->depositReportModel->getMonthData($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }


  public function getTotalPatientsData()
  {
    $clinicId = $this->request->getGet('clinic_id');

    $data = $this->depositReportModel->getPatientsData($clinicId);
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
    $data = $this->depositReportModel->getIncomeData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }


  public function getTotalIncomeData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->depositReportModel->getIncomeData($clinicId);
    return $this->response->setJSON($data);
  }

  /**
   * Obtener datos de plataformas.
   */

  public function getPlatformsData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->depositReportModel->getPlatformsData($clinicId);
    return $this->response->setJSON($data);
  }

  public function getPlatformsData1()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->depositReportModel->getPlatformsData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }


  public function getInsurancePrevalenceData()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $data = $this->depositReportModel->getInsurancePrevalenceData($clinicId);
    return $this->response->setJSON($data);
  }

  public function getInsurancePrevalenceData1()
  {
    $clinicId = $this->request->getGet('clinic_id');
    $start_date = $this->request->getGet('start_date');
    $end_date = $this->request->getGet('end_date');
    $data = $this->depositReportModel->getInsurancePrevalenceData1($clinicId, $start_date, $end_date, $this->login_user->id);
    return $this->response->setJSON($data);
  }

  public function getIndicatePerformance()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');

    $rang_two_1 = $this->request->getGet('rang_two_1');
    $rang_two_2 = $this->request->getGet('rang_two_2');

    $clinic_id = $this->request->getGet('clinic_id');
    $data = $this->depositReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
    return $this->response->setJSON($data);
  }

  public function generarPdf()
  {
    $rang_one_1 = $this->request->getGet('rang_one_1');
    $rang_one_2 = $this->request->getGet('rang_one_2');
    $rang_two_1 = $this->request->getGet('rang_two_1');
    $rang_two_2 = $this->request->getGet('rang_two_2');
    $clinic_id = $this->request->getGet('clinic_id');
    $data = $this->depositReportModel->getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id);
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
}
