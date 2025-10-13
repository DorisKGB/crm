<p class="mb-4"><?php echo app_lang("scan_documents_instruction"); ?></p>
<div class="d-flex">
  <p class="fw-bold pe-2"><?php echo app_lang("report_sent_by"); ?>
  </p>
  <p class="text-primary fw-semibold"><?php echo $login_user->first_name . " " . $login_user->last_name; ?></p> <!-- Mostrar el nombre del usuario -->
</div>


<p class="fw-bold"><?php echo app_lang("select_clinic_reporting"); ?></p>
<div class="form-group">
  <div class="d-flex row align-items-center">
    <label for="clinic_id" class="<?php echo $label_column; ?>"><?php echo app_lang('clinic_list'); ?></label>
    <div class="<?php echo $field_column; ?>">
      <?php $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
      echo form_dropdown(
        "clinic_id",
        $clinic_options,
        $selected_clinic_id,
        'class="select2" id="clinic_id" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
      ); ?> </div>
  </div>
</div>
<p class="fw-bold"><?php echo app_lang("scan_daily_ebo_square_report"); ?>
</p>
<div class="form-group">
  <div class="row"> <label for="report_file"><?php echo app_lang('report_file'); ?></label>
    <div>
      <div id="drop-area" class="drop-area" tabindex="0" role="button" aria-label="<?php echo app_lang('drag_and_drop_files_here_or_click_to_select'); ?>">
        <p><?php echo app_lang('drag_and_drop_files_here_or_click_to_select'); ?></p> <input type="file" id="report_file" name="report_file" class="file-input" required hidden /> <span id="file-name" class="file-name"><?php echo app_lang('no_file_chosen'); ?></span>
      </div>
      <p id="error-message" style="color: red; display: none;">⚠️ Debes seleccionar un archivo antes de enviar.</p>
    </div>
  </div>
</div>
<p class="fw-bold"><?php echo app_lang("report_corresponding_date"); ?>
</p>
<div class="form-group">
  <div class="row">
    <label for="report_date"><?php echo app_lang('report_date'); ?></label>
    <div>
      <input type="date" name="report_date" id="report_date_new" style="opacity: 0; position: absolute;" value="<?php echo date('Y-m-d'); ?>">
      <input class="form-control us-date-input-today-now" type="date" id="report_date" required="true" aria-required="true" aria-label="<?php echo app_lang('report_date') ?>"   placeholder="MM/DD/YYYY" >
    </div>
  </div>
</div>

