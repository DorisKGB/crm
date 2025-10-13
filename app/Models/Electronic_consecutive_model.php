<?php

namespace App\Models;

class Electronic_consecutive_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'electronic_consecutive';
        parent::__construct($this->table);
    }

    public function get_last_consecutive() {
        $consecutive_table = $this->db->prefixTable('electronic_consecutive');
        $sql = "SELECT consecutive FROM $consecutive_table ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql);
        
        if ($query->getNumRows() > 0) {
            return intval($query->getRow()->consecutive);
        }

        return 0; // Si no hay registros, iniciar en 0
    }

    /**
     * Genera un nuevo consecutivo de 3 dígitos
     */
    public function generate_next_consecutive() {
        $last_consecutive = $this->get_last_consecutive();
        return str_pad($last_consecutive + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Registra el nuevo consecutivo en la base de datos
     */
    public function insert_consecutive() {
        $consecutive_table = $this->db->prefixTable('electronic_consecutive'); // Aplica el prefijo correctamente
        $next_consecutive = $this->generate_next_consecutive();
    
        $data = [
            'consecutive' => $next_consecutive,
        ];
    
        $this->db->table($consecutive_table)->insert($data);
    
        return $next_consecutive;
    }

    /**
     * Reinicia el consecutivo a 001 (opcional)
     */
    /*public function reset_consecutive() {
        $this->db->query("TRUNCATE TABLE $this->table");
    }*/
}
