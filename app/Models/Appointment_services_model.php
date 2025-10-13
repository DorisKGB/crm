<?php

namespace App\Models;

use Exception;
use App\Models\Crud_model;

class Appointment_services_model extends Crud_model
{
    protected $table = 'appointment_services';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'appointment_id',
        'service_type',
        'patient_state',
        'patient_city',
        'patient_address',
        'patient_zipcode',
        'priority',
        'service_notes',
        'service_cost',
        'status',
        'assigned_provider_id',
        'scheduled_date',
        'scheduled_time',
        'completion_date',
        'completion_notes',
        'created_at',
        'updated_at',
        'created_by',
        'deleted'
    ];

    public function __construct()
    {
        parent::__construct($this->table);
    }
    /**
     * Obtener servicios con información completa (JOIN con otras tablas)
     */
    public function get_services_with_details($filters = [])
    {
        try {
            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.*,
                p.full_name as patient_name,
                p.phone as patient_phone,
                p.email as patient_email,
                p.dob as patient_dob,
                p.reason as patient_history,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name,
                a.appointment_date,
                a.appointment_time,
                a.comment as appointment_comment
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' u', 'u.id = a.provider_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = s.assigned_provider_id', 'left');
            $builder->where('s.deleted', 0);

            // Aplicar filtros
            if (isset($filters['status']) && $filters['status']) {
                $builder->where('s.status', $filters['status']);
            }

            if (isset($filters['patient_state']) && $filters['patient_state']) {
                $builder->where('s.patient_state', $filters['patient_state']);
            }

            if (isset($filters['scheduled_date']) && $filters['scheduled_date']) {
                $builder->where('s.scheduled_date', $filters['scheduled_date']);
            }

            if (isset($filters['assigned_provider_id']) && $filters['assigned_provider_id']) {
                $builder->where('s.assigned_provider_id', $filters['assigned_provider_id']);
            }

            if (isset($filters['unscheduled']) && $filters['unscheduled']) {
                $builder->where('(s.scheduled_date IS NULL OR s.scheduled_date = "")');
            }

            $builder->orderBy('s.scheduled_date', 'ASC');
            $builder->orderBy('s.scheduled_time', 'ASC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_services_with_details error: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Obtener estadísticas generales de servicios
     */
    public function get_services_statistics()
    {
        try {
            $builder = $this->db->table($this->table);

            // Total de servicios
            $total = $builder->selectCount('id', 'total')
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->total;

            // Servicios por estado
            $statusBuilder = $this->db->table($this->table);
            $statusStats = $statusBuilder->select('status, COUNT(*) as count')
                ->where('deleted', 0)
                ->groupBy('status')
                ->get()
                ->getResult();

            $stats = [
                'total' => $total,
                'pendiente' => 0,
                'en_progreso' => 0,
                'completado' => 0,
                'no_encontrado' => 0,
                'cancelado' => 0
            ];

            foreach ($statusStats as $stat) {
                $stats[$stat->status] = $stat->count;
            }

            // Servicios de hoy
            $todayBuilder = $this->db->table($this->table);
            $stats['today'] = $todayBuilder->selectCount('id', 'count')
                ->where('scheduled_date', date('Y-m-d'))
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->count;

            // Servicios atrasados
            $overdueBuilder = $this->db->table($this->table);
            $stats['overdue'] = $overdueBuilder->selectCount('id', 'count')
                ->where('scheduled_date <', date('Y-m-d'))
                ->where('status', 'pendiente')
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->count;

            return $stats;
        } catch (\Throwable $e) {
            log_message('error', 'get_services_statistics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas por estado geográfico
     */
    public function get_statistics_by_state()
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->select("
                patient_state,
                COUNT(*) as total_services,
                SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN status = 'en_progreso' THEN 1 ELSE 0 END) as en_progreso,
                SUM(CASE WHEN status = 'completado' THEN 1 ELSE 0 END) as completados,
                SUM(CASE WHEN status = 'no_encontrado' THEN 1 ELSE 0 END) as no_encontrados,
                SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
            ");
            $builder->where('deleted', 0);
            $builder->groupBy('patient_state');
            $builder->orderBy('total_services', 'DESC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_statistics_by_state error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener servicios para agenda diaria
     */
    public function get_daily_schedule($date = null, $provider_id = null)
    {
        try {
            $date = $date ?: date('Y-m-d');

            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.*,
                p.full_name as patient_name,
                p.phone as patient_phone,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = s.assigned_provider_id', 'left');
            $builder->where('s.deleted', 0);
            $builder->where('s.scheduled_date', $date);

            if ($provider_id) {
                $builder->where('s.assigned_provider_id', $provider_id);
            }

            $builder->orderBy('s.scheduled_time', 'ASC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_daily_schedule error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener servicios para el mapa (con ubicaciones)
     */
    public function get_services_for_map($date = null, $filters = [])
    {
        try {
            $date = $date ?: date('Y-m-d');

            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.id,
                s.service_type,
                s.status,
                s.patient_address,
                s.patient_city,
                s.patient_state,
                s.patient_zipcode,
                s.scheduled_time,
                p.full_name as patient_name,
                p.phone as patient_phone,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = s.assigned_provider_id', 'left');
            $builder->where('s.deleted', 0);
            $builder->where('s.scheduled_date', $date);
            $builder->where('s.patient_address IS NOT NULL');
            $builder->where('s.patient_address !=', '');

            // Aplicar filtros adicionales
            if (isset($filters['status']) && $filters['status']) {
                $builder->where('s.status', $filters['status']);
            }

            if (isset($filters['assigned_provider_id']) && $filters['assigned_provider_id']) {
                $builder->where('s.assigned_provider_id', $filters['assigned_provider_id']);
            }

            $builder->orderBy('s.scheduled_time', 'ASC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_services_for_map error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Optimizar horarios de servicios por fecha
     */
    public function optimize_schedule($date, $provider_id = null)
    {
        try {
            // Obtener servicios para optimizar
            $builder = $this->db->table($this->table);
            $builder->select('id');
            $builder->where('scheduled_date', $date);
            $builder->where('deleted', 0);
            $builder->whereIn('status', ['pendiente', 'en_progreso']);

            if ($provider_id) {
                $builder->where('assigned_provider_id', $provider_id);
            }

            // Ordenar por código postal y ciudad para optimizar ruta
            $builder->orderBy('patient_zipcode', 'ASC');
            $builder->orderBy('patient_city', 'ASC');
            $builder->orderBy('patient_address', 'ASC');

            $services = $builder->get()->getResult();

            // Asignar horarios optimizados
            $startTime = '08:00:00';
            $timeIncrement = 45; // 45 minutos entre servicios
            $currentTime = $startTime;
            $updatedCount = 0;

            foreach ($services as $service) {
                $updateData = [
                    'scheduled_time' => $currentTime,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->ci_save($updateData, $service->id);
                $updatedCount++;

                // Calcular siguiente hora
                $timestamp = strtotime($currentTime) + ($timeIncrement * 60);
                $currentTime = date('H:i:s', $timestamp);

                // Si pasa de las 6 PM, reiniciar al día siguiente
                if ($currentTime > '18:00:00') {
                    $currentTime = $startTime;
                }
            }

            return $updatedCount;
        } catch (\Throwable $e) {
            log_message('error', 'optimize_schedule error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener servicios pendientes de un proveedor
     */
    public function get_provider_pending_services($provider_id, $limit = 10)
    {
        try {
            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.*,
                p.full_name as patient_name,
                p.phone as patient_phone,
                a.appointment_date
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->where('s.deleted', 0);
            $builder->where('s.assigned_provider_id', $provider_id);
            $builder->whereIn('s.status', ['pendiente', 'en_progreso']);
            $builder->orderBy('s.scheduled_date', 'ASC');
            $builder->orderBy('s.scheduled_time', 'ASC');
            $builder->limit($limit);

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_provider_pending_services error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener servicios por rango de fechas
     */
    public function get_services_by_date_range($start_date, $end_date, $filters = [])
    {
        try {
            $services = $this->get_services_with_details($filters);

            // Filtrar por rango de fechas
            $filtered = array_filter($services, function ($service) use ($start_date, $end_date) {
                if (!$service->scheduled_date) return false;
                return $service->scheduled_date >= $start_date && $service->scheduled_date <= $end_date;
            });

            return array_values($filtered);
        } catch (\Throwable $e) {
            log_message('error', 'get_services_by_date_range error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar estado de múltiples servicios
     */
    public function bulk_update_status($service_ids, $status, $completion_notes = null)
    {
        try {
            if (empty($service_ids) || !is_array($service_ids)) {
                return false;
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($completion_notes) {
                $updateData['completion_notes'] = $completion_notes;
            }

            if ($status === 'completado') {
                $updateData['completion_date'] = date('Y-m-d H:i:s');
            }

            $builder = $this->db->table($this->table);
            $builder->whereIn('id', $service_ids);
            $builder->where('deleted', 0);

            return $builder->update($updateData);
        } catch (\Throwable $e) {
            log_message('error', 'bulk_update_status error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener servicios para calendario (formato FullCalendar)
     */
    public function get_calendar_events($start_date = null, $end_date = null)
    {
        try {
            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.id,
                s.service_type,
                s.status,
                s.scheduled_date,
                s.scheduled_time,
                s.patient_city,
                s.patient_state,
                p.full_name as patient_name,
                CONCAT(s.scheduled_date, ' ', IFNULL(s.scheduled_time, '08:00:00')) as start_datetime
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->where('s.deleted', 0);
            $builder->where('s.scheduled_date IS NOT NULL');

            if ($start_date) {
                $builder->where('s.scheduled_date >=', $start_date);
            }
            if ($end_date) {
                $builder->where('s.scheduled_date <=', $end_date);
            }

            $services = $builder->get()->getResult();

            // Formatear para FullCalendar
            $events = [];
            foreach ($services as $service) {
                $start = $service->start_datetime;
                $end = date('Y-m-d H:i:s', strtotime($start . '+45 minutes'));

                $events[] = [
                    'id' => $service->id,
                    'title' => ($service->patient_name ?: 'Sin paciente') . ' - ' . ($service->service_type ?: 'Servicio'),
                    'start' => $start,
                    'end' => $end,
                    'backgroundColor' => $this->getStatusColor($service->status),
                    'borderColor' => $this->getStatusColor($service->status),
                    'extendedProps' => [
                        'status' => $service->status,
                        'city' => $service->patient_city,
                        'state' => $service->patient_state,
                        'service_type' => $service->service_type
                    ]
                ];
            }

            return $events;
        } catch (\Throwable $e) {
            log_message('error', 'get_calendar_events error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar servicios por texto
     */
    public function search_services($query, $limit = 20)
    {
        try {
            $prefix = $this->db->getPrefix();
            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';

            $builder = $this->db->table($servicesTable . ' s');
            $builder->select("
                s.*,
                p.full_name as patient_name,
                p.phone as patient_phone
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = s.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->where('s.deleted', 0);

            // Búsqueda en múltiples campos
            $builder->groupStart()
                ->like('p.full_name', $query)
                ->orLike('s.service_type', $query)
                ->orLike('s.patient_address', $query)
                ->orLike('s.patient_city', $query)
                ->orLike('s.patient_state', $query)
                ->orLike('s.service_notes', $query)
                ->groupEnd();

            $builder->orderBy('s.scheduled_date', 'DESC');
            $builder->limit($limit);

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'search_services error: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * CAMBIAR - Solo obtener conteos básicos por estado
     */
    public function get_status_counts()
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->select('status, COUNT(*) as count');
            $builder->where('deleted', 0);
            $builder->groupBy('status');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_status_counts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * CAMBIAR - Solo obtener conteo total
     */
    public function get_total_count()
    {
        try {
            return $this->db->table($this->table)
                ->selectCount('id', 'total')
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->total;
        } catch (\Throwable $e) {
            log_message('error', 'get_total_count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * CAMBIAR - Solo obtener conteo por fecha
     */
    public function get_count_by_date($date)
    {
        try {
            return $this->db->table($this->table)
                ->selectCount('id', 'count')
                ->where('scheduled_date', $date)
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->count;
        } catch (\Throwable $e) {
            log_message('error', 'get_count_by_date error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * CAMBIAR - Solo obtener conteo de atrasados
     */
    public function get_overdue_count()
    {
        try {
            return $this->db->table($this->table)
                ->selectCount('id', 'count')
                ->where('scheduled_date <', date('Y-m-d'))
                ->where('status', 'pendiente')
                ->where('deleted', 0)
                ->get()
                ->getRow()
                ->count;
        } catch (\Throwable $e) {
            log_message('error', 'get_overdue_count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Validar disponibilidad de proveedor
     */
    public function check_provider_availability($provider_id, $date, $time, $exclude_service_id = null)
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->where('assigned_provider_id', $provider_id);
            $builder->where('scheduled_date', $date);
            $builder->where('scheduled_time', $time);
            $builder->where('deleted', 0);
            $builder->whereIn('status', ['pendiente', 'en_progreso']);

            if ($exclude_service_id) {
                $builder->where('id !=', $exclude_service_id);
            }

            $existing = $builder->get()->getRow();

            return $existing ? false : true; // false si está ocupado, true si está disponible
        } catch (\Throwable $e) {
            log_message('error', 'check_provider_availability error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Funciones auxiliares privadas
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pendiente' => '#ffc107',
            'en_progreso' => '#17a2b8',
            'completado' => '#28a745',
            'no_encontrado' => '#dc3545',
            'cancelado' => '#6c757d'
        ];
        return $colors[$status] ?? '#f8f9fa';
    }



    public function save_appointment_service($serviceData)
    {
        try {
            // Verificar si ya existe un servicio para esta cita
            $existing = $this->get_one_where(['appointment_id' => $serviceData['appointment_id']]);

            if ($existing && isset($existing->id) && $existing->id > 0) {
                // Actualizar existente
                unset($serviceData['created_at']); // No actualizar created_at
                $serviceData['updated_at'] = date('Y-m-d H:i:s');

                $result = $this->ci_save($serviceData, $existing->id);
                return $result ? $existing->id : false;
            } else {
                // Crear nuevo
                $serviceData['created_at'] = date('Y-m-d H:i:s');
                $result = $this->ci_save($serviceData);
                return $result;
            }
        } catch (\Throwable $e) {
            log_message('error', 'save_appointment_service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del servicio
     */
    public function change_status($id, $status, $completion_notes = null)
    {
        try {
            $data = [
                'status' => $status,
                'completion_notes' => $completion_notes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Si el servicio se completó, guardar fecha de finalización
            if ($status === 'completado') {
                $data['completion_date'] = date('Y-m-d H:i:s');
            }

            return $this->ci_save($data, $id);
        } catch (\Throwable $e) {
            log_message('error', 'change_status error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener servicio por appointment_id
     */
    public function get_by_appointment($appointment_id)
    {
        return $this->get_one_where(['appointment_id' => $appointment_id]);
    }

    /**
     * Obtener todos los servicios activos
     */
    public function get_active_services()
    {
        return $this->get_all_where(['deleted' => 0]);
    }

    /**
     * Obtener servicios agrupados por estado
     */
    public function get_services_by_state()
    {
        try {
            $db = $this->db;
            $prefix = $db->getPrefix();

            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $db->table($servicesTable . ' as');
            $builder->select("
                as.id,
                as.appointment_id,
                as.service_type,
                as.patient_state,
                as.patient_city,
                as.patient_address,
                as.patient_zipcode,
                as.service_notes,
                as.status,
                as.assigned_provider_id,
                as.scheduled_date,
                as.scheduled_time,
                as.completion_date,
                as.completion_notes,
                as.created_at,
                a.appointment_date,
                a.appointment_time,
                p.full_name as patient_name,
                p.phone as patient_phone,
                p.email as patient_email,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = as.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' u', 'u.id = a.provider_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = as.assigned_provider_id', 'left');
            $builder->where('as.deleted', 0);
            $builder->orderBy('as.patient_state', 'ASC');
            $builder->orderBy('as.scheduled_date', 'ASC');

            $services = $builder->get()->getResult();

            // Agrupar por estado
            $servicesByState = [];
            foreach ($services as $service) {
                $state = $service->patient_state ?: 'Sin Estado';
                if (!isset($servicesByState[$state])) {
                    $servicesByState[$state] = [];
                }
                $servicesByState[$state][] = $service;
            }

            return $servicesByState;
        } catch (\Throwable $e) {
            log_message('error', 'get_services_by_state error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener servicios por fecha para la agenda
     */
    public function get_schedule_services($date = null)
    {
        try {
            $db = $this->db;
            $prefix = $db->getPrefix();

            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $db->table($servicesTable . ' as');
            $builder->select("
                as.*,
                a.appointment_date,
                p.full_name as patient_name,
                p.phone as patient_phone,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = as.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = as.assigned_provider_id', 'left');
            $builder->where('as.deleted', 0);

            if ($date) {
                $builder->where('as.scheduled_date', $date);
            } else {
                $builder->where('as.scheduled_date >=', date('Y-m-d'));
            }

            $builder->orderBy('as.scheduled_date', 'ASC');
            $builder->orderBy('as.scheduled_time', 'ASC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_schedule_services error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener servicios para el mapa diario
     */
    public function get_daily_map_services($date)
    {
        try {
            $db = $this->db;
            $prefix = $db->getPrefix();

            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $db->table($servicesTable . ' as');
            $builder->select("
                as.id,
                as.patient_address,
                as.patient_city,
                as.patient_state,
                as.patient_zipcode,
                as.scheduled_time,
                as.status,
                as.service_type,
                p.full_name as patient_name,
                p.phone as patient_phone,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = as.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = as.assigned_provider_id', 'left');
            $builder->where('as.deleted', 0);
            $builder->where('as.scheduled_date', $date);
            $builder->where('as.patient_address IS NOT NULL');
            $builder->where('as.patient_address !=', '');
            $builder->orderBy('as.scheduled_time', 'ASC');

            return $builder->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_daily_map_services error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener servicio con detalles completos para modal
     */
    public function get_service_with_details($id)
    {
        try {
            $db = $this->db;
            $prefix = $db->getPrefix();

            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';
            $usersTable = $prefix . 'users';

            $builder = $db->table($servicesTable . ' as');
            $builder->select("
                as.*,
                a.appointment_date,
                a.appointment_time,
                a.comment as appointment_comment,
                p.full_name as patient_name,
                p.phone as patient_phone,
                p.email as patient_email,
                p.dob as patient_dob,
                p.reason as patient_history,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                CONCAT(au.first_name, ' ', au.last_name) as assigned_provider_name
            ");

            $builder->join($appointmentsTable . ' a', 'a.id = as.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->join($usersTable . ' u', 'u.id = a.provider_id', 'left');
            $builder->join($usersTable . ' au', 'au.id = as.assigned_provider_id', 'left');
            $builder->where('as.id', $id);

            return $builder->get()->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'get_service_with_details error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener servicio con información básica para modal de cambio de estado
     */
    public function get_service_for_status_change($id)
    {
        try {
            $db = $this->db;
            $prefix = $db->getPrefix();

            $servicesTable = $prefix . 'appointment_services';
            $appointmentsTable = $prefix . 'appointments';
            $patientsTable = $prefix . 'patients';

            $builder = $db->table($servicesTable . ' as');
            $builder->select("
                as.*,
                p.full_name as patient_name,
                a.appointment_date
            ");
            $builder->join($appointmentsTable . ' a', 'a.id = as.appointment_id', 'left');
            $builder->join($patientsTable . ' p', 'p.id = a.patient_id', 'left');
            $builder->where('as.id', $id);

            return $builder->get()->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'get_service_for_status_change error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update_service($data, $id)
    {
        try {
            // Agregar timestamp de actualización
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Usar el método heredado ci_save para actualizar
            $result = $this->ci_save($data, $id);

            if ($result) {
                log_message('info', "Servicio {$id} actualizado exitosamente");
                return $result;
            } else {
                log_message('error', "Error al actualizar servicio {$id}");
                return false;
            }
        } catch (\Throwable $e) {
            log_message('error', 'update_service error: ' . $e->getMessage());
            return false;
        }
    }

     /**
     * 1 Método ligero: solo servicios sin fecha
     */
    /*public function get_unscheduled_services()
    {
        // 1) Tablas con prefix correcto
        $svc = $this->table;  // ya incluye prefix
        $app = $this->db->prefixTable('appointments');
        $pat = $this->db->prefixTable('patients');

        // 2) Construir la consulta
        $builder = $this->db->table("$svc s")
            ->select('
                s.id,
                s.appointment_id,
                a.appointment_date,
                p.full_name AS patient_name,
                p.email AS patient_email,
                p.phone AS patient_phone,
                s.patient_address AS patient_address,
                s.priority AS priority,
                s.service_type AS service_type,
            ')
            ->join("$app a", 'a.id = s.appointment_id', 'left')
            ->join("$pat p", 'p.id = a.patient_id',      'left')
            ->where('s.deleted', 0)
            ->where('s.scheduled_date', null)   // genera IS NULL
            ->orderBy('a.appointment_date', 'ASC');

        // 3) Ejecutar con control de errores
        $query = $builder->get();
        if (! $query) {
            // Loguear el error real de la DB
            $dbError = $this->db->error();
            log_message('error', 'get_unscheduled_services SQL failed: ' 
                . ($dbError['message'] ?? 'unknown error'));
            return [];  // devolvemos array vacío en lugar de bool
        }

        return $query->getResult();
    }*/

    /*public function get_unscheduled_services()
    {
        $svc = $this->table;
        $app = $this->db->prefixTable('appointments');
        $pat = $this->db->prefixTable('patients');

        $builder = $this->db->table("$svc s")
            ->select('
                s.id,
                s.appointment_id,
                a.appointment_date,
                p.full_name AS patient_name,
                p.email AS patient_email,
                p.phone AS patient_phone,
                s.patient_address AS patient_address,
                s.priority AS priority,
                s.service_type AS service_type
            ') // ← SIN COMA FINAL
            ->join("$app a", 'a.id = s.appointment_id', 'left')
            ->join("$pat p", 'p.id = a.patient_id', 'left')
            ->where('s.deleted', 0)
            ->where('(s.scheduled_date IS NULL OR s.scheduled_date = "")')
            ->where('p.id IS NOT NULL') // ← ASEGURAR QUE TENGA PACIENTE
            ->orderBy('a.appointment_date', 'ASC');

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }*/

    public function get_unscheduled_services()
    {
        $svc = $this->table;
        $app = $this->db->prefixTable('appointments');
        $pat = $this->db->prefixTable('patients');

        log_message('debug', "=== DEBUG UNSCHEDULED SERVICES ===");
        log_message('debug', "Tabla servicios: $svc");
        log_message('debug', "Tabla appointments: $app");
        log_message('debug', "Tabla patients: $pat");

        // Paso 1: Verificar servicios sin fecha
        $test1 = $this->db->table($svc)
            ->select('id, appointment_id, scheduled_date, scheduled_time')
            ->where('deleted', 0)
            ->where('scheduled_date IS NULL')
            ->get()->getResult();
        
        log_message('debug', "Servicios con scheduled_date NULL: " . count($test1));
        log_message('debug', "IDs encontrados: " . json_encode(array_column($test1, 'id')));

        // Paso 2: Verificar JOIN con appointments
        $test2 = $this->db->table("$svc s")
            ->select('s.id, s.appointment_id, a.id as appointment_exists')
            ->join("$app a", 'a.id = s.appointment_id', 'left')
            ->where('s.deleted', 0)
            ->where('s.scheduled_date IS NULL')
            ->get()->getResult();
        
        log_message('debug', "Después de JOIN con appointments: " . count($test2));

        // Paso 3: Verificar JOIN con patients
        $builder = $this->db->table("$svc s")
            ->select('
                s.id,
                s.appointment_id,
                a.appointment_date,
                a.patient_id,
                p.id as patient_exists,
                p.full_name AS patient_name,
                p.email AS patient_email,
                p.phone AS patient_phone,
                s.patient_address AS patient_address,
                s.priority AS priority,
                s.service_type AS service_type
            ')
            ->join("$app a", 'a.id = s.appointment_id', 'left')
            ->join("$pat p", 'p.id = a.patient_id', 'left')
            ->where('s.deleted', 0)
            ->where('s.scheduled_date IS NULL');

        $test3 = $builder->get()->getResult();
        log_message('debug', "Después de JOIN con patients: " . count($test3));
        log_message('debug', "Datos completos: " . json_encode($test3));

        // Aplicar filtro final
        $builder = $this->db->table("$svc s")
            ->select('
                s.id,
                s.appointment_id,
                a.appointment_date,
                p.full_name AS patient_name,
                p.email AS patient_email,
                p.phone AS patient_phone,
                s.patient_address AS patient_address,
                s.priority AS priority,
                s.service_type AS service_type
            ')
            ->join("$app a", 'a.id = s.appointment_id', 'left')
            ->join("$pat p", 'p.id = a.patient_id', 'left')
            ->where('s.deleted', 0)
            ->where('s.scheduled_date IS NULL')
            ->where('p.id IS NOT NULL')
            ->orderBy('a.appointment_date', 'ASC');

        $final = $builder->get()->getResult();
        log_message('debug', "Resultado final: " . count($final));
        
        return $final;
    }

    public function get_services_without_schedule()
    {
        return $this->get_unscheduled_services();
    }

    public function check_time_slot_availability($date, $time, $exclude_service_id = null)
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->where('scheduled_date', $date);
            $builder->where('scheduled_time', $time);
            $builder->where('deleted', 0);
            $builder->whereIn('status', ['pendiente', 'en_progreso']);

            if ($exclude_service_id) {
                $builder->where('id !=', $exclude_service_id);
            }

            $existing = $builder->get()->getRow();

            return !$existing; // true si NO hay servicios, false si YA hay un servicio
        } catch (\Throwable $e) {
            log_message('error', 'check_time_slot_availability error: ' . $e->getMessage());
            return false;
        }
    }
}
