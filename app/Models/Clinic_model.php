<?php

namespace App\Models;

class Clinic_model extends Crud_model
{

  protected $table = null;

  function __construct()
  {
    $this->table = 'clinic_directory';
    parent::__construct($this->table);
  }


  public function get_all($include_deleted = false): array
  {
     if (!$include_deleted) {
        $this->db_builder->where('deleted', 0);
    }
    // NOTA: ignoramos aquí el campo 'deleted'. Si quieres manejar borrados, hazlo manualmente.
    $query = $this->db_builder->get();      // SELECT * FROM clinic_directory
    return $query->getResult();             // array (vacío si no hay filas)
  }

  //Me trae el registro de todas las clinicas en formato OPTION para un SELECT
  public function getClinicOptions($user_id)
  {
    $clinics = $this->getClinicsCrediCardAvailable($user_id);
    $clinic_options = [];

    foreach ($clinics as $clinic) {
      $clinic_options[$clinic->id] = $clinic->name;
    }

    return $clinic_options;
  }

  public function getClinicOptionsAll($user_id)
  {
    $clinics = $this->getClinicsCrediCardAvailable($user_id);
    $clinic_options = [];

    foreach ($clinics as $clinic) {
      $clinic_options[$clinic->id] = $clinic->name;
    }

    return $clinic_options;
  }



  //Me trae el registro de todas las clinicas.
  public function getClinics()
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      return $this->db->table($clinic_table)
        ->select('id, name')
        ->get()
        ->getResult();
    } catch (\Exception $e) {
      log_message('error', "Error al obtener las clínicas: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $e->getMessage());
    }
  }

  //Me trae el registro de todas las clinicas asociadas a un usuario
  public function getClinics2($user_id)
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      $branch_table = $this->db->prefixTable('branch'); // Corrige "brach_table" por "branch_table"
      return $this->db->table($clinic_table)
        ->select("$clinic_table.id, $clinic_table.name") // Selecciona los campos de la tabla clinic_directory
        ->join($branch_table, "$branch_table.id_clinic = $clinic_table.id") // Realiza el INNER JOIN con branch
        ->where("$branch_table.id_user", $user_id) // Filtra por el user_id en branch
        ->get()
        ->getResult();
    } catch (\Exception $e) {
      log_message('error', "Error al obtener las clínicas: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $clinic_table);
    }
  }

  public function getClinicsWithCameras($user_id)
  {
    $clinic_table = $this->db->prefixTable('clinic_directory');
    $branch_table = $this->db->prefixTable('branch');
    $camera_table = $this->db->prefixTable('cameras');

    return $this->db->table($clinic_table)
      ->select("$clinic_table.id, $clinic_table.name")
      //->join($branch_table, "$branch_table.id_clinic = $clinic_table.id")
      ->join($camera_table, "$camera_table.clinic_id = $clinic_table.id")
      //->where("$branch_table.id_user", $user_id)
      ->where("$camera_table.status", 1)
      ->groupBy("$clinic_table.id")
      ->get()
      ->getResult();
  }

  public function getClinicsCrediCardAvailable($user_id)
  {
    try {
      $clinic_table = $this->db->prefixTable('clinic_directory');
      $branch_table = $this->db->prefixTable('branch'); // Corrige "brach_table" por "branch_table"
      $credi_card_table = $this->db->prefixTable('credit_card');
      return $this->db->table($clinic_table)
        ->select("$clinic_table.id, $clinic_table.name") // Selecciona los campos de la tabla clinic_directory
        ->join($branch_table, "$branch_table.id_clinic = $clinic_table.id") // Realiza el INNER JOIN con branch
        ->where("$branch_table.id_user", $user_id) // Filtra por el user_id en branch
        ->whereNotIn("$clinic_table.id", function ($query) use ($credi_card_table) {
          $query->select('clinic_id')->from($credi_card_table);
        })
        ->get()
        ->getResult();
    } catch (\Exception $e) {
      log_message('error', "Error al obtener las clínicas: " . $e->getMessage());
      throw new \Exception("Error al ejecutar la consulta en la tabla: " . $clinic_table);
    }
  }

  public function generateUnique8DigitId()
  {
    do {
      $id = random_int(10000000, 99999999);
      $exists = $this->db->table($this->table)
        ->where('id', $id)
        ->countAllResults();
    } while ($exists > 0);

    return $id;
  }
}
