<div class="modal fade" id="modalVseeLink" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <style>
                .vsee-action-selector {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 1rem;
                }

                .vsee-action-selector input[type="radio"] {
                    display: none;
                }

                .vsee-action-selector label {
                    flex: 1;
                    text-align: center;
                    padding: 0.75rem;
                    margin: 0 5px;
                    border: 2px solid #ddd;
                    border-radius: 12px;
                    background-color: #f7f7f7;
                    color: #333;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    user-select: none;
                }

                .vsee-action-selector label:hover {
                    background-color: #ececec;
                    border-color: #bbb;
                }

                .vsee-action-selector input[type="radio"]:checked+label {
                    background-color: #7538c9;
                    border-color: #5a22a4;
                    color: #fff;
                    box-shadow: 0 4px 10px rgba(117, 56, 201, 0.3);
                }

                .vsee-action-selector i {
                    margin-right: 8px;
                }
            </style>
            <form id="form-vsee-link">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title">Crear Usuario Vsee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="fw-bold">Seleccione el Usuario</label>
                        <?= form_dropdown('user_id', $users, [], 'class="form-control" required') ?>
                    </div>

                    <!-- Selector visual para acción -->
                    <label class="fw-bold mb-2">¿Cómo actuará en la mensajería?</label>
                    <div class="vsee-action-selector">
                        <input type="radio" name="action" id="action-clinic" value="clinic" >
                        <label for="action-clinic"><i class="fas fa-hospital"></i> Clínica</label>

                        <input type="radio" name="action" id="action-user" value="user" >
                        <label for="action-user"><i class="fas fa-user"></i> Usuario</label>

                        <input type="radio" name="action" id="action-provider" value="provider" >
                        <label for="action-provider"><i class="fas fa-user-md"></i> Médico</label>
                    </div>

                    <!-- Selectores -->


                    <div class="mb-3" id="clinic-selector" style="display:none;">
                        <label class="fw-bold">Selecciona la clínica</label>
                        <?= form_dropdown('clinic_id', $clinics, [], 'class="form-control"') ?>
                    </div>

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
    function toggleClinicField() {
        const action = document.querySelector('input[name="action"]:checked')?.value;
        const clinicSelector = document.getElementById('clinic-selector');

        if (action === 'clinic') {
            clinicSelector.style.display = 'block';
            clinicSelector.querySelector('select').setAttribute('required', true);
        } else {
            clinicSelector.style.display = 'none';
            clinicSelector.querySelector('select').removeAttribute('required');
        }
    }

    // Se asegura que siempre reaccione al mostrar el modal
    $('#modalVseeLink').on('shown.bs.modal', function() {
        toggleClinicField(); // al abrir modal
    });

    // Escuchar cambios de selección
    document.addEventListener("change", function(e) {
        if (e.target.name === "action") {
            toggleClinicField();
        }
    });

    // Form submit
    $('#form-vsee-link').on('submit', function(e) {
        e.preventDefault();
        showLoading();
        $.post("<?= get_uri('vseeusers/save') ?>", $(this).serialize(), function(res) {
            if (res.success) {
                $('#modalVseeLink').modal('hide');
                $('#vsee-links-table').DataTable().ajax.reload();
                showSuccess(res.message);
            } else {
                showError('Error al guardar');
            }
        }, 'json');
    });
</script>