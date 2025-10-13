<?php

namespace App\Models;

class Sessions_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'ci_sessions';
        parent::__construct($this->table);
    }

    /**
     * Actualizar timestamp de la sesión
     */
    function update_session_timestamp($session_id)
    {
        try {
            $sessions_table = $this->db->prefixTable('ci_sessions');
            $current_timestamp = time();
            
            $data = array(
                'timestamp' => $current_timestamp
            );
            
            $where = array('id' => $session_id);
            
            return $this->update_where($data, $where);
            
        } catch (\Exception $e) {
            log_message('error', 'Error actualizando timestamp de sesión: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si la sesión existe y está activa
     */
    function is_session_active($session_id)
    {
        try {
            $sessions_table = $this->db->prefixTable('ci_sessions');
            $current_timestamp = time();
            $session_timeout = 7200; // 2 horas en segundos
            
            $sql = "SELECT COUNT(*) as count 
                    FROM $sessions_table 
                    WHERE id = ? 
                    AND timestamp > ?";
            
            $min_timestamp = $current_timestamp - $session_timeout;
            $result = $this->db->query($sql, [$session_id, $min_timestamp]);
            $row = $result->getRow();
            
            return $row && $row->count > 0;
            
        } catch (\Exception $e) {
            log_message('error', 'Error verificando sesión activa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar sesiones expiradas
     */
    function clean_expired_sessions()
    {
        try {
            $sessions_table = $this->db->prefixTable('ci_sessions');
            $current_timestamp = time();
            $session_timeout = 7200; // 2 horas en segundos
            
            $sql = "DELETE FROM $sessions_table 
                    WHERE timestamp < ?";
            
            $min_timestamp = $current_timestamp - $session_timeout;
            $result = $this->db->query($sql, [$min_timestamp]);
            
            return $result !== false;
            
        } catch (\Exception $e) {
            log_message('error', 'Error limpiando sesiones expiradas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información de la sesión
     */
    function get_session_info($session_id)
    {
        try {
            $sessions_table = $this->db->prefixTable('ci_sessions');
            
            $sql = "SELECT * FROM $sessions_table WHERE id = ?";
            $result = $this->db->query($sql, [$session_id]);
            
            if ($result && $result->getNumRows() > 0) {
                return $result->getRow();
            }
            
            return null;
            
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo información de sesión: ' . $e->getMessage());
            return null;
        }
    }
}
