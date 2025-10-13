<?php echo form_open(get_uri("daily_report/saveReport"), array("id" => "daily_report-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
  <div class="container-fluid">
    <!-- Contenido del Modal -->
    <div class="modal-body clearfix">
      <div class="container-fluid">
        <!-- Incluir campos del formulario -->
        <?php echo view("daily_report/daily_report_form_fields"); ?>
      </div>
    </div>
    <!-- Pie del Modal con botones de cerrar y guardar -->
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-bs-dismiss="modal">
        <span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?>
      </button>
      <button type="submit" id="saveButton"   style="display: none;" class="btn btn-primary">
        <span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?>
      </button>
    </div>
  </div>
</div>
<?php echo form_close(); ?>





<script type="text/javascript">



  $(document).ready(function() {

    $("#report_file").change(function() {
    var fileName = $(this).val().split("\\").pop(); // Obtener solo el nombre del archivo
    $("#file-name").text(fileName ? fileName : "Ningún archivo seleccionado");
    
    if(fileName) {
      $("#saveButton").show(); // Mostrar el botón si hay un archivo
      $("#error-message").hide(); // Ocultar el mensaje de error si se seleccionó archivo
    } else {
      $("#saveButton").hide(); // Ocultar el botón si no hay archivo
    }
  });

      $(document).on("click", "#btnConfirmModal", function() {
        location.reload();
      });

    // Inicializar formulario con appForm
    $("#daily_report-form").appForm({
      onSuccess: function(result) {
        console.log(result);
        if (result.success && result.state == "0") {
          // Mostrar mensaje de éxito y recargar página
          /*appAlert.success(result.message, {
            duration: 10000
          });*/
          $("#btnConfirmModal").addClass('btn-success');
          $("#ajaxModal").addClass("d-none");
          $(".btn-close").click();
          $("#modalBtnReport").click();


          $("#modalM_logo").addClass('successModal');
          $("#modalM_logo").addClass('far fa-check-circle');
          $("#modalM_title").text("Excelente!");
          $("#modalM_description").text("Muy bien, Reporte subido correctamente.");

          
          //NO dejar enviar reporte DUPLICADOS
        } else {
          if((!result.success && result.state == "1") || (!result.success && result.state == "3")){
            $("#ajaxModal").addClass("d-none");
            $(".btn-close").click();
            $("#modalBtnReport").click();

            $("#btnConfirmModal").addClass('btn-danger');
            $("#modalM_logo").addClass('dangerModal');
            $("#modalM_logo").addClass('fas fa-window-close');
            $("#modalM_title").text("Error!");
            $("#modalM_description").text("Error, no se subió el reporte.");
          }else{
            $("#ajaxModal").addClass("d-none");
            $(".btn-close").click();
            $("#modalBtnReport").click();

            $("#btnConfirmModal").addClass('btn-warning');
            $("#modalM_logo").addClass('warningModal');
            $("#modalM_logo").addClass('fas fa-exclamation-triangle');
            $("#modalM_title").text("Ya existe un reporte cargado con esta fecha!");
            $("#modalM_description").text(result.message);
          }
          // Actualizar tabla de reportes
          $("#daily-report-table").appTable({
            newData: result.data,
            dataId: result.id
          });
          // Recargar vista kanban si es visible
          $("#reload-kanban-button:visible").trigger("click");
        }
      },
      onError: function(result) {
        console.log(result);
        // Mostrar mensaje de error
        $("#ajaxModal").addClass("d-none");
          $(".btn-close").click();
          $("#modalBtnReport").click();

          $("#btnConfirmModal").addClass('btn-danger');
          $("#modalM_logo").addClass('dangerModal');
          $("#modalM_logo").addClass('fas fa-window-close');
          $("#modalM_title").text("Error!");
          $("#modalM_description").text(result.message);
      }
    });
  });


  document.getElementById('report_date').addEventListener('change', function() {
    var data = document.getElementById("report_date").value;
    document.getElementById("report_date_new").value = formatDateToISO(data);
  });
</script>