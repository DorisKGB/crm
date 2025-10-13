<?php

namespace App\Models;

class Team_member_job_info_model extends Crud_model
{

    protected $table = null;

    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'date_of_hire', 'deleted', 'salary', 'salary_term'];

    function __construct()
    {
        $this->table = 'team_member_job_info';
        parent::__construct($this->table);
    }
}
