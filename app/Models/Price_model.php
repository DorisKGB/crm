<?php

namespace App\Models;

class Price_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'branch_services';
        parent::__construct($this->table);
    }

    function listPrice($clinic_id) {
        $services_table = $this->db->prefixTable('branch_services');   // Alias: bs
        $clinic_table = $this->db->prefixTable('clinic_directory');    // Alias: cd
        $serv_table = $this->db->prefixTable('services');  
        $category_table = $this->db->prefixTable('category_service');            // Alias: s
        // Construcción de la consulta con alias para evitar ambigüedades
        $query = $this->db->table("$services_table AS bs")
            ->select("bs.*, cd.name AS clinic_name, s.name AS service_name, ct.category as category, ct.color as color") // Seleccionamos columnas específicas con alias
            ->join("$clinic_table AS cd", "bs.clinic_id = cd.id", 'inner')  // Relación con clínica
            ->join("$serv_table AS s", "bs.service_id = s.id", 'inner')     // Relación con servicios
            ->join("$category_table AS ct", "s.category_id = ct.id", 'inner')
            ->where("bs.delete", 0)  // Asegurar que el filtro sea sobre la tabla correcta
            ->where("bs.clinic_id",$clinic_id)
            ->get(); // Ejecuta la consulta
    
        return $query->getResult();
    }

    function listPriceCustomer($clinic_id) {
        $services_table = $this->db->prefixTable('branch_services');   // Alias: bs
        $clinic_table = $this->db->prefixTable('clinic_directory');    // Alias: cd
        $serv_table = $this->db->prefixTable('services');  
        $category_table = $this->db->prefixTable('category_service');            // Alias: s
        // Construcción de la consulta con alias para evitar ambigüedades
        $query = $this->db->table("$services_table AS bs")
            ->select("bs.*, cd.name AS clinic_name, s.name AS service_name, ct.category as category, ct.color as color") // Seleccionamos columnas específicas con alias
            ->join("$clinic_table AS cd", "bs.clinic_id = cd.id", 'inner')  // Relación con clínica
            ->join("$serv_table AS s", "bs.service_id = s.id", 'inner')     // Relación con servicios
            ->join("$category_table AS ct", "s.category_id = ct.id", 'inner')
            ->where("bs.delete", 0)
            ->where("bs.state", 1)  // Asegurar que el filtro sea sobre la tabla correcta
            ->where("bs.clinic_id",$clinic_id)
            ->get(); // Ejecuta la consulta
    
        return $query->getResult();
    }

    function addPrice($clinic_id, $service_id,$description,$user_id,$price) {
        $i = $this->searchServiceForClinic($service_id, $clinic_id);
        if($i == 0){
            $services_table = $this->db->prefixTable('branch_services');
            $sql = "INSERT INTO $services_table (clinic_id, service_id,price,observation,assigned_by) VALUES ('$clinic_id', '$service_id','$price','$description','$user_id')";
            return $this->db->query($sql);
        }   
        return true;
    }

    
    function searchServiceForClinic($service_id, $clinic_id){
        $services_table = $this->db->prefixTable('branch_services');
        $query = $this->db->table($services_table)
            ->select("*") // Selecciona todos los campos de la tabla 'services'
            ->where('service_id', $service_id)
            ->where('clinic_id', $clinic_id)
            ->where('delete', 0)
            ->get(); // Ejecuta la consulta
        return $query->getNumRows(); // Obtiene el número de filas
    }

    
    function deleteService($id) {
        $services_table = $this->db->prefixTable('branch_services');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['delete' => '1']); // Actualizamos los valores 'name' y 'state'
        
        return $query; // Devuelve el resultado de la actualización
    }

    function editPrice($id, $description, $price ,$state) {
        $services_table = $this->db->prefixTable('branch_services');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['observation' => $description, 'price'=> $price,'state' => $state]); // Actualizamos los valores 'name' y 'state'
        
        return $query; // Devuelve el resultado de la actualización
    }
    
}
