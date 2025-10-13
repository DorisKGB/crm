<div id="page-content" class="page-wrapper clearfix grid-button">
  <div class="card">
    <div class="page-title clearfix notes-page-title">
      <h1><?php echo app_lang("deposit_clinic_report"); ?></h1>
      <div class="title-button-group">
      <?php 
        $permissions2 = $login_user->permissions;
        $access_deposit = get_array_value($permissions2, "can_access_deposit_report");  
      ?>

      <?php if($login_user->is_admin || $access_deposit == "all" || $access_deposit == "create"){ ?>
        <!---->
        <?php echo modal_anchor(get_uri("deposit_report/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_report'), array("class" => "btn btn-default addReport", "title" => app_lang('add_report'), "aria-label" => "Añadir reporte diario")); ?>

      <?php }
      ?>
      </div>
    </div>
   
    <?php echo view('deposit_report/modal_success'); ?>
    <?php if($login_user->is_admin || $access_deposit == "all"){ ?>
    <?php echo view('deposit_report/deposit_report_list', ['reports' => $reports]); ?>
    <?php echo view('deposit_report/deposit_report_charts', ['clinic_options' => $clinic_options]); // Pasar clinic_options 
    }
    else if($access_deposit == "view"){ ?>
      <?php echo view('deposit_report/deposit_report_list', ['reports' => $reports]); ?>
    <?php }
    else{ ?>
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

    </style>
      <div class="card" >
       <div class="card-body">
          <div class="report-box" onclick="abrirmodal()">
            <i style="font-size: 100px;"><i class="far fa-money-bill-alt"></i></i><br>
            <h1>Click aqui para crear un <span>Reporte de Deposito</span></h1>
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