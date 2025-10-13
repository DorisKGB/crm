<?php
// Función auxiliar para obtener color del estado
function getStatusColor($status) {
    $colors = [
        'pendiente' => 'warning',
        'en_progreso' => 'info',
        'completado' => 'success',
        'no_encontrado' => 'danger',
        'cancelado' => 'secondary'
    ];
    return $colors[$status] ?? 'light';
}

// Función auxiliar para obtener texto del estado
function getStatusText($status) {
    $texts = [
        'pendiente' => 'Pendiente',
        'en_progreso' => 'En Progreso',
        'completado' => 'Completado',
        'no_encontrado' => 'No Encontrado',
        'cancelado' => 'Cancelado'
    ];
    return $texts[$status] ?? $status;
}
?>
<div class="modal fade" id="modalServiceDetails" tabindex="-1" aria-labelledby="modalServiceDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalServiceDetailsLabel">
                    <i class="fas fa-eye"></i> Detalles del Servicio Domiciliario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Información General -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><b><i class="fas fa-info-circle"></i> Información General</b></h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>ID Servicio:</strong></div>
                                    <div class="col-8">#<?= $service->id ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Tipo:</strong></div>
                                    <div class="col-8">
                                        <span class="badge-ghost badge-ghost-secondary"><?= $service->service_type ?: 'Sin especificar' ?></span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Estado:</strong></div>
                                    <div class="col-8">
                                        <span class="badge-ghost badge-ghost-<?= getStatusColor($service->status) ?>">
                                            <?= getStatusText($service->status) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Fecha Programada:</strong></div>
                                    <div class="col-8">
                                        <?= $service->scheduled_date ? date('m/d/Y', strtotime($service->scheduled_date)) : 'Sin programar' ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Hora Programada:</strong></div>
                                    <div class="col-8">
                                        <?= $service->scheduled_time ? date('h:i A', strtotime($service->scheduled_time)) : 'Sin programar' ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Creado:</strong></div>
                                    <div class="col-8">
                                        <?= date('m/d/Y h:i A', strtotime($service->created_at)) ?>
                                    </div>
                                </div>
                                <?php if($service->completion_date): ?>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Finalizado:</strong></div>
                                    <div class="col-8">
                                        <?= date('m/d/Y h:i A', strtotime($service->completion_date)) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Información del Proveedor -->
                        <div class="card mb-4">
                            <div class="card-header ">
                                <h6 class="mb-0"><b><i class="fas fa-user-md"></i> Proveedor de Servicios</b></h6>
                            </div>
                            <div class="card-body">
                                <?php if($service->assigned_provider_name): ?>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Asignado a:</strong></div>
                                        <div class="col-8">
                                            <i class="fas fa-user-md text-success"></i>
                                            <?= $service->assigned_provider_name ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No hay proveedor asignado a este servicio
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($service->provider_name): ?>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Médico Referente:</strong></div>
                                        <div class="col-8">
                                            <i class="fas fa-stethoscope text-primary"></i>
                                            <?= $service->provider_name ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Paciente -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><b><i class="fas fa-user"></i> Información del Paciente</b></h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Nombre:</strong></div>
                                    <div class="col-8"><?= $service->patient_name ?: 'Sin especificar' ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Teléfono:</strong></div>
                                    <div class="col-8">
                                        <?php if($service->patient_phone): ?>
                                            <a href="tel:<?= $service->patient_phone ?>" class="text-decoration-none">
                                                <i class="fas fa-phone text-success"></i> <?= $service->patient_phone ?>
                                            </a>
                                        <?php else: ?>
                                            Sin teléfono
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8">
                                        <?php if($service->patient_email): ?>
                                            <a href="mailto:<?= $service->patient_email ?>" class="text-decoration-none">
                                                <i class="fas fa-envelope text-info"></i> <?= $service->patient_email ?>
                                            </a>
                                        <?php else: ?>
                                            Sin email
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if($service->patient_dob): ?>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Fecha Nacimiento:</strong></div>
                                    <div class="col-8">
                                        <?= date('m/d/Y', strtotime($service->patient_dob)) ?>
                                        <small class="text-muted">
                                            (<?= floor((time() - strtotime($service->patient_dob)) / 31556926) ?> años)
                                        </small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ubicación del Servicio -->
                        <div class="card mb-4">
                            <div class="card-header ">
                                <h6 class="mb-0"><b><i class="fas fa-map-marker-alt"></i> Ubicación del Servicio</b></h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Dirección:</strong></div>
                                    <div class="col-8"><?= $service->patient_address ?: 'Sin especificar' ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Ciudad:</strong></div>
                                    <div class="col-8"><?= $service->patient_city ?: 'Sin especificar' ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Estado:</strong></div>
                                    <div class="col-8"><?= $service->patient_state ?: 'Sin especificar' ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Código Postal:</strong></div>
                                    <div class="col-8"><?= $service->patient_zipcode ?: 'Sin especificar' ?></div>
                                </div>
                                
                                <?php if($service->patient_address): ?>
                                <div class="mt-3">
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($service->patient_address . ', ' . $service->patient_city . ', ' . $service->patient_state . ' ' . $service->patient_zipcode) ?>" 
                                       target="_blank" class="badge-ghost badge-ghost-sm badge-ghost-primary">
                                        <i class="fas fa-map"></i> Ver en Google Maps
                                    </a>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($service->patient_address . ', ' . $service->patient_city . ', ' . $service->patient_state . ' ' . $service->patient_zipcode) ?>" 
                                       target="_blank" class="badge-ghost badge-ghost-sm badge-ghost-success">
                                        <i class="fas fa-directions"></i> Obtener Direcciones
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial del Paciente -->
                <?php if($service->patient_history): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header ">
                                <h6 class="mb-0"><b><i class="fas fa-history"></i> Historial Médico del Paciente</b></h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $history = json_decode($service->patient_history, true);
                                if($history && is_array($history)):
                                ?>
                                    <div class="timeline">
                                        <?php foreach(array_reverse($history) as $index => $entry): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">
                                                    <?= date('m/d/Y', strtotime($entry['fecha'])) ?>
                                                </h6>
                                                <p class="timeline-description"><?= htmlspecialchars($entry['motivo']) ?></p>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay historial médico disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notas y Comentarios -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header ">
                                <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notas del Servicio</h6>
                            </div>
                            <div class="card-body">
                                <?php if($service->service_notes): ?>
                                    <p><?= nl2br(htmlspecialchars($service->service_notes)) ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No hay notas del servicio</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header ">
                                <h6 class="mb-0"><i class="fas fa-comments"></i> Comentarios de Finalización</h6>
                            </div>
                            <div class="card-body">
                                <?php if($service->completion_notes): ?>
                                    <p><?= nl2br(htmlspecialchars($service->completion_notes)) ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No hay comentarios de finalización</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la Cita Original -->
                <?php if($service->appointment_comment): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header ">
                                <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Información de la Cita Original</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-3"><strong>Fecha de Cita:</strong></div>
                                    <div class="col-9">
                                        <?= date('m/d/Y', strtotime($service->appointment_date)) ?> a las 
                                        <?= date('h:i A', strtotime($service->appointment_time)) ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3"><strong>Comentarios:</strong></div>
                                    <div class="col-9"><?= nl2br(htmlspecialchars($service->appointment_comment)) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-ghost btn-ghost-primary" onclick="editService(<?= $service->id ?>)">
                    <i class="fas fa-edit"></i> Editar Servicio
                </button>
                <button type="button" class="btn-ghost btn-ghost-warning" onclick="changeServiceStatus(<?= $service->id ?>)">
                    <i class="fas fa-flag"></i> Cambiar Estado
                </button>
                <?php if($service->patient_phone): ?>
                <a href="tel:<?= $service->patient_phone ?>" class="btn-ghost btn-ghost-success d-none">
                    <i class="fas fa-phone"></i> Llamar Paciente
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -21px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.timeline-title {
    margin: 0 0 5px 0;
    color: #495057;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-description {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}
</style>

<script>
function getStatusColor(status) {
    const colors = {
        'pendiente': 'warning',
        'en_progreso': 'info',
        'completado': 'success',
        'no_encontrado': 'danger',
        'cancelado': 'secondary'
    };
    return colors[status] || 'light';
}

function getStatusText(status) {
    const texts = {
        'pendiente': 'Pendiente',
        'en_progreso': 'En Progreso',
        'completado': 'Completado',
        'no_encontrado': 'No Encontrado',
        'cancelado': 'Cancelado'
    };
    return texts[status] || status;
}

function editService(id) {
    // Cerrar modal actual
    bootstrap.Modal.getInstance(document.getElementById('modalServiceDetails')).hide();
    
    // Abrir modal de edición
    setTimeout(() => {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_edit_service") ?>', { id }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalEditService'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal de edición');
        });
    }, 300);
}

function changeServiceStatus(id) {
    // Cerrar modal actual
    bootstrap.Modal.getInstance(document.getElementById('modalServiceDetails')).hide();
    
    // Abrir modal de cambio de estado
    setTimeout(() => {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_change_status") ?>', { id }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalChangeStatus'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal de cambio de estado');
        });
    }, 300);
}
</script>