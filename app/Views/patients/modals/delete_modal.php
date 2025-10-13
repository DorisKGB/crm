<div class="modal fade" id="modalDeletePatient" tabindex="-1" role="dialog" aria-labelledby="modalDeletePatientLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-delete-patient">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $patient->id ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDeletePatientLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar al paciente <strong><?= esc($patient->full_name) ?></strong>?</p>
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
    $(document).on("submit", "#form-delete-patient", function(e) {
        e.preventDefault();
        const form = $(this);
        showLoading();
        $.post("<?= get_uri('patients/delete_patient') ?>", form.serialize(), function(res) {
            if (res.success) {
                $("#modalDeletePatient").modal("hide");
                showSuccess('Paciente eliminado correctamente.');
                //alert("Paciente eliminado correctamente.");
                // recarga la tabla o datos
                if (typeof loadPatients === "function") loadPatients();
            } else {
                showError('Error al eliminar.');
                //alert("Error al eliminar.");
            }
        }, "json");
    });
</script>