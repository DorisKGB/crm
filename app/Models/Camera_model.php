<?php

namespace App\Models;

class Camera_model extends Crud_model {

    protected $table = null;

    function __construct()
    {
        $this->table = 'cameras';
        parent::__construct($this->table);
    }

    // Obtiene clínicas con límite y offset, incluyendo el contador de miembros (usando crm_branch)
    public function getCameraByClinicId(int $Clinic_id){
        return $this->get_all_where(['clinic_id' => $Clinic_id]);
    }
    
    // Retorna el total de clínicas
    public function countAllClinics(){
        return $this->db->table($this->table)->countAllResults();
    }
}
