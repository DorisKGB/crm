<?php

namespace App\Controllers;

use App\Models\Provider_model;

class Provider extends Security_Controller {

    protected $providerModel;

    public function __construct() {
        parent::__construct();
        $this->providerModel = new Provider_model();
    }

    // Endpoint para agregar proveedor vía AJAX
    public function storeAjax() {
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $rules = [
                'name' => 'required',
                'npi'  => 'required|numeric',
                'role' => 'required'
            ];
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors'  => $this->validator->getErrors()
                ]);
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'npi'  => $this->request->getPost('npi'),
                'role' => $this->request->getPost('role')
            ];

            $this->providerModel->ci_save($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Proveedor agregado exitosamente'
            ]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
    }

    // Opcional: Endpoint para obtener la lista de proveedores vía AJAX
    public function listAjax() {
        $providers = $this->providerModel->get_all(true)->getResult();
        return $this->response->setJSON(['success' => true, 'providers' => $providers]);
    }
}
