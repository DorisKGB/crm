<?php

namespace App\Models;

class Excuse_model extends Crud_model {

    // Definimos la tabla (se aplicará el prefijo configurado)
    protected $table = 'excuse';
    
    // Habilitamos el manejo automático de timestamps (opcional, si Crud_model extiende de CodeIgniter\Model)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Definimos los campos permitidos (excluimos created_at y updated_at, que se gestionan automáticamente)
    protected $allowedFields = [
        'token',
        'name',
        'birth',
        'type',
        'reason',
        'date_start',
        'date_end',
        'provider',
        'provider_npi',
        'privider_role',
        'privider_signature',
        'provider_user_id',
        'state',
        'deleted',
        'clinic',
        'clinic_phone',
        'clinic_address',
        'date_attention',
        'generate_for',
        'generate_name'
    ];

    public function __construct() {
        // Llama al constructor de Crud_model pasando el nombre de la tabla
        parent::__construct($this->table);
    }

    public function get_all($include_deleted = false) {
        $this->db_builder->orderBy('created_at', 'DESC');
        return $this->db_builder->get();
    }

    public function get_all_where($where = array(), $limit = 1000000, $offset = 0, $sort_by_field = null) {
        $where = $this->escape_array($where);
        $where_in = get_array_value($where, "where_in");
        if ($where_in) {
            foreach ($where_in as $key => $value) {
                $this->db_builder->whereIn($key, $value);
            }
            unset($where["where_in"]);
        }
        
        // Ordenar de forma descendente por created_at
        $this->db_builder->orderBy('created_at', 'DESC');
        
        // Si se requiere ordenar adicionalmente por otro campo, se puede agregar aquí
        if ($sort_by_field) {
            $this->db_builder->orderBy($sort_by_field, 'ASC');
        }
        
        return $this->db_builder->getWhere($where, $limit, $offset);
    }

     /**
     * Obtiene excusas nuevas para notificaciones según el tipo de usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $permission Permiso del usuario (provider, all, request)
     * @param bool $is_admin Si el usuario es administrador
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    public function get_new_excuses_for_notifications($user_id, $permission, $is_admin, $last_check) {
        $builder = $this->db->table($this->table . ' AS e');
        
        // Aplicar filtros base
        $builder->where('e.deleted', 0);
        $builder->where('e.state', 'request');
        $builder->where('e.created_at >', $last_check);
        
        // Aplicar filtros según el tipo de usuario
        if ($permission === "provider") {
            // Para proveedores: buscar excusas pendientes en sus clínicas
            $builder->join('crm_branch AS b', 'e.clinic_id = b.id_clinic', 'inner');
            $builder->where('b.id_user', $user_id);
        } elseif ($permission === "all" || $is_admin) {
            // Para admins y usuarios con permiso "all": todas las excusas
            // No necesitamos filtros adicionales
        } else {
            // Para otros usuarios: solo sus propias excusas
            $builder->where('e.generate_for', $user_id);
        }
        
        // Ordenar y limitar
        $builder->orderBy('e.created_at', 'DESC');
        $builder->limit(1);
        
        $excuses = $builder->get()->getResult();
        
        return [
            'excuses' => $excuses,
            'count' => count($excuses),
            'has_new' => count($excuses) > 0
        ];
    }

    /**
     * Obtiene excusas pendientes para un proveedor específico
     * 
     * @param int $provider_id ID del proveedor
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    public function get_pending_excuses_for_provider($provider_id, $last_check) {
        $builder = $this->db->table($this->table . ' AS e');
        
        // JOIN con crm_branch para verificar acceso a clínicas
        $builder->join('crm_branch AS b', 'e.clinic_id = b.id_clinic', 'inner');
        
        // Filtros
        $builder->where('b.id_user', $provider_id);
        $builder->where('e.deleted', 0);
        $builder->where('e.state', 'request');
        $builder->where('e.created_at >', $last_check);
        
        // Ordenar y limitar
        $builder->orderBy('e.created_at', 'DESC');
        $builder->limit(1);
        
        $excuses = $builder->get()->getResult();
        
        return [
            'excuses' => $excuses,
            'count' => count($excuses),
            'has_new' => count($excuses) > 0
        ];
    }

    /**
     * Obtiene excusas pendientes para administradores
     * 
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    public function get_pending_excuses_for_admin($last_check) {
        $builder = $this->db->table($this->table . ' AS e');
        
        // Filtros
        $builder->where('e.deleted', 0);
        $builder->where('e.state', 'request');
        $builder->where('e.created_at >', $last_check);
        
        // Ordenar y limitar
        $builder->orderBy('e.created_at', 'DESC');
        $builder->limit(1);
        
        $excuses = $builder->get()->getResult();
        
        return [
            'excuses' => $excuses,
            'count' => count($excuses),
            'has_new' => count($excuses) > 0
        ];
    }

    /**
     * Obtiene excusas pendientes para usuarios con permiso "all"
     * 
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    public function get_pending_excuses_for_all_permission($last_check) {
        return $this->get_pending_excuses_for_admin($last_check);
    }

    /**
     * Obtiene excusas pendientes para usuarios con permiso "request"
     * 
     * @param int $user_id ID del usuario
     * @param string $last_check Timestamp de la última verificación
     * @return array Resultado con excusas encontradas
     */
    public function get_pending_excuses_for_request_permission($user_id, $last_check) {
        $builder = $this->db->table($this->table . ' AS e');
        
        // Filtros
        $builder->where('e.generate_for', $user_id);
        $builder->where('e.deleted', 0);
        $builder->where('e.state', 'request');
        $builder->where('e.created_at >', $last_check);
        
        // Ordenar y limitar
        $builder->orderBy('e.created_at', 'DESC');
        $builder->limit(1);
        
        $excuses = $builder->get()->getResult();
        
        return [
            'excuses' => $excuses,
            'count' => count($excuses),
            'has_new' => count($excuses) > 0
        ];
    }

    /**
     * Formatea los datos de una excusa para notificaciones
     * 
     * @param object $excuse Objeto de excusa
     * @return array Datos formateados
     */
    public function format_excuse_for_notification($excuse) {
        return [
            'excuseId' => $excuse->id,
            'patientName' => $excuse->name,
            'excuseType' => $excuse->type === 'medica_escolar' ? 'Excusa Médica Escolar' : 'Excusa Médica Laboral',
            'clinic' => $excuse->clinic,
            'createdAt' => $excuse->created_at
        ];
    }
}
