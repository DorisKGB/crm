<?php

namespace App\Models;

use CodeIgniter\Model;

class Daily_report_model extends Model
{
  protected $table = 'daily_reports';

  protected $primaryKey = 'id';
  protected $allowedFields = [
    'clinic_id',
    'clinic_name',
    'report_file',
    'report_date',
    'sales_cash',
    'sales_card',
    'sales_other',
    'new_patients_total',
    'followup_patients_total',
    'referral_google',
    'referral_referred',
    'referral_mail',
    'referral_walkby',
    'referral_facebook',
    'referral_events',
    'referral_instagram',
    'referral_youtube',
    'referral_tiktok',
    'referral_radio',
    'referral_newspaper',
    'referral_tv',
    'uninsured_patients',
    'insured_patients',
    'boxed_samples',
    'added_to_square_ecw',
    'submitted_by'
  ];

  /**
   * Guardar un reporte diario.
   * @param array $data
   * @return mixed
   */
  public function saveReport($data)
  {
    if ($this->insert($data)) {
      return $this->insertID();  // Devuelve el ID del nuevo registro insertado
    }
    return false;  // Si no se pudo insertar, retorna false
  }

  /**
   * Obtener todos los reportes.
   * @return array
   */
  public function findAllReports()
  {
    return $this->findAll();
  }

  /**
   * Obtener un reporte por su ID.
   * @param int $id
   * @return array
   */
  public function findReportById($id)
  {
    return $this->find($id);
  }

