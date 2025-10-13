<div class="modal fade" id="modalEditarCita" tabindex="-1" role="dialog" aria-labelledby="modalEditarCitaLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-editar-cita">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit-id" value="<?= esc($cita->id) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-provider_id">Médico/Provider</label>
                        <select name="provider_id" id="edit-provider_id" class="form-control" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($providers as $p): ?>
                                <option value="<?= $p->id ?>" <?= $cita->provider_id == $p->id ? 'selected' : '' ?>>
                                    <?= esc($p->first_name . ' ' . $p->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit-patient_id">Paciente</label>
                        <select name="patient_id" id="edit-patient_id" class="form-control" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p->id ?>" <?= $cita->patient_id == $p->id ? 'selected' : '' ?>>
                                    <?= esc($p->full_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit-date">Fecha</label>
                        <input type="date" name="date" id="edit-date" class="form-control" required value="<?= esc($cita->appointment_date) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit-time">Hora</label>
                        <input type="time" name="time" id="edit-time" class="form-control" required value="<?= esc($cita->appointment_time) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit-duration_minutes">Duración</label>
                        <input type="number" name="duration_minutes" id="edit-duration_minutes" class="form-control" required value="<?= esc($cita->duration_minutes) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit-duration_minutes">Teleconsulta</label>
                        <input type="text" name="vsee_link" id="edit-link" class="form-control" required value="<?= esc($cita->vsee_link) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="edit-comment">Comentario</label>
                        <textarea name="comment" id="edit-comment" class="form-control" style="min-height: 150px;"><?= esc($cita->comment) ?></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-button btn-button-purple"><i class="fas fa-save"></i> Actualizar</button>
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editAppointment(id) {
        $.get("<?= get_uri('appointments/get') ?>", { id }, function(data) {
            if (data) {
                $("#edit-id").val(data.id);
                $("#edit-provider_id").val(data.provider_id);
                $("#edit-patient_id").val(data.patient_id);
                $("#edit-date").val(data.appointment_date);
                $("#edit-time").val(data.appointment_time);
                $("#edit-duration_minutes").val(data.duration_minutes);
                $("#edit-link").val(data.vsee_link);
                $("#edit-comment").val(data.comment);
                const modal = new bootstrap.Modal(document.getElementById('modalEditarCita'));
                modal.show();
            }
        }, "json");
    }

    $(document).on("submit", "#form-editar-cita", function(e) {
        e.preventDefault();
             showLoading();
        $.post("<?= get_uri('appointments/update') ?>", $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalEditarCita').modal('hide');
                showSuccess("Cita actualizada correctamente.");
                if ($.fn.DataTable && $('#appointments-table').length) {
                    $('#appointments-table').DataTable().ajax.reload();
                }
            } else {
                showError(res.message || "Error al actualizar la cita.");
            }
        }, "json").fail(() => showError("Error inesperado."));
    });
</script>
