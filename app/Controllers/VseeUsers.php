<?php

namespace App\Controllers;

use App\Models\VseeUsers_model;
use App\Models\Users_model;
use App\Models\Clinic_model;
use App\Libraries\VseeClient;
use Exception;

class VseeUsers extends Security_Controller
{
    protected $VseeUsers_model;
    protected $Clinic_model;

    public function __construct()
    {
        parent::__construct();

        $this->VseeUsers_model = new VseeUsers_model();
        $this->Users_model     = new Users_model();
        $this->Clinic_model    = new Clinic_model();

        $this->access_only_admin(); // Si deseas validar permisos, puedes reemplazar o quitar esto
    }

    public function index()
    {
                    // PASO 1: Ejecutar auto-registro antes de la sincronización individual
            $this->run_auto_register_background();
        return $this->template->rander('vsee/assign_users');
    }

    public function list_data()
    {
        try {
            $data = $this->VseeUsers_model->get_full_data();
            $result = [];

            foreach ($data as $link) {
                $isChecked = $link->state ? 'checked' : '';

                // Etiqueta visual para el tipo de acción
                $actionBadge = match ($link->action) {
                    'clinic'   => '<span class="d-none">clinic</span><span class="badge bg-danger text-white"><i class="fas fa-hospital"></i> Clínica</span>',
                    'provider' => '<span class="d-none">provider</span><span class="badge bg-info text-white"><i class="fas fa-user-md"></i> Provider</span>',
                    'user'     => '<span class="d-none">user</span><span class="badge bg-primary text-white"><i class="fas fa-user"></i> Usuario</span>',
                    default    => '<span class="badge bg-secondary text-white">N/A</span>',
                };

                $isLinked = !empty($link->vsee_id) && !empty($link->vsee_username) && !empty($link->vsee_token);
                $isDisabled = $isLinked ? 'checked' : 'disabled';

                $vseeIcon = $link->vsee_id
                    ? '<i class="fas fa-check-circle fa-2x text-success" title="Ya vinculado con VSee"></i>'
                    : '<i class="fas fa-link fa-2x cursor-pointer class-link" title="Sincronizar con VSee" onclick="syncWithVsee(' . $link->id . ')"></i>';

                $result[] = [
                    $link->id,
                    $link->user_name,
                    $link->clinic_name ?? '<em>—</em>',
                    $actionBadge,
                    '
                <div class="estado-container">
                    <label class="switch">
                        <input type="checkbox" onchange="toggleState(' . $link->id . ')" ' . $isChecked . '>
                        <span class="slider"></span>
                    </label>
                    <span class="estado-text">' . ($link->state ? 'Autenticación Activa' : 'Autenticación Inactiva') . '</span>
                </div>',
                    '
                <div class="estado-container">
                    <label class="switch switch-vsee">
                        <input type="checkbox" ' . $isDisabled . ' '. ' disabled onchange="toggleState(' . $link->id . ')">
                        <span class="slider"></span>
                    </label>
                    <span class="estado-text">' . $vseeIcon . '</span>
                </div>
                ',
                    '<button class="btn-button btn-button-outline-danger" onclick="openDeleteModal(' . $link->id . ')">
                    <i class="fas fa-trash-alt"></i>
                </button>'
                ];
            }

            return $this->response->setJSON(['data' => $result]);
        } catch (Exception $e) {
            log_message('error', 'list_data vsee error: ' . $e->getMessage());
            return $this->response->setJSON(['data' => []]);
        }
    }


    public function form_modal()
    {
        try {
            // Listado de usuarios y clínicas usando tus métodos personalizados
            $users   = $this->Users_model->get_dropdown_list(['first_name', 'last_name']);;
            $clinics = $this->Clinic_model->get_dropdown_list(['name']); // O puedes pasar $login_user->id si es por usuario

            return $this->template->view('vsee/modals/modal_form_assign', [
                'users'   => $users,
                'clinics' => $clinics,
            ]);
        } catch (Exception $e) {
            log_message('error', 'form modal vseee error: ' . $e->getMessage());
        }
    }

