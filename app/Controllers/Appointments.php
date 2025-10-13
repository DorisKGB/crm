<?php

namespace App\Controllers;

use App\Models\Appointment_model;
use App\Models\Patient_model;
use App\Models\Users_model;
use App\Libraries\VseeClient;
use App\Models\Appointment_services_model;
use Exception;

class Appointments extends Security_Controller
{
    protected $appointment_model;
    protected $patient_model;
    protected $user_model;
    protected $VseeUsers_model;
    protected $appointment_services_model;

    public function __construct()
    {
        parent::__construct();
        $this->appointment_model = new Appointment_model();
        $this->patient_model = new Patient_model();
        $this->user_model = new Users_model();
        $this->VseeUsers_model = new \App\Models\VseeUsers_model();
        $this->appointment_services_model = new Appointment_services_model();
    }

    public function index()
    {
        return $this->template->rander("appointments/index");
    }

    public function list()
    {
        $appointments = $this->appointment_model->get_all_with_details();
        return $this->response->setJSON(["data" => $appointments]);
    }

    public function calendar()
    {
        $events = $this->appointment_model->get_calendar_events();
        return $this->response->setJSON($events);
    }

    public function calendar_events()
    {
        try {
            $appointments = $this->appointment_model->get_all_with_details();

            $data = [];

            foreach ($appointments as $event) {
                $start = "{$event->appointment_date} {$event->appointment_time}";
                $duration = $event->duration_minutes ?? 30;
                $end = date("Y-m-d H:i:s", strtotime($start) + ($duration * 60));
                $title = $event->patient_name . " con " . $event->provider_name;

                $data[] = [
                    'title' => $title,
                    'start' => $start,
                    'end'   => $end
                ];
            }

            return $this->response->setJSON($data);
        } catch (\Throwable $e) {
            log_message('error', 'calendar_events error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }


    public function get()
    {
        try {
            $id = $this->request->getGet('id');
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
            }

            $cita = $this->appointment_model->find($id);
            if (!$cita) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Cita no encontrada']);
            }

            return $this->response->setJSON($cita);
        } catch (\Throwable $e) {
            log_message('error', 'appointments::get error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error interno']);
        }
    }