  /**
   * Obtener todas las clínicas.
   * @return array
   */
  public function getClinics()
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      return $this->db->table($clinic_table)
        ->select('id, name')
        ->get()
        ->getResult();
    } catch (\Exception $e) {
      log_message('error', "Error al obtener las clínicas: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $clinic_table);
    }
  }


  //Trae la fila de intentos que tiene un usuario
  public function getRowAttemptsDailyUser($user_id, $date)
  {
    try {
      $attempts_table = $this->db->prefixTable('attempts_edit_daily_report');

      return $this->db->table($attempts_table)
        ->select('*')
        ->where("$attempts_table.user_id", $user_id)
        ->where("MONTH($attempts_table.date)", date('m', strtotime($date)))
        ->where("YEAR($attempts_table.date)", date('Y', strtotime($date)))
        ->get()
        ->getRow();
    } catch (\Exception $e) {
      log_message('error', "Error al contar los intentos " . $e->getMessage());
    }
  }



  public function incrementUsedAttempt($user_id, $date)
  {
    try {
      $attempts_table = $this->db->prefixTable('attempts_edit_daily_report');
      $month =  date('m', strtotime($date)); // Mes en formato 01-12
      $year = date('Y', strtotime($date));   // Año en formato 2025

      // Obtener el valor actual de 'used'
      $current_used = $this->db->table($attempts_table)
        ->select("*")
        ->where("$attempts_table.user_id", $user_id)
        ->where("MONTH($attempts_table.date)", $month) // Comparar solo el mes
        ->where("YEAR($attempts_table.date)", $year)   // Comparar solo el año
        ->get()
        ->getRow(); // Obtenemos el primer resultado (si hay más de uno, lo tomaría el primero)

      if ($current_used && $current_used->used < $current_used->attempts) {
        // Incrementar el valor de 'used' en 1
        $new_used = $current_used->used + 1;

        // Actualizar el campo 'used' con el nuevo valor
        $insert = $this->db->table($attempts_table)
          ->where("$attempts_table.user_id", $user_id)
          ->where("MONTH($attempts_table.date)", $month) // Comparar solo el mes
          ->where("YEAR($attempts_table.date)", $year)   // Comparar solo el año
          ->set("used", $new_used) // Actualizar el campo 'used'
          ->update();
        return true;
      } else {
        log_message('error', "No se encontró el registro para el usuario y fecha especificados.");
        return false;
      }
    } catch (\Exception $e) {
      log_message('error', "Error al actualizar intentos usados: " . $e->getMessage());
      return  $e->getMessage();
    }
  }



  public function insertAttempt($user_id, $attempts = 1, $used = 0)
  {
    try {
      $attempts_table = $this->db->prefixTable('attempts_edit_daily_report');

      $data = [
        'user_id'  => $user_id,
        'attempts' => $attempts,
        'used'     => $used,
        'date'     => date('Y-m-d') // Obtiene la fecha actual en formato Año-Mes-Día
      ];

      return $this->db->table($attempts_table)->insert($data);
    } catch (\Exception $e) {
      log_message('error', "Error al insertar intento: " . $e->getMessage());
      return false;
    }
  }

  //LOG DE EDICIONES
  public function getAllLogEntries($user_id)
  {
    try {
      $log_table = $this->db->prefixTable('log_edit_daily_report');

      return $this->db->table($log_table)
        ->select('*')
        ->where("$log_table.user_id", $user_id)
        ->get()
        ->getResult();  // Obtiene todos los registros y los devuelve
    } catch (\Exception $e) {
      log_message('error', "Error al obtener los registros: " . $e->getMessage());
    }
  }

  public function getAllLog()
  {
    try {
      $log_table = $this->db->prefixTable('log_edit_daily_report');

      return $this->db->table($log_table)
        ->select('*')
        ->orderBy("$log_table.id", 'DESC')
        ->get()
        ->getResult();  // Obtiene todos los registros y los devuelve
    } catch (\Exception $e) {
      log_message('error', "Error al obtener los registros: " . $e->getMessage());
    }
  }

  public function insertLogEntry($report_id, $user_id, $comment)
  {
    try {
      $log_table = $this->db->prefixTable('log_edit_daily_report');

      $data = [
        'report_id' => $report_id,
        'user_id' => $user_id,
        'comment' => $comment,
        'date' => date('Y-m-d H:i:s')
      ];

      // Inserta el nuevo registro en la base de datos
      return $this->db->table($log_table)
        ->insert($data);
    } catch (\Exception $e) {
      log_message('error', "Error al insertar el registro: " . $e->getMessage());
      return $e->getMessage();
    }
  }


    public function checkLogByUserAndToday($user_id,$report_id)
  {
      try {
          // Obtener la tabla de registros de edición
          $log_table = $this->db->prefixTable('log_edit_daily_report');

          // Obtener la fecha actual
          $currentDate = date('Y-m-d');  // Fecha de hoy en formato yyyy-mm-dd

          // Realizar la consulta con filtro por ID de usuario y fecha actual
          $result = $this->db->table($log_table)
              ->select('*')
              ->where("$log_table.report_id", $report_id)  
              ->where("$log_table.user_id", $user_id)// Filtrar por ID de usuario
              ->where("DATE($log_table.date)", $currentDate)  // Filtrar por la fecha actual
              ->get()
              ->getResult();  // Obtener los resultados

          // Si existen registros, devuelve true, de lo contrario false
          return !empty($result);  // Retorna true si hay registros, false si está vacío
      } catch (\Exception $e) {
          log_message('error', "Error al verificar los registros: " . $e->getMessage());
          return false;  // En caso de error, devuelve false
      }
  }



  public function getClinics2($user_id)
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      $branch_table = $this->db->prefixTable('branch'); // Corrige "brach_table" por "branch_table"
      return $this->db->table($clinic_table)
        ->select("$clinic_table.id, $clinic_table.name") // Selecciona los campos de la tabla clinic_directory
        ->join($branch_table, "$branch_table.id_clinic = $clinic_table.id") // Realiza el INNER JOIN con branch
        ->where("$branch_table.id_user", $user_id) // Filtra por el user_id en branch
        ->get()
        ->getResult();
    } catch (\Exception $e) {
      log_message('error', "Error al obtener las clínicas: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $clinic_table);
    }
  }

  public function updateDailyReport($report_id, $column, $value)
  {
    try {
      $builder = $this->db->table('daily_reports'); // Selecciona la tabla

      // Actualiza el campo específico para el reporte dado
      $builder->where('id', $report_id)
        ->set($column, $value)
        ->update();

      // Verifica si la actualización fue exitosa
      if ($this->db->affectedRows() > 0) {
        return true; // Actualización exitosa
      } else {
        return false; // No se realizó la actualización (puede que no haya cambios)
      }
    } catch (\Exception $e) {
      log_message('error', "Error al actualizar el reporte diario: " . $e->getMessage());
      throw new \Exception("Error al actualizar el reporte en la tabla 'daily_reports'");
    }
  }


  /**
   * Obtener el nombre de una clínica por su ID.
   * @param int $clinic_id
   * @return object
   */
  public function getClinicById($clinic_id)
  {
    return $this->db->table('clinic_directory')->select('name')->where('id', $clinic_id)->get()->getRow();
  }

  public function getDailyReportsCountNow($clinicId,$dateArray = false,$report_date = false)
  {
    if(!$dateArray){
      $today = date('Y-m-d'); // Formato YYYY-MM-DD
    }else{
      $today = date('Y-m-d', strtotime($dateArray));
    }

    if($report_date != false){
      $today = $report_date;
    }

    // Construir la consulta
    $builder = $this->table($this->table);

    // Filtrar por la clínica y la fecha de hoy
    if (!empty($clinicId)) {
      $builder->where('clinic_id', $clinicId); // Filtro por la clínica
    }

    // Si la columna es de tipo DATETIME, usamos DATE() para comparar solo la fecha
    $builder->where("DATE(report_date) =", $today); // Asegura que solo se compara la fecha

    // Contar el número de filas
    return $builder->countAllResults();
  }

  public function getReportById($report_id)
  {
    try {
      $report_table = $this->db->prefixTable('daily_reports');

      // Obtiene el reporte por su id
      return $this->db->table($report_table)
        ->select('*')
        ->where('id', $report_id)  // Filtro por el ID del reporte
        ->get()
        ->getRow();  // Devuelve el primer (y único) resultado
    } catch (\Exception $e) {
      log_message('error', "Error al obtener el reporte por ID: " . $e->getMessage());
      return null;  // En caso de error, retorna null
    }
  }


  /**
   * Obtener reportes por ID de clínica.
   * @param int $clinic_id
   * @return array
   */
  public function findReportsByClinic($clinic_id)
  {
    return $this->where('clinic_id', $clinic_id)
      ->findAll();
  }

  /**
   * Obtener reportes filtrados.
   * @param int $start
   * @param int $length
   * @param string $search
   * @param string $columnName
   * @param string $columnSortOrder
   * @param string|null $startDate
   * @param string|null $endDate
   * @return array
   */
  public function getFilteredReports($start, $length, $search, $columnName, $columnSortOrder, $startDate = null, $endDate = null, $clinicId = null)
  {
    $builder = $this->table($this->table);

    if ($search) {
      $builder->groupStart()
        ->like('submitted_by', $search)
        ->orLike('clinic_name', $search)
        ->orLike('report_date', $search)
        ->groupEnd();
    }

    if (!empty($clinicId)) {
      $builder->where('clinic_id =', $clinicId);
    }
    if (!empty($startDate)) {
      $builder->where('report_date >=', $startDate);
    }
    if (!empty($endDate)) {
      $builder->where('report_date <=', $endDate);
    }

    return $builder->orderBy($columnName, $columnSortOrder)
      ->limit($length, $start)
      ->get()
      ->getResultArray();
  }

  public function getFilteredReports2($start, $length, $search, $columnName, $columnSortOrder, $startDate = null, $endDate = null, $clinicId = null, $user_id)
  {
    $builder = $this->table('daily_reports'); // Asegúrate de que esta es la tabla de reportes
    $branch_table = $this->db->prefixTable('branch'); // La tabla de relaciones (crm_branch)

    // Realizamos el INNER JOIN entre la tabla de reportes y la tabla de clínicas asociadas al usuario
    $builder->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id");

    // Filtros de búsqueda
    if ($search) {
      $builder->groupStart()
        ->like('submitted_by', $search)
        ->orLike('clinic_name', $search)
        ->orLike('report_date', $search)
        ->groupEnd();
    }

    // Filtros adicionales
    if (!empty($clinicId)) {
      $builder->where('daily_reports.clinic_id', $clinicId); // Filtro por la clínica si se pasa el clinicId
    }
    if (!empty($startDate)) {
      $builder->where('daily_reports.report_date >=', $startDate); // Filtro por fecha de inicio
    }
    if (!empty($endDate)) {
      $builder->where('daily_reports.report_date <=', $endDate); // Filtro por fecha de fin
    }

    // Filtrar solo por las clínicas asociadas a este usuario
    $builder->where("$branch_table.id_user", $user_id); // Aquí se filtra por el usuario en la tabla crm_branch

    // Ordenar por la columna especificada y por la fecha del reporte de forma descendente (más reciente primero)
    $builder->orderBy($columnName, $columnSortOrder)  // Orden principal
      ->orderBy('daily_reports.report_date', 'DESC');  // Asegura que los reportes más recientes se muestren primero

    return $builder->limit($length, $start)
      ->get()
      ->getResultArray();
  }



  /**
   * Obtener el total de reportes.
   * @return int
   */
  public function getTotalReportsCount()
  {
    return $this->countAll();
  }

  /**
   * Obtener el total de reportes filtrados.
   * @param string $search
   * @param string|null $startDate
   * @param string|null $endDate
   * @return int
   */
  public function getFilteredReportsCount($search, $startDate = null, $endDate = null, $clinicId = null)
  {
    $builder = $this->table($this->table);

    if ($search) {
      $builder->groupStart()
        ->like('submitted_by', $search)
        ->orLike('clinic_name', $search)
        ->orLike('report_date', $search)
        ->groupEnd();
    }

    if (!empty($clinicId) && empty($startDate) && empty($endDate)) {
      $builder->where('clinic_id =', $clinicId);
    }

    if (!empty($startDate) && !empty($endDate) && !empty($clinicId)) {
      $builder->where('report_date >=', $startDate)
        ->where('report_date <=', $endDate)
        ->where('clinic_id =', $clinicId);
    }

    return $builder->countAllResults();
  }

  public function getFilteredReportsCount2($search, $startDate = null, $endDate = null, $clinicId = null, $user_id)
  {
    $builder = $this->table('daily_reports'); // Asegúrate de que esta es la tabla correcta
    $branch_table = $this->db->prefixTable('branch'); // Tabla que relaciona clínicas con usuarios

    // Realizamos el INNER JOIN entre la tabla de reportes y la tabla de clínicas
    $builder->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id");

    // Filtros de búsqueda
    if ($search) {
      $builder->groupStart()
        ->like('submitted_by', $search)
        ->orLike('clinic_name', $search)
        ->orLike('report_date', $search)
        ->groupEnd();
    }

    // Filtros adicionales
    if (!empty($clinicId)) {
      $builder->where('daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $builder->where('daily_reports.report_date >=', $startDate);
    }

    if (!empty($endDate)) {
      $builder->where('daily_reports.report_date <=', $endDate);
    }

    // Filtrar solo por las clínicas asociadas al usuario
    $builder->where("$branch_table.id_user", $user_id);

    // Contar los resultados
    return $builder->countAllResults();
  }


  /**
   * Obtener datos de pacientes.
   * @param int $clinicId
   * @return array
   */
  public function getPatientsData($clinicId)
  {
    $monthlyPatientsQuery = $this->select('DATE_FORMAT(report_date, "%Y-%m") as month, SUM(new_patients_total + uninsured_patients + insured_patients) as total_patients')
      ->where('clinic_id', $clinicId)
      ->groupBy('DATE_FORMAT(report_date, "%Y-%m")')
      ->get();
    $monthlyPatients = $monthlyPatientsQuery->getResultArray();

    if (empty($monthlyPatients)) {
      return [
        'monthlyPatients' => [0],
        'labels' => []
      ];
    }

    $labels = array_column($monthlyPatients, 'month');

    return [
      'monthlyPatients' => array_column($monthlyPatients, 'total_patients'),
      'labels' => $labels
    ];
  }
  public function getPatientsData1($clinicId, $startDate, $endDate, $user_id)
  {
    $branch_table = $this->db->prefixTable('branch'); // Tabla que relaciona clínicas con usuarios

    // Realizamos la consulta con JOIN
    //$this->select('DATE_FORMAT(crm_daily_reports.report_date, "%Y-%m") as month, SUM(new_patients_total + uninsured_patients + insured_patients) as total_patients')
      $this->select('DATE_FORMAT(crm_daily_reports.report_date, "%Y-%m") as month, SUM(new_patients_total + followup_patients_total) as total_patients')
      ->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id")
      ->where("$branch_table.id_user", $user_id) // Filtramos por el usuario
      ->groupBy('DATE_FORMAT(crm_daily_reports.report_date, "%Y-%m")');

    if (empty($clinicId) && empty($startDate) && empty($endDate)) {
      $endDate = date('Y-m-d'); // Fecha actual
      $startDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate))); // Fecha hace un mes
    }

    // Condicionales para filtros dinámicos
    if (!empty($clinicId)) {
      $this->where('crm_daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $this->where('crm_daily_reports.report_date >=', $startDate);
    }

    if (!empty($endDate)) {
      $this->where('crm_daily_reports.report_date <=', $endDate);
    }

    // Ordenación por mes de manera ascendente
    $this->orderBy('crm_daily_reports.report_date', 'ASC'); // Ordenar por fecha (mes) de forma ascendente

    // Ejecutamos la consulta
    $monthlyPatientsQuery = $this->get();
    $monthlyPatients = $monthlyPatientsQuery->getResultArray();

    // Manejo de resultados vacíos
    if (empty($monthlyPatients)) {
      return [
        'monthlyPatients' => [0],
        'labels' => []
      ];
    }

    // Retornamos los resultados procesados
    return [
      'monthlyPatients' => array_column($monthlyPatients, 'total_patients'),
      'labels' => array_column($monthlyPatients, 'month')
    ];
  }



  /**
   * Obtener datos de ingresos.
   * @param int $clinicId
   * @return array
   */
  public function getIncomeData1($clinicId, $startDate, $endDate, $id_user)
  {
    $branch_table = $this->db->prefixTable('branch'); // Tabla de relación entre clínicas y usuarios

    // Construcción de la consulta
    $query = $this->select('DATE(crm_daily_reports.report_date) as date, SUM(sales_cash + sales_card + sales_other) as total_income')
      ->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id")
      ->where("$branch_table.id_user", $id_user); // Filtrar por usuario

    // Aplicación de filtros dinámicos para fechas
    if (empty($clinicId) && empty($startDate) && empty($endDate)) {
      $endDate = date('Y-m-d'); // Fecha actual
      $startDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate))); // Fecha hace un mes
    }

    if (!empty($startDate)) {
      $query->where('crm_daily_reports.report_date >=', $startDate);
    }
    if (!empty($endDate)) {
      $query->where('crm_daily_reports.report_date <=', $endDate);
    }

    if (!empty($clinicId)) {
      $query->where('crm_daily_reports.clinic_id', $clinicId);
    }

    // Agrupación después de aplicar filtros
    $query->groupBy('DATE(crm_daily_reports.report_date)');

    // Asegurarse de ordenar por fecha en orden ascendente (de menor a mayor)
    $query->orderBy('crm_daily_reports.report_date', 'ASC'); // Ordenar por fecha

    // Ejecución de la consulta
    $dailyIncomeQuery = $query->get();
    $dailyIncome = $dailyIncomeQuery->getResultArray();

    // Manejo de resultados vacíos
    if (empty($dailyIncome)) {
      return [
        'dailyIncome' => [],
        'labels' => []
      ];
    }

    // Preparación de los datos para el retorno
    return [
      'dailyIncome' => array_column($dailyIncome, 'total_income'),
      'labels' => array_column($dailyIncome, 'date')
    ];
  }





  public function getIncomeData($clinicId)
  {
    $dailyIncomeQuery = $this->select('DATE(report_date) as date, SUM(sales_cash + sales_card + sales_other) as total_income')
      ->where('clinic_id', $clinicId)
      ->groupBy('DATE(report_date)')
      ->get();
    $dailyIncome = $dailyIncomeQuery->getResultArray();

    if (empty($dailyIncome)) {
      return [
        'dailyIncome' => [],
        'labels' => []
      ];
    }

    $labels = array_column($dailyIncome, 'date');

    return [
      'dailyIncome' => array_column($dailyIncome, 'total_income'),
      'labels' => $labels
    ];
  }

  /**
   * Obtener datos de plataformas de marketing.
   * @param int $clinicId
   * @return array
   */
  public function getPlatformsData($clinicId)
  {
    $platformsQuery = $this->select([
      'SUM(followup_patients_total) as followup_patients_total',
      'SUM(referral_google) as referral_google',
      'SUM(referral_referred) as referral_referred',
      'SUM(referral_mail) as referral_mail',
      'SUM(referral_walkby) as referral_walkby',
      'SUM(referral_facebook) as referral_facebook',
      'SUM(referral_events) as referral_events',
      'SUM(referral_instagram) as referral_instagram',
      'SUM(referral_youtube) as referral_youtube',
      'SUM(referral_tiktok) as referral_tiktok',
      'SUM(referral_radio) as referral_radio',
      'SUM(referral_newspaper) as referral_newspaper'
    ])
      ->where('clinic_id', $clinicId)
      ->get();

    $platformsData = $platformsQuery->getRowArray();

    if ($platformsData === null) {
      $platformsData = [
        'followup_patients_total' => 0,
        'referral_google' => 0,
        'referral_referred' => 0,
        'referral_mail' => 0,
        'referral_walkby' => 0,
        'referral_facebook' => 0,
        'referral_events' => 0,
        'referral_instagram' => 0,
        'referral_youtube' => 0,
        'referral_tiktok' => 0,
        'referral_radio' => 0,
        'referral_newspaper' => 0
      ];
    } else {
      $platformsData = array_map(function ($value) {
        return $value === null ? 0 : $value;
      }, $platformsData);
    }

    $internetSum = $platformsData['followup_patients_total'] + $platformsData['referral_google'] + $platformsData['referral_mail'] + $platformsData['referral_facebook'] + $platformsData['referral_instagram'] + $platformsData['referral_youtube'] +     $platformsData['referral_radio'] + $platformsData['referral_newspaper'];
    $walkingSum = $platformsData['referral_walkby'];
    $referredSum = $platformsData['referral_events'] + $platformsData['referral_referred'];

    return [
      'internetSum' => $internetSum,
      'walkingSum' => $walkingSum,
      'referredSum' => $referredSum
    ];
  }

  public function getPlatformsData1($clinicId, $startDate, $endDate, $id_user,$especific = false)
  {
    $branch_table = $this->db->prefixTable('branch'); // Tabla de relación entre clínicas y usuarios

    // Construcción de la consulta base con JOIN
    $query = $this->select([
      'SUM(followup_patients_total) as followup_patients_total',
      'SUM(referral_google) as referral_google',
      'SUM(referral_referred) as referral_referred',
      'SUM(referral_mail) as referral_mail',
      'SUM(referral_walkby) as referral_walkby',
      'SUM(referral_facebook) as referral_facebook',
      'SUM(referral_events) as referral_events',
      'SUM(referral_instagram) as referral_instagram',
      'SUM(referral_youtube) as referral_youtube',
      'SUM(referral_tiktok) as referral_tiktok',
      'SUM(referral_radio) as referral_radio',
      'SUM(referral_newspaper) as referral_newspaper'
    ])
      ->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id")
      ->where("$branch_table.id_user", $id_user); // Filtrar por usuario

    if (empty($clinicId) && empty($startDate) && empty($endDate)) {
      $endDate = date('Y-m-d'); // Fecha actual
      $startDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate))); // Fecha hace un mes
    }

    // Aplicar filtros dinámicos
    if (!empty($clinicId)) {
      $query->where('crm_daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate) && !empty($endDate)) {
      $query->where('crm_daily_reports.report_date >=', $startDate)
        ->where('crm_daily_reports.report_date <=', $endDate);
    }

    // Ejecutar consulta
    $platformsData = $query->get()->getRowArray();

    // Manejo de datos nulos
    $defaultValues = [
      'followup_patients_total' => 0,
      'referral_google' => 0,
      'referral_referred' => 0,
      'referral_mail' => 0,
      'referral_walkby' => 0,
      'referral_facebook' => 0,
      'referral_events' => 0,
      'referral_instagram' => 0,
      'referral_youtube' => 0,
      'referral_tiktok' => 0,
      'referral_radio' => 0,
      'referral_newspaper' => 0
    ];

    $platformsData = $platformsData ? array_map(function ($value) {
      return $value === null ? 0 : $value;
    }, $platformsData) : $defaultValues;

    // Calcular totales
    $internetSum = $platformsData['referral_google'] +
      $platformsData['referral_mail'] +
      $platformsData['referral_facebook'] +
      $platformsData['referral_instagram'] +
      $platformsData['referral_youtube'] +
      $platformsData['referral_tiktok'] ;

    $means_of_communication = $platformsData['referral_radio'] +
    $platformsData['referral_newspaper'];

    $walkingSum = $platformsData['referral_walkby'];
    $referredSum = $platformsData['referral_events'] + $platformsData['referral_referred'];
    if($especific){
      return [
        'google' => $platformsData['referral_google'],
        'mail' => $platformsData['referral_mail'],
        'facebook' => $platformsData['referral_facebook'],
        'instagram' => $platformsData['referral_instagram'],
        'youtube' =>  $platformsData['referral_youtube'],
        'tiktok' => $platformsData['referral_tiktok'],
        'radio' => $platformsData['referral_radio'],
        'newspaper' => $platformsData['referral_newspaper'],
      ];
    }
    return [
      'internetSum' => $internetSum,
      'meanSum' => $means_of_communication,
      'walkingSum' => $walkingSum,
      'referredSum' => $referredSum,
      
    ];
  }

  public function getIndicatePerformanceData1($rang_one_1, $rang_one_2, $rang_two_1, $rang_two_2, $clinic_id)
  {
    //Data
    $fields = [
      'IFNULL(SUM(new_patients_total), 0) as new_patients_total',
      'IFNULL(SUM(followup_patients_total), 0) as followup_patients_total',
      'IFNULL(SUM(sales_cash + sales_card + sales_other), 0) as sales_total',
      'IFNULL(SUM(referral_referred), 0) as referral_total',
      'IFNULL(SUM(referral_google), 0) as google_website_total',
      'IFNULL(SUM(referral_mail), 0) as email_total',
      'IFNULL(SUM(referral_walkby), 0) as walkby_total',
      'IFNULL(SUM(referral_facebook), 0) as facebook_total',
      'IFNULL(SUM(referral_instagram), 0) as instagram_total',
      'IFNULL(SUM(referral_events), 0) as events_total',
      'IFNULL(SUM(referral_youtube), 0) as youtube_total',
      'IFNULL(SUM(referral_tiktok), 0) as tiktok_total',
      'IFNULL(SUM(referral_radio), 0) as radio_total',
      'IFNULL(SUM(referral_newspaper), 0) as newspaper_total',
      'IFNULL(SUM(referral_tv), 0) as tv_total',
      'IFNULL(SUM(uninsured_patients), 0) as uninsured_patients',
      'IFNULL(SUM(insured_patients), 0) as insured_patients',
    ];


    $range_one = $this->select($fields)
      ->where('crm_daily_reports.report_date >=', $rang_one_1)
      ->where('crm_daily_reports.report_date <=', $rang_one_2)
      ->where('clinic_id', $clinic_id)
      ->get();

    $range_two = $this->select($fields)
      ->where('crm_daily_reports.report_date >=', $rang_two_1)
      ->where('crm_daily_reports.report_date <=', $rang_two_2)
      ->where('clinic_id', $clinic_id)
      ->get();
    //$range_one->getRowArray()
    return [
      'range_1' => $range_one->getRowArray(),
      'range_2' => $range_two->getRowArray()
    ];
  }

  public function getIndicateRangeData($rang_one_1, $rang_one_2, $clinic_id)
  {
    //Data
    $fields = [
      'IFNULL(SUM(sales_cash), 0) as sales_cash',
      'IFNULL(SUM(sales_card), 0) as sales_card',
      'IFNULL(SUM(sales_other), 0) as sales_other',
      'IFNULL(SUM(new_patients_total), 0) as new_patients_total',
      'IFNULL(SUM(followup_patients_total), 0) as followup_patients_total',
      'IFNULL(SUM(referral_referred), 0) as referral_total',
      'IFNULL(SUM(referral_google), 0) as google_website_total',
      'IFNULL(SUM(referral_mail), 0) as email_total',
      'IFNULL(SUM(referral_walkby), 0) as walkby_total',
      'IFNULL(SUM(referral_facebook), 0) as facebook_total',
      'IFNULL(SUM(referral_instagram), 0) as instagram_total',
      'IFNULL(SUM(referral_events), 0) as events_total',
      'IFNULL(SUM(referral_youtube), 0) as youtube_total',
      'IFNULL(SUM(referral_tiktok), 0) as tiktok_total',
      'IFNULL(SUM(referral_radio), 0) as radio_total',
      'IFNULL(SUM(referral_newspaper), 0) as newspaper_total',
      'IFNULL(SUM(referral_tv), 0) as tv_total',
      'IFNULL(SUM(insured_patients), 0) as insured_patients',
      'IFNULL(SUM(uninsured_patients), 0) as uninsured_patients'
    ];


    $range_one = $this->select($fields)
      ->where('crm_daily_reports.report_date >=', $rang_one_1)
      ->where('crm_daily_reports.report_date <=', $rang_one_2)
      ->where('clinic_id', $clinic_id)
      ->get();

    //$range_one->getRowArray()
    return [
      'range_1' => $range_one->getRowArray()
    ];
  }

  public function getFilteredReportsPdf($startDate, $endDate, $clinicId)
  {
    $builder = $this->table('daily_reports'); // Asegúrate de que esta es la tabla correcta
    $branch_table = $this->db->prefixTable('branch'); // Tabla que relaciona clínicas con usuarios

    // Filtros adicionales
    if (!empty($clinicId)) {
      $builder->where('daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $builder->where('daily_reports.report_date >=', $startDate);
    }

    if (!empty($endDate)) {
      $builder->where('daily_reports.report_date <=', $endDate);
    }

    $builder->orderBy('daily_reports.report_date', 'ASC');

    return [
      'range_1' => $builder->get()->getResultArray()
    ];
  }

  /**
   * Obtener datos de prevalencia de seguros.
   * @param int $clinicId
   * @return array
   */
  public function getInsurancePrevalenceData1($clinicId, $startDate, $endDate, $id_user)
  {
    $branch_table = $this->db->prefixTable('branch'); // Tabla de relación entre clínicas y usuarios

    // Construcción de la consulta base con JOIN
    $query = $this->select([
      'SUM(insured_patients) as insured_patients',
      'SUM(uninsured_patients) as uninsured_patients',
      'SUM(referral_tv) as referral_tv'
    ])
      ->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id")
      ->where("$branch_table.id_user", $id_user); // Filtrar por usuario

    if (empty($clinicId) && empty($startDate) && empty($endDate)) {
      $endDate = date('Y-m-d'); // Fecha actual
      $startDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate))); // Fecha hace un mes
    }

    // Aplicar filtros dinámicos
    if (!empty($clinicId)) {
      $query->where('crm_daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $query->where('crm_daily_reports.report_date >=', $startDate);
    }

    if (!empty($endDate)) {
      $query->where('crm_daily_reports.report_date <=', $endDate);
    }

    // Obtener resultados
    $insuranceData = $query->get()->getRowArray();

    // Valores por defecto si no hay resultados
    $defaultValues = [
      'insured_patients' => 0,
      'uninsured_patients' => 0,
      'referral_tv' => 0
    ];

    $insuranceData = array_merge($defaultValues, $insuranceData ?? []);

    // Etiquetas para los datos
    $labels = ['Insured Patients', 'Uninsured Patients'];

    return [
      'insuranceData' => [
        $insuranceData['insured_patients'],
        $insuranceData['uninsured_patients']
      ],
      'labels' => $labels
    ];
  }


  public function getInsurancePrevalenceData($clinicId)
  {
    $insuranceQuery = $this->select([
      'SUM(insured_patients) as insured_patients',
      'SUM(uninsured_patients) as uninsured_patients',
      'SUM(referral_tv) as referral_tv'
    ])
      ->where('clinic_id', $clinicId)
      ->get();

    $insuranceData = $insuranceQuery->getRowArray();

    if ($insuranceData === null) {
      $insuranceData = [
        'insured_patients' => 0,
        'uninsured_patients' => 0,
        'referral_tv' => 0
      ];
    } else {
      $insuranceData = array_map(function ($value) {
        return $value === null ? 0 : $value;
      }, $insuranceData);
    }

    $labels = ['Insured Patients', 'Uninsured Patients'];

    return [
      'insuranceData' => array_values($insuranceData),
      'labels' => $labels
    ];
  }

  public function getTotalsData($clinicId, $startDate, $endDate, $user_id)
  {
    // Define los campos seleccionados
    $fields = [
      'SUM(sales_cash) as sales_cash',
      'SUM(sales_card) as sales_card',
      'SUM(sales_other) as sales_other',
      'SUM(insured_patients) as insured_patients',
      'SUM(uninsured_patients) as uninsured_patients',
      'SUM(new_patients_total) as new_patients_total',
      'SUM(sales_cash + sales_card + sales_other) as sales_total',
      'SUM(new_patients_total + insured_patients + uninsured_patients) as patients_total',
      'SUM(new_patients_total + followup_patients_total) as marketing_total'
    ];


    /* $fields = [
          'SUM(sales_cash) as sales_cash',
          'SUM(sales_card) as sales_card',
          'SUM(sales_other) as sales_other',
          'SUM(insured_patients) as insured_patients',
          'SUM(uninsured_patients) as uninsured_patients',
          'SUM(new_patients_total) as new_patients_total',
          'SUM(sales_cash + sales_card + sales_other) as sales_total',
          'SUM(new_patients_total + insured_patients + uninsured_patients) as patients_total',
          'SUM(new_patients_total + followup_patients_total + referral_google + referral_referred + referral_mail + referral_walkby + referral_facebook + referral_events +  referral_instagram + referral_youtube + referral_tiktok + referral_radio + referral_newspaper + referral_tv) as marketing_total'
        ];*/

    // Inicia la consulta base
    $query = $this->select($fields);
    $branch_table = $this->db->prefixTable('branch');

    // Realizamos el INNER JOIN entre la tabla de reportes (daily_reports) y la tabla de clínicas (branch)
    $query->join($branch_table, "$branch_table.id_clinic = crm_daily_reports.clinic_id");

    // Aplica filtros dinámicos
    if (!empty($clinicId)) {
      $query->where('daily_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $query->where('daily_reports.report_date >=', $startDate);
    }

    if (!empty($endDate)) {
      $query->where('daily_reports.report_date <=', $endDate);
    }

    // Filtrar solo por las clínicas asociadas a este usuario
    $query->where("$branch_table.id_user", $user_id);

    // Ejecuta la consulta
    $insuranceData = $query->get()->getRowArray();

    // Manejo de resultados vacíos
    if ($insuranceData === null) {
      $insuranceData = [
        'sales_cash' => 0,
        'sales_card' => 0,
        'sales_other' => 0,
        'new_patients_total' => 0,
        'insured_patients' => 0,
        'uninsured_patients' => 0,
        'sales_total' => 0,
        'patients_total' => 0,
        'marketing_total' => 0,
      ];
    } else {
      // Reemplaza valores null por 0
      $insuranceData = array_map(fn($value) => $value === null ? 0 : $value, $insuranceData);
    }

    // Define las etiquetas
    $labels = [
      'sales_cash',
      'sales_card',
      'sales_other',
      'insured_patients',
      'uninsured_patients',
      'new_patients_total',
      'sales_total',
      'patients_total',
      'marketing_total'
    ];

    // Retorna los resultados
    return [
      'data' => array_values($insuranceData),
      'labels' => $labels
    ];
  }

  public function getReportForDeletion($report_id)
    {
        try {
            $report_table = $this->db->prefixTable('daily_reports');
            $clinic_table = $this->db->prefixTable('clinic_directory');
            
            return $this->db->table($report_table)
                ->select("
                    $report_table.id,
                    $report_table.clinic_id,
                    $report_table.report_date,
                    $report_table.report_file,
                    $report_table.submitted_by,
                    $clinic_table.name as clinic_name
                ")
                ->join($clinic_table, "$clinic_table.id = $report_table.clinic_id")
                ->where("$report_table.id", $report_id)
                ->get()
                ->getRow();
        } catch (\Exception $e) {
            log_message('error', "Error al obtener información del reporte para eliminación: " . $e->getMessage());
            return null;
        }
    }
    
    public function deleteLogsByReportId($report_id)
    {
        try {
            $log_table = $this->db->prefixTable('log_edit_daily_report');
            
            // Primero verificar si existen logs para este reporte
            $existing_logs = $this->db->table($log_table)
                ->where('report_id', $report_id)
                ->countAllResults();
            
            if ($existing_logs > 0) {
                // Eliminar todos los logs relacionados con este reporte
                $builder = $this->db->table($log_table);
                $builder->where('report_id', $report_id);
                $deleted = $builder->delete();
                
                if ($deleted) {
                    log_message('info', "Se eliminaron $existing_logs logs del reporte ID: $report_id");
                    return true;
                } else {
                    log_message('error', "No se pudieron eliminar los logs del reporte ID: $report_id");
                    return false;
                }
            } else {
                // No hay logs para eliminar, pero no es un error
                log_message('info', "No se encontraron logs para eliminar del reporte ID: $report_id");
                return true;
            }
        } catch (\Exception $e) {
            log_message('error', "Error al eliminar logs del reporte: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteReport($report_id)
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->where('id', $report_id);
            $deleted = $builder->delete();
            
            if ($deleted && $this->db->affectedRows() > 0) {
                log_message('info', "Reporte diario eliminado exitosamente. ID: $report_id");
                return true;
            } else {
                log_message('warning', "No se pudo eliminar el reporte o no existe. ID: $report_id");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Error al eliminar reporte diario: " . $e->getMessage());
            return false;
        }
    }

    public function canDeleteReport($report_id, $user_id)
    {
        try {
            $report = $this->getReportById($report_id);
            
            if (!$report) {
                return false;
            }
            
            // Verificar si el usuario tiene acceso a la clínica del reporte
            $user_clinics = $this->getClinics2($user_id);
            
            foreach ($user_clinics as $clinic) {
                if ($clinic->id == $report->clinic_id) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            log_message('error', "Error al verificar permisos de eliminación: " . $e->getMessage());
            return false;
        }
    }


    
    /**
     * Obtener estadísticas mensuales por rendimiento económico de una clínica
     * Agrupa por mes y suma todos los valores económicos
     * @param int $clinic_id
     * @param array $selected_months Array de meses en formato 'Y-m' o null para todos
     * @return array
     */
    public function getMonthlyEconomicStats($clinic_id, $selected_months = null)
    {
        try {
            $report_table = $this->db->prefixTable('daily_reports');
            
            // Construir la consulta base
            $builder = $this->db->table($report_table);
            $builder->select([
                "DATE_FORMAT(report_date, '%Y-%m') as month",
                "DATE_FORMAT(report_date, '%M %Y') as month_name",
                'SUM(sales_cash + sales_card + sales_other) as total_sales',
                'SUM(sales_cash) as total_cash',
                'SUM(sales_card) as total_card',
                'SUM(sales_other) as total_other',
                'SUM(new_patients_total) as total_new_patients',
                'SUM(followup_patients_total) as total_followup_patients',
                'COUNT(*) as total_reports'
            ]);
            
            $builder->where('clinic_id', $clinic_id);
            
            // Si se especifican meses específicos, filtrar por ellos
            if ($selected_months && is_array($selected_months) && !empty($selected_months)) {
                $builder->groupStart();
                foreach ($selected_months as $month) {
                    $builder->orWhere("DATE_FORMAT(report_date, '%Y-%m')", $month);
                }
                $builder->groupEnd();
            }
            
            $builder->groupBy("DATE_FORMAT(report_date, '%Y-%m')");
            $builder->orderBy('total_sales', 'DESC'); // Ordenar por rendimiento económico descendente
            
            $results = $builder->get()->getResultArray();
            
            if (empty($results)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron reportes para esta clínica',
                    'data' => []
                ];
            }
            
            // Calcular el promedio diario de ventas para cada mes
            foreach ($results as &$result) {
                $result['average_daily_sales'] = $result['total_reports'] > 0 
                    ? round($result['total_sales'] / $result['total_reports'], 2) 
                    : 0;
            }
            
            return [
                'success' => true,
                'data' => $results
            ];
            
        } catch (\Exception $e) {
            log_message('error', "Error al obtener estadísticas mensuales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener lista de meses disponibles para una clínica
     * @param int $clinic_id
     * @return array
     */
    public function getAvailableMonthsForClinic($clinic_id)
    {
        try {
            $report_table = $this->db->prefixTable('daily_reports');
            
            $builder = $this->db->table($report_table);
            $builder->select([
                "DATE_FORMAT(report_date, '%Y-%m') as month_value",
                "DATE_FORMAT(report_date, '%M %Y') as month_label"
            ]);
            
            $builder->where('clinic_id', $clinic_id);
            $builder->groupBy("DATE_FORMAT(report_date, '%Y-%m')");
            $builder->orderBy('report_date', 'DESC');
            
            $results = $builder->get()->getResultArray();
            
            return [
                'success' => true,
                'data' => $results
            ];
            
        } catch (\Exception $e) {
            log_message('error', "Error al obtener meses disponibles: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener meses disponibles',
                'data' => []
            ];
        }
    }

    /**
     * Obtener datos cronológicos para gráfica de tendencias mensuales
     * Devuelve datos ordenados por fecha (cronológicamente)
     * @param int $clinic_id
     * @param array $selected_months Array de meses en formato 'Y-m' o null para todos
     * @return array
     */
    public function getMonthlyTrendsChartData($clinic_id, $selected_months = null)
    {
        try {
            $report_table = $this->db->prefixTable('daily_reports');
            
            // Construir la consulta base
            $builder = $this->db->table($report_table);
            $builder->select([
                "DATE_FORMAT(report_date, '%Y-%m') as month",
                "DATE_FORMAT(report_date, '%Y-%m-01') as month_date",
                "DATE_FORMAT(report_date, '%M %Y') as month_name",
                'SUM(sales_cash + sales_card + sales_other) as total_sales',
                'SUM(new_patients_total) as total_new_patients',
                'SUM(followup_patients_total) as total_followup_patients'
            ]);
            
            $builder->where('clinic_id', $clinic_id);
            
            // Si se especifican meses específicos, filtrar por ellos
            if ($selected_months && is_array($selected_months) && !empty($selected_months)) {
                $builder->groupStart();
                foreach ($selected_months as $month) {
                    $builder->orWhere("DATE_FORMAT(report_date, '%Y-%m')", $month);
                }
                $builder->groupEnd();
            }
            
            $builder->groupBy("DATE_FORMAT(report_date, '%Y-%m')");
            $builder->orderBy('report_date', 'ASC'); // Ordenar cronológicamente (ascendente)
            
            $results = $builder->get()->getResultArray();
            
            if (empty($results)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron reportes para esta clínica',
                    'data' => []
                ];
            }
            
            // Preparar los datos para la gráfica
            $labels = [];
            $salesData = [];
            $newPatientsData = [];
            $followupPatientsData = [];
            
            foreach ($results as $result) {
                $labels[] = $result['month_date']; // Formato: 2024-01-01
                $salesData[] = floatval($result['total_sales']);
                $newPatientsData[] = intval($result['total_new_patients']);
                $followupPatientsData[] = intval($result['total_followup_patients']);
            }
            
            return [
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'sales' => $salesData,
                    'new_patients' => $newPatientsData,
                    'followup_patients' => $followupPatientsData
                ]
            ];
            
        } catch (\Exception $e) {
            log_message('error', "Error al obtener datos de tendencias mensuales: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener datos de tendencias: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