    public function save()
    {
        try {
            $id = $this->request->getPost('id');
            $userId = $this->request->getPost('user_id');
            $action = $this->request->getPost('action');
            $clinicId = $action === 'clinic' ? $this->request->getPost('clinic_id') : null;

            $data = [
                'user_id'   => $userId,
                'clinic_id' => $clinicId,
                'action'    => $action
            ];

            // Validar si ya existe un registro para este user_id (sin importar clinic_id o action)
            if ($this->VseeUsers_model->userHasAssignment($userId, $id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Este usuario ya tiene un enlace asignado.'
                ]);
            }

            $this->VseeUsers_model->ci_save($data, $id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Guardado correctamente'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'VseeUsers::save error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar enlace.'
            ]);
        }
    }


    public function delete()
    {
        $id = $this->request->getPost('id');
        $this->VseeUsers_model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function delete_modal()
    {
        try {
            $id = $this->request->getGet('id');
            $link = $this->VseeUsers_model->get_full_data_by_id($id); // Debes tener este método

            if (!$link) {
                return $this->response->setStatusCode(404)->setBody('Enlace no encontrado.');
            }

            return $this->template->view('vsee/modals/modal_eliminar_link', [
                'link' => $link
            ]);
        } catch (Exception $e) {
            log_message('error', 'Delete error: ' . $e->getMessage());
        }
    }

    public function toggle_state()
    {
        try {
            $id = $this->request->getPost('id');
            $model = $this->VseeUsers_model->get_one($id);

            if (!$model) {
                return $this->response->setJSON(['success' => false, 'message' => 'Enlace no encontrado.']);
            }

            $new_state = $model->state ? 0 : 1;
            $data = ['state' => $new_state];
            $this->VseeUsers_model->ci_save($data, $id);
            //$this->VseeUsers_model->ci_save(['state' => $new_state], $id);

            return $this->response->setJSON(['success' => true, 'new_state' => $new_state]);
        } catch (Exception $e) {
            log_message('error', 'toggle state error: ' . $e->getMessage());
        }
    }

    public function sync()
    {
        try {


            $id = $this->request->getPost('id');
            $link = $this->VseeUsers_model->get_one($id);

            if (!$link) {
                return $this->response->setJSON(['success' => false, 'message' => 'Enlace no encontrado.']);
            }

            // Si es CLINIC y ya hay otro con datos de VSee, copiar esos datos
            if ($link->action === 'clinic' && $link->clinic_id) {
                $existing = $this->VseeUsers_model->get_by_clinic($link->clinic_id, $id);
                if ($existing && $existing->vsee_id && $existing->vsee_username && $existing->vsee_token) {
                    $copiedData = [
                        'vsee_id'       => $existing->vsee_id,
                        'vsee_username' => $existing->vsee_username,
                        'vsee_token'    => $existing->vsee_token,
                    ];
                    $this->VseeUsers_model->ci_save($copiedData, $id);
                    return $this->response->setJSON(['success' => true, 'copied' => true]);
                }
            }

            // Recolectar datos de usuario o clínica
            $user   = $this->Users_model->get_one($link->user_id);
            $clinic = $link->clinic_id ? $this->Clinic_model->get_one($link->clinic_id) : null;

            //$type = $link->action === 'provider' ? 400 : 200;
            $type = 400;
            /*if($link->action != 'provider'){
 
                /*$userData = [
                    "first_name"   => $clinic ? $clinic->name : $user->first_name,
                    "last_name"    => $clinic ? 'Rubymed' : $user->last_name,
                    "dob"          => $user->dob ?? '1990-01-01',
                    "type"         => (string)$type,
                    "code"         => "1000002331",
                    "gender"       => $user->gender ?? 'otro',
                    "street_addr"  => $clinic->address ?? ($user->address ?? 'Sin dirección'),
                    "city"         => "100",
                    "state"        => "CO",
                    "zip"          => "110111",
                    "phone"        => $clinic->phone ?? ($user->phone ?? "3000000000"),
                    "email"        => $clinic->email ?? $user->email,
                ];
            }
            else{  
            }*/

            $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);

                 $userData = [
                    "first_name"   => $user->first_name,
                    "last_name"    => $user->last_name,
                    "dob"          => $user->dob ?? '2002-05-20',
                    "type"         => $type,
                    "code"         => $this->generateUniqueVseeId(),
                    "email"        => $user->email ? $user->email : '',
                ];
                $roomData = [
                    "domain" => "rubymed.vseepreview.com",
                    "slug" => "s-" . strtolower($user->first_name) . "-" . $random,
                    "name" => $user->first_name . " ". $user->last_name
                ];
            

              

            // Llamar a VseeClient
            $vsee = new VseeClient();
            $res = $vsee->createUserSSO($userData);
            $room = $vsee->createRoom($roomData);

            log_message('debug', 'Respuesta completa de VSee: ' . json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if (!isset($res['data']) || !isset($res['data']['vsee'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Respuesta inválida de VSee.']);
            }

            // Guardar la información retornada
            
            $dataToSave = [
                'vsee_id'       => $res['data']['id'],
                'vsee_username' => $res['data']['vsee']['id'],
                'vsee_token'    => $res['data']['vsee']['token'],
                'vsee_room'    => $room['data']['code']
            ];
            $this->VseeUsers_model->ci_save($dataToSave, $id);

            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $e) {
            log_message('error', 'Sync VSee error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno al sincronizar.']);
        }
    }

    private function generateUniqueVseeId()
    {
        do {
            // Genera un número aleatorio de 10 dígitos
            $code = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

            // Verifica si ya existe en la tabla vsee_users
            $exists = $this->VseeUsers_model
                ->where('vsee_id', $code)
                ->where('deleted', 0)
                ->countAllResults() > 0;

        } while ($exists);

        return $code;
    }

    /**
    * Registra automáticamente todos los usuarios de Rubymed que no estén en vsee_users
    */
    public function auto_register_all_users()
    {
        try {
            // Obtener todos los usuarios activos de Rubymed
            $all_users = $this->Users_model->get_all_where(['deleted' => 0]);
            
            if (!$all_users) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron usuarios para procesar.'
                ]);
            }

            $processed = 0;
            $registered = 0;
            $errors = [];

            foreach ($all_users as $user) {
                $processed++;
                
                try {
                    // Verificar si el usuario ya está registrado en vsee_users
                    $existing = $this->VseeUsers_model->get_all_where([
                        'user_id' => $user->id,
                        'deleted' => 0
                    ]);

                    // Si no existe, crear el registro y sincronizar
                    if (!$existing) {
                        // Crear el registro base en vsee_users
                        $data = [
                            'user_id'   => $user->id,
                            'clinic_id' => null, // o asignar una clínica por defecto si es necesario
                            'action'    => 'user', // tipo por defecto
                            'state'     => 1 // activo por defecto
                        ];

                        $new_id = $this->VseeUsers_model->ci_save($data);

                        // Sincronizar con VSee
                        $sync_result = $this->sync_user_by_id($new_id);
                        
                        if ($sync_result['success']) {
                            $registered++;
                            log_message('info', "Usuario {$user->first_name} {$user->last_name} (ID: {$user->id}) registrado exitosamente en VSee");
                        } else {
                            $errors[] = "Error sincronizando usuario {$user->first_name} {$user->last_name}: " . $sync_result['message'];
                            log_message('error', "Error sincronizando usuario ID {$user->id}: " . $sync_result['message']);
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Error procesando usuario {$user->first_name} {$user->last_name}: " . $e->getMessage();
                    log_message('error', "Error procesando usuario ID {$user->id}: " . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Proceso completado. Procesados: {$processed}, Registrados: {$registered}",
                'data' => [
                    'processed' => $processed,
                    'registered' => $registered,
                    'errors' => $errors
                ]
            ]);

        } catch (Exception $e) {
            log_message('error', 'auto_register_all_users error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error general en el proceso de registro automático: ' . $e->getMessage()
            ]);
        }
    }

    /**
    * Función auxiliar para sincronizar un usuario específico por su ID en vsee_users
    */
    /*private function sync_user_by_id($vsee_user_id)
    {
        try {
            $link = $this->VseeUsers_model->get_one($vsee_user_id);

            if (!$link) {
                return ['success' => false, 'message' => 'Enlace no encontrado.'];
            }

            // Recolectar datos de usuario
            $user = $this->Users_model->get_one($link->user_id);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            $type = 400;
            $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);

            $userData = [
                "first_name" => $user->first_name,
                "last_name"  => $user->last_name,
                "dob"        => $user->dob ?? '2002-05-20',
                "type"       => $type,
                "code"       => $this->generateUniqueVseeId(),
                "email"      => $user->email ? $user->email : '',
            ];

            $roomData = [
                "domain" => "rubymed.vseepreview.com",
                "slug"   => "s-" . strtolower($user->first_name) . "-" . $random,
                "name"   => $user->first_name . " " . $user->last_name
            ];

            // Llamar a VseeClient
            $vsee = new VseeClient();
            $res = $vsee->createUserSSO($userData);
            $room = $vsee->createRoom($roomData);

            if (!isset($res['data']) || !isset($res['data']['vsee'])) {
                return ['success' => false, 'message' => 'Respuesta inválida de VSee.'];
            }

            // Guardar la información retornada
            $dataToSave = [
                'vsee_id'       => $res['data']['id'],
                'vsee_username' => $res['data']['vsee']['id'],
                'vsee_token'    => $res['data']['vsee']['token'],
                'vsee_room'     => $room['data']['code']
            ];
            $this->VseeUsers_model->ci_save($dataToSave, $vsee_user_id);

            return ['success' => true];

        } catch (Exception $e) {
            log_message('error', 'sync_user_by_id error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al sincronizar: ' . $e->getMessage()];
        }
    }*/

    /**
    * Endpoint para ejecutar el registro automático via AJAX
    */
    public function run_auto_register()
    {
        // Solo permitir a administradores
        $this->access_only_admin();
        
        return $this->auto_register_all_users();
    }

    /**
    * Comando para ejecutar desde CLI (opcional)
    * Útil para ejecutar desde cron jobs o scripts
    */
    public function cli_auto_register()
    {
        if (!is_cli()) {
            show_404();
            return;
        }

        echo "Iniciando registro automático de usuarios...\n";
        
        $result = $this->auto_register_all_users();
        $data = json_decode($result->getBody(), true);
        
        if ($data['success']) {
            echo "✅ " . $data['message'] . "\n";
            if (!empty($data['data']['errors'])) {
                echo "⚠️  Errores encontrados:\n";
                foreach ($data['data']['errors'] as $error) {
                    echo "   - " . $error . "\n";
                }
            }
        } else {
            echo "❌ Error: " . $data['message'] . "\n";
        }
        
        echo "Proceso terminado.\n";
    }

    public function run_auto_register_background()
    {
        try {
            log_message('info', 'Iniciando auto-registro en segundo plano...');
            
            // Obtener usuarios que NO están en vsee_users
            $unregistered_users = $this->get_unregistered_users();
            
            if (empty($unregistered_users)) {
                log_message('info', 'No hay usuarios sin registrar en VSee');
                return;
            }

            $registered_count = 0;
            $max_per_execution = 5; // Limitar a 5 usuarios por ejecución para no sobrecargar

            foreach (array_slice($unregistered_users, 0, $max_per_execution) as $user) {
                try {
                    // Crear el registro base en vsee_users
                    $data = [
                        'user_id'   => $user->id,
                        'clinic_id' => null,
                        'action'    => 'user',
                        'state'     => 1
                    ];

                    $new_id = $this->VseeUsers_model->ci_save($data);

                    // Sincronizar con VSee
                    $sync_result = $this->sync_user_by_id($new_id);
                    
                    if ($sync_result['success']) {
                        $registered_count++;
                        log_message('info', "Auto-registro exitoso: {$user->first_name} {$user->last_name} (ID: {$user->id})");
                    } else {
                        // Si falla, eliminar el registro creado
                        $this->VseeUsers_model->delete($new_id);
                        log_message('error', "Auto-registro falló para usuario ID {$user->id}: " . $sync_result['message']);
                    }
                    
                    // Pequeña pausa para no sobrecargar el servidor
                    usleep(500000); // 0.5 segundos
                    
                } catch (Exception $e) {
                    log_message('error', "Error en auto-registro para usuario ID {$user->id}: " . $e->getMessage());
                }
            }

            log_message('info', "Auto-registro completado. Usuarios registrados: {$registered_count}");
            
        } catch (Exception $e) {
            log_message('error', 'Error en run_auto_register_background: ' . $e->getMessage());
        }
    }

    /**
    * Obtiene usuarios que NO están registrados en vsee_users
    */
        private function get_unregistered_users()
    {
        try {
            $db = db_connect();
            
            $users_table = $db->prefixTable('users');
            $vsee_users_table = $db->prefixTable('vsee_users');
            
            $sql = "
                SELECT u.* 
                FROM {$users_table} u 
                LEFT JOIN {$vsee_users_table} v ON u.id = v.user_id AND v.deleted = 0
                WHERE u.deleted = 0 AND v.user_id IS NULL
                ORDER BY u.id ASC
                LIMIT 10
            ";
            
            $query = $db->query($sql);
            
            if ($query && $query->getNumRows() > 0) {
                return $query->getResult();
            } else {
                return [];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error en get_unregistered_users: ' . $e->getMessage());
            return [];
        }
    }

    /**
    * Función auxiliar para sincronizar un usuario específico por su ID en vsee_users
    * (La misma función que creamos antes)
    */
    private function sync_user_by_id($vsee_user_id)
    {
        try {
            $link = $this->VseeUsers_model->get_one($vsee_user_id);

            if (!$link) {
                return ['success' => false, 'message' => 'Enlace no encontrado.'];
            }

            // Recolectar datos de usuario
            $user = $this->Users_model->get_one($link->user_id);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            $type = 400;
            $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);

            $userData = [
                "first_name" => $user->first_name,
                "last_name"  => $user->last_name,
                "dob"        => $user->dob ?? '2002-05-20',
                "type"       => $type,
                "code"       => $this->generateUniqueVseeId(),
                "email"      => $user->email ? $user->email : '',
            ];

            $roomData = [
                "domain" => "rubymed.vseepreview.com",
                "slug"   => "s-" . strtolower($user->first_name) . "-" . $random,
                "name"   => $user->first_name . " " . $user->last_name
            ];

            // Llamar a VseeClient
            $vsee = new VseeClient();
            $res = $vsee->createUserSSO($userData);
            $room = $vsee->createRoom($roomData);

            if (!isset($res['data']) || !isset($res['data']['vsee'])) {
                return ['success' => false, 'message' => 'Respuesta inválida de VSee.'];
            }

            // Guardar la información retornada
            $dataToSave = [
                'vsee_id'       => $res['data']['id'],
                'vsee_username' => $res['data']['vsee']['id'],
                'vsee_token'    => $res['data']['vsee']['token'],
                'vsee_room'     => $room['data']['code']
            ];
            $this->VseeUsers_model->ci_save($dataToSave, $vsee_user_id);

            return ['success' => true];

        } catch (Exception $e) {
            log_message('error', 'sync_user_by_id error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al sincronizar: ' . $e->getMessage()];
        }
    }
}
