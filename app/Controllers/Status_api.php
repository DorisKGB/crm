<?php

namespace App\Controllers;

use App\Controllers\Security_Controller;

class Status_api extends Security_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener estado actual del usuario
     * GET: /status_api/get_status?user_id=X
     */
    public function get_status()
    {
        $user_id = $this->request->getGet('user_id') ?? $this->login_user->id ?? null;

        if (!$user_id) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'No se proporcionó ID de usuario'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->table('crm_user_call_status')->where('user_id', $user_id)->get();
            $result = $query->getRowArray();

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => $result['status'],
                    'user_id' => $result['user_id']
                ]);
            } else {
                // Crear registro si no existe
                $db->table('crm_user_call_status')->insert([
                    'user_id' => $user_id,
                    'status' => 'available',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'available',
                    'user_id' => $user_id
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error de base de datos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cambiar estado a disponible
     * GET: /status_api/set_available?user_id=X
     */
    public function set_available()
    {
        $user_id = $this->request->getGet('user_id') ?? $this->login_user->id ?? null;

        if (!$user_id) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'No se proporcionó ID de usuario'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Verificar si existe el registro
            $existing = $db->table('crm_user_call_status')->where('user_id', $user_id)->get()->getRowArray();
            
            if ($existing) {
                // Actualizar a 'available'
                $db->table('crm_user_call_status')
                   ->where('user_id', $user_id)
                   ->update([
                       'status' => 'available',
                       'updated_at' => date('Y-m-d H:i:s')
                   ]);
            } else {
                // Crear nuevo registro con 'available'
                $db->table('crm_user_call_status')->insert([
                    'user_id' => $user_id,
                    'status' => 'available',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'status' => 'available',
                'message' => 'Estado cambiado a disponible correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error de base de datos: ' . $e->getMessage()
            ]);
        }
    }
}
