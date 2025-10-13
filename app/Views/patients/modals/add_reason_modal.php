<div class="modal fade" id="modalAddReason" tabindex="-1" role="dialog" aria-labelledby="modalAddReasonLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-add-reason">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddReasonLabel">Agregar Motivo de Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="form-group">
                        <div class="mb-3">
                            <strong>Paciente:</strong> <?= esc($patient->full_name) ?><br>
                            <strong>Teléfono:</strong> <?= esc($patient->phone) ?><br>
                            <strong>Email:</strong> <?= esc($patient->email) ?>
                        </div>

                        <small>En este campo debes describir de forma breve y clara el problema de salud o razón por la que el paciente solicita atención médica. Esto ayuda al profesional a prepararse antes de atenderte. Puedes incluir síntomas, molestias, duración del problema o si es una cita de control.</small>
                        <textarea class="form-control" name="reason" required style="min-height: 300px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-button btn-button-purple"><i class="fas fa-check"></i> Guardar</button>
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).off('submit', '#form-add-reason').on('submit', '#form-add-reason', function(e) {
        e.preventDefault();
        showLoading();
        const form = $(this);
        const data = form.serialize();

        $.post("<?= get_uri('patients/save_reason') ?>", data, function(response) {
            if (response.success) {
                // Cierra el modal
                $('#modalAddReason').modal('hide');
                showSuccess("Agregado Correctamente.");
                // Opcional: mostrar alerta o refrescar tabla
                loadPatients();
            } else {
                alert(response.message || 'Ocurrió un error al guardar el motivo.');
                showError("Error al guardar.");
            }
        }, 'json');
    });
</script>