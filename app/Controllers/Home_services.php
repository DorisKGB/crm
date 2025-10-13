<?php

namespace App\Controllers;

use App\Models\Appointment_services_model;
use App\Models\Appointment_model;
use App\Models\Patient_model;
use App\Models\Users_model;
use App\Models\Usa_states_model;
use Exception;

class Home_services extends Security_Controller
{
    protected $appointment_services_model;
    protected $appointment_model;
    protected $patient_model;
    protected $users_model;
    protected $usa_states_model;

    public function __construct()
    {
        parent::__construct();
        $this->appointment_services_model = new Appointment_services_model();
        $this->appointment_model = new Appointment_model();
        $this->patient_model = new Patient_model();
        $this->users_model = new Users_model();
        $this->usa_states_model = new Usa_states_model();
    }

    /**
     * Vista principal de servicios domiciliarios
     */
    public function index()
    {
        return $this->template->rander("home_services/index");
    }

    /**
     * Vista de agenda de servicios
     */
    public function schedule()
    {
        return $this->template->rander("home_services/schedule");
    }

    /**
     * Vista de mapa diario
     */
    public function daily_map()
    {
        return $this->template->rander("home_services/daily_map");
    }

    /**
     * Obtener servicios agrupados por estado
     */
    public function get_services_by_state()
    {
        try {
            // OBTENER DATOS DEL MODELO (solo consulta)
            $services = $this->appointment_services_model->get_services_by_state();

            // LÓGICA DE NEGOCIO EN EL CONTROLADOR
            $servicesByState = [];
            foreach ($services as $service) {
                $state = $service->patient_state ?: 'Sin Estado';
                if (!isset($servicesByState[$state])) {
                    $servicesByState[$state] = [];
                }
                $servicesByState[$state][] = $service;
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $servicesByState
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_services_by_state controller error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }

    public function get_dashboard_stats()
    {
        try {
            // OBTENER DATOS DEL MODELO (solo consultas)
            $totalCount = $this->appointment_services_model->get_total_count();
            $statusCounts = $this->appointment_services_model->get_status_counts();
            $todayCount = $this->appointment_services_model->get_count_by_date(date('Y-m-d'));
            $overdueCount = $this->appointment_services_model->get_overdue_count();

            // LÓGICA DE NEGOCIO EN EL CONTROLADOR
            $stats = [
                'total' => $totalCount,
                'today' => $todayCount,
                'overdue' => $overdueCount,
                'pendiente' => 0,
                'en_progreso' => 0,
                'completado' => 0,
                'no_encontrado' => 0,
                'cancelado' => 0
            ];

            // Procesar conteos por estado
            foreach ($statusCounts as $statusCount) {
                $stats[$statusCount->status] = $statusCount->count;
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_dashboard_stats error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }

    public function get_executive_summary()
    {
        try {
            $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
            $endDate = $this->request->getGet('end_date') ?: date('Y-m-t');

            // OBTENER DATOS DEL MODELO (solo consulta)
            $builder = $this->appointment_services_model->db->table($this->appointment_services_model->table);
            $builder->select('
                COUNT(*) as total_services,
                COUNT(CASE WHEN status = "completado" THEN 1 END) as completed_services,
                COUNT(CASE WHEN status = "cancelado" THEN 1 END) as cancelled_services,
                COUNT(CASE WHEN status = "no_encontrado" THEN 1 END) as not_found_services,
                COUNT(DISTINCT patient_state) as states_covered,
                COUNT(DISTINCT assigned_provider_id) as providers_used,
                AVG(CASE WHEN completion_date IS NOT NULL AND scheduled_date IS NOT NULL 
                    THEN DATEDIFF(completion_date, scheduled_date) END) as avg_completion_days
            ');
            $builder->where('deleted', 0);
            $builder->where('scheduled_date >=', $startDate);
            $builder->where('scheduled_date <=', $endDate);

            $summary = $builder->get()->getRow();

            // LÓGICA DE NEGOCIO EN EL CONTROLADOR
            $completionRate = $summary->total_services > 0
                ? round(($summary->completed_services / $summary->total_services) * 100, 2)
                : 0;

            $cancellationRate = $summary->total_services > 0
                ? round((($summary->cancelled_services + $summary->not_found_services) / $summary->total_services) * 100, 2)
                : 0;

            $result = [
                'period' => ['start' => $startDate, 'end' => $endDate],
                'total_services' => (int)$summary->total_services,
                'completed_services' => (int)$summary->completed_services,
                'cancelled_services' => (int)$summary->cancelled_services,
                'not_found_services' => (int)$summary->not_found_services,
                'states_covered' => (int)$summary->states_covered,
                'providers_used' => (int)$summary->providers_used,
                'completion_rate' => $completionRate,
                'cancellation_rate' => $cancellationRate,
                'avg_completion_days' => round($summary->avg_completion_days ?? 0, 1)
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_executive_summary error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }

    public function optimize_service_route()
    {
        try {
            $date = $this->request->getPost('date') ?: date('Y-m-d');
            $providerId = $this->request->getPost('provider_id');
            $timeIncrement = (int)($this->request->getPost('time_increment') ?? 45);

            // OBTENER DATOS DEL MODELO (solo consulta)
            $builder = $this->appointment_services_model->db->table($this->appointment_services_model->table);
            $builder->select('id');
            $builder->where('scheduled_date', $date);
            $builder->where('deleted', 0);
            $builder->whereIn('status', ['pendiente', 'en_progreso']);

            if ($providerId) {
                $builder->where('assigned_provider_id', $providerId);
            }

            // Ordenar por código postal y ciudad para optimizar ruta
            $builder->orderBy('patient_zipcode', 'ASC');
            $builder->orderBy('patient_city', 'ASC');
            $builder->orderBy('patient_address', 'ASC');

            $services = $builder->get()->getResult();

            // LÓGICA DE NEGOCIO EN EL CONTROLADOR
            $startTime = '08:00:00';
            $currentTime = $startTime;
            $updatedCount = 0;

            foreach ($services as $service) {
                $updateData = [
                    'scheduled_time' => $currentTime,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->appointment_services_model->ci_save($updateData, $service->id);
                $updatedCount++;

                // Calcular siguiente hora
                $timestamp = strtotime($currentTime) + ($timeIncrement * 60);
                $currentTime = date('H:i:s', $timestamp);

                // Si pasa de las 6 PM, reiniciar al día siguiente
                if ($currentTime > '18:00:00') {
                    $currentTime = $startTime;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Ruta optimizada para {$updatedCount} servicios",
                'optimized_count' => $updatedCount
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'optimize_service_route error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }
    /**
     * Obtener servicios por fecha para la agenda
     */
    public function get_schedule_services()
    {
        try {
            $date = $this->request->getGet('date');
            $services = $this->appointment_services_model->get_schedule_services($date);

            return $this->response->setJSON([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_schedule_services controller error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Obtener servicios para el mapa diario
     */
    public function get_daily_map_services()
    {
        try {
            $date = $this->request->getGet('date') ?: date('Y-m-d');
            $services = $this->appointment_services_model->get_daily_map_services($date);

            return $this->response->setJSON([
                'success' => true,
                'data' => $services,
                'date' => $date
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'get_daily_map_services controller error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Modal para crear nuevo servicio
     */
    public function modal_create_service()
    {
        try {
            $states = $this->usa_states_model->get_all_states_for_api();
            $providers = $this->users_model->get_all()->getResult();
            //$appointments = $this->appointment_model->get_all()->getResult();
            $appointments = $this->appointment_model->get_all_with_details();  

            return $this->template->view('home_services/modals/modal_create_service', [
                'states' => $states,
                'providers' => $providers,
                'appointments' => $appointments
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_create_service error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor");
        }
    }

    /**
     * Modal para editar servicio
     */
    public function modal_edit_service()
    {
        try {
            $id = $this->request->getGet('id');
            $service = $this->appointment_services_model->get_one($id);
            $states = $this->usa_states_model->get_all_states_for_api();
            $providers = $this->users_model->get_all()->getResult();

            if (!$service || !$service->id) {
                return $this->response->setStatusCode(404)->setBody("Servicio no encontrado");
            }

              $appointment = null;
            if ($service && $service->appointment_id) {
                $appointment = $this->appointment_model
                                    ->get_with_patient($service->appointment_id);
            }

            return $this->template->view('home_services/modals/modal_edit_service', [
                'service' => $service,
                'states' => $states,
                'providers' => $providers,
                'appointment' => $appointment
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_edit_service error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor");
        }
    }

    /**
     * Modal para cambiar estado del servicio
     */
    public function modal_change_status()
    {
        try {
            $id = $this->request->getGet('id');
            $service = $this->appointment_services_model->get_service_for_status_change($id);

            if (!$service) {
                return $this->response->setStatusCode(404)->setBody("Servicio no encontrado");
            }

            return $this->template->view('home_services/modals/modal_change_status', [
                'service' => $service
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_change_status error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor");
        }
    }

    /**
     * Modal para ver detalles del servicio
     */
    public function modal_service_details()
    {
        try {
            $id = $this->request->getGet('id');
            $service = $this->appointment_services_model->get_service_with_details($id);

            if (!$service) {
                return $this->response->setStatusCode(404)->setBody("Servicio no encontrado");
            }

            return $this->template->view('home_services/modals/modal_service_details', [
                'service' => $service
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'modal_service_details error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setBody("Error del servidor modal_service_details");
        }
    }

    /**
     * Crear nuevo servicio
     */
    public function create_service()
    {
        try {
            $data = [
                'appointment_id' => $this->request->getPost('appointment_id'),
                'service_type' => $this->request->getPost('service_type'),
                'patient_state' => $this->request->getPost('patient_state'),
                'patient_city' => $this->request->getPost('patient_city'),
                'patient_address' => $this->request->getPost('patient_address'),
                'patient_zipcode' => $this->request->getPost('patient_zipcode'),
                'service_notes' => $this->request->getPost('service_notes'),
                'status' => 'pendiente',
                'assigned_provider_id' => $this->request->getPost('assigned_provider_id'),
                'scheduled_date' => $this->request->getPost('scheduled_date'),
                'scheduled_time' => $this->request->getPost('scheduled_time'),
                'created_by' => $this->login_user->id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->appointment_services_model->save_appointment_service($data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio creado exitosamente'
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

    /**
     * Actualizar servicio
     */
    public function update_service()
    {
        try {
            $id = $this->request->getPost('service_id');

            $data = [
                'service_type' => $this->request->getPost('service_type'),
                'patient_state' => $this->request->getPost('patient_state'),
                'patient_city' => $this->request->getPost('patient_city'),
                'patient_address' => $this->request->getPost('patient_address'),
                'patient_zipcode' => $this->request->getPost('patient_zipcode'),
                'priority' => $this->request->getPost('priority'),
                'service_notes' => $this->request->getPost('service_notes'),
                'assigned_provider_id' => $this->request->getPost('assigned_provider_id'),
                'service_cost' => $this->request->getPost('service_cost'),
                //'scheduled_date' => $this->request->getPost('scheduled_date'),
                //'scheduled_time' => $this->request->getPost('scheduled_time')
            ];

            $result = $this->appointment_services_model->update_service($data, $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio actualizado exitosamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al actualizar el servicio'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'update_service error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Cambiar estado del servicio
     */
    public function change_service_status()
    {
        try {
            $id = $this->request->getPost('service_id');
            $status = $this->request->getPost('status');
            $completion_notes = $this->request->getPost('completion_notes');

            $result = $this->appointment_services_model->change_status($id, $status, $completion_notes);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al actualizar el estado'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'change_service_status error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Eliminar servicio
     */
    public function delete_service()
    {
        try {
            $id = $this->request->getPost('service_id');

            $result = $this->appointment_services_model->delete($id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio eliminado exitosamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar el servicio'
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'delete_service error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
     * Buscar citas para asignar a servicios
     */
    public function search_appointments()
    {
        try {
            $term = trim($this->request->getGet('q') ?? '');
            $data = $this->appointment_model->search_appointments($term);

            return $this->response->setJSON($data);
        } catch (\Throwable $e) {
            log_message('error', 'search_appointments controller error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error del servidor'
            ]);
        }
    }

    public function get_unscheduled_services()
    {
        $services = $this->appointment_services_model->get_services_without_schedule();
        return $this->response->setJSON(['success' => true, 'data' => $services]);
    }

    public function assign_schedule()
    {
        $id   = $this->request->getPost('service_id');
        $date = $this->request->getPost('scheduled_date');
        $time = $this->request->getPost('scheduled_time');

        $data = [
            'scheduled_date' => $date,
            'scheduled_time' => $time,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        $saved = $this->appointment_services_model->ci_save($data, $id);

        if ($saved) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Servicio programado correctamente'
            ]);
        } else {
            // Pon un código 500 para que tu JS pueda distinguir
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Hubo un error al programar el servicio'
                ]);
        }
    }



    public function unscheduled_services()
    {
        return $this->template->rander("home_services/unscheduled_services");
    }

    public function check_provider_availability()
    {
        try {
            $date = $this->request->getGet('date'); // YYYY-MM-DD
            $time = $this->request->getGet('time'); // HH:MM:SS
            
            if (!$date || !$time) {
                return $this->response->setStatusCode(400)->setJSON([
                    'available' => false,
                    'message' => 'Faltan parámetros requeridos'
                ]);
            }

            $services_model = new \App\Models\Appointment_services_model();
            $is_available = $services_model->check_time_slot_availability($date, $time);

            if ($is_available) {
                return $this->response->setJSON([
                    'available' => true,
                    'message' => 'Horario disponible'
                ]);
            } else {
                return $this->response->setJSON([
                    'available' => false,
                    'message' => 'Ya existe un servicio programado para esa fecha y hora'
                ]);
            }

        } catch (\Throwable $e) {
            log_message('error', 'check_provider_availability error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'available' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    public function delete_service2()
    {
        try {
            $service_id = $this->request->getPost('service_id');
            
            if (!$service_id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'ID del servicio requerido'
                ]);
            }
            
            // CORRECCIÓN: usar appointment_services_model
            $service = $this->appointment_services_model->get_one($service_id);
            if (!$service || !$service->id) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Servicio no encontrado'
                ]);
            }
            
            // CORRECCIÓN: usar appointment_services_model
            $result = $this->appointment_services_model->delete($service_id);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Servicio eliminado correctamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar el servicio'
                ]);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'delete_service2 error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
