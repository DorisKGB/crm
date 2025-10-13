<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;
use App\Models\Stamp_model;
use App\Models\Provider_model;
use App\Models\Notifications_model;
use App\Models\Users_model;

use Exception;

class Stamp extends Security_Controller
{
    protected $userModel;
    protected $stampModel;
    protected $notificationsModel;

    public function __construct()
    {
        parent::__construct();
        $this->stampModel = new Stamp_model();
        $this->notificationsModel = new Notifications_model();
        $this->userModel = new Users_model();
        helper('clinics_helper');
    }

    /**
     * Muestra el formulario para timbrar la excusa médica.
     */
    public function index()
    {
        $providers = new Provider_model();
        return $this->template->rander('stamp/index', ['providers' => $providers->get_all()->getResult()]);
    }

     public function select_template()
    {
        $clinics = get_user_clinics($this->login_user->id);
        return $this->template->rander('stamp/select_template', ['clinics' => $clinics]);
    }

    public function stamp_success($stamp_id)
    {
        return $this->template->rander('stamp/stamp_success', ['stamp' => $this->stampModel->get_one($stamp_id)]);
    }

    public function create_template()
    {
        return $this->template->rander('stamp/create_template');
    }

    public function stamp_direct()
    {
        return $this->template->rander('stamp/stamp_direct');
    }

    public function main()
    {
        return $this->template->rander('stamp/main');
    }

    public function type_request()
    {
        return $this->template->rander('stamp/type_request');
    }

    public function stamp_template()
    {
        $providers = new Provider_model();
        $clinics = get_user_clinics($this->login_user->id);
        return $this->template->rander('stamp/stamp_template', ['providers' => $providers->get_all()->getResult(), 'clinics' => $clinics]);
    }

    public function assistant()
    {
        $clinics = get_user_clinics($this->login_user->id);
        return $this->template->rander('stamp/assistant', ['clinics' => $clinics]);
    }

     public function stamp_v1()
    {
        $providers = new Provider_model();
        $clinics = get_user_clinics($this->login_user->id);
        return $this->template->rander('stamp/index_v1', ['providers' => $providers->get_all()->getResult(), 'clinics' => $clinics]);
    }

    public function listAjax()
    {
        //Podemos obtener los permisos de los usuarios
        $permissions = $this->login_user->permissions;
        $stamp_permission = get_array_value($permissions, "stamp_permission");
        $user_id = $this->login_user->id;

        if ($stamp_permission === "provider") {
            $db = \Config\Database::connect();
            $builder = $db->table($this->stampModel->table . ' AS e');

            // Hacer un JOIN con crm_branch para validar el acceso a la clínica
            $builder->join('crm_branch AS b', 'e.clinic_id = b.id_clinic', 'inner');

            // Seleccionar solo registros únicos
            $builder->select('DISTINCT e.*', false);

            // Condiciones: El usuario debe ser el proveedor o el generador de la excusa
            $builder->groupStart();
            $builder->where('e.provider_user_id', $user_id);
            $builder->orWhere('e.generate_for', $user_id);
            $builder->orWhere('b.id_user', $user_id);
            $builder->groupEnd();

            // Filtrar por excusas no eliminadas
            $builder->where('e.deleted', 0);

            // Ordenar por la fecha de creación (las más recientes primero)
            $builder->orderBy('e.created_at', 'ASC');

            $stamp = $builder->get()->getResult();

            //$stamp = $this->stampModel->where('')->get()->getResult(); //todos los que tengan la clinica
        } else if ($stamp_permission === "request") {
            //Si es request todo los que el genere
            $stamp = $this->stampModel->get_all_where(['generate_for' => $user_id])->getResult();
        } else {
            $stamp = $this->stampModel->get()->getResult();
        }

        return $this->response->setJSON(['success' => true, 'stamp' => $stamp]);
    }

