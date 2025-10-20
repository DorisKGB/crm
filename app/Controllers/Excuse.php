<?php

namespace App\Controllers;
use App\Models\Provider_model;
use App\Models\Excuse_model;
use App\Models\Users_model;
use App\Libraries\Pdf;

class Excuse extends Security_Controller {

    protected $excuseModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new Users_model();
        $this->excuseModel = new Excuse_model();
        
        helper('clinics_helper');
    }

    // ---------------------------
    // Endpoints HTML (ya existentes)
    // ---------------------------
    public function index() {
        $data['excuses'] = $this->excuseModel->get_all()->getResult();
        return $this->template->rander("excuse_medical/index", $data);
    }

    

    public function create() {
        $data['proveedores'] = ['Mariana Quintero', 'Dr. Juan Pérez', 'Dra. María Gómez'];
        return $this->template->rander("excuse_medical/create", $data);
    }

    public function store() {
        // Método no modificado (redirige)
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $rules = [
                'nombre_completo'   => 'required',
                'fecha_nacimiento'  => 'required|valid_date',
                'excuse'            => 'required',
                'motivo'            => 'required',
                'fecha_inicio'      => 'required|valid_date',
                'fecha_fin'         => 'required|valid_date',
                'proveedor'         => 'required'
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            $token = bin2hex(random_bytes(16));
            $data = [
                'token'      => $token,
                'name'       => $this->request->getPost('nombre_completo'),
                'birth'      => $this->request->getPost('fecha_nacimiento'),
                'type'       => $this->request->getPost('excuse'),
                'reason'     => $this->request->getPost('motivo'),
                'date_start' => $this->request->getPost('fecha_inicio'),
                'date_end'   => $this->request->getPost('fecha_fin'),
                'provider'   => $this->request->getPost('proveedor'),
                'provider_npi'  => $this->request->getPost('provider_npi'),
                'privider_role' => $this->request->getPost('privider_role'),
                'state'      => 'request'
            ];
            $this->excuseModel->ci_save($data);
            return redirect()->to(site_url("excuse"))->with('success', 'Excusa creada exitosamente');
        }
    }

    public function edit($id) {
        $data['excuse'] = $this->excuseModel->get_one($id);
        return $this->template->rander("excuse_medical/edit", $data);
    }

    public function update($id) {
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $rules = [
                'nombre_completo'   => 'required',
                'fecha_nacimiento'  => 'required|valid_date',
                'excuse'            => 'required',
                'motivo'            => 'required',
                'fecha_inicio'      => 'required|valid_date',
                'fecha_fin'         => 'required|valid_date',
                'proveedor'         => 'required'
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            $data = [
                'name'       => $this->request->getPost('nombre_completo'),
                'birth'      => $this->request->getPost('fecha_nacimiento'),
                'type'       => $this->request->getPost('excuse'),
                'reason'     => $this->request->getPost('motivo'),
                'date_start' => $this->request->getPost('fecha_inicio'),
                'date_end'   => $this->request->getPost('fecha_fin'),
                'provider'   => $this->request->getPost('proveedor'),
                'provider_npi'  => $this->request->getPost('provider_npi'),
                'privider_role' => $this->request->getPost('privider_role'),
                'state'      => $this->request->getPost('state')
            ];
            $this->excuseModel->ci_save($data, $id);
            return redirect()->to(site_url("excuse"))->with('success', 'Excusa actualizada exitosamente');
        }
    }

    public function approve($id) {
        $data = ['state' => 'approved'];
        $this->excuseModel->ci_save($data, $id);
        return redirect()->to(site_url("excuse"))->with('success', 'Excusa aprobada');
    }

    public function deny($id) {
        $data = ['state' => 'denied'];
        $this->excuseModel->ci_save($data, $id);
        return redirect()->to(site_url("excuse"))->with('success', 'Excusa denegada');
    }

    public function show($id) {
        $data['excuse'] = $this->excuseModel->get_one($id);
        return $this->template->rander("excuse_medical/show", $data);
    }

    // ---------------------------
    // Endpoints para AJAX
    // ---------------------------
    
    // GET excuse/listAjax: devuelve la lista de excusas en JSON
    /*public function listAjax() {
        $excuses = $this->excuseModel->get_all()->getResult();
        return $this->response->setJSON(['success' => true, 'excuses' => $excuses]);
    }*/

    public function listAjax() {
        $permissions = $this->login_user->permissions;
        $excuse_permission = get_array_value($permissions, "excuse_permission");
        $user_id = $this->login_user->id;
        
        // Obtener parámetros de paginación y filtros
        $page = (int) $this->request->getGet('page') ?: 1;
        $per_page = (int) $this->request->getGet('per_page') ?: 20;
        $state = $this->request->getGet('state'); // Filtro por estado
        
        // Validar parámetros
        if ($page < 1) $page = 1;
        if ($per_page < 1 || $per_page > 100) $per_page = 20; // Máximo 100 registros por página
        
        // Usar el nuevo método paginado del modelo
        $result = $this->excuseModel->get_excuses_paginated(
            $user_id, 
            $excuse_permission, 
            $this->login_user->is_admin, 
            $page, 
            $per_page,
            $state
        );
        
        return $this->response->setJSON([
            'success' => true, 
            'excuses' => $result['excuses'],
            'pagination' => $result['pagination']
        ]);
    }

    // GET excuse/countsAjax: devuelve los conteos de excusas por estado
    public function countsAjax() {
        try {
            // Verificar que el usuario esté logueado
            if (!$this->login_user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                    'counts' => ['request' => 0, 'approved' => 0, 'denied' => 0]
                ]);
            }
            
            $permissions = $this->login_user->permissions ?? [];
            $excuse_permission = get_array_value($permissions, "excuse_permission");
            $user_id = $this->login_user->id;
            
            $counts = $this->excuseModel->get_excuse_counts($user_id, $excuse_permission, $this->login_user->is_admin);
            
            return $this->response->setJSON([
                'success' => true,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error en countsAjax: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener conteos: ' . $e->getMessage(),
                'counts' => ['request' => 0, 'approved' => 0, 'denied' => 0]
            ]);
        }
    }
    
    

    // GET excuse/showAjax/{id}: devuelve los datos de la excusa en JSON
    public function showAjax($id) {
        $excuse = $this->excuseModel->get_one($id);
        if ($excuse && isset($excuse->id)) {
            return $this->response->setJSON(['success' => true, 'excuse' => $excuse]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Excusa no encontrada']);
        }
    }

    // POST excuse/storeAjax: guarda una nueva excusa y devuelve JSON
     public function storeAjax() {
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $rules = [
                'nombre_completo'   => 'required',
                'fecha_nacimiento'  => 'required|valid_date',
                'excuse'            => 'required',
                'motivo'            => 'required',
                'fecha_inicio'      => 'required|valid_date',
                'fecha_fin'         => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false, 
                    'errors' => $this->validator->getErrors()
                ]);
            }
            
            // Generar token asegurando que los 8 primeros caracteres sean únicos
            do {
                $token = bin2hex(random_bytes(16));
                $prefix = substr($token, 0, 8);
                $exists = $this->excuseModel
                               ->query("SELECT * FROM " . $this->excuseModel->table . " WHERE SUBSTRING(token, 1, 8) = ?", [$prefix])
                               ->getRow();
            } while ($exists);
            
            /*$data = [
                'token'              => $token,
                'name'               => $this->request->getPost('nombre_completo'),
                'date_attention'     => $this->request->getPost('fecha_atencion'),
                'birth'              => $this->request->getPost('fecha_nacimiento'),
                'type'               => $this->request->getPost('excuse'),
                'reason'             => $this->request->getPost('motivo'),
                'date_start'         => $this->request->getPost('fecha_inicio'),
                'date_end'           => $this->request->getPost('fecha_fin'),
                'provider'           => $this->request->getPost('proveedor'),
                'provider_npi'       => $this->request->getPost('provider_npi'),
                'privider_role'      => $this->request->getPost('privider_role'), 
                'privider_signature' => $this->request->getPost('privider_signature'),
                'provider_user_id'   => $this->request->getPost('provider_user_id'),
                'clinic_id'             => $this->request->getPost('clinic_id'),
                'clinic'             => $this->request->getPost('clinic'),
                'clinic_phone'       => $this->request->getPost('clinic_phone'),
                'clinic_address'     => $this->request->getPost('clinic_address'),
                'generate_for'       => $this->request->getPost('generate_for'),
                'generate_name'      => $this->request->getPost('generate_name'),
                'state'              => 'request'
            ];*/

            $data = [
                'token'              => $token,
                'name'               => $this->request->getPost('nombre_completo'),
                'date_attention'     => $this->request->getPost('fecha_atencion'),
                'birth'              => $this->request->getPost('fecha_nacimiento'),
                'type'               => $this->request->getPost('excuse'),
                'reason'             => $this->request->getPost('motivo'),
                'date_start'         => $this->request->getPost('fecha_inicio'),
                'date_end'           => $this->request->getPost('fecha_fin'),
                'provider'           => "",
                'provider_npi'       => "",
                'privider_role'      => "", 
                'privider_signature' => "",
                'provider_user_id'   => "",
                'clinic_id'             => $this->request->getPost('clinic_id'),
                'clinic'             => $this->request->getPost('clinic'),
                'clinic_phone'       => $this->request->getPost('clinic_phone'),
                'clinic_address'     => $this->request->getPost('clinic_address'),
                'generate_for'       => $this->request->getPost('generate_for'),
                'generate_name'      => $this->request->getPost('generate_name'),
                'state'              => 'request'
            ];
            $insert = $this->excuseModel->ci_save($data);
            if($insert){
                /*$notification_options = [
                    "excuse_id" => $insert,
                    "name"      => $data["name"],
                    "type"      => $data["type"]
                ];*/
                // Dispara notificación de creación. Se asume que el usuario que crea la excusa es el usuario logueado.
                
                    $admins = $this->userModel->get_admin_ids(); //Todos los usuarios admins 
                    $users = get_clinic_users($this->request->getPost('clinic_id')); //Obtengo todos los usuarios que pertenecen a una clinica
                    $clinicUserIds   = array_map(fn($u) => $u->id, $users);  //Obtengo todos los ID de usuarios
                    $recipients = merge_unique_ids($admins, $clinicUserIds);
                    simple_notification($this->login_user->id, 'excuse_created', $recipients, ["excuse_id" => $insert]);

                    //notificaciones push
                    $providerModel = new Provider_model();          
                    $dataProviders = $providerModel->getProviderStamps($this->request->getPost('clinic_id'));   
                    $this->sendPushNotificationsForStamp($dataProviders , $this->request->getPost('nombre_completo'), $this->request->getPost('clinic'));            
                
                //log_notification("excuse_created", $notification_options, $this->login_user->id);
            }

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Excusa creada exitosamente'
            ]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
    }

    /**
    * Enviar notificaciones push para nueva excusa médica
    * Utiliza el servicio centralizado de notificaciones push
    */
    private function sendPushNotificationsForStamp($recipients, $excuse_id, $clinic_name)
    {
        try {
            // Usar el servicio centralizado de notificaciones push
            $pushNotificationService = new \App\Services\PushNotificationService();
            $title = "Nueva Excusa Medica";
            $message = "Se ha solicitada una nueva excusa medica para {$excuse_id} desde {$clinic_name}";
            $data = [
                'type' => 'excuse_created',
                'excuse_id' => $excuse_id,
                'clinic_name' => $clinic_name,
                'url' => site_url('stamp')
            ];        
            // Enviar notificación usando el método específico para excusas


            $result = $pushNotificationService->sendToMultipleUsers($recipients, $title, $message, $data, 'excuse_created');
            
            // Log del resultado
            if ($result['success']) {
                log_message('info', sprintf(
                    'Push notifications for excuse %s: %d sent, %d failed, %d no token',
                    $excuse_id,
                    $result['success_count'],
                    $result['failure_count'],
                    $result['no_token_count']
                ));
            } else {
                log_message('error', 'Error sending push notifications for excuse: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error sending push notifications for excuse: ' . $e->getMessage());
        }
    }  

    // POST excuse/updateAjax/{id}: actualiza una excusa y devuelve JSON
    public function updateAjax($id) {
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $rules = [
                'nombre_completo'   => 'required',
                'fecha_nacimiento'  => 'required|valid_date',
                'excuse'            => 'required',
                'motivo'            => 'required',
                'fecha_inicio'      => 'required|valid_date',
                'fecha_fin'         => 'required|valid_date',
                'proveedor'         => 'required'
            ];
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false, 
                    'errors' => $this->validator->getErrors()
                ]);
            }
            $data = [
                'name'       => $this->request->getPost('nombre_completo'),
                'date_attention' => $this->request->getPost('fecha_atencion'), 
                'birth'      => $this->request->getPost('fecha_nacimiento'),
                'type'       => $this->request->getPost('excuse'),
                'reason'     => $this->request->getPost('motivo'),
                'date_start' => $this->request->getPost('fecha_inicio'),
                'date_end'   => $this->request->getPost('fecha_fin'),
                'provider'   => $this->request->getPost('proveedor'),
                'provider_npi'  => $this->request->getPost('provider_npi'),
                'privider_role' => $this->request->getPost('privider_role'),
                'privider_signature' => $this->request->getPost('privider_signature'), 
                'clinic'  => $this->request->getPost('clinic'),
                'clinic_phone' => $this->request->getPost('clinic_phone'),
                'clinic_address'  => $this->request->getPost('clinic_address'),
                'state'      => $this->request->getPost('state')
            ];


            $this->excuseModel->ci_save($data, $id);
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Excusa actualizada exitosamente'
            ]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
    }

    // GET excuse/approveAjax/{id}: aprueba la excusa y devuelve JSON
    public function approveAjax($id,$userID) {
        $provider = new Provider_model();
        $provider_user = $provider->where('user_id',$userID)->get()->getRow();

        if (!$provider_user) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Proveedor no encontrado'
            ]);
        }

        $data = [
            'state' => 'approved',
            'provider'           => $provider_user->name,
            'provider_npi'       => $provider_user->npi,
            'privider_role'      => $provider_user->role, 
            'privider_signature' => $provider_user->url_signature,
            'provider_user_id'   => $provider_user->user_id,
        ];
        $this->excuseModel->ci_save($data, $id);

        return $this->response->setJSON([
            'success' => true, 
            'message' => 'Excusa aprobada'
        ]);
    }

      // Endpoint para aprobar la excusa y generar PDF
      public function approvePdfAjax($id,$userID) {
        // Actualizar el estado a approved
        $approvedFor = $this->login_user->first_name . ' ' . $this->login_user->last_name;
        $provider = new Provider_model();
        $provider_user = $provider->where('user_id',$userID)->get()->getRow();

        if (!$provider_user) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Proveedor no encontrado'
            ]);
        }

        $data = [
            'state'        => 'approved',
            'approved_for' => $approvedFor,
            'approved_id' => $this->login_user->id,
            'provider'           => $provider_user->name,
            'provider_npi'       => $provider_user->npi,
            'privider_role'      => $provider_user->role, 
            'privider_signature' => $provider_user->url_signature,
            'provider_user_id'   => $provider_user->user_id,
        ];
        $this->excuseModel->ci_save($data, $id);
        // Generar la URL para ver el PDF en línea
        $pdfUrl = site_url("excuse/generatePdf/".$id."?mode=view");
        return $this->response->setJSON([
            'success' => true, 
            'message' => 'Excusa aprobada y PDF generado',
            'pdfUrl'  => $pdfUrl
        ]);
    }

    // GET excuse/denyAjax/{id}: deniega la excusa y devuelve JSON
    public function denyAjax($id,$userID) {
        $provider = new Provider_model();
        $provider_user = $provider->where('user_id',$userID)->get()->getRow();
        
        if (!$provider_user) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Proveedor no encontrado'
            ]);
        }
        
        $data = [
            'state' => 'denied',
            'provider'           => $provider_user->name,
            'provider_npi'       => $provider_user->npi,
            'privider_role'      => $provider_user->role, 
            'privider_signature' => $provider_user->url_signature,
            'provider_user_id'   => $provider_user->user_id,
        ];
        $this->excuseModel->ci_save($data, $id);
        return $this->response->setJSON([
            'success' => true, 
            'message' => 'Excusa denegada'
        ]);
    }

    public function clinicsAjax() {
        // Suponemos que $this->login_user->id contiene el ID del usuario actual
        $user_id = $this->login_user->id;
        
        // Usamos una consulta para obtener las clínicas a las que el usuario tiene acceso:
        $db = db_connect();
        $builder = $db->table('crm_branch');
        $builder->select('crm_clinic_directory.id, crm_clinic_directory.name, crm_clinic_directory.address, crm_clinic_directory.phone');
        $builder->join('crm_clinic_directory', 'crm_branch.id_clinic = crm_clinic_directory.id');
        $builder->where('crm_branch.id_user', $user_id);
        $query = $builder->get();
        $clinics = $query->getResult();
        
        return $this->response->setJSON(['success' => true, 'clinics' => $clinics]);
    }


    function generateQrCodeBase64($text) {
        // Comprobar que el archivo existe
        $qrLib = APPPATH . 'ThirdParty/phpqrcode/qrlib.php';
        if (!file_exists($qrLib)) {
            throw new \Exception("El archivo qrlib.php no se encontró en " . $qrLib);
        }
        ob_start();
        require_once $qrLib;
        // Genera el QR y lo envía a la salida (tamaño de módulo 3)
        \QRcode::png($text, null, QR_ECLEVEL_L, 3);
        $imageString = ob_get_contents();
        ob_end_clean();
        return 'data:image/png;base64,' . base64_encode($imageString);

    }
    

    public function generatePdf($id) {
        // Obtener la excusa por ID
        $excuse = $this->excuseModel->get_one($id);
        if (!$excuse || !isset($excuse->id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Excusa no encontrada']);
        }
        
        // Datos de la clínica (ajusta estos datos según tu sistema)
        $clinicName    = "Clínica Hispana Rubymed";
        $clinicLogo    = "https://www.clinicahispanarubymed.com/wp-content/uploads/2024/07/Logonuevo.png"; // Asegúrate de que la imagen exista
        
        // Generar URL para el código QR (este endpoint debe generar la imagen del QR)
        $qrUrl = site_url("excuse/validate?token=" . urlencode($excuse->token));

        // Generar el código QR en base64
        $qrDataUri = $this->generateQrCodeBase64("https://www.clinicahispanarubymed.com/excusevalidator/index.php?token=".$excuse->token);
        
        // Extraer la cadena base64 (quitando "data:image/png;base64,")
        $base64Data = str_replace('data:image/png;base64,', '', $qrDataUri);
        $qrBinary   = base64_decode($base64Data);
    
       
        $type = ($excuse->type == "medica_escolar" ? "Excusa Médica Escolar" : "Excusa Médica Laboral");
        $date_today = date("m-d-Y h:i A", strtotime($excuse->date_attention));
        $currentDateTime = date("m-d-Y h:i:s A");
        $qrImage = $this->generateQrCodeBase64($excuse->token);

           //Obtener logo de Clinicas
         $db = \Config\Database::connect();
         $builder = $db->table('crm_clinic_directory');
         $builder->select("*");
         $builder->where('id', $excuse->clinic_id);
         $query = $builder->get()->getRow();

         
        $name = strtoupper(($query->is_aliada == 0) ? "Clinica Hispana Rubymed ".$excuse->clinic : $excuse->clinic); 

        $html = '
        <html>
          <head>
            <style>
              body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
              .header { text-align: center; margin-bottom: 30px; }
              .header img { max-width: 150px; }
              .header h1 { margin: 10px 0; font-size: 24px; }
              .header p { margin: 5px 0; font-size: 14px; }
              .content p { margin: 10px 0; }
              .signature { margin-top: 40px; }
              .signature div { display: inline-block; width: 45%; text-align: center; }
              .qr { text-align: center; margin-top: 30px; }
              .qr img { max-width: 120px; }
              .logo {width: 200px !important;}
              .signature {width: 100px !important;}
              .text-center{ text-align: center; }
              .titleClinic{ text-transform: uppercase !important; }
            </style>
          </head>
          <body>
            <div class="header">';
               if($query->is_aliada == 0){
                    $html .= '<img class="logo" src="'.$query->logo.'" alt="Logo Clínica"> <br/>';
               }else{
                    $html .= '<img class="logo" src="" alt="Logo Clínica"> <br/>';
               }
        $html .= '
              <h3 class="titleClinic">'.$name.'</h3>
              <span><b>Address : </b>'.$excuse->clinic_address.'</span> <br/>
              <span><b>Phone : </b>'.$excuse->clinic_phone.'</span><br/>
            </div>
            <div class="content">
              <p class="text-center"><strong>EXCUSE FORM:</strong></p>
              <p>This is to certify that <strong> '.htmlspecialchars($excuse->name).'</strong></p>
              <p>Was seen at this medical office for professional medical service on: <strong>'.htmlspecialchars($date_today).'</p>
              <p>Please excuse his/her absence.</p>';

              if($type == "Excusa Médica Escolar"){
                $html .= '<p>Patient may return to school (<strong>X</strong>) or work (<strong></strong>) on <strong>'.date('m-d-Y', strtotime($excuse->date_end)).'</strong></p>';
              }
              if($type == "Excusa Médica Laboral"){
                $html .= '<p>Patient may return to school (<strong></strong>) or work (<strong>X</strong>) on <strong>'.date('m-d-Y', strtotime($excuse->date_end)).'</strong></p>';
              }
              
              $firm = 'https://www.clinicahispanarubymed.com/crm/writable/firmas/' . basename($excuse->privider_signature);
             // Extrae los primeros 8 caracteres en mayúsculas y el resto en minúsculas
                $tokenPrefix    = strtoupper(substr($excuse->token, 0, 8));
                $tokenRest      = strtolower(substr($excuse->token, 8));
                $tokenFormatted = $tokenPrefix . $tokenRest;
              
                $html .= '
                <p><strong>Reason/Restriction:</strong></p>
                <p>' . htmlspecialchars($excuse->reason) . '</p>
                <p><b>Sincerely,</b></p> <br><br>
                <span><img class="signature" src="' . $firm . '"></span> <br>
                <span><small>Electronic Signature Ref <b>' . $tokenFormatted . '</b> ' . $currentDateTime . '</small></span> <br>
                <span><b>Provider: </b>' . $excuse->provider . '</span> <br>
                <span><b>NPI: </b>' . $excuse->provider_npi . '</span> <br>
                <span><b>Roles: </b>' . $excuse->privider_role . '</span> <br>
                <br/><br/><br/>
                <small>
                  This document has been electronically signed and digitized by the RUBYMED INC. system. If you wish to validate the veracity or validity of this document, please visit www.clinicahispanarubymed.com/excusevalidator and enter the Electronic Reference number: <b>' . $tokenPrefix . '</b>.
                </small>

              </div>
              </body>
              </html>';

        // Crear una instancia de la librería Pdf (que extiende TCPDF)
        $pdf = new Pdf();
        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('helvetica', '', 10);
   

        // Definir posición y dimensiones para la imagen QR
        $x = 130; // posición X en el PDF (ajusta según tu diseño)
        $y = ($query->is_aliada == 0) ? 188 : 168;  // posición Y en el PDF (ajusta según tu diseño)198 logo antes rubyemd 100
        $w = 40;  // ancho
        $h = 40;  // alto

        // Insertar la imagen QR en el PDF usando el método Image() de TCPDF
        $pdf->Image('@' . $qrBinary, $x, $y, $w, $h, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);     
        


        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output("excusa_".$excuse->token.".pdf", 'D');
    }

      // GET excuse/check_new_excuses: verifica si hay excusas nuevas para notificaciones
    public function check_new_excuses() {
        $permissions = $this->login_user->permissions;
        $excuse_permission = get_array_value($permissions, "excuse_permission");
        $user_id = $this->login_user->id;
        $is_admin = $this->login_user->is_admin;
        
        $lastCheck = $this->request->getPost('last_check');
        if (!$lastCheck) {
            $lastCheck = date('Y-m-d H:i:s', strtotime('-1 hour')); // Por defecto, última hora
        }
        
        // Usar el servicio para manejar la lógica de notificaciones
        $notificationService = new \App\Services\ExcuseNotificationService();
        $result = $notificationService->checkNewExcuses($user_id, $excuse_permission, $is_admin, $lastCheck);
        
        // Agregar información de debug si está en modo desarrollo
        if (ENVIRONMENT === 'development') {
            $result['debug'] = [
                'user_id' => $user_id,
                'permission' => $excuse_permission,
                'is_admin' => $is_admin,
                'lastCheck' => $lastCheck,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->response->setJSON($result);
    }

    // GET excuse/notification_stats: obtiene estadísticas de notificaciones
    public function notification_stats() {
        $permissions = $this->login_user->permissions;
        $excuse_permission = get_array_value($permissions, "excuse_permission");
        $user_id = $this->login_user->id;
        $is_admin = $this->login_user->is_admin;
        
        $notificationService = new \App\Services\ExcuseNotificationService();
        $stats = $notificationService->getNotificationStats($user_id, $excuse_permission, $is_admin);
        
        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // GET excuse/notification_config: valida la configuración de notificaciones
    public function notification_config() {
        $permissions = $this->login_user->permissions;
        $excuse_permission = get_array_value($permissions, "excuse_permission");
        $user_id = $this->login_user->id;
        $is_admin = $this->login_user->is_admin;
        
        $notificationService = new \App\Services\ExcuseNotificationService();
        $config = $notificationService->validateNotificationConfig($user_id, $excuse_permission, $is_admin);
        
        return $this->response->setJSON([
            'success' => true,
            'config' => $config
        ]);
    }
}
