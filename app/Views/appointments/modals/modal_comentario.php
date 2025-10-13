<div class="modal fade" id="modalComentarioCita" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Comentario de la cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Paciente:</strong> <?= esc($info->patient_name) ?></p>
                <p><strong>Correo:</strong> <?= esc($info->email) ?></p>
                <p><strong>Teléfono:</strong> <?= esc($info->phone) ?></p>
                <hr>
                <p><strong>Comentario:</strong><br><?= esc($info->comment) ?></p>
            </div>
        </div>
    </div>
</div>
