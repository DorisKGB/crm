<?php

namespace App\Models;

class Services_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'services';
        parent::__construct($this->table);
    }

    //find all app settings and login user's setting
    //user's settings are saved like this: user_[userId]_settings_name;
    function addService($name,$category_id,$state) {
        $services_table = $this->db->prefixTable('services');
        $sql = "INSERT INTO $services_table (name,category_id,state) VALUES ('$name','$category_id','$state')";
        return $this->db->query($sql);
    }
    function addCategory($name,$color) {
        $services_table = $this->db->prefixTable('category_service');
        $sql = "INSERT INTO $services_table (category,color) VALUES ('$name','$color')";
        return $this->db->query($sql);
    }

    function listService() {
        $servicesTable = $this->db->prefixTable('services');
        $categoryServiceTable = $this->db->prefixTable('category_service');
    
        $query = $this->db->table("$servicesTable AS bs")
            ->join("$categoryServiceTable AS cd", "bs.category_id = cd.id", 'inner')
            ->select("bs.*, cd.category AS category_name, cd.color AS category_color")
            ->where('bs.delete', 0)
            ->get(); 
            
        return $query->getResult();
    }

    function listCategory(){
        $categoryServiceTable = $this->db->prefixTable('category_service');
        $query = $this->db->table("$categoryServiceTable AS bs")
            ->select("*")
            ->where('bs.delete', 0)
            ->get(); 
        return $query->getResult();
    }

    function editService($id, $name,$category_id,$state) {
        $services_table = $this->db->prefixTable('services');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['name' => $name,'category_id' => $category_id ,'state' => $state]); // Actualizamos los valores 'name' y 'state'
        return $query; // Devuelve el resultado de la actualización
    }

    function editCategory($id, $name,$color) {
        $services_table = $this->db->prefixTable('category_service');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['category' => $name,'color' => $color]); // Actualizamos los valores 'name' y 'state'
        return $query; // Devuelve el resultado de la actualización
    }


    function deleteService($id) {
        $services_table = $this->db->prefixTable('services');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['delete' => '1']); // Actualizamos los valores 'name' y 'state'
        
        return $query; // Devuelve el resultado de la actualización
    }

    function deleteCategory($id) {
        $services_table = $this->db->prefixTable('category_service');
        // Realizamos la actualización en el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio que se va a actualizar
            ->update(['delete' => '1']); // Actualizamos los valores 'name' y 'state'
        
        return $query; // Devuelve el resultado de la actualización
    }

    function deleteServiceAction($id) {
        $services_table = $this->db->prefixTable('services');
        // Eliminamos el servicio con el ID proporcionado
        $query = $this->db->table($services_table)
            ->where('id', $id) // Especificamos el ID del servicio a eliminar
            ->delete(); // Ejecutamos la eliminación
        
        return $query; // Devuelve el resultado de la eliminación
    }


}