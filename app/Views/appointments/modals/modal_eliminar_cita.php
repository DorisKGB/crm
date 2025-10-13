<div class="modal fade" id="modalEliminarCita" tabindex="-1" role="dialog" aria-labelledby="modalEliminarCitaLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-eliminar-cita">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($cita->id) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la siguiente cita?</p>
                    <ul class="list-unstyled">
                        <li><strong>Paciente:</strong> <?= esc($cita->patient_name) ?></li>
                        <li><strong>Médico:</strong> <?= esc($cita->provider_name) ?></li>
                        <li><strong>Fecha:</strong> <?= format_to_datetime($cita->appointment_date) ?></li>
                        <li><strong>Hora:</strong> <?= esc($cita->appointment_time) ?></li>
                    </ul>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-button btn-button-danger"><i class="fas fa-trash"></i> Eliminar</button>
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on("submit", "#form-eliminar-cita", function(e) {
        e.preventDefault();
        const form = $(this);
             showLoading();
        $.post("<?= get_uri('appointments/delete') ?>", form.serialize(), function(res) {
            if (res.success) {
                $('#modalEliminarCita').modal('hide');
                showSuccess("Cita eliminada correctamente.");
                if ($.fn.DataTable && $('#appointments-table').length) {
                    $('#appointments-table').DataTable().ajax.reload();
                }
            } else {
                showError("Error al eliminar la cita.");
            }
        }, "json").fail(() => showError("Error inesperado."));
    });
</script>
