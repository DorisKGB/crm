<div class="modal fade" id="modalExtraData" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formExtraData">
                <div class="modal-header">
                    <h5 class="modal-title">Información adicional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= esc($id ?? '') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <strong>Paciente:</strong> <?= esc($patient->full_name) ?><br>
                        <strong>Teléfono:</strong> <?= esc($patient->phone) ?><br>
                        <strong>Email:</strong> <?= esc($patient->email) ?>
                    </div>
                    <hr>
                    <div id="extraFields">
                        <?php foreach ($extra_data ?? [] as $key => $value): ?>
                            <div class="mb-3 border p-3 rounded shadow-sm bg-light position-relative">
                                <div class="mb-2">
                                    <label class="form-label"><strong>Observación</strong></label>
                                    <input type="text" name="keys[]" class="form-control" value="<?= esc($key) ?>" placeholder="Ej: Alergia, Diagnóstico">
                                </div>
                                <div>
                                    <label class="form-label"><strong>Descripción</strong></label>
                                    <textarea name="values[]" rows="2" class="form-control" placeholder="Escribe aquí la descripción"><?= esc($value) ?></textarea>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-field" title="Eliminar campo">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn-button btn-button-outline-secondary" id="addField">+ Agregar campo</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-button btn-button-purple"><i class="fas fa-check"></i> Guardar</button>
                    <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#addField').on('click', function() {
        $('#extraFields').append(`
        <div class="mb-3 border p-3 rounded shadow-sm bg-light position-relative">
            <div class="mb-2">
                <label class="form-label"><strong>Observacion</strong></label>
                <input type="text" name="keys[]" class="form-control" placeholder="Ej: Alergia, Diagnóstico">
            </div>
            <div>
                <label class="form-label"><strong>Descripción</strong></label>
                <textarea name="values[]" rows="2" class="form-control" style="min-height: 300px;" placeholder="Escribe aquí la descripción"></textarea>
            </div>
            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-field" title="Eliminar campo">
                <i class="fa fa-times"></i>
            </button>
        </div>
    `);
    });

    $('#extraFields').on('click', '.remove-field', function() {
        $(this).closest('.mb-3').remove();
    });

    $('#formExtraData').on('submit', function(e) {
        e.preventDefault();
        showLoading();
        $.post("<?= get_uri('patients/save_extra_data') ?>", $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalExtraData').modal('hide');
                loadPatients();
                showSuccess('Guardado Correctamente!');
            } else {
                alert(res.message || "Error al guardar datos extra.");
                showError('Error al guardar!');
            }
        }, 'json');
    });
</script>