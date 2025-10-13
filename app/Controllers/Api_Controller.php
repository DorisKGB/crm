<?php

namespace App\Controllers;

use App\Models\Appointment_model;
use App\Models\Users_model;
use App\Models\Patient_model;
use App\Models\VseeUsers_model;
use App\Models\Usa_states_model;
use App\Models\Calls_system_model;
use App\Models\Call_heartbeat_model;
use App\Models\Appointment_services_model;
use App\Models\Notification_Token_Model;
use Exception;

class Api_Controller extends App_Controller
{
    private $modelAppointments, $modelUsers, $modelPatients, $modelVseeUsers, $modelUsaState, $appointment_services_model, $Calls_system_model, $Call_heartbeat_model, $Notification_Token_Model;

    function __construct()
    {
        parent::__construct();
        $this->modelAppointments = new Appointment_model();
        $this->modelUsers = new Users_model();
        $this->modelVseeUsers = new VseeUsers_model();
        $this->modelPatients = new Patient_model();
        $this->modelUsaState = new Usa_states_model();
        $this->appointment_services_model = new Appointment_services_model();
        $this->Calls_system_model = new Calls_system_model();
        $this->Call_heartbeat_model = new Call_heartbeat_model(); 
        $this->Notification_Token_Model = new Notification_Token_Model();

    }

