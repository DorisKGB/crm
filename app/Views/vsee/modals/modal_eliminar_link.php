<div class="modal fade" id="modalEliminarVseeLink" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-eliminar-vsee-link">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($link->id) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este enlace?</p>
                    <ul class="list-unstyled">
                        <li><strong>Usuario:</strong> <?= esc($link->user_name) ?></li>
                        <li><strong>Clínica:</strong> <?= esc($link->clinic_name) ?></li>
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
    $(document).on("submit", "#form-eliminar-vsee-link", function(e) {
        e.preventDefault();
        const form = $(this);
        showLoading();
        $.post("<?= get_uri('vseeusers/delete') ?>", form.serialize(), function(res) {
            if (res.success) {
                $('#modalEliminarVseeLink').modal('hide');
                showSuccess("Enlace eliminado correctamente.");
                if ($.fn.DataTable && $('#vsee-links-table').length) {
                    $('#vsee-links-table').DataTable().ajax.reload();
                }
            } else {
                showError("Error al eliminar el enlace.");
            }
        }, "json").fail(() => showError("Error inesperado."));
    });
</script>
