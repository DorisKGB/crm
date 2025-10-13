<?php

namespace App\Controllers;

use App\Models\Usa_states_model;

class Usa_states extends Security_Controller
{

    protected $modelUsaState;

    function __construct()
    {
        parent::__construct();
        $this->modelUsaState = new Usa_states_model();
    }
}
