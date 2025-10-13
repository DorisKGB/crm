<?php

namespace App\Models;
use Exception;
use App\Models\Crud_model;

class Appointment_model extends Crud_model
{
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'patient_id',
        'provider_id',
        'appointment_date',
        'appointment_time',
        'comment',
        'vsee_link',
        'meeting_id',
        'status',
        'duration_minutes',
        'price',
        'reference_file',
        'created_at',
        'updated_at',
        'token',
        'deleted'
    ];

    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * Obtener citas con información de paciente y médico
     */
    public function get_all_with_details()
    {
        $alias = $this->table;

        return $this->db->table($alias)
            ->select("{$alias}.*, p.full_name AS patient_name, CONCAT(u.first_name, ' ', u.last_name) AS provider_name")
            ->join("patients p", "p.id = {$alias}.patient_id", "left")
            ->join("users u", "u.id = {$alias}.provider_id", "left")
            ->where("{$alias}.deleted", 0)
            ->orderBy("{$alias}.appointment_date", "DESC")
            ->orderBy("{$alias}.appointment_time", "DESC")
            ->get()
            ->getResult();
    }


    /**
     * Verifica si existe una cita para el médico en fecha y hora específicas
     */
    public function exists_conflict_1($provider_id, $date, $time, $duration_minutes = 30)
    {
        try {
            // Calcular hora de fin de la nueva cita
            $end_time = date('H:i:s', strtotime($time . ' +' . $duration_minutes . ' minutes'));

            // Usar query builder correctamente
            $builder = $this->db->table($this->table);

            $result = $builder->where('provider_id', $provider_id)
                ->where('appointment_date', $date)
                ->groupStart() // Equivale a abrir paréntesis
                ->groupStart()
                ->where('appointment_time >=', $time)
                ->where('appointment_time <', $end_time)
                ->groupEnd()
                ->orGroupStart()
                ->where("TIME(DATE_ADD(CONCAT(appointment_date, ' ', appointment_time), INTERVAL $duration_minutes MINUTE)) >", $time)
                ->where('appointment_time <=', $time)
                ->groupEnd()
                ->groupEnd() // Equivale a cerrar paréntesis
                ->get()
                ->getFirstRow();

            return $result;
        } catch (Exception $e) {
            log_message('error', 'exists_conflict_1 error: ' . $e->getMessage());
            return null;
        }
    }

    // Versión más simple y confiable
    /*public function exists_conflict($provider_id, $date, $time, $duration_minutes = 30)
    {
        // Calcular rango de tiempo
        $start_timestamp = strtotime($time);
        $end_timestamp = $start_timestamp + ($duration_minutes * 60);
        $end_time = date('H:i:s', $end_timestamp);

        // Obtener todas las citas del proveedor en esa fecha
        $existing_appointments = $this->where('provider_id', $provider_id)
            ->where('appointment_date', $date)
            ->findAll();

        // Verificar conflictos manualmente
        foreach ($existing_appointments as $appointment) {
            $existing_start = strtotime($appointment['appointment_time']);
            $existing_end = $existing_start + ($duration_minutes * 60); // Asumiendo misma duración

            // Verificar solapamiento
            if ($start_timestamp < $existing_end && $end_timestamp > $existing_start) {
                return $appointment; // Hay conflicto
            }
        }

        return null; // No hay conflicto
    }*/

    public function exists_conflict($provider_id, $date, $time, $duration_minutes = 30, $exclude_id = null)
    {
        try {
            // Calcular rango de tiempo
            $start_timestamp = strtotime($time);
            $end_timestamp = $start_timestamp + ($duration_minutes * 60);

            // Usar directamente el Query Builder del modelo
            $builder = $this->db->table($this->table);
            
            $builder->where('provider_id', $provider_id)
                   ->where('appointment_date', $date)
                   ->where('deleted', 0);

            // Excluir cita específica si se proporciona
            if ($exclude_id) {
                $builder->where('id !=', $exclude_id);
            }

            $existing_appointments = $builder->get()->getResultArray();

            // Verificar conflictos manualmente
            foreach ($existing_appointments as $appointment) {
                $existing_start = strtotime($appointment['appointment_time']);
                $existing_duration = $appointment['duration_minutes'] ?? 30;
                $existing_end = $existing_start + ($existing_duration * 60);

                // Verificar solapamiento
                if ($start_timestamp < $existing_end && $end_timestamp > $existing_start) {
                    return $appointment; // Hay conflicto
                }
            }

            return null; // No hay conflicto
        } catch (\Exception $e) {
            log_message('error', 'exists_conflict error: ' . $e->getMessage());
            return null;
        }
    }

    // Versión usando SQL raw (más directo)
    public function exists_conflict_raw($provider_id, $date, $time, $duration_minutes = 30)
    {
        $end_time = date('H:i:s', strtotime($time . ' +' . $duration_minutes . ' minutes'));

        $sql = "SELECT * FROM {$this->table} 
            WHERE provider_id = ? 
            AND appointment_date = ? 
            AND (
                (appointment_time >= ? AND appointment_time < ?) OR
                (TIME(DATE_ADD(CONCAT(appointment_date, ' ', appointment_time), INTERVAL ? MINUTE)) > ? AND appointment_time <= ?)
            )
            LIMIT 1";

        $query = $this->db->query($sql, [
            $provider_id,
            $date,
            $time,
            $end_time,
            $duration_minutes,
            $time,
            $time
        ]);

        return $query->getFirstRow();
    }

    public function get_with_patient($id)
    {
        $appointments_table = $this->table; // ya contiene el prefijo dinámicamente
        $patients_table = $this->db->prefixTable('patients'); // se convierte a crm_patients

        return $this->db->table($appointments_table)
            ->select("{$appointments_table}.*, p.full_name AS patient_name, p.email, p.phone")
            ->join("{$patients_table} p", "p.id = {$appointments_table}.patient_id", "left")
            ->where("{$appointments_table}.id", $id)
            ->get()
            ->getRow();
    }

      /**
     * Buscar citas para asignar a servicios
     */
    public function search_appointments($term = '')
    {
        try {
            $appointmentsTable = $this->db->prefixTable('appointments');
            $patientsTable = $this->db->prefixTable('patients');

            $builder = $this->db->table($appointmentsTable . ' a');
            $builder->select('a.id, a.appointment_date, a.appointment_time, p.full_name as patient_name');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->where('a.deleted', 0);

            if ($term !== '') {
                $builder->groupStart()
                    ->like('p.full_name', $term)
                    ->orLike('a.appointment_date', $term)
                    ->groupEnd();
            }

            $results = $builder->limit(20)->get()->getResult();

            $data = [];
            foreach ($results as $appointment) {
                $data[] = [
                    'id' => $appointment->id,
                    'text' => $appointment->patient_name . ' - ' . date('m/d/Y', strtotime($appointment->appointment_date)) . ' ' . date('h:i A', strtotime($appointment->appointment_time))
                ];
            }

            return $data;
        } catch (\Throwable $e) {
            log_message('error', 'search_appointments error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function search_for_services(string $term = ''): array
    {
        $appointmentsTable = $this->db->prefixTable('appointments');
        $patientsTable     = $this->db->prefixTable('patients');

        $b = $this->db->table($appointmentsTable . ' a');
        $b->select('a.id, a.appointment_date, a.appointment_time, p.full_name AS patient_name');
        $b->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
        $b->where('a.deleted', 0);

        if ($term !== '') {
            $b->groupStart()
                ->like('p.full_name', $term);

            if (ctype_digit($term)) {
                $b->orWhere('a.id', (int)$term);
            }

            // Fecha US
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $term)) {
                $iso = date('Y-m-d', strtotime($term));
                $b->orWhere('a.appointment_date', $iso);
            }

            // Hora tipo "02:15 PM"
            if (preg_match('/\d{1,2}:\d{2}\s*(AM|PM)/i', $term)) {
                $t24 = date('H:i:s', strtotime($term));
                $b->orWhere('a.appointment_time', $t24);
            }
            $b->groupEnd();
        }

        $b->orderBy('a.appointment_date', 'DESC')
        ->orderBy('a.appointment_time', 'DESC')
        ->limit(20);

        $rows = $b->get()->getResult();
        $out  = [];

        foreach ($rows as $r) {
            $out[] = [
                'id'           => (int)$r->id,
                'patient_name' => (string)($r->patient_name ?? ''),
                'date'         => $r->appointment_date ? date('m/d/Y', strtotime($r->appointment_date)) : '',
                'time'         => $r->appointment_time ? date('h:i A', strtotime($r->appointment_time)) : ''
            ];
        }
        return $out;
    }


    public function find_for_services(int $id): ?array
    {
        if (!$id) return null;
        $row = $this->get_with_patient($id);
        if (!$row) return null;

        return [
            'id'           => (int)$row->id,
            'patient_name' => (string)($row->patient_name ?? ''),
            'date'         => $row->appointment_date ? date('m/d/Y', strtotime($row->appointment_date)) : '',
            'time'         => $row->appointment_time ? date('h:i A', strtotime($row->appointment_time)) : ''
        ];
    }

    public function create_quick_for_services(string $patient_name, string $dateUS, string $timeUS, string $reason = '', string $phone = '', string $email = ''): array
    {
        // Validaciones básicas
        if ($patient_name === '' || $dateUS === '' || $timeUS === '') {
            throw new \InvalidArgumentException('Nombre del paciente, fecha y hora son obligatorios');
        }

        // Parsear US -> ISO
        $dt = \DateTime::createFromFormat('m/d/Y h:i A', $dateUS . ' ' . $timeUS);
        if (!$dt) {
            throw new \InvalidArgumentException('Formato de fecha/hora inválido');
        }
        $appointment_date = $dt->format('Y-m-d');
        $appointment_time = $dt->format('H:i:s');

        // Buscar/crear paciente usando el Patient_model
        $patient_model = new \App\Models\Patient_model();
        $existing = $patient_model->get_one_where(['full_name' => $patient_name, 'deleted' => 0]);
        
        if ($existing && $existing->id) {
            $patient_id = (int)$existing->id;
        } else {
            // Crear paciente usando ci_save
            $patientData = [
                'full_name'  => $patient_name,
                'email'      => $email,
                'phone'      => $phone,  
                'reason'     => json_encode([]),
                'extra_data' => json_encode(new \stdClass()),
                'source'     => 'quick_appointment',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            $patient_id = $patient_model->ci_save($patientData);
            if (!$patient_id) {
                $error = $this->db->error();
                log_message('error', 'Error creating patient: ' . print_r($error, true));
                throw new \Exception('Error al crear el paciente: ' . ($error['message'] ?? 'Unknown error'));
            }
        }

        // Insertar cita usando ci_save del modelo actual
        $appointmentData = [
            'provider_id'      => null,
            'patient_id'       => $patient_id,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'duration_minutes' => 30,
            'comment'          => $reason,
            'status'           => 'pendiente',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        $newId = $this->ci_save($appointmentData);
        if (!$newId) {
            $error = $this->db->error();
            log_message('error', 'Error creating appointment: ' . print_r($error, true));
            throw new \Exception('Error al crear la cita: ' . ($error['message'] ?? 'Unknown error'));
        }

        return [
            'id'           => $newId,
            'patient_name' => $patient_name,
            'date'         => date('m/d/Y', strtotime($appointment_date)),
            'time'         => date('h:i A', strtotime($appointment_time))
        ];
    }


}
