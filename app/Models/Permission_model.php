<?php

namespace App\Models;

class Permission_model extends Crud_model {

    protected $table = null;
    protected $table_clinic = null;

    function __construct() {
        $this->table = 'branch';
        $this->table_clinic = 'clinic_directory';
        parent::__construct($this->table);
    }

    function list_clinic() {
        $clinic_table = $this->db->prefixTable($this->table_clinic);
        $sql = "SELECT * FROM ".$clinic_table;
        return $this->db->query($sql)->getResultArray(); // Devuelve los datos como array asociativo
    }

    function list_branch_user($id_user) {
        $clinic_table = $this->db->prefixTable("branch");
        $sql = "SELECT * FROM $clinic_table WHERE id_user = '$id_user'";
        return $this->db->query($sql)->getResultArray(); // Devuelve los datos como array asociativo
    }

    function is_exits_count_table($id_user, $clinicID) {
        $branch_table = $this->db->prefixTable('branch');
        $sql = "SELECT COUNT(*) as count FROM $branch_table WHERE id_user = '$id_user' AND id_clinic = '$clinicID'";
        return $this->db->query($sql)->getResultArray();
    }

    function delete_data_table($id_user) {
        $ci_sessions_table = $this->db->prefixTable("branch");
        $sql = "DELETE FROM $ci_sessions_table WHERE id_user = '$id_user'";
        $this->db->query($sql);
    }

    function get_clockin_status($id_user, $clinic_id) {
        $branch_table = $this->db->prefixTable('branch');
        $sql = "SELECT clockin FROM $branch_table WHERE id_user = '$id_user' AND id_clinic = '$clinic_id'";
        $result = $this->db->query($sql)->getResultArray();
        return isset($result[0]['clockin']) ? (int)$result[0]['clockin'] : 0;
    }
   
   
}
