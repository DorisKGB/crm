<?php

namespace App\Models;

use CodeIgniter\Model;

class Notification_Token_Model extends Model
{
    protected $table = 'crm_notification_push';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_user', 'token_device'];

    // Uso de timestamps (created_at y updated_at)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Opcional: Si quieres retornar los datos como objetos en lugar de arrays
    // protected $returnType = 'object'; 


    /**
     * Guarda o actualiza el token de notificación para un usuario
     * @param int $user_id ID del usuario
     * @param string $token_device Token del dispositivo
     * @return bool|int ID del registro insertado/actualizado o false en caso de error
     */
    public function saveOrUpdateToken($user_id, $token_device) {
        if (!$user_id || !$token_device) {
            return false;
        }

        try {
            // Verificar si ya existe este token específico
            $existing_token = $this->where('token_device', $token_device)->first();
            if ($existing_token) {
                if ($existing_token['id_user'] == $user_id) {
                    return true;
                }
                // Si el token ya existe pero pertenece a otro usuario, actualizar
                if ($existing_token['id_user'] != $user_id) {
                    $result = $this->update($existing_token['id'], [
                        'id_user' => $user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    return $result ? $existing_token['id'] : false;
                } else {
                    // Si el token ya pertenece a este usuario, solo actualizar timestamp
                    $result = $this->update($existing_token['id'], [
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    return $result ? $existing_token['id'] : false;
                }
            } else {
                // Verificar si el usuario ya tiene un token (y eliminarlo si es necesario)
                $user_existing_token = $this->where('id_user', $user_id)->first();
                if ($user_existing_token ) {
                    // Eliminar token anterior del usuario
                    $this->delete($user_existing_token['id']);
                }
                
                // Crear nuevo token
                $insert_id = $this->insert([
                    'id_user' => $user_id,
                    'token_device' => $token_device
                ]);
                return $insert_id;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error en saveOrUpdateToken: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el token de notificación de un usuario
     * @param int $user_id ID del usuario
     * @return string|null Token del dispositivo o null si no existe
     */
    public function getUserToken($user_id) {
        if (!$user_id) {
            return null;
        }

        $token_data = $this->where('id_user', $user_id)->first();
        return $token_data ? $token_data['token_device'] : null;
    }

    /**
     * Obtiene todos los tokens de un usuario (por si tiene múltiples dispositivos)
     * @param int $user_id ID del usuario
     * @return array Array de tokens
     */
    public function getAllUserTokens($user_id) {
        if (!$user_id) {
            return [];
        }

        $tokens = $this->where('id_user', $user_id)->findAll();
        return array_column($tokens, 'token_device');
    }

    /**
     * Elimina el token de notificación de un usuario
     * @param int $user_id ID del usuario
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function deleteUserToken($user_id) {
        if (!$user_id) {
            return false;
        }

        return $this->where('id_user', $user_id)->delete();
    }

    /**
     * Elimina un token específico
     * @param string $token_device Token específico a eliminar
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function deleteSpecificToken($token_device) {
        if (!$token_device) {
            return false;
        }

        return $this->where('token_device', $token_device)->delete();
    }

    /**
     * Obtiene todos los usuarios con tokens activos
     * @return array Array de usuarios con sus tokens
     */
    public function getAllActiveTokens() {
        return $this->findAll();
    }

    /**
     * Verifica si un token existe
     * @param string $token_device Token a verificar
     * @return bool True si existe, false si no
     */
    public function tokenExists($token_device) {
        if (!$token_device) {
            return false;
        }

        $token = $this->where('token_device', $token_device)->first();
        return $token ? true : false;
    }

    /**
     * Obtiene el ID del usuario por token
     * @param string $token_device Token del dispositivo
     * @return int|null ID del usuario o null si no existe
     */
    public function getUserIdByToken($token_device) {
        if (!$token_device) {
            return null;
        }

        $token_data = $this->where('token_device', $token_device)->first();
        return $token_data ? $token_data['id_user'] : null;
    }  
}