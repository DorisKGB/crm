<div class="modal fade" id="modalEditPatient" tabindex="-1" role="dialog" aria-labelledby="modalEditPatientLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit-patient">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $patient->id ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditPatientLabel">Editar Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="full_name" class="form-control" value="<?= esc($patient->full_name) ?>"
                            required>
                    </div>

                    <div class="form-group mt-2">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" value="<?= esc($patient->email) ?>">
                    </div>

                    <div class="form-group mt-2">
                        <label>Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="<?= esc($patient->phone) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-button btn-button-purple"><i class="fas fa-check"></i> Guardar
                        cambios</button>
                    <button type="button" class="btn-button btn-button-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).off("submit", "#form-edit-patient").on("submit", "#form-edit-patient", function(e) {
    e.preventDefault();
    showLoading();
    const form = $(this);
    $.post("<?= get_uri('patients/update_patient') ?>", form.serialize(), function(res) {
        if (res.success) {
            $("#modalEditPatient").modal("hide");
            // Refrescar la tabla sin recargar la página
            if (typeof loadPatients === "function") loadPatients();
            showSuccess('Paciente actualizado correctamente.');
        } else {
            showError('Error al actualizar.');
            //alert(res.message || "Error al actualizar.");
        }
    }, "json");
});
</script>