<p class="fw-bold text-center fs-5"><?php echo app_lang("sales_report"); ?>
</p>
<div class="form-group">
  <div class="row"> <label for="sales_cash"><?php echo app_lang('sales_cash'); ?></label>
    <div> <?php echo form_input(array("id" => "sales_cash", "name" => "sales_cash", "type" => "number", "class" => "form-control zeroValidate", "placeholder" => "e.g., 1500.00", "required" => false, "aria-label" => app_lang('sales_cash'))); ?> </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="sales_card"><?php echo app_lang('sales_card'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "sales_card",
        "name" => "sales_card",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 2000.00",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="sales_other"><?php echo app_lang('sales_other'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "sales_other",
        "name" => "sales_other",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 500.00",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>
<!--- SECCION DE REPORTE DE MARKETING --->
<p class="fw-bold text-center fs-5"><?php echo app_lang("marketing_report"); ?>
</p>
<div class="form-group">
  <div class="row">
    <label for="new_patients_total"><?php echo app_lang('new_patients_total'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "new_patients_total",
        "name" => "new_patients_total",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 5",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="followup_patients_total"><?php echo app_lang('followup_patients_total'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "followup_patients_total",
        "name" => "followup_patients_total",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 8",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_google"><?php echo app_lang('referral_google'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_google",
        "name" => "referral_google",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 2",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_referred"><?php echo app_lang('referral_referred'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_referred",
        "name" => "referral_referred",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 3",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_mail"><?php echo app_lang('referral_mail'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_mail",
        "name" => "referral_mail",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_walkby"><?php echo app_lang('referral_walkby'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_walkby",
        "name" => "referral_walkby",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_facebook"><?php echo app_lang('referral_facebook'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_facebook",
        "name" => "referral_facebook",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_events"><?php echo app_lang('referral_events'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_events",
        "name" => "referral_events",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_instagram"><?php echo app_lang('referral_instagram'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_instagram",
        "name" => "referral_instagram",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_youtube"><?php echo app_lang('referral_youtube'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_youtube",
        "name" => "referral_youtube",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_tiktok"><?php echo app_lang('referral_tiktok'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_tiktok",
        "name" => "referral_tiktok",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_radio"><?php echo app_lang('referral_radio'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_radio",
        "name" => "referral_radio",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_newspaper"><?php echo app_lang('referral_newspaper'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_newspaper",
        "name" => "referral_newspaper",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="referral_tv"><?php echo app_lang('referral_tv'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "referral_tv",
        "name" => "referral_tv",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<style>
  #total_patients_marketing{
    border: none;
    background-color:rgb(236, 241, 248);
    font-weight: bold !important;
  }
  .badge-figure{
    background-color:rgb(149, 200, 254);
    color: #000 !important;
    border-radius: 15px;
    padding-left: 6px;
    padding-right: 6px;
    padding-top: 2px;
    padding-bottom: 2px;
        
  }
  .error-message {
    color: #901805;
    background-color: #ffd5ce;
    padding: 7px;
    font-size: 12px;
    font-weight: bold;
    border-radius: 5px;
    margin-top: 10px;
    margin-bottom: 8px;
    display: none;
  }
 
</style>

<div class="form-group">
  <div class="row">
    <label for="total_patients_marketing"><span class="badge-figure"><i class="fas fa-check-double"></i><?php echo app_lang('total_patients_marketing'); ?></span></label>
    <div class="form-grup">
      <input type="text" class="form-control" id="total_patients_marketing" readonly >
    </div>
  </div>
</div>


<p class="fw-bold text-center fs-5"><?php echo app_lang("insurance_prevalence_report"); ?>
</p>
<div class="form-group">
  <div class="row">
    <label for="uninsured_patients"><?php echo app_lang('uninsured_patients'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "uninsured_patients",
        "name" => "uninsured_patients",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 1",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="form-group">
  <div class="row">
    <label for="insured_patients"><?php echo app_lang('insured_patients'); ?></label>
    <div>
      <?php
      echo form_input(array(
        "id" => "insured_patients",
        "name" => "insured_patients",
        "type" => "number",
        "class" => "form-control zeroValidate",
        "placeholder" => "e.g., 4",
        "required" => false
      ));
      ?>
    </div>
  </div>
</div>

<div class="error-message" id="errorMessage">
        ❌  <?php echo app_lang('message_exceded_patient'); ?> <span id="patients_permit"></span> <?php echo app_lang('patient_permited'); ?>
</div>

<p class="fw-bold text-center fs-5"><?php echo app_lang("daily_closure_protocols"); ?>
</p>
<p class="fw-bold pb-4"><?php echo app_lang("select_completed_protocols"); ?>
</p>
<div class="form-group ps-3">
  <div class="row align-items-center">
    <div class="form-check">
      <?php
      echo form_checkbox(array(
        "id" => "boxed_samples",
        "name" => "boxed_samples",
        "value" => "1",
        "class" => "form-check-input",
        "required" => true
      ));
      ?>
      <label for="boxed_samples" class="form-check-label"><?php echo app_lang('boxed_samples'); ?></label>
    </div>
  </div>
</div>

<div class="form-group ps-3">
  <div class="row align-items-center">
    <div class="form-check">
      <?php
      echo form_checkbox(array(
        "id" => "added_to_square_ecw",
        "name" => "added_to_square_ecw",
        "value" => "1",
        "class" => "form-check-input",
        "required" => true
      ));
      ?>
      <label for="added_to_square_ecw" class="form-check-label" ><?php echo app_lang('added_to_square_ecw'); ?></label>
    </div>
  </div>
</div>

<style>
  .bg-message-red{
    border-radius:15px !important;
    border: 2px solid rgb(250, 228, 226);
  }
  .span-resalt-bg{
    color: #901805 !important;
  }
  .noteText{
    background-color: #901805;
    color: #fff;
    border-radius: 15px;
  }
</style>


  <div class="bg-message-red p-3 mt-5">
  <h5 class="fw-bold text-center noteText p-2">Recomendaciones</h5>
  <span><i class="fas fa-check-double"></i> Si deseas enviar un reporte diario debes <b class="span-resalt-bg">SUBIR UN ARCHIVO</b> de lo contrario el boton de enviar estará oculto.</span> <br>
  <span><i class="fas fa-check-double"></i> El tamaño del archivo debe ser MAXIMO <b class="span-resalt-bg">10Mb</b>, si es mayor no se agregará el reporte diario.</span> <br>
  <span><i class="fas fa-check-double"></i> Asegurate de completar <b class="span-resalt-bg">TODOS</b> los campos.</span>

  </div>


<script type="text/javascript">
  $(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('#clinic_id').select2({});

    const dropArea = document.getElementById("drop-area");
    const fileInput = document.getElementById("report_file");
    const fileNameDisplay = document.getElementById("file-name");

    dropArea.addEventListener("click", () => fileInput.click());
    dropArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropArea.classList.add("dragging");
    });
    dropArea.addEventListener("dragleave", () => dropArea.classList.remove("dragging"));
    dropArea.addEventListener("drop", (e) => {
        e.preventDefault();
        dropArea.classList.remove("dragging");
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileNameDisplay(files[0]);
        }
    });
    fileInput.addEventListener("change", () => {
        const files = fileInput.files;
        if (files.length > 0) {
            updateFileNameDisplay(files[0]);
        }
    });

    function updateFileNameDisplay(file) {
        fileNameDisplay.textContent = file.name;
    }

    USDateInputs('.us-date-input-today-now'); //ejecutar script date
});


// Función para prevenir valores negativos o cero
function preventNegativeOrZero() {
    const inputs = document.querySelectorAll('.zeroValidate');

    inputs.forEach(input => {
        // Escuchar el evento 'input' para validar el valor ingresado
        input.addEventListener('input', function () {
            if (parseFloat(this.value) < 0 || isNaN(this.value)) {
                this.value = '';
            }
        });

        // Evitar pegar valores negativos o cero
        input.addEventListener('paste', function (event) {
            const clipboardData = event.clipboardData.getData('text');
            if (parseFloat(clipboardData) < 0 || isNaN(clipboardData)) {
                event.preventDefault();
            }
        });
    });
}

preventNegativeOrZero();


function sumarInputs() {
    let ids = [
        "new_patients_total",
        "followup_patients_total",
    ];

    let suma = 0;

    ids.forEach(id => {
        let input = document.getElementById(id);
        if (input) {
            let valor = parseFloat(input.value) || 0;
            suma += valor;
        }
    });

    document.getElementById("total_patients_marketing").value = suma;
}

// Delegación de eventos: escucha los eventos en el documento y verifica si el evento proviene de los elementos con los IDs adecuados.
document.addEventListener('input', function (event) {
    if (event.target && (event.target.id === 'new_patients_total' || event.target.id === 'followup_patients_total')) {
        sumarInputs();
    }
});

function validarSuma() {
    let totalPermitido = parseFloat(document.getElementById("total_patients_marketing").value) || 0;
    let num1 = parseFloat(document.getElementById("uninsured_patients").value) || 0;
    let num2 = parseFloat(document.getElementById("insured_patients").value) || 0;

    let suma = num1 + num2;

    if (suma > totalPermitido) {
        document.getElementById("saveButton").classList.add('d-none');
        document.getElementById("patients_permit").textContent = totalPermitido;
        document.getElementById("errorMessage").style.display = "block";
    } else {
        document.getElementById("saveButton").classList.remove('d-none');
        document.getElementById("errorMessage").style.display = "none";
    }
}

// Delegación de eventos para los inputs de validación
document.addEventListener('input', function (event) {
    if (event.target && (event.target.id === 'uninsured_patients' || event.target.id === 'insured_patients')) {
        validarSuma();
    }
});

</script>
<style>
  .drop-area {
    border: 2px dashed #ccc;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .drop-area:hover,
  .drop-area:focus {
    background-color: #f8f9fa;
  }

  .drop-area.dragging {
    background-color: #e9ecef;
  }

  .drop-area p {
    margin: 0;
    font-size: 16px;
    color: #666;
  }

  .file-name {
    display: block;
    margin-top: 10px;
    font-size: 14px;
    color: #333;
  }
</style>