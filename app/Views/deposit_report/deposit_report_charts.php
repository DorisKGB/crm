<div id="page-content" class="page-wrapper clearfix">
  <div class="card bg-white">
    <div class="card-header clearfix">
      <i data-feather="pie-chart" class="icon-16"></i> &nbsp;<?php echo app_lang("graphs"); ?>
    </div>
    <div class="card-body rounded-bottom d-none">
      <div class="form-group">
        <div class="d-flex row align-items-center">
          <label for="clinic_select" class="col-md-4"><?php echo app_lang("select_clinic_report"); ?></label>
          <div class="col-md-8">
            <?php
            $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
            echo form_dropdown(
              "clinic_select",
              $clinic_options,
              $selected_clinic_id,
              'class="select_graph w-100" id="clinic_select" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
            ); ?>
          </div>
        </div>
      </div>
    </div>
    <div id="patients-data-container"></div>
    <div class="row">
    <div class="col-12 col-md-6">
        <?php echo view("deposit_report/deposit_report_patients_chart"); ?> <!--LISOT-->
      </div>

      <div class="col-12 col-md-6">
        <?php echo view("deposit_report/deposit_report_income_chart"); ?> <!--LISOT-->
      </div>
    </div>
    <hr>

  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $(".select2").select2();

    var clinicSelect = document.getElementById("clinic_select");
    var selectClinicMessage = document.getElementById("select-clinic-message");
    if (selectClinicMessage) {
      selectClinicMessage.style.display = 'block';
    }
  });




  $(document).ready(function() {

    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    //prepareTotalPlatformsChart();
    //prepareInsurancePrevalenceChart();
  });

  $("#filter_dates").click(function(){


    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    //prepareTotalPlatformsChart();
    //prepareInsurancePrevalenceChart();
  });
  $("#filter_clean").click(function(){
    $('#start_date').val('');
    $('#end_date').val('');
    $('#clinic_select').val('');

    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    //prepareTotalPlatformsChart();
    //prepareInsurancePrevalenceChart();
  });

</script>

<style>
  .select_graph {
    padding: 0.5rem;
    border: none;
    background-color: #F6F8F9;
  }
</style>