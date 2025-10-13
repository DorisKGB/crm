<?php

namespace App\Models;

use App\Models\Crud_model;

class StampTemplate_model extends Crud_model
{

    protected $table = 'stamp_templates';
    protected $allowedFields = [
        'name',
        'image',
        'signature_x',
        'signature_y',
        'page_size',
        'show_provider',
        'show_npi',
        'show_role',
        'show_electronic_signature_text',
        'electronic_signature_text',
        'show_qr',
        'clinic_id',
        'deleted',
        // Campos de orientación
        'is_horizontal',
        'aspect_ratio',
        'rotation',
        'orientation'
    ];

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function getAllByClinicsOrGlobal(array $clinicIds, bool $include_deleted = false)
    {
        $builder = $this->db->table($this->table);
        // 1) Filtrar borrados lógicos si corresponde
        if (!$include_deleted) {
            $builder->where('deleted', 0);
        }
        $builder->groupStart()
            ->whereIn('clinic_id', $clinicIds) 
            ->orWhere('clinic_id IS NULL', null, false)
            ->groupEnd();
        return $builder->get()->getResult();
    }

    public function get_one_with_clinic($id)
    {
        $this->db_builder = $this->db->table($this->table . ' AS t');
        $this->db_builder->select('t.*, c.name AS clinic_name, c.address AS clinic_address');
        $this->db_builder->join(
            $this->db->prefixTable('clinic_directory') . ' AS c',
            'c.id = t.clinic_id',
            'left'
        );
        $result = $this->db_builder
                       ->where('t.id', (int)$id)
                       ->get(1)
                       ->getRow();
        if (!$result) {
            return $this->get_one($id);
        }

        return $result;
    }

}
