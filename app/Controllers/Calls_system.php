<?php

namespace App\Controllers;

use App\Models\Calls_system_model;
use App\Models\Users_model;

class Calls_system extends Security_Controller
{
    protected $Calls_system_model;

    public function __construct()
    {
        parent::__construct();
        $this->Calls_system_model = new Calls_system_model();
        $this->Users_model = new Users_model();
    }

    /**
     * Vista principal del sistema de llamadas
     */
    public function index()
    {
        $view_data['user_id'] = $this->login_user->id;
        return $this->template->rander('calls_system/index', $view_data);
    }

    /**
     * API: Obtener usuarios disponibles para llamar
     */
    public function get_available_users()
    {
        try {
            // Debug: Verificar que el modelo esté inicializado
            if (!$this->Calls_system_model) {
                log_message('error', 'Calls_system_model no está inicializado');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error del modelo',
                    'users' => []
                ]);
            }

            // Debug: Verificar usuario logueado
            if (!$this->login_user || !$this->login_user->id) {
                log_message('error', 'Usuario no logueado o sin ID');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no válido',
                    'users' => []
                ]);
            }

            $users = $this->Calls_system_model->get_available_users($this->login_user->id);
            $result = [];

            // Verificar si la consulta fue exitosa
            if (!$users) {
                log_message('error', 'get_available_users retornó false');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al consultar usuarios (posiblemente las tablas no existen)',
                    'users' => []
                ]);
            }

            // Verificar si hay resultados
            if ($users->getNumRows() === 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'No hay usuarios disponibles',
                    'users' => []
                ]);
            }

            foreach ($users->getResult() as $user) {
                $result[] = [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'image' => get_avatar($user->image), // Usar helper get_avatar
                    'status' => $user->call_status,
                    'available' => in_array($user->call_status, ['available'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'users' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Excepción en get_available_users: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage(),
                'users' => []
            ]);
        }
    }

    

    /**
     * API: Iniciar llamada
     */
    public function initiate_call()
    {
        $receiver_id = $this->request->getPost('receiver_id');
        $caller_id = $this->login_user->id;

        // Validar datos
        if (!$receiver_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Destinatario requerido'
            ]);
        }

        // VERIFICAR DISPONIBILIDAD DEL DESTINATARIO
        $receiver_status = $this->Calls_system_model->get_user_status($receiver_id);
        if (!$receiver_status || $receiver_status->status !== 'available') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El usuario está ocupado en otra llamada',
                'user_busy' => true
            ]);
        }

        // VERIFICAR DISPONIBILIDAD DEL CALLER
        $caller_status = $this->Calls_system_model->get_user_status($caller_id);
        if (!$caller_status || $caller_status->status !== 'available') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No puedes iniciar una llamada mientras estás ocupado'
            ]);
        }

        try {
            // Crear llamada en estado "initiating"
            $call_data = [
                'caller_id' => $caller_id,
                'receiver_id' => $receiver_id,
                'status' => 'initiating',
                'start_time' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $call_id = $this->Calls_system_model->create_call($call_data);

            if (!$call_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al crear llamada'
                ]);
            }

            // GENERAR SESIÓN VSEE
                $vsee_session_result = $this->generate_vsee_session($caller_id, $receiver_id);
            
            if (!$vsee_session_result || !$vsee_session_result['success']) {
                // Si falla VSee, cancelar la llamada
                $this->Calls_system_model->update_call_status($call_id, 'failed', [
                    'end_time' => date('Y-m-d H:i:s'),
                    'error_message' => 'Error al crear sesión de videollamada'
                ]);
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al preparar videollamada: ' . ($vsee_session_result['message'] ?? 'Error desconocido')
                ]);
            }



             // Actualizar llamada con datos de VSee
            $this->Calls_system_model->update_call_status($call_id, 'ringing', [
                'vsee_session_id' => $vsee_session_result['session_id'],
                'meeting_id' => $vsee_session_result['meeting_id']
            ]);

            // Actualizar estados de usuarios a "in_call"
             // ✅ GENERAR URLs ESPECÍFICAS PARA CADA USUARIO
            $caller_url = $this->get_vsee_join_url($vsee_session_result['meeting_id'], $caller_id);
            $receiver_url = $this->get_vsee_join_url($vsee_session_result['meeting_id'], $receiver_id);


            // Actualizar estados de usuarios a "in_call"
            $this->Calls_system_model->set_user_status($caller_id, 'in_call', $call_id);
            $this->Calls_system_model->set_user_status($receiver_id, 'in_call', $call_id);


            return $this->response->setJSON([
                'success' => true,
                'call_id' => $call_id,
                'message' => 'Llamada iniciada',
                'vsee_session_id' => $vsee_session_result['session_id'],
                'meeting_id' => $vsee_session_result['meeting_id'],
                // ✅ DEVOLVER URLs ESPECÍFICAS
                'caller_vsee_url' => $caller_url,
                'receiver_vsee_url' => $receiver_url
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error al iniciar llamada: ' . $e->getMessage());
            
            // Limpiar estados si algo falló
            if (isset($call_id)) {
                $this->Calls_system_model->update_call_status($call_id, 'failed', [
                    'end_time' => date('Y-m-d H:i:s'),
                    'error_message' => $e->getMessage()
                ]);
            }
            $this->Calls_system_model->set_user_status($caller_id, 'available');
            $this->Calls_system_model->set_user_status($receiver_id, 'available');

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor al iniciar llamada'
            ]);
        }
    }

    /**
     * API: Responder llamada (aceptar/rechazar)
     */
    public function answer_call()
    {
        $call_id = $this->request->getPost('call_id');
        $action = $this->request->getPost('action'); // 'accept' o 'reject'

        if (!$call_id || !$action) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parámetros requeridos'
            ]);
        }

        $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
        
        if (!$call || $call->receiver_id != $this->login_user->id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Llamada no válida'
            ]);
        }

        if ($action === 'accept') {
            try {
                // Verificar que ya tengamos sesión VSee
                if (empty($call->meeting_id)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: Sesión de videollamada no encontrada'
                    ]);
                }

                // Marcar llamada como "in_progress"
                $this->Calls_system_model->update_call_status($call_id, 'in_progress', [
                    'answer_time' => date('Y-m-d H:i:s')
                ]);

                // Obtener URLs para ambos usuarios
                $caller_url = $this->get_vsee_join_url($call->meeting_id, $call->caller_id);
                $receiver_url = $this->get_vsee_join_url($call->meeting_id, $call->receiver_id);

                return $this->response->setJSON([
                    'success' => true,
                    'action' => 'accepted',
                    'call_id' => $call_id,
                    'meeting_id' => $call->meeting_id,
                    'receiver_vsee_url' => $receiver_url,
                    'caller_vsee_url' => $caller_url,
                    'message' => 'Llamada aceptada - conectando videollamada'
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Error al aceptar llamada: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al conectar videollamada'
                ]);
            }

        } else if ($action === 'reject') {
            // Rechazar llamada
            $this->Calls_system_model->update_call_status($call_id, 'rejected', [
                'end_time' => date('Y-m-d H:i:s')
            ]);

            // Liberar usuarios
            $this->Calls_system_model->set_user_status($call->caller_id, 'available');
            $this->Calls_system_model->set_user_status($call->receiver_id, 'available');

            return $this->response->setJSON([
                'success' => true,
                'action' => 'rejected',
                'message' => 'Llamada rechazada'
            ]);
        }
    }

    /**
     * API: Finalizar llamada
     */
    /*public function end_call()
    {
        $call_id = $this->request->getPost('call_id');

        if (!$call_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de llamada requerido'
            ]);
        }

        $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
        
        if (!$call) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Llamada no encontrada'
            ]);
        }

        // Calcular duración
        $start = strtotime($call->start_time);
        $end = time();
        $duration = $end - $start;

        $this->Calls_system_model->update_call_status($call_id, 'completed', [
            'end_time' => date('Y-m-d H:i:s'),
            'call_duration' => $duration
        ]);

        // Liberar usuarios
        $this->Calls_system_model->set_user_status($call->caller_id, 'available');
        $this->Calls_system_model->set_user_status($call->receiver_id, 'available');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Llamada finalizada'
        ]);
    }*/

    public function end_call()
    {
        $call_id = $this->request->getPost('call_id');

        if (!$call_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de llamada requerido'
            ]);
        }

        $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
        
        if (!$call) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Llamada no encontrada'
            ]);
        }

        // ✅ VERIFICAR QUE EL USUARIO ESTÉ AUTORIZADO PARA TERMINAR LA LLAMADA
        $user_id = $this->login_user->id;
        if ($call->caller_id != $user_id && $call->receiver_id != $user_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado para terminar esta llamada'
            ]);
        }

        // ✅ DETERMINAR EL ESTADO FINAL SEGÚN LA SITUACIÓN
        $final_status = 'completed';
        $message = 'Llamada finalizada';

        // Si la llamada está timbrando (ringing) y la termina el caller, es "no contestada"
        if ($call->status === 'ringing' && $call->caller_id == $user_id) {
            $final_status = 'missed';
            $message = 'Llamada no contestada';
        }
        // Si la llamada está timbrando y la termina el receiver, es "rechazada"
        else if ($call->status === 'ringing' && $call->receiver_id == $user_id) {
            $final_status = 'rejected';
            $message = 'Llamada rechazada';
        }
        // Si está en progreso, es una llamada completada normal
        else if ($call->status === 'in_progress') {
            $final_status = 'completed';
            $message = 'Llamada finalizada';
        }

        // Calcular duración si hay tiempo de inicio
        $duration = 0;
        if ($call->start_time) {
            $start = strtotime($call->start_time);
            $end = time();
            $duration = $end - $start;
        }

        // ✅ ACTUALIZAR EL ESTADO DE LA LLAMADA
        $update_data = [
            'end_time' => date('Y-m-d H:i:s'),
            'call_duration' => $duration
        ];

        $this->Calls_system_model->update_call_status($call_id, $final_status, $update_data);

        // ✅ LIBERAR A AMBOS USUARIOS
        $this->Calls_system_model->set_user_status($call->caller_id, 'available');
        $this->Calls_system_model->set_user_status($call->receiver_id, 'available');

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'call_status' => $final_status
        ]);
    }

        /**
    * API: Verificar llamadas entrantes MEJORADO
    */
    /*public function check_incoming_calls()
    {
        $user_id = $this->login_user->id;
        $calls = $this->Calls_system_model->get_pending_calls_for_user($user_id);
        $result = [];

        if (!$calls) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al consultar llamadas',
                'incoming_calls' => []
            ]);
        }

        foreach ($calls->getResult() as $call) {
            // Verificar timeout (30 segundos)
            $created_time = strtotime($call->created_at);
            $current_time = time();
            
            if (($current_time - $created_time) > 30) {
                // Marcar como perdida por timeout
                $this->Calls_system_model->update_call_status($call->id, 'missed', [
                    'end_time' => date('Y-m-d H:i:s')
                ]);
                
                // Liberar usuarios
                $this->Calls_system_model->set_user_status($call->caller_id, 'available');
                $this->Calls_system_model->set_user_status($call->receiver_id, 'available');
                continue;
            }

            $result[] = [
                'call_id' => $call->id,
                'caller_name' => $call->caller_name,
                'caller_image' => get_avatar($call->caller_image),
                'time_remaining' => 30 - ($current_time - $created_time),
                'meeting_id' => $call->meeting_id ?? null
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'incoming_calls' => $result
        ]);
    }*/

    public function check_incoming_calls()
    {
        $user_id = $this->login_user->id;
        $calls   = $this->Calls_system_model->get_pending_calls_for_user($user_id);
        $result  = [];

        if (!$calls) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al consultar llamadas',
                'incoming_calls' => []
            ]);
        }

        // 1) Timeout configurable (segundos). Si no existe el setting, usa 60.
        $ringTimeout = (int) (get_setting('call_ring_timeout') ?: 60);

        foreach ($calls->getResult() as $call) {
            $created_time = strtotime($call->created_at);
            $current_time = time();
            $elapsed      = $current_time - $created_time;  // 2) calcular una vez

            // 3) Expirar si supera el timeout
            if ($elapsed > $ringTimeout) {
                $this->Calls_system_model->update_call_status($call->id, 'missed', [
                    'end_time' => date('Y-m-d H:i:s')
                ]);

                // Liberar usuarios
                $this->Calls_system_model->set_user_status($call->caller_id, 'available');
                $this->Calls_system_model->set_user_status($call->receiver_id, 'available');
                continue;
            }

            $timeRemaining = max(0, $ringTimeout - $elapsed); // nunca negativo

            $result[] = [
                'call_id'        => $call->id,
                'caller_name'    => $call->caller_name,
                'caller_image'   => get_avatar($call->caller_image),
                'time_remaining' => $timeRemaining,
                'meeting_id'     => $call->meeting_id ?? null
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'incoming_calls' => $result
        ]);
    }

        /**
    * NUEVA: API para verificar estado de llamada activa
    */
    public function check_call_status()
    {
        $call_id = $this->request->getPost('call_id');
        
        if (!$call_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de llamada requerido'
            ]);
        }

        $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
        
        if (!$call) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Llamada no encontrada'
            ]);
        }

        // Verificar si el usuario está autorizado para esta llamada
        $user_id = $this->login_user->id;
        if ($call->caller_id != $user_id && $call->receiver_id != $user_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ]);
        }

        $response_data = [
            'success' => true,
            'call_status' => $call->status,
            'call_id' => $call->id
        ];

        // Si la llamada fue aceptada, incluir URL de VSee
        if ($call->status === 'in_progress' && !empty($call->meeting_id)) {
            $response_data['vsee_url'] = $this->get_vsee_join_url($call->meeting_id, $user_id);
            $response_data['meeting_id'] = $call->meeting_id;
        }

        return $this->response->setJSON($response_data);
    }


    /**
    * Generar sesión VSee MEJORADO con add_walkin
    */
    /*private function generate_vsee_session($caller_id, $receiver_id)
    {
        try {
            // Obtener datos VSee de ambos usuarios
            $caller_vsee = $this->Calls_system_model->get_vsee_user_data($caller_id);
            $receiver_vsee = $this->Calls_system_model->get_vsee_user_data($receiver_id);
            log_message('error', 'Data caller: ' . print_r($caller_vsee, true));
            log_message('error', 'Data receiver: ' . print_r($receiver_vsee, true));

            if (!$caller_vsee || !$receiver_vsee) {
                return [
                    'success' => false,
                    'message' => 'Usuarios no tienen credenciales VSee configuradas'
                ];
            }

            // Crear cliente VSee
            $vsee_client = new \App\Libraries\VseeClient();
            
            // Crear intake para el paciente (receiver en este caso actúa como paciente)
            $intake_data = [
                "provider_id" => $caller_vsee->vsee_id,
                "type" => 1,
                "room_code" => $caller_vsee->vsee_room ?? 'npxca',
                "member_id" => $receiver_vsee->vsee_id
            ];
            
            $intake_response = $vsee_client->createIntake($intake_data);
            
            if (!isset($intake_response['data']['id'])) {
                //log_message('error', 'Error creando intake VSee: ' . print_r($intake_response, true));
                return [
                    'success' => false,
                    'message' => 'Error al crear sala de espera VSee'
                ];
            }

            $visit_id = $intake_response['data']['id'];

            // Crear la sesión walk-in
            $walkin_data = [
                "provider_id" => $caller_vsee->vsee_id,
                "intake_id" => $visit_id,
                "room_code" => $caller_vsee->vsee_room ?? 'npxca'
            ];

            //log_message('error', 'Data crear reunion: ' . print_r($walkin_data, true));
            
            $walkin_response = $vsee_client->add_walkin($walkin_data);
            


            if (!isset($walkin_response['data']['meeting']['meeting_id'])) {
                //log_message('error', 'Error creando walk-in VSee: ' . print_r($walkin_response, true));
                return [
                    'success' => false,
                    'message' => 'Error al crear conferencia VSee'
                ];
            }

            $meeting_id = $walkin_response['data']['meeting']['meeting_id'];
            $data = [
                'success' => true,
                'session_id' => $visit_id,
                'meeting_id' => $meeting_id,
                'intake_id' => $visit_id
            ];
            log_message('error', 'Walkin final: ' . print_r($data, true));



            return [
                'success' => true,
                'session_id' => $visit_id,
                'meeting_id' => $meeting_id,
                'intake_id' => $visit_id
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en generate_vsee_session: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al crear sesión VSee'
            ];
        }
    }*/

    private function generate_vsee_session($caller_id, $receiver_id)
    {
        try {
            // Obtener datos VSee de ambos usuarios
            $caller_vsee = $this->Calls_system_model->get_vsee_user_data($caller_id);
            $receiver_vsee = $this->Calls_system_model->get_vsee_user_data($receiver_id);

            if (!$caller_vsee || !$receiver_vsee) {
                return [
                    'success' => false,
                    'message' => 'Usuarios no tienen credenciales VSee configuradas'
                ];
            }

            // ✅ USAR SOLO LAS CREDENCIALES DEL PROVIDER (CALLER) PARA CREAR LA SESIÓN
            $vsee_client = new \App\Libraries\VseeClient();
            
            // Crear intake usando las credenciales del caller como provider
            $intake_data = [
                "provider_id" => $caller_vsee->vsee_id,
                "type" => 1,
                "room_code" => $caller_vsee->vsee_room ?? 'npxca',
                "member_id" => $receiver_vsee->vsee_id  // ✅ USAR ID DEL RECEIVER
            ];
            
            $intake_response = $vsee_client->createIntake($intake_data);
            
            if (!isset($intake_response['data']['id'])) {
                return [
                    'success' => false,
                    'message' => 'Error al crear sala de espera VSee'
                ];
            }

            $visit_id = $intake_response['data']['id'];

            // Crear la sesión walk-in
            $walkin_data = [
                "provider_id" => $caller_vsee->vsee_id,
                "intake_id" => $visit_id,
                "room_code" => $caller_vsee->vsee_room ?? 'npxca'
            ];
            
            $walkin_response = $vsee_client->add_walkin($walkin_data);

            if (!isset($walkin_response['data']['meeting']['meeting_id'])) {
                return [
                    'success' => false,
                    'message' => 'Error al crear conferencia VSee'
                ];
            }

            $meeting_id = $walkin_response['data']['meeting']['meeting_id'];

            return [
                'success' => true,
                'session_id' => $visit_id,
                'meeting_id' => $meeting_id,
                'intake_id' => $visit_id,
                // ✅ GUARDAR LAS CREDENCIALES DE AMBOS USUARIOS
                'caller_credentials' => [
                    'vsee_username' => $caller_vsee->vsee_username,
                    'vsee_token' => $caller_vsee->vsee_token
                ],
                'receiver_credentials' => [
                    'vsee_username' => $receiver_vsee->vsee_username,
                    'vsee_token' => $receiver_vsee->vsee_token
                ]
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en generate_vsee_session: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al crear sesión VSee'
            ];
        }
    }
    

    /**
    * Obtener URL de join para VSee
    */
    /*private function get_vsee_join_url($meeting_id, $user_id)
    {
        $user_vsee = $this->Calls_system_model->get_vsee_user_data($user_id);
        
        if (!$user_vsee) {
            return null;
        }

        // Construir URL con parámetros de autenticación
        $base_url = get_setting('vsee_base_url') ?: 'https://vsee.com';
        
        $params = [
            'meeting_id' => $meeting_id,
            'username' => $user_vsee->vsee_username,
            'token' => $user_vsee->vsee_token,
            'display_name' => $this->get_user_display_name($user_id)
        ];
        
        return $base_url . '/join?' . http_build_query($params);
    }*/

    /*private function get_vsee_join_url($meeting_id, $user_id)
    {
        $user_vsee = $this->Calls_system_model->get_vsee_user_data($user_id);
        
        if (!$user_vsee) {
            return null;
        }
        
        // URL base para llamadas internas
        $base_url = 'https://teleconsulta.clinicahispanarubymed.com';
        
        $params = [
            'meeting_id' => $meeting_id,
            'username' => $user_vsee->vsee_username,
            'token' => $user_vsee->vsee_token,
            'display_name' => $this->get_user_display_name($user_id)
        ];
        
        return $base_url . '/llamada_interna.html?' . http_build_query($params);
    }*/

    private function get_vsee_join_url($meeting_id, $user_id)
    {
        // ✅ OBTENER LAS CREDENCIALES ESPECÍFICAS DEL USUARIO
        $user_vsee = $this->Calls_system_model->get_vsee_user_data($user_id);
        
        if (!$user_vsee) {
            log_message('error', 'No se encontraron credenciales VSee para usuario: ' . $user_id);
            return null;
        }
        
        // ✅ VERIFICAR QUE LAS CREDENCIALES SEAN DEL USUARIO CORRECTO
        if (empty($user_vsee->vsee_username) || empty($user_vsee->vsee_token)) {
            log_message('error', 'Credenciales VSee incompletas para usuario: ' . $user_id);
            return null;
        }
        
        $base_url = 'https://teleconsulta.clinicahispanarubymed.com';
        
        $params = [
            'meeting_id' => $meeting_id,
            'username' => $user_vsee->vsee_username,    // ✅ CREDENCIALES ESPECÍFICAS DEL USUARIO
            'token' => $user_vsee->vsee_token,          // ✅ CREDENCIALES ESPECÍFICAS DEL USUARIO
            'display_name' => $this->get_user_display_name($user_id),
            'user_id' => $user_id 
        ];
        
        // ✅ LOG ESPECÍFICO POR USUARIO
        log_message('info', 'URL VSee para usuario ' . $user_id . ' (' . $this->get_user_display_name($user_id) . '): username=' . $user_vsee->vsee_username . ', meeting_id=' . $meeting_id);
        
        return $base_url . '/llamada_interna.html?' . http_build_query($params);
    }

    /**
    * Obtener nombre para mostrar del usuario
    */
    private function get_user_display_name($user_id)
    {
        $user = $this->Users_model->get_one($user_id);
        if ($user) {
            return $user->first_name . ' ' . $user->last_name;
        }
        return 'Usuario';
    }


    /**
     * API: Obtener estado actual del usuario
     */
    public function get_user_status()
    {
        $user_id = $this->login_user->id;
        $status = $this->Calls_system_model->get_user_status($user_id);

        return $this->response->setJSON([
            'success' => true,
            'status' => $status->status,
            'current_call_id' => $status->current_call_id ?? null
        ]);
    }

    /**
     * Redirigir a videollamada VSee
     */
    public function video_call($call_id)
    {
        $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
        
        if (!$call || !$call->vsee_session_id) {
            show_404();
        }

        // Redirigir a VSee con tu lógica existente
        $vsee_url = $this->get_vsee_url($call->vsee_session_id);
        return redirect()->to($vsee_url);
    }

    /**
     * Generar sesión VSee (integrar con tu lógica existente)
     */
    /**
    * Obtener datos VSee del usuario
    */
    

    /**
    * Obtener URL de VSee (integrar con tu lógica existente)
    */
    private function get_vsee_url($session_id)
    {
        // Usar tu URL base de VSee existente
        $base_vsee_url = get_setting('vsee_base_url') ?: 'https://vsee.com';
        return $base_vsee_url . '/join/' . $session_id;
    }

    /**
    * API: Obtener usuarios agrupados por clínicas
    */
    public function get_users_grouped_by_clinics()
    {
        try {
            $users_data = $this->Calls_system_model->get_users_grouped_by_clinics($this->login_user->id);

            if (!$users_data) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al consultar usuarios agrupados',
                    'data' => []
                ]);
            }

            $result = [
                'clinics' => [],
                'administrative_staff' => []
            ];

            // Procesar usuarios médicos agrupados por clínica
            $current_clinic = null;
            $clinic_users = [];

            foreach ($users_data['medical_users']->getResult() as $user) {
                if ($current_clinic !== $user->clinic_id) {
                    if ($current_clinic !== null) {
                        $result['clinics'][] = [
                            'clinic_id' => $current_clinic,
                            'clinic_name' => $clinic_name,
                            'users' => $clinic_users
                        ];
                    }
                    $current_clinic = $user->clinic_id;
                    $clinic_name = $user->clinic_name;
                    $clinic_users = [];
                }

                // Verificar si el usuario está en línea
                $is_online = is_online_user($user->last_online);
                $is_call_available = in_array($user->call_status, ['available']);
                
                $clinic_users[] = [
                    'id' => $user->user_id,
                    'name' => $user->full_name,
                    'image' => get_avatar($user->image),
                    'role_id' => $user->role_id,
                    'status' => $user->call_status,
                    'available' => $is_online && $is_call_available, // Solo disponible si está en línea Y disponible para llamadas
                    'is_online' => $is_online,
                    'last_online' => $user->last_online,
                    'user_type' => 'medical'
                ];
            }

            if ($current_clinic !== null) {
                $result['clinics'][] = [
                    'clinic_id' => $current_clinic,
                    'clinic_name' => $clinic_name,
                    'users' => $clinic_users
                ];
            }

            // Procesar personal administrativo
            foreach ($users_data['administrative_users']->getResult() as $user) {
                // Verificar si el usuario está en línea
                $is_online = is_online_user($user->last_online);
                $is_call_available = in_array($user->call_status, ['available']);
                
                $result['administrative_staff'][] = [
                    'id' => $user->user_id,
                    'name' => $user->full_name,
                    'image' => get_avatar($user->image),
                    'role_id' => $user->role_id,
                    'status' => $user->call_status,
                    'available' => $is_online && $is_call_available, // Solo disponible si está en línea Y disponible para llamadas
                    'is_online' => $is_online,
                    'last_online' => $user->last_online,
                    'user_type' => 'administrative',
                    'clinic_names' => $user->clinic_names,
                    'clinic_count' => $user->clinic_count
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }


    public function get_call_history()
    {
        try {
            $user_id = $this->login_user->id;
            $page = (int)($this->request->getGet('page') ?? 1);
            $limit = (int)($this->request->getGet('limit') ?? 20);
            $status_filter = $this->request->getGet('status') ?? 'all';
            $date_filter = $this->request->getGet('date_range') ?? 'all';
            $search = $this->request->getGet('search') ?? '';

            $options = [
                'user_id' => $user_id,
                'page' => $page,
                'limit' => $limit,
                'status_filter' => $status_filter,
                'date_filter' => $date_filter,
                'search' => $search
            ];

            $result = $this->Calls_system_model->get_user_call_history($options);

            if (!$result) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al consultar historial',
                    'data' => []
                ]);
            }

            $calls = [];
            foreach ($result['calls']->getResult() as $call) {
                $is_caller = ($call->caller_id == $user_id);
                $other_user_name = $is_caller ? $call->receiver_name : $call->caller_name;
                $other_user_image = $is_caller ? $call->receiver_image : $call->caller_image;

                // Calcular duración legible
                $duration_text = '';
                if ($call->call_duration && $call->call_duration > 0) {
                    $minutes = floor($call->call_duration / 60);
                    $seconds = $call->call_duration % 60;
                    $duration_text = sprintf('%02d:%02d', $minutes, $seconds);
                }

                // Determinar texto del estado
                $status_info = $this->getCallStatusInfo($call->status, $is_caller);

                $calls[] = [
                    'id' => $call->id,
                    'other_user_name' => $other_user_name,
                    'other_user_image' => get_avatar($other_user_image),
                    'is_caller' => $is_caller,
                    'call_type' => $is_caller ? 'outgoing' : 'incoming',
                    'status' => $call->status,
                    'status_text' => $status_info['text'],
                    'status_icon' => $status_info['icon'],
                    'status_color' => $status_info['color'],
                    'start_time' => $call->start_time,
                    'end_time' => $call->end_time,
                    'duration' => $call->call_duration,
                    'duration_text' => $duration_text,
                    'date_formatted' => $this->formatCallDate($call->start_time),
                    'time_formatted' => date('h:i A', strtotime($call->start_time))
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'calls' => $calls,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $result['total_pages'],
                        'total_calls' => $result['total_calls'],
                        'has_more' => $page < $result['total_pages']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en get_call_history: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor',
                'data' => []
            ]);
        }
    }

    private function getCallStatusInfo($status, $is_caller)
    {
        $status_map = [
            'completed' => [
                'text' => 'Completada',
                'icon' => 'fas fa-check-circle',
                'color' => '#10b981'
            ],
            'rejected' => [
                'text' => $is_caller ? 'Rechazada' : 'Rechazaste',
                'icon' => 'fas fa-times-circle',
                'color' => '#ef4444'
            ],
            'missed' => [
                'text' => $is_caller ? 'No contestada' : 'Perdida',
                'icon' => 'fas fa-phone-slash',
                'color' => '#f59e0b'
            ],
            'failed' => [
                'text' => 'Fallida',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#ef4444'
            ]
        ];

        return $status_map[$status] ?? [
            'text' => ucfirst($status),
            'icon' => 'fas fa-info-circle',
            'color' => '#6b7280'
        ];
    }

    private function formatCallDate($datetime)
    {
        $call_date = strtotime($datetime);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');

        if ($call_date >= $today) {
            return 'Hoy';
        } elseif ($call_date >= $yesterday) {
            return 'Ayer';
        } elseif ($call_date >= strtotime('-7 days')) {
            return date('l', $call_date); // Día de la semana
        } else {
            return date('d/m/Y', $call_date);
        }
    }

    public function check_call_status_by_meeting()
    {
        $meeting_id = $this->request->getGet('meeting_id');
        
        if (!$meeting_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'meeting_id requerido'
            ]);
        }

        $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
        
        if (!$call) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Llamada no encontrada'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'call_status' => $call->status,
            'call_id' => $call->id,
            'meeting_id' => $meeting_id
        ]);
    }

    /**
    * API: Actualizar estado de usuario a "in_call" al entrar a videollamada
    */
    public function set_user_in_call()
    {
        try {
            $user_id = $this->request->getGet('user_id');
            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$user_id || !$meeting_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parámetros requeridos: user_id, meeting_id'
                ]);
            }
            
            // Verificar que el usuario existe
            $user = $this->Users_model->get_one($user_id);
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
            }
            
            // Buscar si existe una llamada con este meeting_id
            $call = $this->Calls_system_model->get_details(['meeting_id' => $meeting_id])->getRow();
            $call_id = $call ? $call->id : null;
            
            // Actualizar estado a "in_call"
            $result = $this->Calls_system_model->set_user_status($user_id, 'in_call', $call_id);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Usuario marcado como en llamada',
                    'user_id' => $user_id,
                    'status' => 'in_call'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al actualizar estado del usuario'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en set_user_in_call: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
    * API: Liberar estado de usuario al salir de videollamada
    */
    public function set_user_available()
    {
        try {
            $user_id = $this->request->getGet('user_id');
            $meeting_id = $this->request->getGet('meeting_id');
            
            if (!$user_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parámetro requerido: user_id'
                ]);
            }
            
            // Verificar que el usuario existe
            $user = $this->Users_model->get_one($user_id);
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
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
                        $this->Calls_system_model->set_user_status($call->caller_id, 'available');
                    }
                    if ($call->receiver_id) {
                        $this->Calls_system_model->set_user_status($call->receiver_id, 'available');
                    }
                }
            } else {
                // Solo liberar este usuario específico
                $this->Calls_system_model->set_user_status($user_id, 'available');
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuario marcado como disponible',
                'user_id' => $user_id,
                'status' => 'available'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en set_user_available: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }

    /**
    * API: Rechazar llamada por popup bloqueado (específico para modal)
    */
    public function reject_call_popup_blocked()
    {
        try {
            $call_id = $this->request->getPost('call_id');
            $reason = $this->request->getPost('reason') ?? 'popup_blocked';
            
            if (!$call_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID de llamada requerido'
                ]);
            }

            $call = $this->Calls_system_model->get_details(['id' => $call_id])->getRow();
            
            if (!$call) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Llamada no encontrada'
                ]);
            }

            // Verificar que el usuario esté autorizado (debe ser el receiver)
            if ($call->receiver_id != $this->login_user->id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No autorizado para rechazar esta llamada'
                ]);
            }

            // ACTUALIZAR la llamada existente como rechazada (NO crear nueva)
            $update_data = [
                'end_time' => date('Y-m-d H:i:s'),
            ];
            
            $this->Calls_system_model->update_call_status($call_id, 'rejected', $update_data);

            // Liberar AMBOS usuarios
            $this->Calls_system_model->set_user_status($call->caller_id, 'available');
            $this->Calls_system_model->set_user_status($call->receiver_id, 'available');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Llamada rechazada por ventanas emergentes bloqueadas',
                'action' => 'popup_blocked_rejection'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en reject_call_popup_blocked: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
    }


    
    /**
     * API: Obtener llamadas perdidas de video en las últimas 24 horas
     */
    public function get_missed_video_calls_24h()
    {
        try {
            // Verificar que el usuario esté logueado
            if (!$this->login_user || !$this->login_user->id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no válido',
                    'data' => []
                ]);
            }

            // Obtener las llamadas perdidas del usuario
            $missed_calls = $this->Calls_system_model->get_missed_video_calls_24h($this->login_user->id);

            // Formatear los datos para la respuesta
            $formatted_calls = [];
            foreach ($missed_calls as $call) {
                $formatted_calls[] = [
                    'id' => $call['id'],
                    'caller_id' => $call['caller_id'],
                    'receiver_id' => $call['receiver_id'],
                    'status' => $call['status'],
                    'start_time' => $call['start_time'],
                    'start_time_formatted' => date('M j, Y g:i A', strtotime($call['start_time'])),
                    'answer_time' => $call['answer_time'],
                    'end_time' => $call['end_time'],
                    'call_duration' => $call['call_duration'],
                    'vsee_session_id' => $call['vsee_session_id'],
                    'meeting_id' => $call['meeting_id'],
                    'error_message' => $call['error_message'],
                    'created_at' => $call['created_at'],
                    'updated_at' => $call['updated_at'],
                    'missed_call_acknowledged' => $call['missed_call_acknowledged'],
                    'caller' => [
                        'first_name' => $call['caller_first_name'],
                        'last_name' => $call['caller_last_name'],
                        'image' => get_avatar($call['caller_image'])
                    ],
                    'receiver' => [
                        'first_name' => $call['receiver_first_name'],
                        'last_name' => $call['receiver_last_name'],
                        'image' => get_avatar($call['receiver_image'])
                    ]
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Llamadas perdidas obtenidas correctamente',
                'data' => $formatted_calls,
                'count' => count($formatted_calls)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en get_missed_video_calls_24h: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor al obtener llamadas perdidas',
                'data' => []
            ]);
        }
    }

    /**
     * API: Aceptar una llamada perdida
     */
    public function acknowledge_missed_call()
    {
        try {
            // Verificar que el usuario esté logueado
            if (!$this->login_user || !$this->login_user->id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no válido'
                ]);
            }

            // Obtener el ID de la llamada desde la petición
            $call_id = null;
            
            // Try different methods to get the call_id
            if ($this->request->getMethod() === 'post') {
                $call_id = $this->request->getPost('call_id');
            }
            
            // If not found in POST, try JSON
            if (!$call_id) {
                $json_data = $this->request->getJSON(true);
                if ($json_data && isset($json_data['call_id'])) {
                    $call_id = $json_data['call_id'];
                }
            }
            
            // If still not found, try raw input
            if (!$call_id) {
                $raw_input = $this->request->getBody();
                if ($raw_input) {
                    $decoded = json_decode($raw_input, true);
                    if ($decoded && isset($decoded['call_id'])) {
                        $call_id = $decoded['call_id'];
                    }
                }
            }

            if (!$call_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID de llamada requerido'
                ]);
            }

            // Validar que el ID sea numérico
            if (!is_numeric($call_id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID de llamada inválido'
                ]);
            }

            // Llamar al modelo para actualizar el estado
            $result = $this->Calls_system_model->acknowledge_missed_call($call_id);

            if ($result['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Llamada aceptada correctamente',
                    'action' => $result['action']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'action' => $result['action']
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en acknowledge_missed_call: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error del servidor al aceptar llamada'
            ]);
        }
    }
}