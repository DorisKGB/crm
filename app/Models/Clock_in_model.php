<?php

namespace App\Models;

class Clock_in_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'clock_in';
        parent::__construct($this->table);
    }
    
    /**
     * Sobrescribe get_one_where para excluir registros eliminados por defecto
     */
    function get_one_where($where = array()) {
        // Agregar filtro de deleted = 0 si no se especifica
        if (!isset($where['deleted'])) {
            $where['deleted'] = 0;
        }
        
        // Usar query builder directamente para evitar problemas con db_builder
        $builder = $this->db->table($this->table);
        $where = $this->escape_array($where);
        $result = $builder->getWhere($where, 1);

        if ($result->getRow()) {
            return $result->getRow();
        } else {
            $db_fields = $this->db->getFieldNames($this->table);
            $fields = new \stdClass();
            foreach ($db_fields as $field) {
                $fields->$field = "";
            }
            return $fields;
        }
    }
    
    /**
     * Sobrescribe get_all_where para excluir registros eliminados por defecto
     */
    function get_all_where($where = array(), $limit = 1000000, $offset = 0, $sort_by_field = null) {
        // Agregar filtro de deleted = 0 si no se especifica
        if (!isset($where['deleted'])) {
            $where['deleted'] = 0;
        }
        
        // Usar query builder directamente para evitar problemas con db_builder
        $builder = $this->db->table($this->table);
        $where = $this->escape_array($where);
        
        $where_in = get_array_value($where, "where_in");
        if ($where_in) {
            foreach ($where_in as $key => $value) {
                $builder->whereIn($key, $value);
            }
            unset($where["where_in"]);
        }

        if ($sort_by_field) {
            $builder->orderBy($sort_by_field, 'ASC');
        }

        return $builder->getWhere($where, $limit, $offset);
    }
    
    public function get_by_range($userId, $from, $to)
    {
        return $this->db->table('clock_in')
            ->where('user_id', $userId)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->where('deleted', 0) // Excluir registros eliminados
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->getResult('object'); // Muy importante
    }

    /**
     * Obtiene registros incluyendo los eliminados (para importaciones)
     */
    public function get_by_range_including_deleted($userId, $from, $to)
    {
        return $this->db->table('clock_in')
            ->where('user_id', $userId)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->getResult('object');
    }

    /**
     * Obtiene un registro por condiciones incluyendo eliminados (para importaciones)
     */
    public function get_one_where_including_deleted($conditions)
    {
        $builder = $this->db->table($this->table);
        foreach ($conditions as $key => $value) {
            $builder->where($key, $value);
        }
        return $builder->get()->getRow();
    }

    /**
     * Obtiene todos los registros por condiciones incluyendo eliminados (para importaciones)
     */
    public function get_all_where_including_deleted($conditions)
    {
        $builder = $this->db->table($this->table);
        foreach ($conditions as $key => $value) {
            $builder->where($key, $value);
        }
        return $builder->get();
    }

    /**
     * Cuenta registros incluyendo eliminados (para importaciones)
     */
    public function count_where_including_deleted($conditions)
    {
        $builder = $this->db->table($this->table);
        foreach ($conditions as $key => $value) {
            $builder->where($key, $value);
        }
        return $builder->countAllResults();
    }

    /**
     * Elimina lógicamente un registro (marca como deleted = 1)
     */
    public function soft_delete($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['deleted' => 1]);
    }

    /**
     * Restaura un registro eliminado lógicamente (marca como deleted = 0)
     */
    public function restore($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['deleted' => 0]);
    }

    /**
     * Elimina físicamente un registro (eliminación permanente)
     */
    public function hard_delete($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

}