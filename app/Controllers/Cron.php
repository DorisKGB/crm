<?php

namespace App\Controllers;

use App\Libraries\Cron_job;
use App\Models\Clinic_model;
use App\Models\Clock_in_model;

class Cron extends App_Controller {

    private $cron_job, $clockInModel, $clinicModel;

    function __construct() {
        parent::__construct();
        $this->cron_job = new Cron_job();
        $this->clockInModel = new Clinic_model();
        $this->clinicModel = new Clock_in_model();
    }

    function index() {
        ini_set('max_execution_time', 300); //execute maximum 300 seconds 
        //wait at least 5 minute befor starting new cron job
        $last_cron_job_time = get_setting('last_cron_job_time');

        $current_time = strtotime(get_current_utc_time());

        if ($last_cron_job_time == "" || ($current_time > ($last_cron_job_time * 1 + 300))) {
            $this->cron_job->run();
            app_hooks()->do_action("app_hook_after_cron_run");
            $this->Settings_model->save_setting("last_cron_job_time", $current_time);
        }
    }

    public function alertaMarcajes()
    {
        $token = $this->request->getGet('token');
        if ($token !== 'xjqpwznsulevmdtkraohgbycfexinrukdwqmplzto') {
            echo "No autorizado";
            return;
        }
        $this->cron_job = new Cron_job();
        $this->cron_job->alertar_marcajes_incompletos();  // ← solo ejecuta esta función
        echo "✅ Alerta ejecutada.";
    }

}

/* End of file Cron.php */
/* Location: ./app/controllers/Cron.php */