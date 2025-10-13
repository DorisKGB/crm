<?php

namespace App\Models;

class Clock_connect_model extends Crud_model
{
    protected $table = null;

    function __construct()
    {
        $this->table = 'clock_connect';
        parent::__construct($this->table);
    }
}
