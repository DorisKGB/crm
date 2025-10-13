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
        <?php echo view("daily_report/daily_report_patients_chart"); ?>  <!--LISOT-->
      </div>
      <div class="col-12 col-md-6">
        <?php echo view("daily_report/daily_report_income_chart"); ?> <!--LISOT-->
      </div>
      <div class="col-12 col-md-6">
        <?php echo view("daily_report/daily_report_platforms_chart"); ?>
      </div>
      <div class="col-12 col-md-6">
        <?php echo view("daily_report/daily_report_marketing_chart"); ?>
      </div>
      <div class="col-12 col-md-6">
        <?php echo view("daily_report/daily_report_insurance_prevalence_chart"); ?>
      </div>
    </div>
    <hr>
    <div class="row">
    <?php echo view("daily_report/daily_report_performance.php"); ?>
    </div>
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

  var prepareTotalDate = function() {
    var start_date = document.getElementById("start_date").value;
    var end_date = document.getElementById("end_date").value;
    var clinicId = document.getElementById("clinic_select").value;
    if(start_date && end_date){
        start_date = formatDateToISO(start_date);
        end_date = formatDateToISO(end_date);
    }

    $.ajax({
      url: "<?php echo get_uri('daily_report/getTotalsData'); ?>",
      type: 'GET',
      data: {
        clinic_id: clinicId,
        start_date: start_date,
        end_date: end_date
      },
      dataType: "json",
      success: function(response) {
   
         // Insertar los totales en la fila del pie
          $("#total_sales_cash").html("<?php echo app_lang('sign_money'); ?> " + Number(response.data[0]).toLocaleString('en-US'));
          $("#total_sales_card").html("<?php echo app_lang('sign_money'); ?> " + Number(response.data[1]).toLocaleString('en-US'));
          $("#total_sales_other").html("<?php echo app_lang('sign_money'); ?> " + Number(response.data[2]).toLocaleString('en-US'));
          $("#total_new_patients").html(response.data[8]);
            //$("#total_followup_patients").html(totalFollowupPatients);
          $("#total_insured_patients").html(response.data[3]);
          $("#total_uninsured_Patients").html(response.data[4]);

          $("#total_sales").html("<?php echo app_lang('sign_money'); ?> " + Number(response.data[6]).toLocaleString('en-US'));
          $("#total_patients").html(response.data[8]);
          console.log(response.data);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Error en la llamada AJAX (Pacientes):", textStatus, errorThrown);
      }
    });
  };


  $(document).ready(function() {
    prepareTotalDate();
    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    prepareTotalPlatformsChart();
    prepareInsurancePrevalenceChart();
    prepareTotalPlatformsChartEspecific();
  });

  $("#filter_dates").click(function(){

    prepareTotalDate();
    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    prepareTotalPlatformsChart();
    prepareInsurancePrevalenceChart();
    prepareTotalPlatformsChartEspecific();
  });
  $("#filter_clean").click(function(){
    $('#start_date').val('');
    $('#end_date').val('');
    $('#clinic_select').val('');
    prepareTotalDate();
    prepareTotalPatientsChart();
    prepareTotalIncomeChart();
    prepareTotalPlatformsChart();
    prepareInsurancePrevalenceChart();
    prepareTotalPlatformsChartEspecific();
  });

</script>

<style>
  .select_graph {
    padding: 0.5rem;
    border: none;
    background-color: #F6F8F9;
  }
</style>