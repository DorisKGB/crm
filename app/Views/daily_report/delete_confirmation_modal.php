<div class="modal-header">
    <h4 class="modal-title" id="exampleModalLabel"><?php echo app_lang('delete_daily_report'); ?></h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<?php echo form_open(get_uri("daily_report/delete"), array("id" => "delete-daily-report-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        
        <!-- Área de contenido dinámico -->
        <div id="delete-content">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <i data-feather="alert-triangle" class="icon-24 text-warning"></i>
                        <strong><?php echo app_lang('delete_daily_report'); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <p><?php echo app_lang('delete_daily_report_confirmation'); ?></p>
                        <p class="text-off"><?php echo app_lang('undone_action'); ?></p>
                        
                        <?php if($model_info->clinic_name && $model_info->report_date) { ?>
                        <div class="mt15">
                            <strong><?php echo app_lang('clinic'); ?>:</strong> <?php echo $model_info->clinic_name; ?><br>
                            <strong><?php echo app_lang('report_date'); ?>:</strong> <?php echo format_to_date($model_info->report_date, false); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Área de resultado (inicialmente oculta) -->
        <div id="delete-result" style="display: none;">
            <div class="text-center">
                <div id="result-icon" class="mb-3"></div>
                <h4 id="result-title"></h4>
                <p id="result-message"></p>
            </div>
        </div>

        <!-- Spinner de carga -->
        <div id="delete-loading" style="display: none;">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Eliminando...</span>
                </div>
                <p class="mt-2">Eliminando reporte...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <div id="initial-buttons">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">
            <span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?>
        </button>
        <button type="submit" class="btn btn-danger" id="confirm-delete-btn">
            <span data-feather="trash-2" class="icon-16"></span> <?php echo app_lang('delete'); ?>
        </button>
    </div>
    
    <div id="result-buttons" style="display: none;">
        <button type="button" class="btn btn-primary d-none" data-bs-dismiss="modal" id="close-result-btn">
            <span data-feather="check" class="icon-16"></span> <?php echo app_lang('ok'); ?>
        </button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#delete-daily-report-form").appForm({
            beforeAjaxSubmit: function() {
                // Mostrar loading
                $("#delete-content").hide();
                $("#initial-buttons").hide();
                $("#delete-loading").show();
            },
            onSuccess: function (result) {
                // Ocultar loading
                $("#delete-loading").hide();
                
                // Mostrar resultado
                $("#delete-result").show();
                $("#result-buttons").show();
                
                if (result.success) {
                    // Éxito
                    $("#result-icon").html('<i data-feather="check-circle" class="icon-48 text-success"></i>');
                    $("#result-title").text("<?php echo app_lang('success'); ?>").addClass('text-success');
                    $("#result-message").text(result.message || "<?php echo app_lang('record_deleted'); ?>");
                    
                    // Recargar la página después de 2 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Error
                    $("#result-icon").html('<i data-feather="x-circle" class="icon-48 text-danger"></i>');
                    $("#result-title").text("<?php echo app_lang('error'); ?>").addClass('text-danger');
                    $("#result-message").text(result.message || "<?php echo app_lang('error_occurred'); ?>");
                }
                
                // Actualizar iconos de Feather
                feather.replace();
            },
            onError: function(xhr, status, error) {
                // Ocultar loading
                $("#delete-loading").hide();
                
                // Mostrar error
                $("#delete-result").show();
                $("#result-buttons").show();
                
                $("#result-icon").html('<i data-feather="x-circle" class="icon-48 text-danger"></i>');
                $("#result-title").text("<?php echo app_lang('error'); ?>").addClass('text-danger');
                $("#result-message").text("<?php echo app_lang('error_occurred'); ?>");
                
                // Actualizar iconos de Feather
                feather.replace();
            }
        });
        
        // También recargar cuando se haga clic en el botón OK después de éxito
        $(document).on('click', '#close-result-btn', function() {
            location.reload();
        });
    });
</script>

<style>
    .icon-48 {
        font-size: 48px;
    }
    
    #delete-result {
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #delete-loading {
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
</style>