    public function update()
    {
        try {
            $id = $this->request->getPost('id');
            if (!$id || !$this->appointment_model->get_one($id)) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Cita no encontrada']);
            }

            $data = [
                'provider_id' => $this->request->getPost('provider_id'),
                'patient_id' => $this->request->getPost('patient_id'),
                'appointment_date' => $this->request->getPost('date'),
                'appointment_time' => $this->request->getPost('time'),
                'duration_minutes' => $this->request->getPost('duration_minutes') ?? 30,
                'vsee_link' => $this->request->getPost('vsee_link') ?? '',
                'comment' => $this->request->getPost('comment')
            ];

            $this->appointment_model->update($id, $data);

            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $e) {
            log_message('error', 'appointments::update error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error interno']);
        }
    }

    public function delete()
    {
        try {
            $id = $this->request->getPost("id");
            if (!$id || !$this->appointment_model->get_one($id)) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Cita no encontrada']);
            }

            $this->appointment_model->delete($id);
            return $this->response->setJSON(["success" => true]);
        } catch (\Throwable $e) {
            log_message('error', 'appointments::delete error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error interno']);
        }
    }

    public function modal_editar_cita()
    {
        try {
            $id = $this->request->getGet('id');
            $cita = $this->appointment_model->get_one($id);

            if (!$cita) {
                return $this->response->setStatusCode(404)->setBody("Cita no encontrada");
            }

            $patients = $this->patient_model->get_all()->getResult();
            $providers = $this->user_model->get_all()->getResult();

            return $this->template->view('appointments/modals/modal_editar_cita', [
                'cita' => $cita,
                'patients' => $patients,
                'providers' => $providers,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_editar_cita error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor");
        }
    }

    public function modal_eliminar_cita()
    {
        try {
            $id = $this->request->getGet('id');

            // Filtrar desde el método que sí incluye los JOINs
            $citas = $this->appointment_model->get_all_with_details();
            $cita = null;

            foreach ($citas as $c) {
                if ((int)$c->id === (int)$id) {
                    $cita = $c;
                    break;
                }
            }

            if (!$cita) {
                return $this->response->setStatusCode(404)->setBody("Cita no encontrada.");
            }

            return $this->template->view("appointments/modals/modal_eliminar_cita", [
                'cita' => $cita
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_eliminar_cita error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor.");
        }
    }

    public function list_data()
    {
        try {
            $appointments = $this->appointment_model->get_all_with_details();
            $data = [];

            foreach ($appointments as $a) {
                $start = "{$a->appointment_date} {$a->appointment_time}";
                $duration = $a->duration_minutes ?? 30;
                $end = date("Y-m-d H:i:s", strtotime($start) + ($duration * 60));
                $usDate = date('m/d/Y', strtotime($a->appointment_date));

                // Determinar el color del estado
                $statusClass = '';
                $statusText = $a->status ?? 'pendiente';
                switch (strtolower($statusText)) {
                    case 'confirmada':
                        $statusClass = 'btn-rubymed btn-rubymed-success btn-sm';
                        break;
                    case 'cancelada':
                        $statusClass = 'btn-rubymed btn-rubymed-danger btn-sm';
                        break;
                    case 'finalizada':
                        $statusClass = 'btn-rubymed btn-rubymed-secondary btn-sm';
                        break;
                    default:
                        $statusClass = 'btn-rubymed btn-rubymed-warning btn-sm';
                        $statusText = 'pendiente';
                }

                $data[] = [
                    'id' => $a->id,
                    'date' => $usDate,
                    'time' => $a->appointment_time,
                    'patient_name' => $a->patient_name,
                    'provider_name' => $a->provider_name,
                    'status' => "<span class='{$statusClass}'>" . ucfirst($statusText) . "</span>",
                    'comment' => $a->comment
                        ? '<button class="btn-button btn-button-outline-primary" onclick="showComment(' . $a->id . ')"><i class="fas fa-comment-alt"></i></button>'
                        : '',
                    'video_link' => $a->token
                        ? '<button class="btn-button btn-button-purple" onclick="showTeleconsultaLink(' . $a->id . ')" title="Ver link de teleconsulta">
                        <i class="fas fa-video me-1"></i>Link
                    </button>'
                        : '<span class="text-muted small">Sin link</span>',
                    'actions' =>
                    '<div class="d-flex gap-1 flex-wrap">
                    <button class="btn-button btn-button-danger" onclick="openDetail(' . $a->patient_id . ')" title="Ver historial médico">
                        <i class="fas fa-heartbeat"></i>
                    </button>
                    <button class="btn-ghost btn-ghost-info" onclick="openEditAppointment(' . $a->id . ')" title="Editar cita">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-ghost btn-ghost-warning" onclick="openStatusModal(' . $a->id . ')" title="Cambiar estado">
                        <i class="fas fa-flag"></i>
                    </button>
                    <button class="btn-ghost btn-ghost-success" onclick="openRescheduleModal(' . $a->id . ')" title="Reprogramar">
                        <i class="fas fa-calendar-alt"></i>
                    </button>
                    <button class="btn-ghost btn-ghost-danger" onclick="openDeleteAppointment(' . $a->id . ')" title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>'
                ];
            }

            return $this->response->setJSON(['data' => $data]);
        } catch (\Throwable $e) {
            log_message('error', 'list_data error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }


    public function get_appointment_link()
    {
        try {
            $id = $this->request->getGet('id');

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'ID requerido'
                ]);
            }

            // Obtener información completa de la cita
            $appointments = $this->appointment_model->get_all_with_details();
            $appointment = null;

            foreach ($appointments as $a) {
                if ((int)$a->id === (int)$id) {
                    $appointment = $a;
                    break;
                }
            }

            if (!$appointment) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Cita no encontrada'
                ]);
            }

            // Verificar que tenga token
            if (!$appointment->token) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Esta cita no tiene un token de teleconsulta'
                ]);
            }

            // Formatear fecha para mostrar
            $formattedDate = date('d/m/Y', strtotime($appointment->appointment_date));
            $formattedTime = date('h:i A', strtotime($appointment->appointment_time));

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'id' => $appointment->id,
                    'token' => $appointment->token,
                    'patient_name' => $appointment->patient_name,
                    'provider_name' => $appointment->provider_name,
                    'appointment_date' => $formattedDate,
                    'appointment_time' => $formattedTime,
                    'comment' => $appointment->comment
                ]
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_appointment_link error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }
    




    public function modal_agendar_cita()
    {
        try {
            $date = $this->request->getGet('date');

            $patients = $this->patient_model->get_all()->getResult();
            $providers = $this->user_model->get_all()->getResult();

            return $this->template->view('appointments/modals/modal_agendar_cita', [
                'date' => $date,
                'patients' => $patients,
                'providers' => $providers,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'list_data error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }

    public function search_providers()
    {
        try {
            $term = trim($this->request->getGet('term') ?? '');

            $vseeUsersModel = new \App\Models\VseeUsers_model();
            $builder = $vseeUsersModel->db->table('vsee_users vu');
            $builder->select('vu.id, CONCAT(u.first_name, " ", u.last_name) AS name');
            $builder->join('users u', 'u.id = vu.user_id');
            $builder->where('vu.action', 'provider');
            $builder->where('vu.state', 1);
            $builder->where('vu.deleted', 0);
            $builder->where("vu.vsee_id IS NOT NULL");

            if ($term !== '') {
                $builder->groupStart()
                    ->like('u.first_name', $term)
                    ->orLike('u.last_name', $term)
                    ->groupEnd();
            }

            $result = $builder->limit(20)->get()->getResult();

            return $this->response->setJSON($result);
        } catch (\Throwable $e) {
            log_message('error', 'search_providers error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al buscar providers.'
            ]);
        }
    }

    public function check_availability()
    {
        try {
            $provider = $this->request->getGet('provider');
            $dateRaw  = $this->request->getGet('date'); // viene en MM/DD/YYYY
            $time     = $this->request->getGet('time'); // viene en HH:MM
            $duration = (int) ($this->request->getGet('duration_minutes') ?? 30);
            $excludeAppointment = $this->request->getGet('exclude_appointment'); // Para reprogramación

            log_message('debug', "Parameters: provider={$provider}, date={$dateRaw}, time={$time}, exclude={$excludeAppointment}");

            if (!$provider || !$dateRaw || !$time) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => 'Faltan parámetros']);
            }

            // 1) Reformateo a YYYY-MM-DD
            $date = date('Y-m-d', strtotime($dateRaw));

            // 2) Calcular hora de fin
            $endTime = date('H:i:s', strtotime("$time +{$duration} minutes"));

            // 3) Uso el Builder del modelo
            $builder = $this->appointment_model->builder();

            $query = $builder
                ->where('provider_id', $provider)
                ->where('appointment_date', $date)
                ->where('deleted', 0); // Agregar filtro por eliminados

            // Excluir cita específica si se proporciona (para reprogramación)
            if ($excludeAppointment) {
                $query->where('id !=', $excludeAppointment);
            }

            $overlap = $query
                ->groupStart() // abre paréntesis
                // caso A: la nueva cita empieza dentro de una existente
                ->groupStart()
                ->where('appointment_time >=', $time)
                ->where('appointment_time <',  $endTime)
                ->groupEnd()
                ->orGroupStart()
                // caso B: la nueva cita cubre parte de una existente
                ->where("TIME(DATE_ADD(CONCAT(appointment_date,' ',appointment_time), INTERVAL {$duration} MINUTE)) >", $time)
                ->where('appointment_time <=', $time)
                ->groupEnd()
                ->groupEnd() // cierra paréntesis
                ->limit(1)
                ->get()
                ->getFirstRow();

            if ($overlap) {
                return $this->response->setJSON([
                    'available' => false,
                    'message'   => 'El Profesional ya tiene una cita en ese rango de tiempo.'
                ]);
            }

            return $this->response->setJSON(['available' => true]);
        } catch (\Throwable $e) {
            log_message('error', 'check_availability error: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Error del servidor']);
        }
    }




    public function modal_comentario()
    {
        $id = $this->request->getGet('id');
        $info = $this->appointment_model->get_with_patient($id);

        if (!$info) {
            return $this->response->setStatusCode(404)->setBody("No encontrado");
        }

        return view('appointments/modals/modal_comentario', [
            'info' => $info
        ]);
    }


    public function save()
    {
        try {
            $provider_id = $this->request->getPost('provider_id');
            $patient_id  = $this->request->getPost('patient_id');
            $date        = $this->request->getPost('date');
            $time        = $this->request->getPost('time');
            $comment     = $this->request->getPost('comment');
            $price       = $this->request->getPost('price');
            $duration    = (int) ($this->request->getPost('duration_minutes') ?? 30);
            $vsee_link   = $this->request->getPost('vsee_link') ?? '';

            // 1) Parsear fecha y hora juntos con createFromFormat
            $dt = \DateTime::createFromFormat('m/d/Y H:i', "$date $time");
            if (! $dt) {
                // formato inválido: lanzar excepción o devolver error
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => 'Fecha u hora en formato incorrecto']);
            }

            // 2) Obtener fecha y hora ya en formato ISO
            $appointment_date = $dt->format('Y-m-d');    // "2025-07-24"
            $appointment_time = $dt->format('H:i:s');    // "14:00:00"
            $start_timestamp  = $dt->getTimestamp();     // timestamp del inicio

            // 3) Calcular fin sumando duración
            $end_timestamp = $start_timestamp + ($duration * 60);
            $end_date_time = date('Y-m-d H:i:s', $end_timestamp);

            // 2. Procesar archivo adjunto (si existe)
            $reference_path = null;
            $file = $this->request->getFile('reference_image');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $uploadPath = WRITEPATH . 'uploads/appointments/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $file->move($uploadPath, $newName);
                $reference_path = 'uploads/appointments/' . $newName;
            }
            $appointment_token = $this->generateUniqueAppointmentToken($this->appointment_model);
            log_message('error', 'Token Generado : ' . $appointment_token);

            $vseeUsersModel = new \App\Models\VseeUsers_model();
             $prov = $vseeUsersModel->get_one($provider_id);
            $provider_id = $prov->user_id;
            // 3. Insertar cita
            $appointment = $this->appointment_model->insert([
                'provider_id'       => $provider_id,
                'patient_id'        => $patient_id,
                'appointment_date'  => $appointment_date,
                'appointment_time'  => $appointment_time,
                'duration_minutes'  => $duration,
                'price'             => $price,
                'comment'           => $comment,
                'vsee_link'         => $vsee_link,
                'reference_file'    => $reference_path,
                'status'            => 'PENDIENTE',
                'token'             => $appointment_token // ✅ nuevo campo
            ]);

            try {
  
                $providers = $vseeUsersModel->get_one_where(['user_id' => $provider_id, 'action' => 'provider']);

                $patiensInfo = $this->patient_model->get_one($patient_id);
                $vsee = new VseeClient();
                //CREAR PACIENTE COMO INVITADO

                    $userData = [
                        "first_name"   => $patiensInfo->full_name,
                        "last_name"    => '',
                        "dob"          => $patiensInfo->dob ?? '2002-05-20',
                        "type"         => '600',
                        "code"         => "1000002331",
                        "email"        => $patiensInfo->email ?? ''
                    ];
                     log_message('error', 'pACIENTE: ' . print_r($userData, true));
                    $res = $vsee->createUserSSO($userData);
                    $ID = $res['data']['id'];
                    $data = [
                        'vsee_id'       => $res['data']['id'],
                        'vsee_username' => $res['data']['vsee']['id'],
                        'vsee_token'    => $res['data']['vsee']['token'],
                    ];
                    $this->patient_model->ci_save($data, $patient_id);



                //CREAR SALA DE ESPERA
                $data = [
                    "provider_id"   => $providers->vsee_id,
                    "type"    => 1,
                    "room_code" => $providers->vsee_room,
                    "member_id" => $ID
                ];
                
                  log_message('error', 'Sala de Espera: ' . print_r($data, true));
                $dataSalaEspera = $vsee->createIntake($data);
                $visit_id = $dataSalaEspera['data']['id'];


                //CREAR VISTA
                $data = [
                    "provider_id" => $providers->vsee_id,
                    "intake_id"   => $visit_id,
                    "room_code"   => $providers->vsee_room
                ];
                log_message('error', 'CONFEREENCE4: ' . print_r($data, true));
                $conference = $vsee->add_walkin($data);
                log_message('error', 'Cenference: ' . print_r($conference, true));
                
                if (!isset($conference['data']['meeting']['meeting_id'])) {

                    throw new \Exception('Error creando conferencia: respuesta incompleta');
                }

                $conference_id = $conference['data']['meeting']['meeting_id'];
                $data = [
                    'meeting_id' => $conference_id,
                ];
                log_message('error', 'appointment: ' . $appointment);
                $this->appointment_model->ci_save($data, $appointment);
            } catch (Exception $e) {
                log_message('error', 'Erro al crear conferencia: ' . $e->getMessage());
            }

            return $this->response->setJSON(['success' => true , 'token' => $appointment_token]);
        } catch (\Throwable $e) {
            log_message('error', 'save error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }

    // Función para generar token único de 50 caracteres
    function generateUniqueAppointmentToken(): string
    {
        $db = \Config\Database::connect();
        $builder = $db->table($db->getPrefix() . 'appointments');

        for ($i = 0; $i < 10; $i++) {
            $token = bin2hex(random_bytes(25)); // 50 caracteres

            $exists = $builder
                ->select('id')
                ->where('token IS NOT NULL')
                ->where('token', $token)
                ->countAllResults();

            if ($exists == 0) {
                return $token;
            }
        }

        throw new \Exception("No se pudo generar un token único después de 10 intentos.");
    }

    public function provider_info()
    {
        try {
            $id = $this->request->getGet('id');

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
            }

            $builder = $this->user_model->db->table('vsee_users vu');
            $builder->select('
            vu.id AS vsee_id,
            u.id AS user_id,
            CONCAT(u.first_name, " ", u.last_name) AS name,
            u.email,
            u.phone,
            u.image,
            vu.action,
            vu.vsee_username,
            vu.clinic_id
        ');
            $builder->join('users u', 'u.id = vu.user_id');
            $builder->where('vu.id', $id);
            $builder->where('vu.deleted', 0);

            $info = $builder->get()->getRow();

            if (!$info) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Profesional no encontrado']);
            }

            return $this->response->setJSON($info);
        } catch (\Throwable $e) {
            log_message('error', 'provider_info error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error del servidor']);
        }
    }
    
    function teleconsultas()
    {
        $user_id = $this->login_user->id;
        $is_admin = $this->login_user->is_admin;

        // Si no es admin, verificar que sea provider
        if (!$is_admin) {
            $vseeUsersModel = new \App\Models\VseeUsers_model();
            $assignment = $vseeUsersModel->get_one_where(['user_id' => $user_id, 'deleted' => 0]);
            if (!$assignment || $assignment->action !== 'provider') {
                show_404();
            }
        }

        return $this->template->rander('appointments/teleconsultas');
    }

    /**
     * API para obtener teleconsultas del provider/admin
     */
    /**
     * API para obtener teleconsultas del provider/admin
     */
  function get_provider_consultations()
{
    try {
        $user_id = $this->login_user->id;
        $is_admin = $this->login_user->is_admin;

        $db = $this->appointment_model->db;
        $prefix = $db->getPrefix();

        // Construcción segura con prefijo manual y sin alias en db->table()
        $appointmentsTable = $prefix . 'appointments';
        $patientsTable     = $prefix . 'patients';
        $usersTable        = $prefix . 'users';
        $vseeUsersTable    = $prefix . 'vsee_users';

        $builder = $db->table($appointmentsTable);
        $builder->select("
            {$appointmentsTable}.id,
            {$appointmentsTable}.token,
            {$appointmentsTable}.appointment_date,
            {$appointmentsTable}.appointment_time,
            {$appointmentsTable}.duration_minutes,
            {$appointmentsTable}.comment,
            {$appointmentsTable}.status,
            {$appointmentsTable}.patient_id,
            {$appointmentsTable}.provider_id,
            {$appointmentsTable}.meeting_id,
            {$patientsTable}.full_name as patient_name,
            {$patientsTable}.email as patient_email,
            {$patientsTable}.phone as patient_phone,
            {$usersTable}.first_name as provider_first_name,
            {$usersTable}.last_name as provider_last_name,
            {$vseeUsersTable}.vsee_username as provider_vsee_username,
            {$vseeUsersTable}.vsee_token as provider_vsee_token
        ");

        $builder->join($patientsTable, "{$patientsTable}.id = {$appointmentsTable}.patient_id", 'left');
        $builder->join($usersTable, "{$usersTable}.id = {$appointmentsTable}.provider_id", 'left');
        $builder->join($vseeUsersTable, "{$vseeUsersTable}.user_id = {$appointmentsTable}.provider_id AND {$vseeUsersTable}.action = 'provider' AND {$vseeUsersTable}.deleted = 0", 'left');

        // Teleconsultas con token
        $builder->where("{$appointmentsTable}.token IS NOT NULL");
        $builder->where("{$appointmentsTable}.token !=", '');

        if (!$is_admin) {
            $assignment = $this->VseeUsers_model->get_one_where(['user_id' => $user_id, 'deleted' => 0]);
            if (!$assignment || $assignment->action !== 'provider') {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
            }
            $builder->where("{$appointmentsTable}.provider_id", $user_id);
        }

        $builder->where("{$appointmentsTable}.deleted", 0);
        $builder->orderBy("{$appointmentsTable}.appointment_date", 'ASC');
        $builder->orderBy("{$appointmentsTable}.appointment_time", 'ASC');

        $query = $builder->get();

        if (!$query) {
            throw new \Exception('Error al ejecutar la consulta SQL.');
        }

        $appointments = $query->getResult();

        $consultations = [];
        $now = new \DateTime();

        foreach ($appointments as $appointment) {
            if (!$appointment->patient_name) {
                continue;
            }

            $appointmentDateTime = new \DateTime($appointment->appointment_date . ' ' . $appointment->appointment_time);
            $endDateTime = clone $appointmentDateTime;
            $endDateTime->add(new \DateInterval('PT' . ($appointment->duration_minutes ?? 30) . 'M'));

            $compareDateTime = clone $appointmentDateTime;
            $compareDateTime->sub(new \DateInterval('PT15M'));

            $status = $appointment->status ?? 'pendiente';

            $consultations[] = [
                'id' => $appointment->id,
                'token' => $appointment->token,
                'patient_id' => $appointment->patient_id,
                'provider_id' => $appointment->provider_id,
                'patient_name' => $appointment->patient_name,
                'patient_email' => $appointment->patient_email ?? '',
                'patient_phone' => $appointment->patient_phone ?? '',
                'provider_name' => trim(($appointment->provider_first_name ?? '') . ' ' . ($appointment->provider_last_name ?? '')),
                'appointment_datetime' => $appointmentDateTime->format('c'),
                'formatted_date' => $appointmentDateTime->format('m/d/Y'),
                'formatted_time' => $appointmentDateTime->format('h:i A'),
                'duration_minutes' => $appointment->duration_minutes ?? 30,
                'comment' => $appointment->comment ?? '',
                'status' => $status,
                'clinic_name' => 'Clínica Principal',
                'vsee_username' => $appointment->provider_vsee_username ?? '',
                'vsee_token' => $appointment->provider_vsee_token ?? '',
                'meeting_id' => $appointment->meeting_id ?? '',
                'is_admin_view' => $is_admin
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'consultations' => $consultations,
            'is_admin' => $is_admin
        ]);
    } catch (\Throwable $e) {
        log_message('error', 'get_provider_consultations error: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'error' => 'Error del servidor: ' . $e->getMessage()
        ]);
    }
}



    

    /**
     * Vista de detalle de consulta específica - USANDO PREFIJOS DEL MODELO
     */
    function consultation_detail($token = null)
    {
     

        try {
            $user_id = $this->login_user->id;
            $is_admin = $this->login_user->is_admin;

            // Usar el sistema de prefijos del modelo
            $db = $this->appointment_model->db;
            $appointmentsTable = $db->prefixTable('appointments');
            $patientsTable = $db->prefixTable('patients');
            $usersTable = $db->prefixTable('users');
            $vseeUsersTable = $db->prefixTable('vsee_users');

            // Construir query para obtener datos de la consulta
            $builder = $db->table($appointmentsTable . ' a');
            $builder->select('
            a.*,
            p.full_name as patient_name,
            p.email as patient_email,
            p.phone as patient_phone,
            p.dob as patient_dob,
            p.vsee_username as patient_vsee_username,
            p.vsee_token as patient_vsee_token,
            u.first_name as provider_first_name,
            u.last_name as provider_last_name,
            vu.vsee_username as provider_vsee_username,
            vu.vsee_token as provider_vsee_token,
            vu.vsee_id as provider_vsee_id
        ');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' u', 'u.id = a.provider_id', 'left');
            $builder->join($vseeUsersTable . ' vu', 'vu.user_id = a.provider_id AND vu.action = "provider" AND vu.deleted = 0', 'left');
            $builder->where('a.token', $token);

            // Si no es admin, verificar que sea su cita
             /*if (!$is_admin) {
                $assignment = $this->VseeUsers_model->get_one_where(['user_id' => $user_id, 'deleted' => 0]);
               if (!$assignment || $assignment->action !== 'provider') {
                    show_404();
                }
                $builder->where('a.provider_id', $user_id);
            }*/

            $builder->where('a.deleted', 0);
            $consultation = $builder->get()->getRow();

            /*if (!$consultation) {
                show_404();
            }*/

            // Obtener historial médico del paciente
            $historyBuilder = $db->table($appointmentsTable . ' a');
            $historyBuilder->select('
            a.appointment_date,
            a.appointment_time,
            a.comment,
            a.status,
            u.first_name,
            u.last_name
        ');
            $historyBuilder->join($usersTable . ' u', 'u.id = a.provider_id', 'left');
            $historyBuilder->where('a.patient_id', $consultation->patient_id);
            $historyBuilder->where('a.id !=', $consultation->id);
            $historyBuilder->where('a.deleted', 0);
            $historyBuilder->orderBy('a.appointment_date', 'DESC');
            $historyBuilder->orderBy('a.appointment_time', 'DESC');
            $historyBuilder->limit(5);

            $history = $historyBuilder->get()->getResult();

            // Determinar las credenciales a usar para la videollamada
            $vsee_credentials = [
                'username' => $consultation->provider_vsee_username ?? '',
                'token' => $consultation->provider_vsee_token ?? '',
                'conference_id' => $consultation->meeting_id ?? ''
            ];

            return $this->template->rander('appointments/consultation_detail', [
                'consultation' => $consultation,
                'history' => $history,
                'vsee_credentials' => $vsee_credentials,
                'is_admin' => $is_admin,
                'current_user' => $this->login_user
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'consultation_detail error: ' . $e->getMessage());
        }
    }

     public function modal_cambiar_estado()
    {
        try {
            $id = $this->request->getGet('id');
            
            // Obtener información completa de la cita
            $appointments = $this->appointment_model->get_all_with_details();
            $cita = null;

            foreach ($appointments as $c) {
                if ((int)$c->id === (int)$id) {
                    $cita = $c;
                    break;
                }
            }

            if (!$cita) {
                return $this->response->setStatusCode(404)->setBody("Cita no encontrada.");
            }

            return $this->template->view("appointments/modals/modal_cambiar_estado", [
                'cita' => $cita
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_cambiar_estado error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor.");
        }
    }

    /**
     * Modal para reprogramar cita
     */
    public function modal_reprogramar()
    {
        try {
            $id = $this->request->getGet('id');
            
            // Obtener información completa de la cita
            $appointments = $this->appointment_model->get_all_with_details();
            $cita = null;

            foreach ($appointments as $c) {
                if ((int)$c->id === (int)$id) {
                    $cita = $c;
                    break;
                }
            }

            if (!$cita) {
                return $this->response->setStatusCode(404)->setBody("Cita no encontrada.");
            }

            return $this->template->view("appointments/modals/modal_reprogramar", [
                'cita' => $cita
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_reprogramar error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor.");
        }
    }

     /**
     * Actualizar estado de la cita
     */
    public function update_status()
    {
        try {
            $appointmentId = $this->request->getPost('appointment_id');
            $newStatus = $this->request->getPost('status');
            $cancelReason = $this->request->getPost('cancel_reason');

            if (!$appointmentId || !$newStatus) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
            }

            // Verificar que la cita existe
            $appointment = $this->appointment_model->get_one($appointmentId);
            if (!$appointment || !$appointment->id) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Cita no encontrada'
                ]);
            }

            // Preparar datos para actualizar
            $updateData = [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Si es cancelación, agregar motivo al comentario
            if ($newStatus === 'cancelada' && $cancelReason) {
                $currentComment = $appointment->comment ? $appointment->comment . "\n\n" : "";
                $updateData['comment'] = $currentComment . "CANCELADA: " . $cancelReason;
            }

            // Actualizar la cita
            $result = $this->appointment_model->ci_save($updateData, $appointmentId);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al actualizar el estado'
                ]);
            }

        } catch (\Throwable $e) {
            log_message('error', 'update_status error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Reprogramar cita
     */
    public function reschedule()
    {
        try {
            $appointmentId = $this->request->getPost('appointment_id');
            $newDate = $this->request->getPost('new_date');
            $newTime = $this->request->getPost('new_time');
            $newDuration = $this->request->getPost('new_duration');
            $rescheduleReason = $this->request->getPost('reschedule_reason');
            $otherReason = $this->request->getPost('other_reason');

            if (!$appointmentId || !$newDate || !$newTime) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
            }

            // Verificar que la cita existe
            $appointment = $this->appointment_model->get_one($appointmentId);
            if (!$appointment || !$appointment->id) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Cita no encontrada'
                ]);
            }

            // Convertir fecha de MM/DD/YYYY a YYYY-MM-DD
            $formattedDate = date('Y-m-d', strtotime($newDate));
            
            // Verificar disponibilidad del proveedor (excluyendo la cita actual)
            $conflict = $this->appointment_model->exists_conflict(
                $appointment->provider_id, 
                $formattedDate, 
                $newTime, 
                (int)$newDuration,
                $appointmentId  // Excluir la cita actual
            );

            // Si hay conflicto
            if ($conflict) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El Profesional ya tiene una cita en ese horario'
                ]);
            }

            // Guardar información anterior para el historial
            $oldInfo = "Fecha anterior: " . date('d/m/Y', strtotime($appointment->appointment_date)) . 
                      " a las " . date('h:i A', strtotime($appointment->appointment_time));
            
            // Preparar motivo completo
            $fullReason = $rescheduleReason;
            if ($rescheduleReason === 'otro' && $otherReason) {
                $fullReason = $otherReason;
            }

            // Preparar datos para actualizar
            $updateData = [
                'appointment_date' => $formattedDate,
                'appointment_time' => $newTime,
                'duration_minutes' => (int)$newDuration,
                'status' => 'pendiente', // Resetear a pendiente al reprogramar
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Agregar información de reprogramación al comentario
            $currentComment = $appointment->comment ? $appointment->comment . "\n\n" : "";
            $updateData['comment'] = $currentComment . 
                                   "REPROGRAMADA: " . $fullReason . " | " . $oldInfo . 
                                   " | Nueva fecha: " . date('d/m/Y', strtotime($formattedDate)) . 
                                   " a las " . date('h:i A', strtotime($newTime));

            // Actualizar la cita usando el método correcto
            $result = $this->appointment_model->ci_save($updateData, $appointmentId);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cita reprogramada correctamente'
                ]);
            } else {
                throw new \Exception('Error al guardar los cambios en la base de datos');
            }

        } catch (\Throwable $e) {
            log_message('error', 'reschedule error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function search()
    {
        try {
            $q = trim($this->request->getGet('q') ?? '');
            $data = $this->appointment_model->search_for_services($q);
            return $this->response->setJSON($data);
        } catch (\Throwable $e) {
            log_message('error', 'appointments::search error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }

    public function find()
    {
        try {
            $id = (int)$this->request->getGet('id');
            if (!$id) return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
            $row = $this->appointment_model->find_for_services($id);
            if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'Cita no encontrada']);
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            log_message('error', 'appointments::find error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error']);
        }
    }

    // POST /appointments/create_quick
    public function create_quick()
    {
        try {
            $patient_name = trim($this->request->getPost('patient_name') ?? '');
            $patient_phone = trim($this->request->getPost('patient_phone') ?? '');
            $patient_email = trim($this->request->getPost('patient_email') ?? '');
            $dateUS       = trim($this->request->getPost('appointment_date') ?? '');
            $timeUS       = trim($this->request->getPost('appointment_time') ?? '');
            $reason       = trim($this->request->getPost('reason') ?? '');

            // Validaciones adicionales
            if (empty($patient_name)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false, 
                    'message' => 'El nombre del paciente es obligatorio'
                ]);
            }

            if (empty($dateUS) || empty($timeUS)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false, 
                    'message' => 'La fecha y hora son obligatorias'
                ]);
            }

            // Log de entrada para debug
            log_message('debug', 'create_quick input: ' . json_encode([
                'patient_name' => $patient_name,
                'dateUS' => $dateUS,
                'timeUS' => $timeUS,
                'reason' => $reason
            ]));

        $appt = $this->appointment_model->create_quick_for_services($patient_name, $dateUS, $timeUS, $reason, $patient_phone, $patient_email);

            // Log de resultado exitoso
            log_message('debug', 'create_quick success: ' . json_encode($appt));

            return $this->response->setJSON(['success' => true, 'appointment' => $appt]);

        } catch (\InvalidArgumentException $e) {
            log_message('error', 'create_quick validation error: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Log del error completo con stack trace
            log_message('error', 'create_quick error: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // En desarrollo, devolver el error real; en producción, mensaje genérico
            $isDevelopment = ENVIRONMENT === 'development';
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false, 
                'message' => $isDevelopment ? $e->getMessage() : 'Error del servidor'
            ]);
        }
    }

    public function create_service()
    {
        try {
            $appointment_id = $this->request->getPost('appointment_id');
            
            if (!$appointment_id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Debe seleccionar una cita'
                ]);
            }

            // Validar que la cita exista y tenga paciente
            $appointment = $this->appointment_model->get_with_patient($appointment_id);
            if (!$appointment || !$appointment->patient_name) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'La cita seleccionada no tiene un paciente asociado'
                ]);
            }

            $data = [
                'appointment_id' => $appointment_id,
                'service_type' => $this->request->getPost('service_type'),
                'patient_state' => $this->request->getPost('patient_state'),
                'patient_city' => $this->request->getPost('patient_city'),
                'patient_address' => $this->request->getPost('patient_address'),
                'patient_zipcode' => $this->request->getPost('patient_zipcode'),
                'service_notes' => $this->request->getPost('service_notes'),
                'service_cost' => $this->request->getPost('service_cost'),
                'priority' => $this->request->getPost('priority'),
                'assigned_provider_id' => $this->request->getPost('assigned_provider_id'),
                'scheduled_date' => $this->request->getPost('scheduled_date'),
                'scheduled_time' => $this->request->getPost('scheduled_time'),
                'status' => 'pendiente',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->appointment_services_model->ci_save($data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio creado correctamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al crear el servicio'
                ]);
            }

        } catch (\Throwable $e) {
            log_message('error', 'create_service error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

}
