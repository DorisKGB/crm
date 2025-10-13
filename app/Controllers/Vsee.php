<?php

namespace App\Controllers;

use App\Models\VseeUsers_model;

class Vsee extends Security_Controller
{
    protected $VseeUsers_model;

    function __construct()
    {
        parent::__construct();
        $this->VseeUsers_model = new VseeUsers_model();
    }

    function index()
    {
        return $this->template->rander('vsee/index');
    }

    function pacientes()
    {
        return $this->template->rander('vsee/pacientes');
    }

    function provider()
    {
        return $this->template->rander('vsee/provider');
    }
}
