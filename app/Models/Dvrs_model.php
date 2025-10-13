<?php

namespace App\Models;

class Dvrs_model extends Crud_model {

    protected $table = null;

    function __construct()
    {
        $this->table = 'clinic_dvrs';
        parent::__construct($this->table);
    }

}
