<?php

namespace App\Models;

use CodeIgniter\Model;

class Deposit_report_model extends Model
{
  protected $table = 'deposit_reports';

  protected $primaryKey = 'id';
  protected $allowedFields = [
    'deposit_datetime',
    'clinic_id',
    'clinic_name',
    'depositor',
    'deposit_amount',
    'deposit_start_date',
    'deposit_end_date',
    'signature_electronic',
    'deposit_receipt_scan',
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

  public function getClinicNameById($clinic_id)
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      return $this->db->table($clinic_table)
        ->select('name')
        ->where('id', $clinic_id)
        ->get()
        ->getRow(); // Devuelve un solo resultado
    } catch (\Exception $e) {
      log_message('error', "Error al obtener el nombre de la clínica: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $clinic_table);
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

  public function updateReport($report_id, $column, $value)
  {
    try {
      $builder = $this->db->table('deposit_reports'); // Selecciona la tabla

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

  public function getDailyReportsCountNow($clinicId)
  {
    // Obtener la fecha de hoy
    $today = date('Y-m-d'); // Formato YYYY-MM-DD

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
      $builder = $this->table('deposit_reports'); // Tabla de reportes de depósitos
      $branch_table = $this->db->prefixTable('crm_branch'); // Tabla de relaciones entre usuarios y clínicas

      // Filtros de búsqueda
      if ($search) {
          $builder->groupStart()
              ->like('deposit_reports.clinic_name', $search) // Filtro de búsqueda por nombre de clínica
              ->orLike('deposit_reports.deposit_datetime', $search) // Filtro de búsqueda por fecha de depósito
              ->groupEnd();
      }

      // Filtros adicionales
      if (!empty($clinicId)) {
          $builder->where('deposit_reports.clinic_id', $clinicId); // Filtro por la clínica si se pasa el clinicId
      }

      if (!empty($startDate) && strtotime($startDate) !== null) {
        $builder->where('DATE(deposit_datetime) >=', $startDate);
    }
    if (!empty($endDate) && strtotime($endDate) !== null) {
        $builder->where('DATE(deposit_datetime) <=', $endDate);
    }

      // Filtrar solo los reportes que pertenecen a las clínicas asociadas a este usuario
      // Aquí filtramos los id_clinic de la tabla crm_branch
      $builder->whereIn('deposit_reports.clinic_id', function($query) use ($user_id) {
          $query->select('id_clinic')
              ->from('crm_branch')
              ->where('id_user', $user_id); // Solo los que el usuario puede ver
      });

      // Ordenar por la columna especificada y por la fecha del reporte de forma descendente (más reciente primero)
      $builder->orderBy($columnName, $columnSortOrder)  // Orden principal
          ->orderBy('deposit_reports.deposit_datetime', 'DESC');  // Asegura que los reportes más recientes se muestren primero

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
    $builder = $this->table('deposit_reports'); // Asegúrate de que esta es la tabla correcta
    $branch_table = $this->db->prefixTable('branch'); // Tabla que relaciona clínicas con usuarios

    // Realizamos el INNER JOIN entre la tabla de reportes y la tabla de clínicas
    $builder->join($branch_table, "$branch_table.id_clinic = crm_deposit_reports.clinic_id");

    // Filtros de búsqueda
    if ($search) {
      $builder->groupStart()
        ->orLike('clinic_name', $search)
        ->orLike('deposit_datetime', $search)
        ->groupEnd();
    }

    // Filtros adicionales
    if (!empty($clinicId)) {
      $builder->where('deposit_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $builder->where('DATE(deposit_datetime) >=', $startDate);
    }

    if (!empty($endDate)) {
      $builder->where('DATE(deposit_datetime) <=', $endDate);
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
  public function getMonthData($clinicId, $startDate, $endDate, $id_user)
  {
      $branch_table = $this->db->prefixTable('branch'); 
      $deposit_table = $this->db->prefixTable('deposit_reports'); 
  
      // Obtener las clínicas relacionadas al usuario
      $clinicQuery = $this->db->table($branch_table)
          ->select('id_clinic')
          ->where('id_user', $id_user)
          ->get();
  
      $clinics = array_column($clinicQuery->getResultArray(), 'id_clinic');
  
      // Si el usuario no tiene clínicas asignadas, no hay datos que devolver
      if (empty($clinics)) {
          return [
              'monthlyDeposit' => [],
              'labels' => []
          ];
      }
  
      // Si no se proporcionan fechas, obtenemos los datos de los últimos dos meses
      if (empty($startDate) && empty($endDate)) {
          $endDate = date('Y-m-d');
          $startDate = date('Y-m-d', strtotime('-2 months', strtotime($endDate)));
      }
  
      // Construcción de la consulta principal
      $query = $this->db->table($deposit_table)
          ->select('MONTH(deposit_datetime) as month, YEAR(deposit_datetime) as year, SUM(deposit_amount) as total_deposit')
          ->whereIn('clinic_id', $clinics); // Filtrar por las clínicas del usuario
  
      // Aplicación de filtros dinámicos para fechas
      if (!empty($startDate)) {
          // Aplicamos DATE() en la condición de fecha
          $query->where('DATE(deposit_datetime) >=', $startDate);
      }
      if (!empty($endDate)) {
          // Aplicamos DATE() en la condición de fecha
          $query->where('DATE(deposit_datetime) <=', $endDate);
      }
  
      if (!empty($clinicId)) {
          $query->where('clinic_id', $clinicId);
      }
  
      // Agrupar por mes y año
      $query->groupBy('YEAR(deposit_datetime), MONTH(deposit_datetime)');
      $query->orderBy('YEAR(deposit_datetime)', 'ASC');
      $query->orderBy('MONTH(deposit_datetime)', 'ASC');
  
      // Ejecución de la consulta
      $monthlyDepositQuery = $query->get();
      $monthlyDeposit = $monthlyDepositQuery->getResultArray();
  
      // Procesar los resultados para formatear el nombre del mes
      $labels = [];
      $depositAmounts = [];
      foreach ($monthlyDeposit as $row) {
          $monthName = date('F', strtotime($row['year'] . '-' . $row['month'] . '-01')); // Obtiene el nombre del mes
          $labels[] = $monthName . ' ' . $row['year']; // Etiqueta con el nombre del mes y año
          $depositAmounts[] = $row['total_deposit']; // Total de depósitos
      }
  
      return [
          'monthlyDeposit' => $depositAmounts,
          'labels' => $labels
      ];
  }
  
  





  /**
   * Obtener datos de ingresos.
   * @param int $clinicId
   * @return array
   */
  public function getIncomeData1($clinicId, $startDate, $endDate, $id_user)
  {
      $branch_table = $this->db->prefixTable('branch'); 
      $deposit_table = $this->db->prefixTable('deposit_reports'); 
  
      // Obtener las clínicas relacionadas al usuario
      $clinicQuery = $this->db->table($branch_table)
          ->select('id_clinic')
          ->where('id_user', $id_user)
          ->get();
  
      $clinics = array_column($clinicQuery->getResultArray(), 'id_clinic');
  
      // Si el usuario no tiene clínicas asignadas, no hay datos que devolver
      if (empty($clinics)) {
          return [
              'dailyDeposit' => [],
              'labels' => []
          ];
      }
  
      // Construcción de la consulta principal
      $query = $this->db->table($deposit_table)
          ->select('DATE(deposit_datetime) as date, SUM(deposit_amount) as total_deposit')
          ->whereIn('clinic_id', $clinics); // Filtrar por las clínicas del usuario
  
      // Aplicación de filtros dinámicos para fechas
      if (empty($clinicId) && empty($startDate) && empty($endDate)) {
          $endDate = date('Y-m-d');
          $startDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate)));
      }
  
      if (!empty($startDate)) {
          // Aplicamos DATE() en la condición de fecha
          $query->where('DATE(deposit_datetime) >=', $startDate);
      }
      if (!empty($endDate)) {
          // Aplicamos DATE() en la condición de fecha
          $query->where('DATE(deposit_datetime) <=', $endDate);
      }
  
      if (!empty($clinicId)) {
          $query->where('clinic_id', $clinicId);
      }
  
      // Agrupar y ordenar resultados
      $query->groupBy('DATE(deposit_datetime)');
      $query->orderBy('deposit_datetime', 'ASC');
  
      // Ejecución de la consulta
      $dailyDepositQuery = $query->get();
      $dailyDeposit = $dailyDepositQuery->getResultArray();
  
      return [
          'dailyDeposit' => array_column($dailyDeposit, 'total_deposit'),
          'labels' => array_column($dailyDeposit, 'date')
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

  public function getPlatformsData1($clinicId, $startDate, $endDate, $id_user)
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
    $internetSum = $platformsData['followup_patients_total'] +
      $platformsData['referral_google'] +
      $platformsData['referral_mail'] +
      $platformsData['referral_facebook'] +
      $platformsData['referral_instagram'] +
      $platformsData['referral_youtube'] +
      $platformsData['referral_tiktok'] +
      $platformsData['referral_radio'] +
      $platformsData['referral_newspaper'];

    $walkingSum = $platformsData['referral_walkby'];
    $referredSum = $platformsData['referral_events'] + $platformsData['referral_referred'];

    return [
      'internetSum' => $internetSum,
      'walkingSum' => $walkingSum,
      'referredSum' => $referredSum
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
      'IFNULL(SUM(insured_patients), 0) as insured_patients',
      'IFNULL(SUM(uninsured_patients), 0) as uninsured_patients'
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
      'SUM(deposit_amount) as deposit_amount_total',
      'COUNT(*) AS total_deposits'
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
    $query->join($branch_table, "$branch_table.id_clinic = crm_deposit_reports.clinic_id");

    // Aplica filtros dinámicos
    if (!empty($clinicId)) {
      $query->where('deposit_reports.clinic_id', $clinicId);
    }

    if (!empty($startDate)) {
      $query->where('DATE(deposit_datetime) >=', $startDate);
    }

    if (!empty($endDate)) {
      $query->where('DATE(deposit_datetime) <=', $endDate);
    }

    // Filtrar solo por las clínicas asociadas a este usuario
    $query->where("$branch_table.id_user", $user_id);

    // Ejecuta la consulta
    $insuranceData = $query->get()->getRowArray();

    // Manejo de resultados vacíos
    if ($insuranceData === null) {
      $insuranceData = [
        'deposit_amount' => 0,
        'total_deposits' => 0,
      ];
    } else {
      // Reemplaza valores null por 0
      $insuranceData = array_map(fn($value) => $value === null ? 0 : $value, $insuranceData);
    }

    // Define las etiquetas
    $labels = [
      'deposit_amount'
    ];

    // Retorna los resultados
    return [
      'data' => array_values($insuranceData),
      'labels' => $labels
    ];
  }
}
