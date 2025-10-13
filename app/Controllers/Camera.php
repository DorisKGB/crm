<?php


namespace App\Controllers;

use App\Models\Clinic_model;
use App\Models\Camera_model;
use App\Models\Dvrs_model;

class Camera extends Security_Controller
{

  protected $clinic_model, $camera_model;

  public function __construct()
  {
    parent::__construct();
    $this->init_permission_checker("client");
    $this->clinic_model = new Clinic_model();
    $this->camera_model = new Camera_model();
        $this->dvrs_model = new Dvrs_model();
  }

  /**
   * Mostrar la página principal del módulo de reporte diario.
   */
  public function index()
  {
    /*$data = [
      'cameras' => $this->clinic_model->getClinicsWithCameras($this->login_user->id),
    ];
    return $this->template->rander("camera/index", $data);*/

     $clinics = $this->clinic_model->getClinicsWithCameras($this->login_user->id);

    foreach ($clinics as &$clinic) {
      $has_dvr = $this->dvrs_model->get_one_where([
        'clinic_id' => $clinic->id,
        'status' => 1
      ]);

      // Si hay DVR activo, poner true. Si no, false.
      $clinic->connect_dvr = isset($has_dvr->id) && $has_dvr->id ? true : false;
    }


    $data = [
      'cameras' => $clinics,
    ];

    return $this->template->rander("camera/index", $data);
  }

  public function dvr($clinic_id)
  {
    $clinic = $this->clinic_model->get_one($clinic_id);
    $dvr = $this->dvrs_model->get_one_where([
      'clinic_id' => $clinic_id,
      'status' => 1
    ]);
    $data = [
      'clinic' => $clinic,
      'dvr' => $dvr
    ];
    return $this->template->rander("camera/dvr", $data);
  }

  public function view($id_clinic)
  {
    $data = [
      'clinic' => $this->clinic_model->get_one($id_clinic),
      'cameras' => $this->camera_model->getCameraByClinicId($id_clinic)
    ]; 
    return $this->template->rander("camera/view", $data);
  }
  public function live($clinic_id) {
      $data['clinic'] = $this->clinic_model->get_one($clinic_id);
      $data['cameras'] = $this->camera_model->getCameraByClinicId($clinic_id);
      $data['user_id'] = $this->login_user->id;; // O como manejes el usuario

      return view('camera/live_monitoring', $data);
  }
}
