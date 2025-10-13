<?php
// views/appointments/modals/modal_reprogramar.php
load_css(["assets/js/flatpickr/flatpickr.min.css"]);
load_js(["assets/js/flatpickr/flatpickr.min.js"]);
?>
<div class="modal fade" id="modalReprogramar" tabindex="-1" aria-labelledby="modalReprogramarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReprogramarLabel">
                    <i class="fas fa-calendar-alt me-2"></i>Reprogramar Teleconsulta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReprogramar">
                <?= csrf_field() ?>
                <input type="hidden" name="appointment_id" value="<?= $cita->id ?>">
                
                <div class="modal-body">
                    <!-- Información actual de la cita -->
                    <div class="current-appointment bg-light p-3 rounded mb-4">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Información Actual de la Cita</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-2">
                                    <small class="text-muted">Paciente:</small><br>
                                    <strong><?= $cita->patient_name ?></strong>
                                </div>
                                <div class="info-item mb-2">
                                    <small class="text-muted">Fecha actual:</small><br>
                                    <span class="btn-ghost badge"><?= date('d/m/Y', strtotime($cita->appointment_date)) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-2">
                                    <small class="text-muted">Profesional:</small><br>
                                    <strong><?= $cita->provider_name ?></strong>
                                </div>
                                <div class="info-item mb-2">
                                    <small class="text-muted">Hora actual:</small><br>
                                    <span class="btn-ghost badge"><?= date('h:i A', strtotime($cita->appointment_time)) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nueva programación -->
                    <div class="new-schedule">
                        <h6 class="mb-3"><i class="fas fa-calendar-plus me-2"></i>Nueva Programación</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_date" class="form-label"><strong>Nueva Fecha:</strong></label>
                                    <input type="text" name="new_date" id="new_date" class="form-control flatpickr" 
                                           placeholder="MM/DD/YYYY" required>
                                    <div class="invalid-feedback" id="date-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_time" class="form-label"><strong>Nueva Hora:</strong></label>
                                    <input type="time" name="new_time" id="new_time" class="form-control" required>
                                    <div class="invalid-feedback" id="time-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_duration" class="form-label"><strong>Duración (minutos):</strong></label>
                                    <input type="number" name="new_duration" id="new_duration" class="form-control" 
                                           value="<?= $cita->duration_minutes ?? 30 ?>" min="15" max="120">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reschedule_reason" class="form-label"><strong>Motivo de Reprogramación:</strong></label>
                                    <select name="reschedule_reason" id="reschedule_reason" class="form-control" required>
                                        <option value="">Seleccionar motivo...</option>
                                        <option value="solicitud_paciente">Solicitud del paciente</option>
                                        <option value="indisponibilidad_medico">Indisponibilidad del Profesional</option>
                                        <option value="emergencia">Emergencia</option>
                                        <option value="problema_tecnico">Problema técnico</option>
                                        <option value="otro">Otro motivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3" id="other-reason-group" style="display: none;">
                            <label for="other_reason" class="form-label"><strong>Especificar motivo:</strong></label>
                            <textarea name="other_reason" id="other_reason" class="form-control" rows="2" 
                                      placeholder="Detalle el motivo de la reprogramación..."></textarea>
                        </div>
                    </div>

                    <!-- Verificación de disponibilidad -->
                    <div id="availability-check" class="alert mt-3" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status" id="availability-spinner" style="display: none;">
                                <span class="visually-hidden">Verificando...</span>
                            </div>
                            <span id="availability-message"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn-button btn-button-info" id="check-availability-btn">
                        <i class="fas fa-search me-2"></i>Verificar Disponibilidad
                    </button>
                    <button type="submit" class="btn-button btn-button-success" disabled>
                        <i class="fas fa-calendar-check me-2"></i>Reprogramar Cita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar Flatpickr
    $('.flatpickr').flatpickr({
        dateFormat: "m/d/Y",
        minDate: "today"
    });

    // Mostrar/ocultar campo de otro motivo
    $('#reschedule_reason').on('change', function() {
        const otherGroup = $('#other-reason-group');
        if ($(this).val() === 'otro') {
            otherGroup.show();
            $('#other_reason').prop('required', true);
        } else {
            otherGroup.hide();
            $('#other_reason').prop('required', false);
        }
    });

    // Verificar disponibilidad
    $('#check-availability-btn').on('click', function() {
        const providerId = <?= $cita->provider_id ?>;
        const newDate = $('#new_date').val();
        const newTime = $('#new_time').val();
        const duration = $('#new_duration').val();

        if (!newDate || !newTime) {
            appAlert.warning('Por favor, selecciona fecha y hora antes de verificar disponibilidad');
            return;
        }

        const checkBtn = $(this);
        const submitBtn = $('button[type="submit"]');
        const availabilityDiv = $('#availability-check');
        const spinner = $('#availability-spinner');
        const message = $('#availability-message');

        // Mostrar loading
        checkBtn.prop('disabled', true);
        availabilityDiv.show().removeClass('alert-success alert-danger alert-warning').addClass('alert-info');
        spinner.show();
        message.text('Verificando disponibilidad...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: '<?= get_uri("appointments/check_availability") ?>',
            method: 'GET',
            data: {
                provider: providerId,
                date: newDate,
                time: newTime,
                duration_minutes: duration,
                exclude_appointment: <?= $cita->id ?> // Excluir la cita actual
            },
            success: function(res) {
                spinner.hide();
                
                if (res.available) {
                    availabilityDiv.removeClass('alert-info alert-danger alert-warning').addClass('alert-success');
                    message.html('<i class="fas fa-check-circle me-2"></i>Profesional disponible! Puedes proceder con la reprogramación.');
                    submitBtn.prop('disabled', false);
                } else {
                    availabilityDiv.removeClass('alert-info alert-success alert-warning').addClass('alert-danger');
                    message.html('<i class="fas fa-times-circle me-2"></i>El Profesional ya tiene una cita en ese horario. Por favor, selecciona otra fecha/hora.');
                    submitBtn.prop('disabled', true);
                }
            },
            error: function() {
                spinner.hide();
                availabilityDiv.removeClass('alert-info alert-success alert-danger').addClass('alert-warning');
                message.html('<i class="fas fa-exclamation-triangle me-2"></i>Error al verificar disponibilidad. Inténtalo nuevamente.');
                submitBtn.prop('disabled', true);
            },
            complete: function() {
                checkBtn.prop('disabled', false);
            }
        });
    });

    // Limpiar verificación cuando cambian fecha/hora
    $('#new_date, #new_time, #new_duration').on('change', function() {
        $('#availability-check').hide();
        $('button[type="submit"]').prop('disabled', true);
    });

    // Envío del formulario
    $('#formReprogramar').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Reprogramando...').prop('disabled', true);
        
        $.ajax({
            url: '<?= get_uri("appointments/reschedule") ?>',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#modalReprogramar').modal('hide');
                    appAlert.success('Cita reprogramada correctamente');
                    $('#appointments-table').DataTable().ajax.reload();
                } else {
                    appAlert.error(res.message || 'Error al reprogramar la cita');
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

<style>
.current-appointment {
    border-left: 4px solid #6c757d;
}

.new-schedule {
    border-left: 4px solid #28a745;
    padding-left: 15px;
}

.info-item {
    padding: 5px 0;
}

#availability-check {
    border-radius: 8px;
    font-weight: 500;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>