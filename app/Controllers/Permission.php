<?php

namespace App\Controllers;

use App\Models\Permission_model;

class Permission extends Security_Controller {

    protected $permissionModel;

    public function __construct()
    {
        parent::__construct();
        $this->permissionModel = new Permission_model();
    }

    //load client groups list view
    function index() {
        return $this->template->rander("client_groups/index");
    }

}
