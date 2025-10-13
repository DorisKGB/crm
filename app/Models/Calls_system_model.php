<?php

namespace App\Models;
use App\Models\Crud_model; 

class Calls_system_model extends Crud_model
{
    protected $table = null;
    protected $user_status_table = null;

    function __construct()
    {
        $this->table = 'calls_system';
        $this->user_status_table = 'user_call_status';
        parent::__construct($this->table);
        
        // Verificar que las tablas existan
        $this->ensure_tables_exist();
    }

        protected $allowedFields = [
        'caller_id','receiver_id','status',
        'start_time','answer_time','end_time','call_duration',
        'vsee_session_id','meeting_id','error_message',
        'created_at','updated_at','deleted', 'missed_call_acknowledged'
    ];

    
    /**
     * Verificar que las tablas del sistema de llamadas existan
     */
    private function ensure_tables_exist()
    {
        $db = \Config\Database::connect();
        
        // Verificar tabla calls_system
        if (!$db->tableExists($this->table)) {
            log_message('error', 'Tabla calls_system no existe. Ejecute la migración: php spark migrate');
        }
        
        // Verificar tabla user_call_status
        if (!$db->tableExists($this->user_status_table)) {
            log_message('error', 'Tabla user_call_status no existe. Ejecute la migración: php spark migrate');
        }
    }

    function get_details($options = array())
    {
        $calls_table = $this->db->prefixTable('calls_system');
        $users_table = $this->db->prefixTable('users');
        
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $calls_table.id=$id";
        }

        // AGREGAR SOPORTE PARA MEETING_ID
        $meeting_id = $this->_get_clean_value($options, "meeting_id");
        if ($meeting_id) {
            $where .= " AND $calls_table.meeting_id='" . $this->db->escapeString($meeting_id) . "'";
        }

        $caller_id = $this->_get_clean_value($options, "caller_id");
        if ($caller_id) {
            $where .= " AND $calls_table.caller_id=$caller_id";
        }

        $receiver_id = $this->_get_clean_value($options, "receiver_id");
        if ($receiver_id) {
            $where .= " AND $calls_table.receiver_id=$receiver_id";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $calls_table.status='$status'";
        }

        $sql = "SELECT $calls_table.*, 
                CONCAT(caller.first_name, ' ', caller.last_name) AS caller_name,
                CONCAT(receiver.first_name, ' ', receiver.last_name) AS receiver_name,
                caller.image AS caller_image,
                receiver.image AS receiver_image
                FROM $calls_table
                LEFT JOIN $users_table caller ON caller.id = $calls_table.caller_id
                LEFT JOIN $users_table receiver ON receiver.id = $calls_table.receiver_id
                WHERE $calls_table.deleted=0 $where";

