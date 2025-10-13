<?php

namespace App\Models;
use App\Models\Crud_model; 

class Call_heartbeat_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'call_participant_heartbeat';
        parent::__construct($this->table);
    }

    protected $allowedFields = [
        'meeting_id', 'user_id', 'last_heartbeat', 
        'user_agent', 'ip_address', 'created_at', 'updated_at','deleted'
    ];

    /**
     * Actualizar o crear heartbeat de usuario
     */
    public function upsert_heartbeat($meeting_id, $user_id, $user_agent = null, $ip_address = null)
    {
        // Buscar registro existente
        $existing = $this->get_one_where([
            'meeting_id' => $meeting_id,
            'user_id' => $user_id
        ]);

        // Obtener timestamp actual del servidor MySQL una sola vez
        $now = $this->db->query('SELECT NOW() as now')->getRow()->now;

        $data = [
            'meeting_id' => $meeting_id,
            'user_id' => $user_id,
            'last_heartbeat' => $now,
            'user_agent' => $user_agent,
            'ip_address' => $ip_address
        ];

        if ($existing && isset($existing->id) && $existing->id) {
            // Actualizar registro existente
            $data['updated_at'] = $now;
            return $this->ci_save($data, $existing->id);
        } else {
            // Crear nuevo registro
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            return $this->ci_save($data);
        }
    }

    /**
     * Obtener participantes activos en una llamada
     */
  public function get_active_participants($meeting_id, $windowSeconds = 60)
{
    try {
        $heartbeat_table = $this->table;
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT 
                    h.user_id,
                    h.last_heartbeat,
                    h.meeting_id,
                    CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS user_name,
                    u.image AS user_image,
                    TIMESTAMPDIFF(SECOND, h.last_heartbeat, NOW()) AS seconds_since_last_heartbeat
                FROM $heartbeat_table h
                LEFT JOIN $users_table u ON u.id = h.user_id
                WHERE h.meeting_id = ?
                  AND h.deleted = 0
                  AND h.last_heartbeat >= DATE_SUB(NOW(), INTERVAL ? SECOND)
                ORDER BY h.last_heartbeat DESC";

        log_message('debug', "get_active_participants SQL: $sql con parámetros: meeting_id=$meeting_id, windowSeconds=$windowSeconds");
        
        // ⬇️ devuelve el objeto de consulta (NO getResult)
        return $this->db->query($sql, [$meeting_id, $windowSeconds]);
    } catch (\Throwable $e) {
        log_message('error', 'get_active_participants error: ' . $e->getMessage());
        // para que el controlador no truene si intenta getResult()
        return $this->db->query('SELECT 1 WHERE 0'); 
    }
}
    /**
     * Eliminar heartbeat específico
     */
    public function clear_user_heartbeat($meeting_id, $user_id)
    {
        return $this->db->table($this->table)
                       ->where('meeting_id', $meeting_id)
                       ->where('user_id', $user_id)
                       ->delete();
    }

    /**
     * Limpiar heartbeats antiguos (más de X minutos)
     */
    public function cleanup_old_heartbeats($minutes = 2)
    {
        $heartbeat_table = $this->table;
        
        $sql = "DELETE FROM $heartbeat_table 
                WHERE last_heartbeat < DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        return $this->db->query($sql, [$minutes]);
    }

    /**
     * Contar participantes activos por meeting
     */
    public function count_active_participants($meeting_id, $seconds = 8)
    {
        $heartbeat_table = $this->table;
        
        $sql = "SELECT COUNT(*) as total
                FROM $heartbeat_table 
                WHERE meeting_id = ? 
                AND deleted = 0
                AND last_heartbeat >= DATE_SUB(NOW(), INTERVAL ? SECOND)";

        log_message('debug', "count_active_participants SQL: $sql con parámetros: meeting_id=$meeting_id, seconds=$seconds");
        $result = $this->db->query($sql, [$meeting_id, $seconds])->getRow();
        return $result ? $result->total : 0;
    }

    /**
     * Verificar si un usuario específico está activo
     */
    public function is_user_active($meeting_id, $user_id, $seconds = 8)
    {
        $heartbeat_table = $this->table;
        
        $sql = "SELECT COUNT(*) as active
                FROM $heartbeat_table 
                WHERE meeting_id = ? 
                AND user_id = ?
                AND deleted = 0
                AND last_heartbeat >= DATE_SUB(NOW(), INTERVAL ? SECOND)";

        $result = $this->db->query($sql, [$meeting_id, $user_id, $seconds])->getRow();
        return $result ? $result->active > 0 : false;
    }
}