    public function listAjax2(string $status = 'approved', int $offset = 0, int $limit = 20)
    {
        // Sanitizar inputs mínimos
        $offset = max(0, $offset);
        $limit  = max(1, $limit);
        $search = trim($this->request->getGet('search'));

        // Permisos y usuario
        $permissions      = $this->login_user->permissions;
        $stamp_permission = get_array_value($permissions, 'stamp_permission');
        $user_id          = $this->login_user->id;

        // Closure para filtrar por estado
        $applyStatusFilter = function ($builder, $status) {
            switch ($status) {
                case 'approved':
                    $builder->where('e.approved', 1);
                    break;
                case 'pending':
                    $builder->where('e.approved', 0)
                        ->where('e.provider_user_id IS NULL', null, false);
                    break;
                case 'denied':
                    $builder->where('e.approved', 0)
                        ->where('e.provider_user_id IS NOT NULL', null, false);
                    break;
                default:
                    $builder->where('e.approved', 1);
            }
        };

        // Construcción del builder base según rol
        $db = \Config\Database::connect();
        if ($stamp_permission === 'provider') {
            $builder = $db->table($this->stampModel->table . ' AS e')
                ->distinct()
                ->select('e.*', false)
                ->join('crm_branch AS b', 'e.clinic_id = b.id_clinic', 'inner')
                ->where('e.deleted', 0)
                ->groupStart()
                ->where('e.provider_user_id', $user_id)
                ->orWhere('e.generate_for',   $user_id)
                ->orWhere('b.id_user',        $user_id)
                ->groupEnd();
        } elseif ($stamp_permission === 'request') {
            $builder = $db->table($this->stampModel->table . ' AS e')
                ->where('e.generate_for', $user_id)
                ->where('e.deleted',      0);
        } else {
            $builder = $db->table($this->stampModel->table . ' AS e')
                ->where('e.deleted', 0);
        }

        // Filtro por búsqueda
        if ($search !== '') {
            $builder->groupStart()
                ->like('e.description',   $search)
                ->orLike('e.clinic_select', $search)
                ->orLike('e.token',        $search)
                ->groupEnd();
        }

        // Clonar antes de filtrar por estado para calcular conteos
        $baseBuilder = clone $builder;

        // Conteos según lo que VE este usuario (clonando baseBuilder)

        // Aprobadas
        $approvedCount = (clone $baseBuilder)
            ->where('e.approved', 1)
            ->countAllResults(false);

        // Pendientes
        $pendingCount = (clone $baseBuilder)
            ->where('e.approved', 0)
            ->where('e.provider_user_id IS NULL', null, false)
            ->countAllResults(false);

        // Negadas
        $deniedCount = (clone $baseBuilder)
            ->where('e.approved', 0)
            ->where('e.provider_user_id IS NOT NULL', null, false)
            ->countAllResults(false);

        // Aplicar filtro por estado solicitado
        $applyStatusFilter($builder, $status);

        // Conteo total tras estado y búsqueda
        $totalCount = $builder->countAllResults(false);

        // Obtener la página actual
        $stamp = $builder
            ->orderBy('e.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        return $this->response->setJSON([
            'success'       => true,
            'stamp'         => $stamp,
            'totalCount'    => $totalCount,
            'approvedCount' => $approvedCount,
            'pendingCount'  => $pendingCount,
            'deniedCount'   => $deniedCount,
        ]);
    }



    public function clinicsAjax()
    {
        // Suponemos que $this->login_user->id contiene el ID del usuario actual
        $user_id = $this->login_user->id;

        // Usamos una consulta para obtener las clínicas a las que el usuario tiene acceso:
        $db = db_connect();
        $builder = $db->table('crm_branch');
        $builder->select('crm_clinic_directory.id, crm_clinic_directory.name, crm_clinic_directory.address, crm_clinic_directory.phone, crm_clinic_directory.provider_id, ');
        $builder->join('crm_clinic_directory', 'crm_branch.id_clinic = crm_clinic_directory.id');
        $builder->where('crm_branch.id_user', $user_id);
        $query = $builder->get();
        $clinics = $query->getResult();

        return $this->response->setJSON(['success' => true, 'clinics' => $clinics]);
    }

    public function detail($token)
    {
        $stamp = $this->stampModel->get_one_where(array('token' => $token));

        /*$stamp = $this->stampModel
            ->select('stamp.*, provider.name as provider_name, provider.npi as provider_npi, provider.url_signature as provider_signature, provider.role as provider_role')
            ->join('providers as provider', 'provider.id = stamp.provider_id')
            ->where('stamp.token', $token)
            ->get()
            ->getRow();*/

        if (!$stamp) {
            return $this->response->setJSON(['success' => false]);
        }

        return $this->response->setJSON([
            'success' => true,
            'stamp' => $stamp
        ]);
    }

    // Endpoint para aprobar la solicitud de timbre (similar a approveAjax en Excuse)
    public function approveAjax($id, $userID)
    {
        // Obtener datos del proveedor (para rellenar datos de firma, npi, rol, etc.)
        $providerModel = new Provider_model();
        $provider_user = $providerModel->where('user_id', $userID)->get()->getRow();

        $data = [
            'approved' => 1,   // Puedes usar un campo "approved" o actualizar "state" a "approved"
            'provider' => $provider_user->name,
            'provider_npi' => $provider_user->npi,
            'provider_role' => $provider_user->role,
            'provider_signature' => $provider_user->url_signature,
            'provider_user_id' => $provider_user->user_id
        ];
        $this->stampModel->ci_save($data, $id);

        $stampData = $this->stampModel->get_one($id);
        $admins = $this->userModel->get_admin_ids(); //Todos los usuarios admins 
        $users = get_clinic_users($stampData->clinic_id); //Obtengo todos los usuarios que pertenecen a una clinica
        $clinicUserIds   = array_map(fn($u) => $u->id, $users);  //Obtengo todos los ID de usuarios
        $recipients = merge_unique_ids($admins, $clinicUserIds);
        simple_notification($this->login_user->id, 'stamp_created', $recipients, ["stamp_id" => $id]);

        $subject = $provider_user->name . " Ha APROBADO el Timbre #{$id} de la clinica " . $stampData->clinic_select;
        $creatorName = $this->login_user->first_name . ' ' . $this->login_user->last_name;
        $stampUrl    = site_url("stamp");
        $message = view('stamp/template_email', [
            'type'        => 'approved',
            'stamp'       => $stampData,
            'provider'    => $provider_user ?? null,
            'creatorName' => $creatorName,
            'stampUrl'    => $stampUrl
        ]);

        try {
            $clinic = getClinicById($stampData->clinic_id);
            if ($clinic->email !=  "") {
                $sent = send_app_mail($clinic->email, $subject, $message);
                if (! $sent) {
                    log_message('error', "Falló el envío de correo para el timbre #{$stampData->id}");
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error al enviar correo de timbre: " . $e->getMessage());
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Timbre aprobado']);
    }

    // Endpoint para denegar la solicitud de timbre (similar a denyAjax en Excuse)
    public function denyAjax($id, $userID)
    {
        $providerModel = new Provider_model();
        $provider_user = $providerModel->where('user_id', $userID)->get()->getRow();

        $data = [
            'approved' => 0,
            'provider' => $provider_user->name,
            'provider_npi' => $provider_user->npi,
            'provider_role' => $provider_user->role,
            'provider_signature' => $provider_user->url_signature,
            'provider_user_id' => $provider_user->user_id,

        ];
        $this->stampModel->ci_save($data, $id);

        $stampData = $this->stampModel->get_one($id);
        $admins = $this->userModel->get_admin_ids(); //Todos los usuarios admins 
        $users = get_clinic_users($stampData->clinic_id); //Obtengo todos los usuarios que pertenecen a una clinica
        $clinicUserIds   = array_map(fn($u) => $u->id, $users);  //Obtengo todos los ID de usuarios
        $recipients = merge_unique_ids($admins, $clinicUserIds);
        simple_notification($this->login_user->id, 'stamp_created', $recipients, ["stamp_id" => $id]);

        $subject = $provider_user->name . " Ha DENEGADO el Timbre #{$id} de la clinica " . $stampData->clinic_select;
        $creatorName = $this->login_user->first_name . ' ' . $this->login_user->last_name;
        $stampUrl    = site_url("stamp");
        $message = view('stamp/template_email', [
            'type'        => 'denied',
            'stamp'       => $stampData,
            'provider'    => $provider_user ?? null,
            'creatorName' => $creatorName,
            'stampUrl'    => $stampUrl
        ]);

        try {
            $clinic = getClinicById($stampData->clinic_id);
            if ($clinic->email !=  "") {
                $sent = send_app_mail($clinic->email, $subject, $message);
                if (! $sent) {
                    log_message('error', "Falló el envío de correo para el timbre #{$stampData->id}");
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error al enviar correo de timbre: " . $e->getMessage());
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Timbre denegado']);
    }

    public function timbrarAjax($id)
    {
        // Actualizamos el registro para marcarlo como timbrado
        $data = ['stamped' => 1];
        $this->stampModel->ci_save($data, $id);
        $stamp = $this->stampModel->get_one($id);
        return $this->response->setJSON([
            'success' => true,
            'stamp' => $stamp
        ]);
    }

    public function timbrarAjax2($id)
    {
        // Actualizamos el registro para marcarlo como timbrado
        $stamp = $this->stampModel->get_one($id);
        $numero = $stamp->stamped;
        $data = ['stamped' => $numero + 1];
        $this->stampModel->ci_save($data, $id);
        $stamp = $this->stampModel->get_one($id);
        
        // Obtener información de orientación de la plantilla
        if ($stamp->template_name) {
            $stampTemplateModel = new \App\Models\StampTemplate_model();
            $template = $stampTemplateModel->get_one_where(['name' => $stamp->template_name]);
            if ($template) {
                $stamp->template_orientation = $template->orientation;
                $stamp->template_is_horizontal = $template->is_horizontal;
                $stamp->template_rotation = $template->rotation;
                $stamp->template_aspect_ratio = $template->aspect_ratio;
            }
        }
        
        // ✅ DEBUG: Log de orientación enviada al frontend
        log_message('debug', '=== ORIENTACIÓN ENVIADA AL FRONTEND ===');
        log_message('debug', 'ID del timbre: ' . $id);
        log_message('debug', 'template_name: ' . ($stamp->template_name ?? 'N/A'));
        log_message('debug', 'orientation: ' . ($stamp->orientation ?? 'N/A'));
        log_message('debug', 'is_horizontal: ' . ($stamp->is_horizontal ?? 'N/A'));
        log_message('debug', 'rotation: ' . ($stamp->rotation ?? 'N/A'));
        log_message('debug', 'aspect_ratio: ' . ($stamp->aspect_ratio ?? 'N/A'));
        log_message('debug', 'template_orientation: ' . ($stamp->template_orientation ?? 'N/A'));
        log_message('debug', 'template_is_horizontal: ' . ($stamp->template_is_horizontal ?? 'N/A'));
        log_message('debug', '=====================================');
        
        // También log en consola del servidor
        error_log("=== ORIENTACIÓN ENVIADA AL FRONTEND ===");
        error_log("ID del timbre: " . $id);
        error_log("template_name: " . ($stamp->template_name ?? 'N/A'));
        error_log("orientation: " . ($stamp->orientation ?? 'N/A'));
        error_log("is_horizontal: " . ($stamp->is_horizontal ?? 'N/A'));
        error_log("rotation: " . ($stamp->rotation ?? 'N/A'));
        error_log("aspect_ratio: " . ($stamp->aspect_ratio ?? 'N/A'));
        error_log("template_orientation: " . ($stamp->template_orientation ?? 'N/A'));
        error_log("template_is_horizontal: " . ($stamp->template_is_horizontal ?? 'N/A'));
        error_log("=====================================");
        
        return $this->response->setJSON([
            'success' => true,
            'stamp' => $stamp
        ]);
    }


    public function create()
    {
        try {
            $clinic_id = $this->request->getPost('clinic_id');
            $clinic_select = $this->request->getPost('clinic_select');
            $size = $this->request->getPost('size');
            $description = $this->request->getPost('contenido');
            $template_image  = $this->request->getPost('template_image');
            $template_name = $this->request->getPost('template_name');
            $signature_y = $this->request->getPost('signature_y');
            $signature_x = $this->request->getPost('signature_x');
            $page_size = $this->request->getPost('page_size');



            $token = bin2hex(random_bytes(8));

            $data = [
                'clinic_id' => $clinic_id,
                'clinic_select' => $clinic_select,
                'size' => $size,
                'description' => $description,
                'token' => $token,
                'template_name' => $template_name,
                'template_image'  => $template_image,
                'signature_y' => $signature_y,
                'signature_x' => $signature_x,
                'page_size' => $page_size,
                'generate_for' => $this->login_user->id,
                'generate_name' => $this->login_user->first_name . " " . $this->login_user->last_name,
                'stamped' => '0'
            ];

            // Depuramos el contenido de $data
            log_message('debug', 'Datos recibidos: ' . print_r($data, true));

            $insert_id = $this->stampModel->ci_save($data);

            if ($insert_id) {

                $admins = $this->userModel->get_admin_ids(); //Todos los usuarios admins 
                $users = get_clinic_users($clinic_id); //Obtengo todos los usuarios que pertenecen a una clinica
                $clinicUserIds   = array_map(fn($u) => $u->id, $users);  //Obtengo todos los ID de usuarios
                $recipients = merge_unique_ids($admins, $clinicUserIds);
                simple_notification($this->login_user->id, 'stamp_created', $recipients, ["stamp_id" => $insert_id]);
                $stampData = $this->stampModel->get_one($insert_id);
                $subject = $clinic_select . " Ha solicitado un Nuevo Timbre #{$insert_id}";
                $creatorName = $this->login_user->first_name . ' ' . $this->login_user->last_name;
                $stampUrl    = site_url("stamp");
                $message = view('stamp/template_email', [
                    'type'        => 'created',
                    'stamp'       => $stampData,
                    'provider'    => $provider_user ?? null,
                    'creatorName' => $creatorName,
                    'stampUrl'    => $stampUrl
                ]);
                try {
                    $clinic = getClinicById($stampData->clinic_id);
                    if ($clinic->email !=  "") {
                        $sent = send_app_mail($clinic->email, $subject, $message);
                        if (! $sent) {
                            log_message('error', "Falló el envío de correo para el timbre #{$stampData->id}");
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', "Error al enviar correo de timbre: " . $e->getMessage());
                }

                return $this->response->setJSON(['success' => true, 'stamp' => '']);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar.']);
        } catch (\Exception $e) {
            log_message('error', 'Error en create(): ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }

    public function create2()
    {
        try {
            $clinic_id = $this->request->getPost('clinic_id');
            $clinic_select = (getClinicById($clinic_id)->name);
            $size = $this->request->getPost('size');
            $description = $this->request->getPost('contenido');
            $template_image  = $this->request->getPost('template_image');
            $template_name = $this->request->getPost('template_name');
            $signature_y = $this->request->getPost('signature_y');
            $signature_x = $this->request->getPost('signature_x');
            $page_size = $this->request->getPost('page_size');
            
            // ✅ Nuevos campos de orientación
            $orientation = $this->request->getPost('orientation') ?? 'portrait';
            $is_horizontal = $this->request->getPost('is_horizontal') ?? '0';
            $rotation = $this->request->getPost('rotation') ?? '0';
            $aspect_ratio = $this->request->getPost('aspect_ratio') ?? null;

            $token = bin2hex(random_bytes(8));

            $rawImage = $this->request->getPost('template_image');
            if (strpos($rawImage, 'data:image/') === 0) {
                // separar cabecera y datos
                list($meta, $dataPart) = explode(',', $rawImage);
                // extraer extensión
                if (preg_match('#^data:image/(\w+);base64#', $meta, $m)) {
                    $ext = $m[1]; // jpg, png, etc.
                } else {
                    $ext = 'png';
                }
                // decodificar
                $bin = base64_decode($dataPart);
                // directorio de subida (dentro de writable/uploads)
                $dir = WRITEPATH . 'uploads/stamps/';
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                // nombre único
                $filename = "stamp_{$token}." . $ext;
                $filepath = $dir . $filename;
                file_put_contents($filepath, $bin);
                // guardar en el array la ruta relativa
                $template_image = 'uploads/stamps/' . $filename;
            } else {
                // viene ruta de plantilla
                $template_image = $rawImage;
            }

            $data = [
                'clinic_id' => $clinic_id,
                'clinic_select' => $clinic_select,
                'size' => $size,
                'description' => $description,
                'token' => $token,
                'template_name' => $template_name,
                'template_image'  => $template_image,
                'signature_y' => $signature_y,
                'signature_x' => $signature_x,
                'page_size' => $page_size,
                'generate_for' => $this->login_user->id,
                'generate_name' => $this->login_user->first_name . " " . $this->login_user->last_name,
                'stamped' => '0',
                            // ✅ Campos de orientación agregados
            'orientation' => $orientation,
            'is_horizontal' => $is_horizontal,
            'rotation' => $rotation,
            'aspect_ratio' => $aspect_ratio
        ];
        
                // ✅ DEBUG: Log de orientación recibida en create2
        log_message('debug', '=== ORIENTACIÓN RECIBIDA EN CREATE2 ===');
        log_message('debug', 'orientation: ' . $orientation);
        log_message('debug', 'is_horizontal: ' . $is_horizontal);
        log_message('debug', 'rotation: ' . $rotation);
        log_message('debug', 'aspect_ratio: ' . $aspect_ratio);
        log_message('debug', '=====================================');
        
        // También log en consola del servidor
        error_log("=== ORIENTACIÓN RECIBIDA EN CREATE2 ===");
        error_log("orientation: " . $orientation);
        error_log("is_horizontal: " . $is_horizontal);
        error_log("rotation: " . $rotation);
        error_log("aspect_ratio: " . $aspect_ratio);
        error_log("=====================================");
        
        // Depuramos el contenido de $data
        log_message('debug', 'Datos recibidos: ' . print_r($data, true));

            $insert_id = $this->stampModel->ci_save($data);

            if ($insert_id) {

                $providerModel = new Provider_model();          
                $dataProviders = $providerModel->getProviderStamps($clinic_id);   
                $this->sendPushNotificationsForStamp($dataProviders , $insert_id, $clinic_select);  

                return $this->response->setJSON(['success' => true, 'stamp' => $insert_id]);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar.']);
        } catch (\Exception $e) {
            log_message('error', 'Error en create(): ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno del servidor.']);
        }
    }





    /**
     * Procesa el timbrado de la excusa médica.
     */
    /*public function timbrar()
    {
        // Validar los datos del formulario
        $validation = \Config\Services::validation();
        $rules = [
            'proveedor_id'  => 'required|integer',
            'tipo_documento'=> 'required',
            'contenido'     => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Recoger datos del formulario
        $proveedor_id   = $this->request->getPost('proveedor_id');
        $tipo_documento = $this->request->getPost('tipo_documento');
        $contenido      = $this->request->getPost('contenido');

        // Generar un token único
        $token = bin2hex(random_bytes(10));

        // Guardar el registro del documento timbrado
        $docModel = new DocumentoTimbradoModel();
        $data = [
            'proveedor_id'  => $proveedor_id,
            'tipo_documento'=> $tipo_documento,
            'contenido'     => $contenido,
            'token'         => $token,
        ];

        $doc_id = $docModel->ci_save($data);
        if (!$doc_id) {
            return redirect()->back()->with('error', 'Error al guardar el documento.');
        }

        // Generar el PDF y enviarlo a impresión
        return $this->generarPDF($doc_id);
    }

    /**
     * Genera el PDF timbrado con firma, código QR y token.
     */
    /*private function generarPDF($doc_id)
    {
        $docModel = new DocumentoTimbradoModel();
        $proveedorModel = new ProveedorModel();

        $documento = $docModel->get_one($doc_id);
        if (!$documento || !$documento->id) {
            return redirect()->back()->with('error', 'Documento no encontrado.');
        }

        $proveedor = $proveedorModel->get_one($documento->proveedor_id);

        // Generar la URL de validación usando el token
        $validationUrl = base_url('timbrado/validar?token=' . $documento->token);

        // Generar el código QR
        $qrCode = QrCode::create($validationUrl);
        $qrWriter = new PngWriter();
        $qrResult = $qrWriter->write($qrCode);
        $qrBase64 = base64_encode($qrResult->getString());

        // Generar el HTML del PDF a partir de la vista
        $html = view('timbrado/pdf_template', [
            'documento'     => $documento,
            'proveedor'     => $proveedor,
            'qrBase64'      => $qrBase64,
            'validationUrl' => $validationUrl,
        ]);

        // Configurar Dompdf
        $options = new Options();
        $options->setChroot(FCPATH);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Definir el tamaño de papel según la selección
        $paperSize = in_array($documento->tipo_documento, ['A4','A5','A6','A7']) ? $documento->tipo_documento : 'A4';
        $dompdf->setPaper($paperSize, 'portrait');

        $dompdf->render();

        // Enviar el PDF al navegador; la plantilla incluye JavaScript para auto imprimir
        $dompdf->stream('excusa_medica_timbrada.pdf', ['Attachment' => 1]);
    }

    /**
     * Valida la autenticidad del documento a partir del token.
     */
    /*public function validar()
    {
        $token = $this->request->getGet('token');

        if (!$token) {
            return view('timbrado/validar', ['mensaje' => 'Token no proporcionado.']);
        }

        $docModel = new DocumentoTimbradoModel();
        $documento = $docModel->where('token', $token)->first();

        if ($documento) {
            return view('timbrado/validar', [
                'mensaje'   => 'El documento es válido.',
                'documento' => $documento
            ]);
        } else {
            return view('timbrado/validar', ['mensaje' => 'Documento no válido o token inexistente.']);
        }
    }*/

     /*ESTADISTICAS*/
    public function statistics()
    {
        return $this->template->rander('stamp/statistics');
    }

     public function providerStats()
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->stampModel->table . ' AS s')
            ->select('p.user_id AS provider_id, p.name AS provider_name, COUNT(s.id) AS stamps_signed')
            ->join('providers AS p', 's.provider_user_id = p.user_id', 'left')
            ->where('s.approved', 1)
            ->groupBy('p.user_id, p.name')
            ->orderBy('stamps_signed', 'DESC');

        $data = $builder->get()->getResult();
        return $this->response->setJSON($data);
    }

    /**
     * JSON: clínicas Rubymed y timbres generados (incluye cero)
     */
    public function clinicStatsRubymed()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('crm_clinic_directory AS c')
            ->select('c.id AS clinic_id, c.name AS clinic_name, COUNT(s.id) AS stamps_generated')
            ->join($this->stampModel->table . ' AS s', 's.clinic_id = c.id AND s.deleted = 0', 'left')
            ->where('c.is_aliada', 0)
            ->groupBy('c.id, c.name')
            ->where('c.deleted', 0)   
            ->orderBy('stamps_generated', 'DESC');

        $data = $builder->get()->getResult();
        return $this->response->setJSON($data);
    }

    /**
     * JSON: clínicas aliadas y timbres generados (incluye cero)
     */
    public function clinicStatsAliadas()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('crm_clinic_directory AS c')
            ->select('c.id AS clinic_id, c.name AS clinic_name, COUNT(s.id) AS stamps_generated')
            ->join($this->stampModel->table . ' AS s', 's.clinic_id = c.id AND s.deleted = 0', 'left')
            ->where('c.is_aliada', 1)
            ->groupBy('c.id, c.name')
            ->where('c.deleted', 0)   
            ->orderBy('stamps_generated', 'DESC');

        $data = $builder->get()->getResult();
        return $this->response->setJSON($data);
    }

    
    /**
    * Enviar notificaciones push para nuevo timbre
    * Utiliza el servicio centralizado de notificaciones push
    */
    private function sendPushNotificationsForStamp($recipients, $stamp_id, $clinic_name)
    {
        try {
            // Usar el servicio centralizado de notificaciones push
            $pushNotificationService = new \App\Services\PushNotificationService();
            
            $title = "Nuevo Timbre Solicitado";
            $message = "Se ha solicitado un nuevo timbre #{$stamp_id} para {$clinic_name}";
            $data = [
                'type' => 'stamp_created',
                'stamp_id' => $stamp_id,
                'clinic_name' => $clinic_name,
                'url' => site_url('stamp')
            ];
            
            $result = $pushNotificationService->sendToMultipleUsers($recipients, $title, $message, $data, 'excuse_created');
            // Log del resultado
            if ($result['success']) {
                log_message('info', sprintf(
                    'Push notifications for stamp %s: %d sent, %d failed, %d no token',
                    $stamp_id,
                    $result['success_count'],
                    $result['failure_count'],
                    $result['no_token_count']
                ));
            } else {
                log_message('error', 'Error sending push notifications for stamp: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error sending push notifications for stamp: ' . $e->getMessage());
        }
    }    

}
