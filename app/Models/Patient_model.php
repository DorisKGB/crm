<?php

namespace App\Models;

use App\Models\Crud_model;

class Patient_model extends Crud_model
{
    protected $table = null;

    public function __construct()
    {
        $this->table = 'patients';
        parent::__construct($this->table);
    }

    protected $allowedFields = [
        'full_name',
        'email',
        'phone',
        'reason',
        'extra_data',
        'source',
        'vsee_id',
        'vsee_username',
        'vsee_token'
    ];

     protected $returnType = 'object';

}
