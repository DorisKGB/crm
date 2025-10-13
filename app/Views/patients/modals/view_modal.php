<div class="modal fade" id="modalVerPaciente" tabindex="-1" role="dialog" aria-labelledby="modalVerPacienteLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4">
            <div class="modal-header border-bottom-0">
                <h4 class="modal-title" id="modalVerPacienteLabel">Historia Clínica del Paciente</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="p-3" style="border: 1px solid #e5e5e5;">
                    <div class="text-center">
                        <img src="https://www.clinicahispanarubymed.com/wp-content/uploads/2024/07/Logonuevo.png" width="150px" alt="">
                    </div>
                    <hr>
                    <style>
                        .comment-box {
                            border-left: 4px solid #6c63ff;
                            background: #f8f9fa;
                        }

                        .timeline-container {
                            max-height: 400px;
                            overflow-y: auto;
                            padding-right: 10px;
                        }
                    </style>
                    <div class="mb-4">
                        <h5 class="mb-3 ">Datos del Paciente</h5>
                        <p><strong>Nombre completo:</strong> <?= esc($patient->full_name) ?></p>
                        <p><strong>Teléfono:</strong> <?= esc($patient->phone) ?></p>
                        <p><strong>Email:</strong> <?= esc($patient->email) ?></p>
                        <p><strong>Fecha de Registro:</strong> <?= format_to_datetime($patient->created_at) ?></p>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5 class="mb-3"> Motivos de Consulta</h5>

                        <?php if (!empty($reasons)) : ?>
                            <div class="timeline-container">
                                <?php foreach (array_reverse($reasons) as $r): ?>
                                    <div class="comment-box mb-3 p-3 rounded shadow-sm bg-light">
                                        <div class="mb-1 text-muted small">
                                            <i class="fa fa-calendar-alt me-1"></i>
                                            <?= format_to_date($r['fecha']) ?>
                                        </div>
                                        <div class="text-dark" style="white-space: pre-line;">
                                            <?= esc($r['motivo']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No se han registrado motivos de consulta.</p>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5 class="mb-3 ">Información Extra</h5>
                        <?php if (!empty($extra_data)) : ?>
                            <?php foreach ($extra_data as $k => $v): ?>
                                <p><strong><?= esc($k) ?>:</strong> <?= esc($v) ?></p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Sin información adicional registrada.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn-button btn-button-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>