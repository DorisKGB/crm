<div id="page-content" class="page-wrapper clearfix grid-button">
  <div class="card">
    <div class="page-title clearfix notes-page-title">
      <h1><?php echo app_lang("daily_clinic_report"); ?></h1>
      <div class="title-button-group">

        <!---->
        <?php echo modal_anchor(get_uri("daily_report/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_report'), array("class" => "btn btn-default addReport", "title" => app_lang('add_report'), "aria-label" => "Añadir reporte diario")); ?>
        <?php if($login_user->is_admin){ ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#monthlyStatsModal" title="<?php echo app_lang('monthly_stats_title'); ?>">
              <i data-feather="bar-chart-2" class="icon-16"></i> <?php echo app_lang('best_months'); ?>
            </button>
            
            <button type="button" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#logModal">
              <i class="fas fa-book"></i>
            </button>
        <?php } ?>
      </div>
    </div>
    <?php 
      $permissions2 = $login_user->permissions;
      $access_daily = get_array_value($permissions2, "daily_permission");  
    ?>
    <?php if($login_user->is_admin){ 
      echo view('daily_report/modal_log'); 
      echo view('daily_report/monthly_stats_modal', ['clinic_options' => $clinic_options]);
      } ?>
    <?php echo view('daily_report/modal_success'); ?>
    <?php if($login_user->is_admin || $access_daily == "all"){ ?>
    <?php echo view('daily_report/daily_report_list', ['reports' => $reports]); ?>
    <?php echo view('daily_report/daily_report_charts', ['clinic_options' => $clinic_options]); // Pasar clinic_options 
    }else{ ?>
    <style>
              .report-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 300px;
            border: 2px dotted #87ceeb;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .report-box:hover{
          background-color:rgb(248, 248, 248);
        }

        .report-box img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }

        .report-box h1 {
            font-size: 20px;
            color: #333333;
            text-align: center;
            margin: 0;
        }

        .report-box h1 span {
            color: #87ceeb; /* Azul claro */
        }
    </style>
      <div class="card" >
       <div class="card-body">
          <div class="report-box" onclick="abrirmodal()">
            <img src="https://img.icons8.com/ios-filled/100/87ceeb/report-card.png" alt="Reporte Diario">
            <h1>Click aqui para crear un <span>Reporte Diario</span></h1>
        </div>
       </div>
      </div>
        <script>
          function abrirmodal(){
            $(".addReport").click();
          }
        </script>

    <?php }
    ?>
  </div>
</div>