    public function get_us_states()
    {
        try {
            // Agregar estas líneas:
            $this->setCorsHeaders();
            
            // Manejar petición OPTIONS (preflight)
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // Obtener todos los estados
            $states = $this->modelUsaState->get_all_states_for_api();

            if (empty($states)) {
                // Si no hay datos en BD, devolver estados básicos
                $states = $this->get_fallback_states();
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $states,
                'total' => count($states)
            ]);
            return;
        } catch (\Throwable $e) {
            log_message('error', 'get_us_states error: ' . $e->getMessage());

            // En caso de error, devolver estados básicos
            $this->jsonResponse([
                'success' => true,
                'data' => $this->get_fallback_states(),
                'message' => 'Datos obtenidos desde fallback'
            ]);
            return;
        }
    }

    public function get_user_by_vsee_username()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $username = $this->request->getGet('vsee_username');
            
            if (!$username) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Username requerido'
                ], 400);
            }
            
            $db = \Config\Database::connect();
            $vseeUsersTable = $db->prefixTable('vsee_users');
            
            $builder = $db->table($vseeUsersTable);
            $builder->select('user_id');
            $builder->where('vsee_username', $username);
            $builder->where('deleted', 0);
            $result = $builder->get()->getRow();
            
            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'user_id' => $result->user_id
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario VSee no encontrado'
                ], 404);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en get_user_by_vsee_username: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }


    public function set_call_failed() {
        $this->setCorsHeaders();
        $meeting_id = $this->request->getGet('meeting_id');
        $reason = $this->request->getGet('reason') ?? 'unknown';
        
        $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
        if ($call) {
            $this->Calls_system_model->update_call_status($call->id, 'failed', [
                'end_time' => date('Y-m-d H:i:s'),
                'failure_reason' => $reason
            ]);
        }
        return $this->jsonResponse(['success' => true]);
    }

    /**
    * API: Verificar estado de llamada por meeting_id
    */
    public function check_call_status_by_meeting()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$meeting_id) {
                log_message('debug', 'check_call_status_by_meeting: meeting_id no proporcionado');
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'meeting_id requerido'
                ], 400);
            }
            
            log_message('debug', 'check_call_status_by_meeting: Buscando llamada con meeting_id: ' . $meeting_id);
            
            // Verificar que el modelo esté inicializado
            if (!isset($this->Calls_system_model)) {
                log_message('error', 'Calls_system_model no está inicializado');
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error de configuración del servidor'
                ], 500);
            }
            
            $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
            
            if (!$call) {
                log_message('debug', 'check_call_status_by_meeting: Llamada no encontrada para meeting_id: ' . $meeting_id);
                return $this->jsonResponse([
                        'success' => true, // ← SOLUCIÓN
                        'data' => [
                            'call_status' => 'not_found',
                            'meeting_id' => $meeting_id
                        ]
                    ]);
            }
            
            // Asegurar que el status nunca sea null o undefined
            $status = $call->status ?? 'unknown';
            
            log_message('debug', 'check_call_status_by_meeting: Llamada encontrada. Status: ' . $status);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'call_status' => $status,
                    'call_id' => $call->id ?? null,
                    'meeting_id' => $meeting_id,
                    'caller_id' => $call->caller_id ?? null,
                    'receiver_id' => $call->receiver_id ?? null,
                    'start_time' => $call->start_time ?? null,
                    'end_time' => $call->end_time ?? null,
                    'call_duration' => $call->call_duration ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en check_call_status_by_meeting: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor',
                'data' => [
                    'call_status' => 'error',
                    'meeting_id' => $meeting_id ?? 'unknown'
                ]
            ], 500);
        }
    }

    /*public function set_user_in_call()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $user_id = $this->request->getGet('user_id');
            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$user_id || !$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Parámetros requeridos: user_id, meeting_id'
                ], 400);
            }
            
            // Verificar que el usuario existe
            $user = $this->modelUsers->get_one($user_id);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Buscar si existe una llamada con este meeting_id
            $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
            $call_id = $call ? $call->id : null;
            
            // ✅ VERIFICAR ESTADO ACTUAL ANTES DE ACTUALIZAR
            $currentStatus = $this->Calls_system_model->get_user_status2($user_id);
            
            log_message('debug', "set_user_in_call: Usuario $user_id, Status actual: " . ($currentStatus ? $currentStatus->status : 'NO_EXISTE'));
            
            // ✅ SI YA ESTÁ EN LLAMADA, NO HACER NADA
            if ($currentStatus && $currentStatus->status === 'in_call') {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Usuario ya estaba marcado como en llamada',
                    'user_id' => $user_id,
                    'status' => 'in_call',
                    'action' => 'no_change_needed'
                ]);
            }
            
            // ✅ CREAR O ACTUALIZAR SOLO SI ES NECESARIO
            $result = $this->Calls_system_model->set_user_status2($user_id, 'in_call', $call_id);
            
            if ($result) {
                $action = $currentStatus ? 'updated' : 'created';
                return $this->jsonResponse([
                    'success' => true,
                    'message' => $action === 'created' ? 'Registro creado - Usuario marcado como en llamada' : 'Estado actualizado - Usuario marcado como en llamada',
                    'user_id' => $user_id,
                    'status' => 'in_call',
                    'action' => $action
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al actualizar estado del usuario'
                ], 500);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en set_user_in_call: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }*/

    public function set_user_in_call()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $user_id = $this->request->getGet('user_id');
            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$user_id || !$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Parámetros requeridos: user_id, meeting_id'
                ], 400);
            }
            
            // Verificar que el usuario existe
            $user = $this->modelUsers->get_one($user_id);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Buscar si existe una llamada con este meeting_id
            $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
            $call_id = $call ? $call->id : null;
            
            // ✅ USAR MÉTODO ATÓMICO
            $result = $this->Calls_system_model->set_user_status_atomic($user_id, 'in_call', $call_id);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'user_id' => $user_id,
                'status' => 'in_call',
                'action' => $result['action']
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en set_user_in_call: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

    public function set_user_available()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $user_id = $this->request->getGet('user_id');
            $meeting_id = $this->request->getGet('meeting_id');
            $force = $this->request->getGet('force'); // NUEVO PARÁMETRO
            
            if (!$user_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Parámetro requerido: user_id'
                ], 400);
            }
            
            // Verificar que el usuario existe
            $user = $this->modelUsers->get_one($user_id);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Si se proporciona meeting_id, finalizar la llamada
            if ($meeting_id) {
                $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
                if ($call && in_array($call->status, ['ringing', 'in_progress'])) {
                    // Calcular duración si la llamada estaba en progreso
                    $duration = 0;
                    if ($call->status === 'in_progress' && $call->start_time) {
                        $start = strtotime($call->start_time);
                        $end = time();
                        $duration = $end - $start;
                    }
                    
                    // Actualizar llamada como completada
                    $this->Calls_system_model->update_call_status($call->id, 'completed', [
                        'end_time' => date('Y-m-d H:i:s'),
                        'call_duration' => $duration
                    ]);
                    
                    // Liberar ambos usuarios de la llamada
                    if ($call->caller_id) {
                        $this->Calls_system_model->set_user_status2($call->caller_id, 'available');
                    }
                    if ($call->receiver_id) {
                        $this->Calls_system_model->set_user_status2($call->receiver_id, 'available');
                    }
                }
            }
            
            // NUEVA LÓGICA: Si force=true, forzar actualización sin importar estado anterior
            if ($force === 'true') {
                log_message('debug', "FORZANDO estado available para usuario $user_id");
                $this->Calls_system_model->set_user_status2($user_id, 'available');
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Usuario FORZADO como disponible',
                    'user_id' => $user_id,
                    'status' => 'available',
                    'forced' => true
                ]);
            } else {
                // Lógica normal (sin forzar)
                $this->Calls_system_model->set_user_status2($user_id, 'available');
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Usuario marcado como disponible',
                'user_id' => $user_id,
                'status' => 'available'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en set_user_available: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

        /**
    * API: Finalizar llamada con confirmación
    */
    public function end_call_confirmed()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // CAMBIAR: Buscar por meeting_id en lugar de call_id
            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Meeting ID requerido'
                ],400);
            }

            // CAMBIAR: Buscar llamada por meeting_id
            $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
            
            if (!$call) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Llamada no encontrada'
                ],400);
            }

            // Calcular duración
            $start = strtotime($call->start_time);
            $end = time();
            $duration = $end - $start;

            // Finalizar llamada usando $call->id
            $this->Calls_system_model->update_call_status($call->id, 'completed', [
                'end_time' => date('Y-m-d H:i:s'),
                'call_duration' => $duration
            ]);

            // Liberar usuarios
            $this->Calls_system_model->set_user_status($call->caller_id, 'available');
            $this->Calls_system_model->set_user_status($call->receiver_id, 'available');

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Llamada finalizada correctamente'
            ],400);

        } catch (\Exception $e) {
            log_message('error', 'Error finalizando llamada: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }


    public function validate_us_address()
    {
        try {
            // Agregar estas líneas:
            $this->setCorsHeaders();
            
            // Manejar petición OPTIONS (preflight)
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            $input = $this->request->getGet();

            $state = $input['state'] ?? '';
            $city = $input['city'] ?? '';
            $address = $input['address'] ?? '';
            $zipcode = $input['zipcode'] ?? '';

            // Validaciones básicas
            if (empty($state) || empty($city) || empty($zipcode)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Todos los campos son obligatorios'
                ], 400);
                return;
            }

            // Validar formato ZIP code (5 dígitos)
            if (!preg_match('/^\d{5}$/', $zipcode)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'ZIP code debe tener exactamente 5 dígitos'
                ], 400);
                return;
            }

            // Validar que el estado existe
            $stateExists = $this->modelUsaState->validate_state_exists($state);

            if (!$stateExists) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Estado no válido'
                ], 400);
                return;
            }

            // Validaciones adicionales (puedes expandir según necesites)
            $validationResult = $this->perform_address_validation($state, $city, $address, $zipcode);

            if ($validationResult['valid']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Dirección validada correctamente',
                    'data' => [
                        'formatted_address' => $validationResult['formatted_address'],
                        'state_info' => $this->modelUsaState->get_state_by_code($state)
                    ]
                ]);
                return;
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 400);
                return;
            }
        } catch (\Throwable $e) {
            log_message('error', 'validate_us_address error: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
            return;
        }
    }

    public function save_service_data()
    {
        try {
            // Agregar headers CORS
            $this->setCorsHeaders();
            
            // Manejar petición OPTIONS (preflight)
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }
            
            // CORREGIR: Cambiar de getGet() a getJSON() o getPost() según el método HTTP
            $input = null;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = $this->request->getJSON(true); // Para JSON
                // O si envías form-data:
                // $input = $this->request->getPost();
            } else {
                $input = $this->request->getGet();
            }

            $token = $input['token'] ?? '';
            $service = $input['service'] ?? '';
            $address = $input['address'] ?? [];
            $notes = $input['notes'] ?? '';
            $consultationData = json_decode($input['consultation_data'] ?? '[]', true);

            // Validaciones básicas
            if (empty($token) || empty($service) || empty($address)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Datos incompletos. Token, servicio y dirección son obligatorios.'
                ], 400);
                return;
            }

            log_message('debug', 'Buscando cita con token: ' . $token);

            // CORREGIR: Verificar que el modelo esté inicializado correctamente
            if (!isset($this->modelAppointments)) {
                log_message('error', 'Appointment model no está inicializado');
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error de configuración del servidor'
                ], 500);
                return;
            }

            // Buscar la cita por token
            $appointment = $this->modelAppointments->get_one_where(['token' => $token]);
            
            // Verificar si get_one_where retornó false o null
            if ($appointment === false || $appointment === null) {
                log_message('warning', 'Cita no encontrada para token: ' . $token);
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Consulta no encontrada con el token proporcionado'
                ], 404);
                return;
            }

            // Verificar que la cita tenga ID válido
            if (!isset($appointment->id) || empty($appointment->id)) {
                log_message('warning', 'Cita encontrada pero sin ID válido para token: ' . $token);
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Datos de consulta incompletos'
                ], 400);
                return;
            }

            log_message('debug', 'Cita encontrada con ID: ' . $appointment->id);

            // Inicializar el modelo de servicios si no existe
            if (!isset($this->appointment_services_model)) {
                $this->appointment_services_model = new \App\Models\Appointment_services_model();
            }

            // Validar estructura de address
            if (!is_array($address)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Formato de dirección inválido'
                ], 400);
                return;
            }

            // Preparar datos del servicio con validaciones
            $serviceData = [
                'appointment_id' => $appointment->id,
                'service_type' => $service,
                'patient_state' => $address['state'] ?? '',
                'patient_city' => $address['city'] ?? '',
                'patient_address' => $address['complement'] ?? $address['address'] ?? '', // CORREGIR: Manejar ambas opciones
                'patient_zipcode' => $address['zipcode'] ?? '',
                'service_notes' => $notes,
                'status' => 'active',
                'created_by' => $this->login_user->id ?? 0,
                'created_at' => date('Y-m-d H:i:s'), // AGREGAR: Campo created_at
                'deleted' => 0
            ];

            // Log para debug
            log_message('debug', 'Datos del servicio a guardar: ' . json_encode($serviceData));

            // Guardar usando el modelo de servicios
            $serviceId = $this->appointment_services_model->save_appointment_service($serviceData);

            if ($serviceId && $serviceId > 0) {
                // CORREGIR: Usar el modelo correcto (modelAppointments en lugar de appointment_model)
                $updateData = [
                    'service_type' => $service,
                    'service_id' => $serviceId,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // CORREGIR: Usar el modelo correcto
                $updateResult = $this->modelAppointments->ci_save($updateData, $appointment->id);

                if ($updateResult) {
                    log_message('debug', 'Servicio guardado exitosamente. ID: ' . $serviceId);
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Servicio guardado correctamente',
                        'data' => [
                            'service_id' => $serviceId,
                            'appointment_id' => $appointment->id,
                            'service_type' => $service,
                            'created_at' => date('Y-m-d H:i:s')
                        ]
                    ]);
                } else {
                    log_message('error', 'Error al actualizar la cita con ID: ' . $appointment->id);
                    // Aún así consideramos éxito porque el servicio se guardó
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Servicio guardado correctamente',
                        'warning' => 'No se pudo actualizar algunos datos de la cita',
                        'data' => [
                            'service_id' => $serviceId,
                            'appointment_id' => $appointment->id,
                            'service_type' => $service
                        ]
                    ]);
                }
            } else {
                log_message('error', 'Error al guardar el servicio para appointment_id: ' . $appointment->id . '. Resultado: ' . var_export($serviceId, true));
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo guardar el servicio. Por favor, intente nuevamente.'
                ], 500);
            }
            
        } catch (\Throwable $e) {
            log_message('error', 'save_service_data error: ' . $e->getMessage() . ' en línea: ' . $e->getLine() . ' archivo: ' . $e->getFile());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno del servidor',
                'debug_info' => ENVIRONMENT === 'development' ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }


    private function get_fallback_states()
    {
        return [
            ['name' => 'Alabama', 'code' => 'AL'],
            ['name' => 'Alaska', 'code' => 'AK'],
            ['name' => 'Arizona', 'code' => 'AZ'],
            ['name' => 'Arkansas', 'code' => 'AR'],
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'Colorado', 'code' => 'CO'],
            ['name' => 'Connecticut', 'code' => 'CT'],
            ['name' => 'Delaware', 'code' => 'DE'],
            ['name' => 'Florida', 'code' => 'FL'],
            ['name' => 'Georgia', 'code' => 'GA'],
            ['name' => 'Hawaii', 'code' => 'HI'],
            ['name' => 'Idaho', 'code' => 'ID'],
            ['name' => 'Illinois', 'code' => 'IL'],
            ['name' => 'Indiana', 'code' => 'IN'],
            ['name' => 'Iowa', 'code' => 'IA'],
            ['name' => 'Kansas', 'code' => 'KS'],
            ['name' => 'Kentucky', 'code' => 'KY'],
            ['name' => 'Louisiana', 'code' => 'LA'],
            ['name' => 'Maine', 'code' => 'ME'],
            ['name' => 'Maryland', 'code' => 'MD'],
            ['name' => 'Massachusetts', 'code' => 'MA'],
            ['name' => 'Michigan', 'code' => 'MI'],
            ['name' => 'Minnesota', 'code' => 'MN'],
            ['name' => 'Mississippi', 'code' => 'MS'],
            ['name' => 'Missouri', 'code' => 'MO'],
            ['name' => 'Montana', 'code' => 'MT'],
            ['name' => 'Nebraska', 'code' => 'NE'],
            ['name' => 'Nevada', 'code' => 'NV'],
            ['name' => 'New Hampshire', 'code' => 'NH'],
            ['name' => 'New Jersey', 'code' => 'NJ'],
            ['name' => 'New Mexico', 'code' => 'NM'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'North Carolina', 'code' => 'NC'],
            ['name' => 'North Dakota', 'code' => 'ND'],
            ['name' => 'Ohio', 'code' => 'OH'],
            ['name' => 'Oklahoma', 'code' => 'OK'],
            ['name' => 'Oregon', 'code' => 'OR'],
            ['name' => 'Pennsylvania', 'code' => 'PA'],
            ['name' => 'Rhode Island', 'code' => 'RI'],
            ['name' => 'South Carolina', 'code' => 'SC'],
            ['name' => 'South Dakota', 'code' => 'SD'],
            ['name' => 'Tennessee', 'code' => 'TN'],
            ['name' => 'Texas', 'code' => 'TX'],
            ['name' => 'Utah', 'code' => 'UT'],
            ['name' => 'Vermont', 'code' => 'VT'],
            ['name' => 'Virginia', 'code' => 'VA'],
            ['name' => 'Washington', 'code' => 'WA'],
            ['name' => 'West Virginia', 'code' => 'WV'],
            ['name' => 'Wisconsin', 'code' => 'WI'],
            ['name' => 'Wyoming', 'code' => 'WY']
        ];
    }

    
    /**
     * Método auxiliar: Validación de dirección
     */
    private function perform_address_validation($state, $city, $address, $zipcode)
    {
        // Validaciones básicas
        $result = [
            'valid' => true,
            'message' => 'Dirección válida',
            'formatted_address' => ''
        ];

        // Validar longitud de campos
        if (strlen($city) < 2) {
            return [
                'valid' => false,
                'message' => 'Nombre de ciudad muy corto'
            ];
        }

        if (strlen($address) < 5) {
            return [
                'valid' => false,
                'message' => 'Dirección debe tener al menos 5 caracteres'
            ];
        }

        // Aquí puedes agregar validaciones más específicas:
        // - Conectar con API de USPS
        // - Validar códigos postales por estado
        // - Verificar nombres de ciudades

        // Formatear dirección
        $result['formatted_address'] = trim($address) . ', ' . trim($city) . ', ' . strtoupper($state) . ' ' . $zipcode;

        return $result;
    }

    public function getDataConference($token, $typeUser)
    {
        try {
            // Validar que el token no esté vacío
            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => 'Token no válido',
                    'data' => null
                ];
            }

            // Obtener datos de la cita
            $appointments = $this->modelAppointments->get_one_where(['token' => $token]);

            if (!$appointments) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la cita con el token proporcionado',
                    'data' => null
                ];
            }

            // Obtener datos del paciente
            $patients = $this->modelPatients->get_one($appointments->patient_id);

            if (!$patients) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron datos del paciente',
                    'data' => null
                ];
            }

            // Obtener datos del proveedor/doctor
            $dataProviders = $this->modelVseeUsers->get_one_where(['user_id' => $appointments->provider_id, 'action' => 'provider']);
            $providers = $this->modelUsers->get_one($appointments->provider_id);

            if (!$providers) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron datos del doctor',
                    'data' => null
                ];
            }

            $vsee_username = '';
            $vsee_token = '';
            $displayName = '';

            if($typeUser === "3847629051"){ //Codigo Paciente
                $vsee_username = $patients->vsee_username;
                $vsee_token = $patients->vsee_token;
                $displayName = $patients->full_name;

            }else if($typeUser === "7194863520"){ //Codigo Provider
                $vsee_username = $dataProviders->vsee_username;
                $vsee_token = $dataProviders->vsee_token;
                $displayName = $providers->first_name . " " . $providers->last_name;

            }else{
                $vsee_username = $patients->vsee_username;
                $vsee_token = $patients->vsee_token;
                $displayName = $patients->full_name;
            }

            // Preparar los datos para el frontend
            $data = [
                "patient_name" => $patients->full_name,
                "appointment_date" => $appointments->appointment_date,
                "appointment_time" => $appointments->appointment_time,
                "doctor_name" => $providers->first_name . " " . $providers->last_name,
                "specialty" => $providers->specialty ?? "Medicina General",
                "vsee_username" => $vsee_username,
                "vsee_token" => $vsee_token,
                "conference_id" => $appointments->meeting_id,
                "display_name" => $displayName
            ];

            // Validar que los datos críticos de VSee estén presentes
            if (empty($data['vsee_username']) || empty($data['vsee_token'])) {
                return [
                    'success' => false,
                    'message' => 'Datos de videollamada incompletos. Contacte al administrador.',
                    'data' => null
                ];
            }

            // Retornar éxito con los datos
            return [
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'data' => $data
            ];

        } catch (Exception $e) {
            // Manejar cualquier error inesperado
            error_log("Error en getDataConference: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error interno del servidor. Intente nuevamente.',
                'data' => null
            ];
        }
    }

    // Función para el endpoint de la API
    public function apiGetConference($token = null,  $typeUser = null)
    {

        // Manejar petición OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Validar token
        if (empty($token)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Token no proporcionado',
                'data' => null
            ], 400);
            return;
        }

          // Validar token
        if (empty($typeUser)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Token de Tipo no proporcionado',
                'data' => null
            ], 400);
            return;
        }

        // Obtener datos y responder
        $result = $this->getDataConference($token, $typeUser);
        $statusCode = $result['success'] ? 200 : 400;
        $this->jsonResponse($result, $statusCode);
    }

    // Método auxiliar para configurar CORS
    private function setCorsHeaders()
    {
        // Permitir múltiples orígenes
        $allowedOrigins = [
            'https://teleconsulta.clinicahispanarubymed.com',
            'http://teleconsulta.clinicahispanarubymed.com',
            'https://www.clinicahispanarubymed.com'
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true'); // si usas cookies o auth
        }

        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 horas cache para preflight
        header('Access-Control-Max-Age: 86400'); // Cache preflight por 24 horas
    }

    // Método auxiliar para respuestas JSON
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
    * API: Enviar heartbeat del usuario (cada 4 segundos)
    */
    public function send_heartbeat()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // ✅ LIMPIAR Y VALIDAR ENTRADA
            $user_id = $this->_get_clean_value(['user_id' => $this->request->getGet('user_id')], 'user_id');
            $meeting_id = trim($this->request->getGet('meeting_id') ?? '');
            
            if (!$user_id || !$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'user_id y meeting_id son requeridos'
                ], 400);
            }

            // ✅ VALIDAR QUE EL USUARIO EXISTE (usando modelo)
            $user = $this->modelUsers->get_one($user_id);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // ✅ LIMPIAR DATOS ADICIONALES
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

            // ✅ USAR MODELO PARA ACTUALIZAR HEARTBEAT
            $result = $this->Call_heartbeat_model->upsert_heartbeat(
                $meeting_id, 
                $user_id, 
                $user_agent, 
                $ip_address
            );

            if ($result) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Heartbeat registrado',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error registrando heartbeat'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en send_heartbeat: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

    /**
    * API: Obtener participantes activos en una llamada
    */
    public function get_active_participants()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }


            $meeting_id = $this->request->getGet('meeting_id');
            $requesting_user_id = $this->_get_clean_value(['user_id' => $this->request->getGet('user_id')], 'user_id');

                        // ✅ LIMPIAR Y VALIDAR ENTRADA
            log_message('debug', 'Buscando participantes para meeting_id: ' . $meeting_id);

            
            if (!$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'meeting_id es requerido'
                ], 400);
            }

            // ✅ USAR MODELO PARA OBTENER PARTICIPANTES ACTIVOS (ventana de 5 segundos balanceada)
            $participants_query = $this->Call_heartbeat_model->get_active_participants($meeting_id, 5);

            if (method_exists($participants_query, 'getResult')) {
                $active_participants = $participants_query->getResult();
                log_message('debug', 'Participantes encontrados: ' . count($active_participants));
            } else {
                log_message('error', 'get_active_participants no retornó objeto válido');
                $active_participants = [];
            }

            // Verificar que no sea false
            if ($participants_query === false || !method_exists($participants_query, 'getResult')) {
                $active_participants = [];
            } else {
                $active_participants = $participants_query->getResult();
            }

            // ✅ PROCESAR DATOS EN EL CONTROLADOR (lógica de negocio)
            $other_participants = [];
            $current_user_active = false;
            
            foreach ($active_participants as $participant) {
                if ($requesting_user_id && $participant->user_id == $requesting_user_id) {
                    $current_user_active = true;
                } else {
                    $other_participants[] = [
                        'user_id' => $participant->user_id,
                        'user_name' => $participant->user_name,
                        'user_image' => $participant->user_image,
                        'last_heartbeat' => $participant->last_heartbeat,
                        'seconds_since_last_heartbeat' => $participant->seconds_since_last_heartbeat
                    ];
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'meeting_id' => $meeting_id,
                    'total_active_participants' => count($active_participants),
                    'other_participants' => $other_participants,
                    'other_participants_count' => count($other_participants),
                    'current_user_active' => $current_user_active,
                    'check_timestamp' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en get_active_participants: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

    /**
    * API: Limpiar heartbeat al salir de la llamada
    */
    public function clear_heartbeat()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // ✅ LIMPIAR Y VALIDAR ENTRADA
            $user_id = $this->_get_clean_value(['user_id' => $this->request->getGet('user_id')], 'user_id');
            $meeting_id = trim($this->request->getGet('meeting_id') ?? '');
            
            if (!$user_id || !$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'user_id y meeting_id son requeridos'
                ], 400);
            }

            // ✅ USAR MODELO PARA LIMPIAR HEARTBEAT
            $result = $this->Call_heartbeat_model->clear_user_heartbeat($meeting_id, $user_id);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Heartbeat limpiado',
                'rows_affected' => $result ? 1 : 0
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en clear_heartbeat: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

    /**
    * API: Limpiar heartbeats antiguos (para cron job)
    */
    public function cleanup_old_heartbeats()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // ✅ LIMPIAR PARÁMETRO OPCIONAL
            $minutes = (int)($this->request->getGet('minutes') ?? 2);
            
            // Validar rango razonable
            if ($minutes < 1 || $minutes > 60) {
                $minutes = 2; // Default seguro
            }

            // ✅ USAR MODELO PARA LIMPIAR
            $result = $this->Call_heartbeat_model->cleanup_old_heartbeats($minutes);

            return $this->jsonResponse([
                'success' => true,
                'message' => "Heartbeats de más de {$minutes} minutos eliminados",
                'cleanup_completed' => true
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en cleanup_old_heartbeats: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

    /**
    * API: Verificar estado de participantes (endpoint adicional más simple)
    */
    public function check_participant_status()
    {
        try {
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // ✅ LIMPIAR ENTRADA
            $meeting_id = trim($this->request->getGet('meeting_id') ?? '');
            
            if (!$meeting_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'meeting_id es requerido'
                ], 400);
            }

            // ✅ USAR MODELO PARA CONTAR PARTICIPANTES
            $active_count = $this->Call_heartbeat_model->count_active_participants($meeting_id);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'meeting_id' => $meeting_id,
                    'active_participants_count' => $active_count,
                    'has_other_participants' => $active_count > 1,
                    'check_timestamp' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en check_participant_status: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'
            ], 500);
        }
    }

     /**
     * API: Guardar token FCM del usuario
     */
    public function save_fcm_token()
    {
        log_message('info', 'save_fcm_token - Iniciando proceso');

        try {
            
            $this->setCorsHeaders();
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // Obtener datos del POST
            $input = $this->request->getJSON(true);
            $token = $input['token'] ?? '';
            $user_id = $input['user_id'] ?? null;
            
            log_message('info', 'save_fcm_token - Datos recibidos: ' . json_encode($input));

            
            if (!$token) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token FCM requerido'
                ], 400);
            }
            
            // Si no se proporciona user_id, usar el usuario logueado
            if (!$user_id) {
                $user_id = $this->login_user->id ?? null;
            }
            
            log_message('info', 'save_fcm_token - User ID: ' . $user_id);
            
            if (!$user_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no identificado'
                ], 400);
            }


            // Verificar que el usuario existe
            $user = $this->modelUsers->get_one($user_id);
            if (!$user) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Usar el modelo Notification_Token_Model para guardar el token
            $result = $this->Notification_Token_Model->saveOrUpdateToken($user_id, $token);                      
            if ($result) {
                log_message('info', "Token FCM guardado para usuario $user_id usando Notification_Token_Model");
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Token FCM guardado correctamente',
                    'user_id' => $user_id,
                    'token_id' => $result
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al guardar token FCM'
                ], 500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en save_fcm_token: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error del servidor'+$e->getMessage()
            ], 500);
        }
    }


    private function _get_clean_value($options, $key)
    {
        $value = isset($options[$key]) ? $options[$key] : '';
        
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        // Usar el método de escape del Crud_model base
        $db = \Config\Database::connect();
        return $db->escapeString(trim($value));
    }

    
}

?>