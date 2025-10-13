<p class="mb-4"><?php echo app_lang("scan_documents_instruction_deposit"); ?></p>
<div class="d-flex">
  <p class="fw-bold pe-2"><?php echo app_lang("report_sent_by"); ?>
  </p>
  <p class="text-primary fw-semibold"><?php echo $login_user->first_name . " " . $login_user->last_name; ?></p> <!-- Mostrar el nombre del usuario -->
</div>

<div class="form-group">
  <div class="row">
    <label for="deposit_datetime"><?php echo app_lang('deposit_datetime'); ?></label>
    <div>
      <!-- Campo oculto con formato ISO para el backend -->
      <input type="hidden" name="deposit_datetime" id="deposit_datetime_hidden">

      <!-- Input visible pero oculto (datetime-local) para seleccionar fecha -->
      <input type="datetime-local" id="deposit_datetime_picker" onchange="DateCambio()" require class="form-control" style="opacity: 0; position: absolute;">

      <!-- Input visible en formato MM/DD/YYYY HH:MM AM/PM -->
      <input type="text" id="deposit_datetime_display" class="form-control" style="background-color: #ffff;" placeholder="MM/DD/YYYY HH:MM AM/PM" required aria-required="true" readonly>
      
      <!-- Botón para abrir el selector -->
       <div class="d-flex justify-content-end">
       <button type="button" style="border-radius: none !important;background:#eeeeee;border:none;" class="btn w-100" onclick="document.getElementById('deposit_datetime_picker').showPicker();">📅 Seleccionar Fecha</button>
       </div>
 
    </div>
  </div>
</div>


<p class="fw-bold"><?php echo app_lang("select_clinic_reporting"); ?></p>
<div class="form-group">
  <div class="d-flex row align-items-center">
    <label for="clinic_id" class="<?php echo $label_column; ?>"><?php echo app_lang('clinic_list'); ?></label>
    <div class="<?php echo $field_column; ?>">
      <input type="hidden" name="clinic_name" id="clinic_name">
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
        <p><?php echo app_lang('drag_and_drop_files_here_or_click_to_select'); ?></p> <input type="file" id="report_file" name="deposit_receipt_scan" class="file-input" required hidden /> <span id="file-name" class="file-name"><?php echo app_lang('no_file_chosen'); ?></span>
      </div>
      <p id="error-message" style="color: red; display: none;">⚠️ Debes seleccionar un archivo antes de enviar.</p>
    </div>
  </div>
</div>


<div class="form-group">
  <div class="row"> <label for="deposit_amount"><?php echo app_lang('amount_deposit'); ?></label>
    <div> <?php echo form_input(array("id" => "deposit_amount", "name" => "deposit_amount", "type" => "number", "class" => "form-control zeroValidate", "placeholder" => "e.g., 1500.00", "required" => false, "aria-label" => app_lang('sales_cash'))); ?> </div>
  </div>
</div>


<div class="form-group">
  <div class="row">
    <label for="report_date"><?php echo app_lang('report_date_start'); ?></label>
    <div>
      <input type="date" name="deposit_start_date" id="report_date_new" style="opacity: 0; position: absolute;" value="<?php echo date('Y-m-d'); ?>">
      <input class="form-control us-date-input-today-now" type="date" id="report_date" required="true" aria-required="true" aria-label="<?php echo app_lang('report_date') ?>"   placeholder="MM/DD/YYYY" >
    </div>
  </div>
</div>


<div class="form-group">
  <div class="row">
    <label for="report_date"><?php echo app_lang('report_date_end'); ?></label>
    <div>
      <input type="date" name="deposit_end_date" id="report_date_new1" style="opacity: 0; position: absolute;" value="<?php echo date('Y-m-d'); ?>">
      <input class="form-control us-date-input-today-now" type="date" id="report_date1" required="true" aria-required="true" aria-label="<?php echo app_lang('report_date') ?>"   placeholder="MM/DD/YYYY" >
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
  <button type="button" id="generateSignature"   class="btn btn-primary w-100">Generar Firma Electronica</button>

  <div class="row d-none">
    <label for="total_patients_marketing"><span class="badge-figure"><i class="fas fa-check-double"></i><?php echo app_lang('register_electronic'); ?></span></label>
    <div class="form-grup">
      <input type="text" class="form-control" name="signature_electronic" id="total_patients_marketing" readonly value="<?php echo $signature_electronic; ?>" >
    </div>
  </div>
</div>



<style>
  .bg-message-red{
    text-align: center;
    border: 3px dashed black; /* Borde discontinuo */
    border-color: #e9ecef;
  }
  .span-resalt-bg{
    color: #901805 !important;
  }
  .noteText{
    color:#000;
    border-radius: 15px;
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
  }
  .signature_electronic{
    text-align: center;
    font-size: 20px !important;
    color:rgb(62, 63, 63);
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
    font-style: italic;
  }
</style>


  <div class="bg-message-red  mt-5 d-none" id="signature">
    <h5 class="fw-bold text-center noteText">FIRMA ELECTRONICA GENERADA</h5>
    <span class="signature_electronic w-100"><?php echo $signature_electronic; ?></span> <br>
  </div>


<script type="text/javascript">
  $(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    $('#clinic_id').select2({})
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

 

document.getElementById('generateSignature').addEventListener('click', function() {
  
  document.getElementById('generateSignature').classList.add('d-none');
  document.getElementById('signature').classList.remove('d-none');

  if (!$('#saveButton').hasClass('d-none')) {
    if($('#report_file').get(0).files.length > 0){
      $('#saveButton').show();
    }
  } 

});


document.addEventListener("DOMContentLoaded", function () {
  
  let now = new Date();
let picker = document.getElementById("deposit_datetime_picker");
let display = document.getElementById("deposit_datetime_display");
let hiddenInput = document.getElementById("deposit_datetime_hidden");
    // Establecer valores iniciales
    picker.value = formatToISO(now);
    console.log( picker.value);
    display.value = formatDateToUS(now);
    hiddenInput.value = formatToISO(now);

    // Actualizar valores al seleccionar fecha y hora
    picker.addEventListener("change", function () {
      console.log("Hoola");
      /*let selectedDate = new Date(this.value);
      if (!isNaN(selectedDate.getTime())) {
        display.value = formatDateToUS(selectedDate);
        hiddenInput.value = formatToISO(selectedDate);
      }*/
    });


    
  });

  function formatDateToUS(date) {
    // Asegúrate de que 'date' sea un objeto Date válido
    if (!(date instanceof Date) || isNaN(date.getTime())) {
        console.error("Invalid date:", date);
        return "Invalid Date";  // O cualquier valor predeterminado si la fecha no es válida
    }

    let options = { 
        year: 'numeric', month: '2-digit', day: '2-digit', 
        hour: '2-digit', minute: '2-digit', hour12: true 
    };
    return new Intl.DateTimeFormat('en-US', options).format(date);
}

function formatToISO(date) {
    return date.toISOString().slice(0, 16);
}



function DateCambio(){
  let display = document.getElementById("deposit_datetime_display");
    let picker = document.getElementById("deposit_datetime_picker");
    let hiddenInput = document.getElementById("deposit_datetime_hidden");
    var val = picker.value;
    
    // Convertir la cadena a un objeto Date
    let selectedDate = new Date(val);

    // Comprobar si la fecha es válida
    if (!isNaN(selectedDate.getTime())) {
        display.value = formatDateToUS(selectedDate);
        hiddenInput.value = formatToISO(selectedDate);
    } else {
        console.error("Invalid date format:", val);
    }
}
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