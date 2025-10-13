<?php

namespace App\Models;

class Usa_states_model extends Crud_model {

    protected $table = null;

    function __construct()
    {
        $this->table = 'usa_states';
        parent::__construct($this->table);
    }

     public function get_all_states_for_api()
    {
        try {
            return $this->db_builder
                ->select('id, nombre as name, abreviacion as code, capital, poblacion, area_km2, region')
                ->where('deleted', 0)
                ->orderBy('nombre', 'ASC')
                ->get()
                ->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_all_states_for_api error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_state_by_code($code)
    {
        try {
            return $this->db_builder
                ->select('id, nombre as name, abreviacion as code, capital, poblacion, area_km2, region')
                ->where('abreviacion', $code)
                ->where('deleted', 0)
                ->get()
                ->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'get_state_by_code error: ' . $e->getMessage());
            return null;
        }
    }

     /**
     * Buscar estado por nombre
     */
    public function get_state_by_name($name)
    {
        try {
            return $this->db_builder
                ->select('id, nombre as name, abreviacion as code, capital, poblacion, area_km2, region')
                ->where('nombre', $name)
                ->where('deleted', 0)
                ->get()
                ->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'get_state_by_name error: ' . $e->getMessage());
            return null;
        }
    }

     public function validate_state_exists($identifier)
    {
        try {
            return $this->db_builder
                ->where('deleted', 0)
                ->groupStart()
                    ->where('abreviacion', $identifier)
                    ->orWhere('nombre', $identifier)
                ->groupEnd()
                ->countAllResults() > 0;
        } catch (\Throwable $e) {
            log_message('error', 'validate_state_exists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estados por región
     */
    public function get_states_by_region($region)
    {
        try {
            return $this->db_builder
                ->select('id, nombre as name, abreviacion as code, capital, region')
                ->where('region', $region)
                ->where('deleted', 0)
                ->orderBy('nombre', 'ASC')
                ->get()
                ->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'get_states_by_region error: ' . $e->getMessage());
            return [];
        }
    }
}