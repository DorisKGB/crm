<?php

namespace App\Models;

use App\Models\Crud_model;

class VseeUsers_model extends Crud_model
{
    protected $table = 'vsee_users';
    protected $allowedFields = ['user_id', 'clinic_id', 'action', 'state','vsee_id','vsee_username','vsee_token'];
    protected $returnType = 'object';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function get_full_data()
    {
        $prefix = $this->db->getPrefix(); // obtener todas las clinicas...

        return $this->db->table($prefix . 'vsee_users l')
            ->select('l.*, CONCAT(u.first_name, " ", u.last_name) as user_name, c.name as clinic_name')
            ->join($prefix . 'users u', 'u.id = l.user_id')
            ->join($prefix . 'clinic_directory c', 'c.id = l.clinic_id', 'left')
            ->where('l.deleted', 0)
            ->get()
            ->getResult();
    }

    public function exists($user_id, $clinic_id, $exclude_id = null)
    {; // Existe un usuario asociado a la clninica
        $builder = $this->db_builder;
        $builder->where('user_id', $user_id);
        $builder->where('clinic_id', $clinic_id);
        $builder->where('deleted', 0);

        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }

        return $builder->countAllResults() > 0;
    }

    public function get_full_data_by_id($id)
    {
        // Existe un usuario asociado a la clninica
        try {
            $prefix = $this->db->getPrefix();

            $query = $this->db->table($prefix . 'vsee_users l')
                ->select('l.*, CONCAT(u.first_name, " ", u.last_name) as user_name, c.name as clinic_name')
                ->join($prefix . 'users u', 'u.id = l.user_id')
                ->join($prefix . 'clinic_directory c', 'c.id = l.clinic_id')
                ->where('l.deleted', 0)
                ->where('l.id', $id)
                ->get();

            if (!$query) {
                log_message('error', 'Consulta fallida en get_full_data_by_id con ID: ' . $id);
                return null;
            }

            return $query->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'Error en get_full_data_by_id: ' . $e->getMessage());
            return null;
        }
    }

    public function userHasAssignment($user_id, $exclude_id = null)
    {
        $builder = $this->db_builder;
        $builder->where('user_id', $user_id);
        $builder->where('deleted', 0);

        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }

        return $builder->countAllResults() > 0;
    }

    public function get_all_providers()
    {
        return $this->db_builder
            ->where('action', 'provider')
            ->where('state', 1)
            ->where('deleted', 0)
            ->get()
            ->getResult();
    }

    public function get_by_clinic($clinic_id, $exclude_id = null)
    {
        $builder = $this->db_builder;
        $builder->where('clinic_id', $clinic_id);
        $builder->where('deleted', 0);
        $builder->where('vsee_id IS NOT NULL');

        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }

        return $builder->get()->getRow();
    }
}