        return $this->db->query($sql);
    }

    function create_call($data)
    {
        return $this->ci_save($data);
    }

    public function update_call_status($call_id, $status, $additional_data = [])
    {
        $data = array_merge(['status' => $status], $additional_data);
        return $this->ci_save($data, $call_id);
    }

    public function get_user_status($user_id)
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        // Verificar si existe la tabla de estado de llamadas
        if (!$this->db->tableExists($call_status_table)) {
            // Si no existe, crear estructura básica
            $this->create_call_status_table();
        }
        
        $sql = "SELECT * FROM $call_status_table 
                WHERE user_id = $user_id 
                AND deleted = 0";
        
        $result = $this->db->query($sql)->getRow();
        
        // Si no existe registro, crear uno por defecto
        if (!$result) {
            $this->set_user_status($user_id, 'available');
            $result = (object)[
                'user_id' => $user_id,
                'status' => 'available',
                'current_call_id' => null
            ];
        }
        
        return $result;
    }

    public function get_user_status2($user_id)
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        // Verificar si existe la tabla de estado de llamadas
        if (!$this->db->tableExists($call_status_table)) {
            // Si no existe, crear estructura básica
            $this->create_call_status_table();
            return null; // Tabla recién creada, no hay registros
        }
        
        $sql = "SELECT * FROM $call_status_table 
                WHERE user_id = ? 
                AND deleted = 0";
        
        $result = $this->db->query($sql, [$user_id])->getRow();
        
        // ✅ RETORNAR NULL SI NO EXISTE (no crear automáticamente)
        return $result;
    }

    public function set_user_status2($user_id, $status, $call_id = null)
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        // Verificar si existe la tabla
        if (!$this->db->tableExists($call_status_table)) {
            $this->create_call_status_table();
        }
        
        // Verificar si ya existe registro
        $existing = $this->db->query(
            "SELECT id FROM $call_status_table WHERE user_id = ? AND deleted = 0", 
            [$user_id]
        )->getRow();
        
        $data = [
            'user_id' => $user_id,
            'status' => $status,
            'current_call_id' => $call_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            if ($existing) {
                // ✅ ACTUALIZAR registro existente
                $result = $this->db->table($call_status_table)
                                ->where('id', $existing->id)
                                ->update($data);
                
                log_message('debug', "set_user_status2: Actualizando usuario $user_id a status '$status'");
                return $result;
                
            } else {
                // ✅ CREAR nuevo registro
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['deleted'] = 0;
                
                $result = $this->db->table($call_status_table)->insert($data);
                
                log_message('debug', "set_user_status2: Creando usuario $user_id con status '$status'");
                return $result;
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error en set_user_status2: " . $e->getMessage());
            return false;
        }
    }


    private function create_call_status_table()
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        $sql = "CREATE TABLE IF NOT EXISTS $call_status_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            status enum('available','busy','in_call','do_not_disturb') DEFAULT 'available',
            current_call_id int(11) DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            deleted tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY idx_user_status (user_id, deleted),
            KEY idx_call_id (current_call_id)
        )";
        
        $this->db->query($sql);
    }

    
    public function get_vsee_user_data(int $userId)
    {
        $table = $this->db->prefixTable('vsee_users');
        return $this->db->table($table)
                        ->where('user_id', (int)$userId)
                        ->where('deleted', 0)
                        ->get()
                        ->getRow();
    }

    public function get_call_by_meeting_id($meeting_id)
    {
        return $this->db_builder->where('meeting_id', $meeting_id)->get();
    }

    public function set_user_status($user_id, $status, $call_id = null)
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        // Verificar si ya existe registro
        $existing = $this->db->query("SELECT id FROM $call_status_table WHERE user_id = $user_id AND deleted = 0")->getRow();
        
        $data = [
            'user_id' => $user_id,
            'status' => $status,
            'current_call_id' => $call_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->table($call_status_table)
                    ->where('id', $existing->id)
                    ->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['deleted'] = 0;
            $this->db->table($call_status_table)->insert($data);
        }
        
        return true;
    }

    /*function get_available_users($exclude_user_id = null)
    {
        try {
            $users_table = $this->db->prefixTable('users');
            $user_status_table = $this->db->prefixTable('user_call_status');

            // Usar Query Builder para mayor compatibilidad
            $builder = $this->db->table($users_table . ' u');
            $builder->select('u.id, CONCAT(u.first_name, " ", u.last_name) AS full_name, u.image, COALESCE(s.status, "available") AS call_status');
            $builder->join($user_status_table . ' s', 's.user_id = u.id', 'left');
            $builder->where('u.deleted', 0);
            $builder->where('u.status', 'active');
            $builder->where('u.user_type', 'staff');
            
            if ($exclude_user_id) {
                $builder->where('u.id !=', $exclude_user_id);
            }
            
            $builder->orderBy('u.first_name');
            
            $query = $builder->get();
            
            if (!$query) {
                log_message('error', 'Error en get_available_users usando Query Builder');
                return false;
            }
            
            return $query;
            
        } catch (\Exception $e) {
            log_message('error', 'Excepción en get_available_users: ' . $e->getMessage());
            return false;
        }
    }*/

    public function get_available_users($exclude_user_id = null)
    {
        try {
            $users_table        = $this->db->prefixTable('users');
            $user_status_table  = $this->db->prefixTable('user_call_status');

            $builder = $this->db->table("$users_table u");
            $builder->select("
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                u.image,
                u.last_online,
                COALESCE(s.status, 'available') AS call_status
            ");
            $builder->join("$user_status_table s", "s.user_id = u.id", "left");

            // Solo usuarios activos del staff
            $builder->where("u.deleted", 0);
            $builder->where("u.status", "active");
            $builder->where("u.user_type", "staff");

            if ($exclude_user_id) {
                $builder->where("u.id !=", $exclude_user_id);
            }

            // ✅ ONLINE: last_online en los últimos 60s
            // Si guardas last_online en UTC, usa UTC_TIMESTAMP(). Si es hora local, cambia por NOW().
            $builder->where("u.last_online IS NOT NULL");
            $builder->where("DATE_ADD(u.last_online, INTERVAL 60 SECOND) >= UTC_TIMESTAMP()");

            // ✅ NO EN LLAMADA: solo 'available'
            $builder->where("COALESCE(s.status, 'available')", "available");

            $builder->orderBy("u.first_name", "ASC");

            $query = $builder->get();
            if (!$query) {
                log_message('error', 'Error en get_available_users usando Query Builder');
                return false;
            }

            return $query;

        } catch (\Exception $e) {
            log_message('error', 'Excepción en get_available_users: ' . $e->getMessage());
            return false;
        }
    }


    public function get_pending_calls_for_user($user_id)
    {
        $calls_table = $this->db->prefixTable('calls_system');
        $users_table = $this->db->prefixTable('users');
        
        $sql = "SELECT c.*, 
                    u_caller.first_name as caller_first_name,
                    u_caller.last_name as caller_last_name,
                    CONCAT(u_caller.first_name, ' ', u_caller.last_name) as caller_name,
                    u_caller.image as caller_image
                FROM $calls_table c
                LEFT JOIN $users_table u_caller ON u_caller.id = c.caller_id
                WHERE c.receiver_id = $user_id 
                AND c.status IN ('initiating','ringing')
                AND c.deleted = 0
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql);
    }


    /**
    * Obtener usuarios agrupados por clínicas
    */
    function get_users_grouped_by_clinics($exclude_user_id = null)
    {
        try {
            $users_table = $this->db->prefixTable('users');
            $clinic_table = $this->db->prefixTable('clinic_directory');
            $branch_table = $this->db->prefixTable('branch');
            $user_status_table = $this->db->prefixTable('user_call_status');

            $exclude_condition = "";
            if ($exclude_user_id) {
                $exclude_user_id = $this->_get_clean_value(['id' => $exclude_user_id], 'id');
                $exclude_condition = "AND $users_table.id != $exclude_user_id";
            }

            // Usuarios médicos (roles 1 y 5) agrupados por clínica
            $medical_sql = "SELECT 
                $clinic_table.id as clinic_id,
                $clinic_table.name as clinic_name,
                $users_table.id as user_id,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS full_name,
                $users_table.image,
                $users_table.role_id,
                $users_table.last_online,
                COALESCE(status_table.status, 'available') AS call_status,
                'medical' as user_type
                FROM $users_table
                INNER JOIN $branch_table ON $branch_table.id_user = $users_table.id
                INNER JOIN $clinic_table ON $clinic_table.id = $branch_table.id_clinic
                LEFT JOIN $user_status_table status_table ON status_table.user_id = $users_table.id AND status_table.deleted = 0
                WHERE $users_table.deleted = 0 
                AND $users_table.status = 'active' 
                AND $users_table.user_type = 'staff'
                AND $users_table.role_id IN (1, 5, 10, 19)
                AND $clinic_table.deleted = 0
                $exclude_condition
                ORDER BY $clinic_table.name, $users_table.first_name";

            $medical_users = $this->db->query($medical_sql);

            // Personal administrativo (otros roles, sin duplicados)
            $admin_sql = "SELECT 
                $users_table.id as user_id,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS full_name,
                $users_table.image,
                $users_table.role_id,
                $users_table.last_online,
                COALESCE(status_table.status, 'available') AS call_status,
                'administrative' as user_type,
                GROUP_CONCAT(DISTINCT $clinic_table.name ORDER BY $clinic_table.name SEPARATOR ', ') as clinic_names,
                COUNT(DISTINCT $branch_table.id_clinic) as clinic_count
                FROM $users_table
                INNER JOIN $branch_table ON $branch_table.id_user = $users_table.id
                INNER JOIN $clinic_table ON $clinic_table.id = $branch_table.id_clinic
                LEFT JOIN $user_status_table status_table ON status_table.user_id = $users_table.id AND status_table.deleted = 0
                WHERE $users_table.deleted = 0 
                AND $users_table.status = 'active' 
                AND $users_table.user_type = 'staff'
                AND $users_table.role_id NOT IN (1, 5, 19)
                $exclude_condition
                GROUP BY $users_table.id
                ORDER BY $users_table.first_name";

            $admin_users = $this->db->query($admin_sql);

            return [
                'medical_users' => $medical_users,
                'administrative_users' => $admin_users
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Excepción en get_users_grouped_by_clinics: ' . $e->getMessage());
            return false;
        }
    }

    public function get_user_call_history($options = [])
    {
        try {
            $calls_table = $this->db->prefixTable('calls_system');
            $users_table = $this->db->prefixTable('users');
            
            $user_id = $options['user_id'];
            $page = $options['page'] ?? 1;
            $limit = $options['limit'] ?? 20;
            $status_filter = $options['status_filter'] ?? 'all';
            $date_filter = $options['date_filter'] ?? 'all';
            $search = $options['search'] ?? '';
            
            $offset = ($page - 1) * $limit;
            
            // Construir WHERE clause
            $where_conditions = [
                "($calls_table.caller_id = $user_id OR $calls_table.receiver_id = $user_id)",
                "$calls_table.deleted = 0",
                "$calls_table.status IN ('completed', 'rejected', 'missed', 'failed')"
            ];
            
            // Filtro por estado
            if ($status_filter !== 'all') {
                $where_conditions[] = "$calls_table.status = '" . $this->db->escapeString($status_filter) . "'";
            }
            
            // Filtro por fecha
            if ($date_filter !== 'all') {
                $date_condition = $this->getDateFilterCondition($date_filter, $calls_table);
                if ($date_condition) {
                    $where_conditions[] = $date_condition;
                }
            }
            
            // Filtro de búsqueda
            if (!empty($search)) {
                $search_escaped = $this->db->escapeString($search);
                $where_conditions[] = "(
                    CONCAT(caller.first_name, ' ', caller.last_name) LIKE '%$search_escaped%' OR 
                    CONCAT(receiver.first_name, ' ', receiver.last_name) LIKE '%$search_escaped%'
                )";
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Consulta principal
            $sql = "SELECT 
                        $calls_table.*,
                        CONCAT(caller.first_name, ' ', caller.last_name) AS caller_name,
                        CONCAT(receiver.first_name, ' ', receiver.last_name) AS receiver_name,
                        caller.image AS caller_image,
                        receiver.image AS receiver_image
                    FROM $calls_table
                    LEFT JOIN $users_table caller ON caller.id = $calls_table.caller_id
                    LEFT JOIN $users_table receiver ON receiver.id = $calls_table.receiver_id
                    WHERE $where_clause
                    ORDER BY $calls_table.start_time DESC
                    LIMIT $limit OFFSET $offset";
            
            $calls = $this->db->query($sql);
            
            // Consulta para contar total
            $count_sql = "SELECT COUNT(*) as total
                        FROM $calls_table
                        LEFT JOIN $users_table caller ON caller.id = $calls_table.caller_id
                        LEFT JOIN $users_table receiver ON receiver.id = $calls_table.receiver_id
                        WHERE $where_clause";
            
            $total_result = $this->db->query($count_sql)->getRow();
            $total_calls = $total_result->total;
            $total_pages = ceil($total_calls / $limit);
            
            return [
                'calls' => $calls,
                'total_calls' => $total_calls,
                'total_pages' => $total_pages
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error en get_user_call_history: ' . $e->getMessage());
            return false;
        }
    }

    private function getDateFilterCondition($date_filter, $table)
    {
        switch ($date_filter) {
            case 'today':
                return "DATE($table.start_time) = CURDATE()";
            case 'yesterday':
                return "DATE($table.start_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            case 'week':
                return "$table.start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "$table.start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return null;
        }
    }

    public function set_user_status_atomic($user_id, $status, $call_id = null)
    {
        $call_status_table = $this->db->prefixTable('user_call_status');
        
        // Verificar si existe la tabla
        if (!$this->db->tableExists($call_status_table)) {
            $this->create_call_status_table();
        }
        
        try {
            // Usar transacción para operación atómica
            $this->db->transStart();
            
            // Verificar estado actual dentro de la transacción
            $current = $this->db->query(
                "SELECT id, status FROM $call_status_table WHERE user_id = ? AND deleted = 0", 
                [$user_id]
            )->getRow();
            
            // Si ya está en llamada, no hacer nada
            if ($current && $current->status === 'in_call') {
                $this->db->transRollback();
                return [
                    'updated' => false,
                    'message' => 'Usuario ya estaba marcado como en llamada',
                    'action' => 'no_change_needed'
                ];
            }
            
            // Preparar datos
            $data = [
                'user_id' => $user_id,
                'status' => $status,
                'current_call_id' => $call_id,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($current) {
                // Actualizar registro existente
                $this->db->table($call_status_table)
                        ->where('id', $current->id)
                        ->update($data);
                $action = 'updated';
            } else {
                // Crear nuevo registro
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['deleted'] = 0;
                $this->db->table($call_status_table)->insert($data);
                $action = 'created';
            }
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                return [
                    'updated' => false,
                    'message' => 'Error en la transacción',
                    'action' => 'error'
                ];
            }
            
            return [
                'updated' => true,
                'message' => $action === 'created' ? 'Usuario marcado como en llamada' : 'Estado actualizado',
                'action' => $action
            ];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error en set_user_status_atomic: ' . $e->getMessage());
            return [
                'updated' => false,
                'message' => 'Error de base de datos',
                'action' => 'error'
            ];
        }
    }

    
    /**
     * Get missed video calls for a user in the last 24 hours
     * 
     * @param int $user_id The user ID to get missed calls for
     * @return array Array of missed video calls
     */
    public function get_missed_video_calls_24h($user_id)
    {
        try {
            $calls_table = $this->db->prefixTable('calls_system');
            $users_table = $this->db->prefixTable('users');
            
            // Calculate 24 hours ago timestamp
            $twenty_four_hours_ago = date('Y-m-d H:i:s', strtotime('-24 hours'));
            
            // Use raw query for complex joins
            $sql = "
                SELECT 
                    cs.id,
                    cs.caller_id,
                    cs.receiver_id,
                    cs.status,
                    cs.start_time,
                    cs.answer_time,
                    cs.end_time,
                    cs.call_duration,
                    cs.vsee_session_id,
                    cs.meeting_id,
                    cs.error_message,
                    cs.created_at,
                    cs.updated_at,
                    cs.missed_call_acknowledged,
                    caller.first_name as caller_first_name,
                    caller.last_name as caller_last_name,
                    caller.image as caller_image,
                    receiver.first_name as receiver_first_name,
                    receiver.last_name as receiver_last_name,
                    receiver.image as receiver_image
                FROM {$calls_table} cs
                LEFT JOIN {$users_table} caller ON cs.caller_id = caller.id
                LEFT JOIN {$users_table} receiver ON cs.receiver_id = receiver.id
                WHERE cs.receiver_id = ?
                AND cs.status = 'missed'
                AND cs.missed_call_acknowledged = 0
                AND cs.start_time >= ?
                AND cs.deleted = 0
                ORDER BY cs.start_time DESC
            ";
            
            $query = $this->db->query($sql, [$user_id, $twenty_four_hours_ago]);
            
            if ($query->getNumRows() > 0) {
                return $query->getResultArray();
            }
            
            return [];
            
        } catch (\Exception $e) {
            log_message('error', 'Error en get_missed_video_calls_24h: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update missed_call_acknowledged status for a specific call
     * 
     * @param int $call_id The call ID to update
     * @return array Result of the update operation
     */
    public function acknowledge_missed_call($call_id)
    {
        try {
            $this->db->transStart();
            
            // Verify the call exists and is missed using raw query
            $calls_table = $this->db->prefixTable('calls_system');
            $verify_sql = "
                SELECT id FROM {$calls_table} 
                WHERE id = ? AND status = 'missed' AND deleted = 0
            ";
            $verify_query = $this->db->query($verify_sql, [$call_id]);
            
            if ($verify_query->getNumRows() === 0) {
                $this->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Call not found or not a missed call',
                    'action' => 'error'
                ];
            }
            
            // Update the missed_call_acknowledged status using raw query
            $update_sql = "
                UPDATE {$calls_table} 
                SET missed_call_acknowledged = 1, updated_at = ?
                WHERE id = ?
            ";
            $update_query = $this->db->query($update_sql, [date('Y-m-d H:i:s'), $call_id]);
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Error updating call acknowledgment',
                    'action' => 'error'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Call acknowledged successfully',
                'action' => 'acknowledged'
            ];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error en acknowledge_missed_call: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error',
                'action' => 'error'
            ];
        }
    }
}