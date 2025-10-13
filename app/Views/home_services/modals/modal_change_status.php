<?php
// home_services/modals/modal_change_status.php

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

<div class="modal fade" id="modalChangeStatus" tabindex="-1" aria-labelledby="modalChangeStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChangeStatusLabel">
                    <i class="fas fa-flag"></i> Cambiar Estado del Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeStatusForm">
                <?= csrf_field() ?>
                <input type="hidden" name="service_id" value="<?= $service->id ?>">
                <div class="modal-body">
                    <!-- Información del Servicio -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información del Servicio</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ID:</strong> #<?= $service->id ?></p>
                                    <p class="mb-1"><strong>Paciente:</strong> <?= $service->patient_name ?></p>
                                    <p class="mb-1"><strong>Tipo:</strong> <?= $service->service_type ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Fecha:</strong> <?= $service->appointment_date ? date('m/d/Y', strtotime($service->appointment_date)) : 'Sin fecha' ?></p>
                                    <p class="mb-1"><strong>Programado:</strong> <?= $service->scheduled_date ? date('m/d/Y', strtotime($service->scheduled_date)) : 'Sin programar' ?></p>
                                    <p class="mb-1"><strong>Hora:</strong> <?= $service->scheduled_time ? date('h:i A', strtotime($service->scheduled_time)) : 'Sin hora' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Actual -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Estado Actual</strong></label>
                        <div class="d-flex align-items-center">
                            <span class="badge-ghost bg-<?= getStatusColor($service->status) ?> fs-6 me-2">
                                <?= getStatusText($service->status) ?>
                            </span>
                            <small class="text-muted">
                                <?php if($service->updated_at): ?>
                                    Último cambio: <?= date('m/d/Y h:i A', strtotime($service->updated_at)) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>

                    <!-- Nuevo Estado -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Nuevo Estado *</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <select name="status" class="input-ghost" required id="statusSelect">
                                    <option value="">Seleccionar estado...</option>
                                    <option value="pendiente" <?= $service->status == 'pendiente' ? 'selected' : '' ?>>
                                        🟡 Pendiente
                                    </option>
                                    <option value="en_progreso" <?= $service->status == 'en_progreso' ? 'selected' : '' ?>>
                                        🔵 En Progreso
                                    </option>
                                    <option value="completado" <?= $service->status == 'completado' ? 'selected' : '' ?>>
                                        🟢 Completado
                                    </option>
                                    <option value="no_encontrado" <?= $service->status == 'no_encontrado' ? 'selected' : '' ?>>
                                        🔴 No Encontrado
                                    </option>
                                    <option value="cancelado" <?= $service->status == 'cancelado' ? 'selected' : '' ?>>
                                        ⚫ Cancelado
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div id="statusPreview" class="d-flex align-items-center justify-content-center h-100">
                                    <span class="badge-ghost bg-light text-dark">Vista previa del estado</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas de Estado -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Notas del Estado</strong></label>
                        <textarea name="completion_notes" class="input-ghost" rows="4" 
                                  placeholder="Describe el resultado del servicio, motivos de cambio, observaciones, etc."><?= $service->completion_notes ?? '' ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            Estas notas son importantes para el seguimiento y registro del servicio.
                        </div>
                    </div>

                    <!-- Información Adicional según Estado -->
                    <div id="additionalInfo" class="alert d-none">
                        <!-- Se llenará dinámicamente con JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-ghost btn-ghost-warning">
                        <i class="fas fa-flag"></i> Cambiar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Vista previa del estado
document.getElementById('statusSelect').addEventListener('change', function() {
    const status = this.value;
    const preview = document.getElementById('statusPreview');
    const additionalInfo = document.getElementById('additionalInfo');
    
    const statusColors = {
        'pendiente': { color: 'warning', text: 'Pendiente', icon: '🟡' },
        'en_progreso': { color: 'info', text: 'En Progreso', icon: '🔵' },
        'completado': { color: 'success', text: 'Completado', icon: '🟢' },
        'no_encontrado': { color: 'danger', text: 'No Encontrado', icon: '🔴' },
        'cancelado': { color: 'secondary', text: 'Cancelado', icon: '⚫' }
    };
    
    if (status && statusColors[status]) {
        const statusInfo = statusColors[status];
        preview.innerHTML = `<span class="badge-ghost bg-${statusInfo.color} fs-6">${statusInfo.icon} ${statusInfo.text}</span>`;
        
        // Mostrar información adicional según el estado
        let infoHTML = '';
        switch(status) {
            case 'completado':
                infoHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Servicio Completado:</strong> Se registrará la fecha de finalización automáticamente.
                    </div>
                `;
                break;
            case 'no_encontrado':
                infoHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Paciente No Encontrado:</strong> Considera reprogramar el servicio o contactar al paciente.
                    </div>
                `;
                break;
            case 'cancelado':
                infoHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        <strong>Servicio Cancelado:</strong> Por favor especifica el motivo en las notas.
                    </div>
                `;
                break;
            default:
                infoHTML = '';
        }
        
        if (infoHTML) {
            additionalInfo.innerHTML = infoHTML;
            additionalInfo.classList.remove('d-none');
        } else {
            additionalInfo.classList.add('d-none');
        }
    } else {
        preview.innerHTML = '<span class="badge bg-light text-dark">Vista previa del estado</span>';
        additionalInfo.classList.add('d-none');
    }
});

// Función para establecer notas rápidas
function setQuickNote(note) {
    const textarea = document.querySelector('textarea[name="completion_notes"]');
    const currentText = textarea.value.trim();
    
    if (currentText && !currentText.includes(note)) {
        textarea.value = currentText + '\n\n' + note;
    } else if (!currentText) {
        textarea.value = note;
    }
    
    // Hacer scroll al textarea
    textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    textarea.focus();
}

// Envío del formulario
document.getElementById('changeStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validar que se haya seleccionado un estado
    if (!formData.get('status')) {
        alert('Por favor selecciona un estado');
        return;
    }
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('<?= get_uri("home_services/change_service_status") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalChangeStatus')).hide();
            
            // Recargar datos en todas las vistas
            if (typeof loadServices === 'function') loadServices();
            if (typeof loadSchedule === 'function') loadSchedule();
            if (typeof loadMapData === 'function') loadMapData();
            
            // Mostrar mensaje de éxito
            showSuccessMessage(data.message);
        } else {
            throw new Error(data.message || 'Error al cambiar el estado');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>