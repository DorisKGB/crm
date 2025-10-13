<?php

namespace App\Controllers;
use App\Models\Daily_report_model;
use App\Models\Services_model;
use App\Models\Price_model;

class Price extends Security_Controller {

    protected $dailyReportModel;
    protected $Services_model;
    protected $Price_model;

    function __construct() {
        parent::__construct();
        $this->dailyReportModel = new Daily_report_model();
        $this->Services_model = new Services_model();
        $this->Price_model = new Price_model();

        $this->init_permission_checker("client");
    }

    protected function getClinicOptions2($user_id)
    {
      $clinics = $this->dailyReportModel->getClinics2($user_id);
      $clinic_options = [];
  
      foreach ($clinics as $clinic) {
        $clinic_options[$clinic->id] = $clinic->name;
      }
  
      return $clinic_options;
    }
  

    function index() {

        if (!$this->can_access_price_create()) {
            app_redirect("forbidden");
            return;
        }
     

        $clinic_options = $this->getClinicOptions2($this->login_user->id);
        $list_service = $this->Services_model->listService();

        $service_options = [];
        foreach ($list_service as $service) {
            if($service->state == 1){
                $service_options[$service->id] = $service->name;
            }
        }
  
        $data = [
            'clinic_options' => $clinic_options,
            'list_service' => $service_options
          ];
        return $this->template->rander("price/index", $data);
    }

    function customer() {

        if (!$this->can_access_price_view()) {
            app_redirect("forbidden");
            return;
        }

        $clinic_options = $this->getClinicOptions2($this->login_user->id);
        $list_service = $this->Services_model->listService();

        $service_options = [];
        foreach ($list_service as $service) {
            if($service->state == 1){
                $service_options[$service->id] = $service->name;
            }
        }
  
        $data = [
            'clinic_options' => $clinic_options,
            'list_service' => $service_options
          ];
        return $this->template->rander("price/customer", $data);
    }


    function service(){
        if (!$this->can_access_price_create()) {
            app_redirect("forbidden");
            return;
        }
     
        $clinic_options = $this->getClinicOptions2($this->login_user->id);
        $list_category = $this->Services_model->listCategory();

        $category_options = [];
        foreach ($list_category as $category) {
            $category_options[$category->id] = $category->category;
        }

        $data = [
            'clinic_options' => $clinic_options,
            'category_options' => $category_options
          ];
        return $this->template->rander("price/service", $data);
    }

    function addService(){
        $name = $this->request->getGet('name');
        $state = $this->request->getGet('state');
        $category_id = $this->request->getGet('category_id');
        $data = $this->Services_model->addService($name,$category_id,$state);
        return $this->response->setJSON($data);
    }

    function listService(){
        $data = $this->Services_model->listService();
        return $this->response->setJSON($data);
    }

    function createCategory(){
        $name = $this->request->getGet('name');
        $color = $this->request->getGet('color');
        $state = $this->request->getGet('state');
        $data = $this->Services_model->addCategory($name,$color);
        return $this->response->setJSON($data);
    }
    function editCategory(){
        $name = $this->request->getGet('name');
        $color = $this->request->getGet('color');
        $id = $this->request->getGet('id');
        $data = $this->Services_model->editCategory($id,$name,$color);
        return $this->response->setJSON($data);
    }

    function listCategory(){
        $list_category = $this->Services_model->listCategory();
        return $this->response->setJSON($list_category);
    }

    function editService(){
        $id = $this->request->getGet('id');
        $name = $this->request->getGet('name');
        $category_id = $this->request->getGet('category_id');
        $state = $this->request->getGet('state');
        $data = $this->Services_model->editService($id, $name,$category_id, $state);
        
        return $this->response->setJSON($data);
    }

    function deleteService(){
        $id = $this->request->getGet('id');
        $data = $this->Services_model->deleteService($id);
        return $this->response->setJSON($data);
    }
    function deleteCategory(){
        $id = $this->request->getGet('id');
        $data = $this->Services_model->deleteCategory($id);
        return $this->response->setJSON($data);
    }

    function addPrice(){
        $clinic_id = $this->request->getGet('clinic_id');
        $examen_id = $this->request->getGet('examen_id');
        $description = $this->request->getGet('description');
        $price = $this->request->getGet('price');
        $user = $this->request->getGet('user');
        $data = $this->Price_model->addPrice($clinic_id,$examen_id,$description,$user,$price);
        return $this->response->setJSON($data);

    }
    function listPrice(){
        $clinic_id = $this->request->getGet('clinic_id');
        $data = $this->Price_model->listPrice($clinic_id);  
        return $this->response->setJSON($data);
    }

    function listPriceCustomer(){
        $clinic_id = $this->request->getGet('clinic_id');
        $data = $this->Price_model->listPriceCustomer($clinic_id);  
        return $this->response->setJSON($data);
    }

    function deletePrice(){
        $id = $this->request->getGet('id');
        $data = $this->Price_model->deleteService($id);
        return $this->response->setJSON($data);
    }

    function editPrice(){
        $id = $this->request->getGet('id');
        $description = $this->request->getGet('description');
        $price = $this->request->getGet('price');
        $state = $this->request->getGet('state');
        $data = $this->Price_model->editPrice($id, $description,$price,$state);
        
        return $this->response->setJSON($data);
    }

}
