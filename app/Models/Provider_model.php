<?php


namespace App\Models;

class Provider_model extends Crud_model {

    protected $table = null;

    // Define los campos permitidos
    protected $allowedFields = [
        'name',
        'npi',
        'role',
        'url_signature',
        'deleted',
        'user_id'
    ];

    public function __construct() {
        $this->table = 'providers';
        parent::__construct($this->table);
    }

    public function get_all($include_deleted = false) {
        if (!$include_deleted) {
            $this->db_builder->where('deleted', 0);
        }
        $this->db_builder->orderBy('id', 'DESC');
        return $this->db_builder->get();
    }

    public function get_by_user_id($user_id) {
        $this->db_builder->where('user_id', $user_id);
        $this->db_builder->where('deleted', 0);
        return $this->db_builder->get()->getRow();
    }

    public function get_next_npi() {
        $this->db_builder->select('MAX(npi) as max_npi');
        $this->db_builder->where('deleted', 0);
        $result = $this->db_builder->get()->getRow();
        return $result ? ($result->max_npi + 1) : 1000000000;
    }

    public function getProviderStamps($id_clinic) {
        $providers_table = $this->db->prefixTable('providers');
        $branch_table = $this->db->prefixTable('branch');
        
        $id_clinic = $this->_get_clean_value(['id_clinic' => $id_clinic], 'id_clinic');
        
        $sql = "SELECT p.user_id 
                FROM $providers_table p
                INNER JOIN $branch_table b ON b.id_user = p.user_id
                WHERE p.deleted = 0 AND b.id_clinic = $id_clinic";
                
        $result = $this->db->query($sql)->getResult();
        
        // Extraer solo los user_id como array simple
        return array_column($result, 'user_id');
    }   
}
