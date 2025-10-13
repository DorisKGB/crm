<?php

namespace App\Models;

use App\Models\Crud_model;


class Stamp_model extends Crud_model
{

    // Definimos la tabla (se aplicará el prefijo configurado)
    protected $table = 'stamp';

    // Habilitamos el manejo automático de timestamps (opcional, si Crud_model extiende de CodeIgniter\Model)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'clinic_id',
        'clinic_select',
        'provider_id',
        'size',
        'description',
        'token',
        'deleted',
        'template_name',
        'template_image',
        'signature_y',
        'signature_x',
        'page_size',
        'generate_for',
        'generate_name',
        'stamped',
        'provider',
        'provider_npi',
        'provider_role',
        'provider_signature',
        'provider_user_id',
        'approved',
        // ✅ Campos de orientación agregados
        'orientation',
        'is_horizontal',
        'rotation',
        'aspect_ratio'
    ];

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function get_all($include_deleted = false)
    {
        $this->db_builder->orderBy('created_at', 'DESC');
        return $this->db_builder->get();
    }

        /**
     * Obtiene timbres pendientes para proveedores
     * 
     * @param int $user_id ID del usuario proveedor
     * @param string $last_check Timestamp de la última verificación
     * @return array Timbres encontrados
     */
    public function get_pending_stamps_for_provider($user_id, $last_check)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' AS s');
        
        // Hacer JOIN con crm_branch para validar el acceso a la clínica
        $builder->join('crm_branch AS b', 's.clinic_id = b.id_clinic', 'inner');
        
        // Seleccionar solo registros únicos
        $builder->select('DISTINCT s.*', false);
        
        // Condiciones: El usuario debe ser el proveedor o el generador del timbre
        $builder->groupStart();
        $builder->where('s.provider_user_id', $user_id);
        $builder->orWhere('s.generate_for', $user_id);
        $builder->orWhere('b.id_user', $user_id);
        $builder->groupEnd();
        
        // Filtrar por timbres creados después de last_check
        $builder->where('s.created_at >', $last_check);
        
        // Filtrar por timbres no eliminados
        $builder->where('s.deleted', 0);
        
        // Ordenar por la fecha de creación (más recientes primero)
        $builder->orderBy('s.created_at', 'DESC');
        
        // Limitar a los últimos 10 timbres
        $builder->limit(10);
        
        return $builder->get()->getResult();
    }

    /**
     * Obtiene timbres pendientes para usuarios con permiso 'all'
     * 
     * @param string $last_check Timestamp de la última verificación
     * @return array Timbres encontrados
     */
    public function get_pending_stamps_for_all_permission($last_check)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' AS s');
        
        // Filtrar por timbres creados después de last_check
        $builder->where('s.created_at >', $last_check);
        
        // Filtrar por timbres no eliminados
        $builder->where('s.deleted', 0);
        
        // Ordenar por la fecha de creación (más recientes primero)
        $builder->orderBy('s.created_at', 'DESC');
        
        // Limitar a los últimos 10 timbres
        $builder->limit(10);
        
        return $builder->get()->getResult();
    }

    /**
     * Obtiene timbres pendientes para usuarios con permiso 'request'
     * 
     * @param int $user_id ID del usuario
     * @param string $last_check Timestamp de la última verificación
     * @return array Timbres encontrados
     */
    public function get_pending_stamps_for_request_permission($user_id, $last_check)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' AS s');
        
        // Filtrar por timbres generados para este usuario
        $builder->where('s.generate_for', $user_id);
        
        // Filtrar por timbres creados después de last_check
        $builder->where('s.created_at >', $last_check);
        
        // Filtrar por timbres no eliminados
        $builder->where('s.deleted', 0);
        
        // Ordenar por la fecha de creación (más recientes primero)
        $builder->orderBy('s.created_at', 'DESC');
        
        // Limitar a los últimos 10 timbres
        $builder->limit(10);
        
        return $builder->get()->getResult();
    }

    /**
     * Obtiene timbres pendientes para administradores
     * 
     * @param string $last_check Timestamp de la última verificación
     * @return array Timbres encontrados
     */
    public function get_pending_stamps_for_admin($last_check)
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table . ' AS s');
        
        // Filtrar por timbres creados después de last_check
        $builder->where('s.created_at >', $last_check);
        
        // Filtrar por timbres no eliminados
        $builder->where('s.deleted', 0);
        
        // Ordenar por la fecha de creación (más recientes primero)
        $builder->orderBy('s.created_at', 'DESC');
        
        // Limitar a los últimos 10 timbres
        $builder->limit(10);
        
        return $builder->get()->getResult();
    }

    /**
     * Obtiene timbres recientes para debugging (últimas 24 horas)
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario
     * @param bool $is_admin Si el usuario es administrador
     * @return array Timbres encontrados
     */
    public function get_recent_stamps_for_debug($user_id, $permission, $is_admin)
    {
        $last_24h = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        // Usar el método específico según el tipo de usuario
        if ($permission === 'provider') {
            return $this->get_pending_stamps_for_provider($user_id, $last_24h);
        } elseif ($permission === 'all') {
            return $this->get_pending_stamps_for_all_permission($last_24h);
        } elseif ($permission === 'request') {
            return $this->get_pending_stamps_for_request_permission($user_id, $last_24h);
        } elseif ($is_admin) {
            return $this->get_pending_stamps_for_admin($last_24h);
        }

        return [];
    }
}
