<?php
// views/appointments/modals/modal_cambiar_estado.php
?>
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarEstadoLabel">
                    <i class="fas fa-flag me-2"></i>Cambiar Estado de la Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCambiarEstado">
                <?= csrf_field() ?>
                <input type="hidden" name="appointment_id" value="<?= $cita->id ?>">
                
                <div class="modal-body">
                    <div class="appointment-info bg-light p-3 rounded mb-3">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Información de la Cita</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small><strong>Paciente:</strong></small><br>
                                <span><?= $cita->patient_name ?></span>
                            </div>
                            <div class="col-md-6">
                                <small><strong>Médico:</strong></small><br>
                                <span><?= $cita->provider_name ?></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small><strong>Fecha:</strong></small><br>
                                <span><?= format_to_date($cita->appointment_date) ?></span>
                            </div>
                            <div class="col-md-6">
                                <small><strong>Hora:</strong></small><br>
                                <span><?= format_to_time($cita->appointment_time) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label"><strong>Nuevo Estado:</strong></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="pendiente" <?= ($cita->status == 'pendiente') ? 'selected' : '' ?>>
                                <i class="fas fa-clock"></i> Pendiente
                            </option>
                            <option value="confirmada" <?= ($cita->status == 'confirmada') ? 'selected' : '' ?>>
                                <i class="fas fa-check"></i> Confirmada
                            </option>
                            <option value="cancelada" <?= ($cita->status == 'cancelada') ? 'selected' : '' ?>>
                                <i class="fas fa-times"></i> Cancelada
                            </option>
                            <option value="finalizada" <?= ($cita->status == 'finalizada') ? 'selected' : '' ?>>
                                <i class="fas fa-flag-checkered"></i> Finalizada
                            </option>
                        </select>
                    </div>

                    <div class="form-group mt-3" id="cancel-reason-group" style="display: none;">
                        <label for="cancel_reason" class="form-label"><strong>Motivo de Cancelación:</strong></label>
                        <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="3" 
                                  placeholder="Explique el motivo de la cancelación..."></textarea>
                    </div>

                    <div class="alert alert-warning mt-3" style="display: none;" id="status-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="warning-text"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn-button btn-button-warning">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mostrar/ocultar campo de motivo según el estado
    $('#status').on('change', function() {
        const status = $(this).val();
        const cancelGroup = $('#cancel-reason-group');
        const warningDiv = $('#status-warning');
        const warningText = $('#warning-text');
        
        // Reset
        cancelGroup.hide();
        warningDiv.hide();
        
        if (status === 'cancelada') {
            cancelGroup.show();
            $('#cancel_reason').prop('required', true);
            warningText.text('Una cita cancelada no puede volver a activarse fácilmente.');
            warningDiv.show();
        } else if (status === 'finalizada') {
            warningText.text('Una cita finalizada indica que la consulta ya se realizó.');
            warningDiv.show();
            $('#cancel_reason').prop('required', false);
        } else {
            $('#cancel_reason').prop('required', false);
        }
    });

    // Envío del formulario
    $('#formCambiarEstado').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);
        
        $.ajax({
            url: '<?= get_uri("appointments/update_status") ?>',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#modalCambiarEstado').modal('hide');
                    appAlert.success('Estado actualizado correctamente');
                    $('#appointments-table').DataTable().ajax.reload();
                } else {
                    appAlert.error(res.message || 'Error al actualizar el estado');
                }
            },
            error: function() {
                appAlert.error('Error de conexión. Intenta nuevamente.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>