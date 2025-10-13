<?php
// home_services/modals/modal_edit_service.php

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

<style>
.timeline-sm .timeline-item {
    border-left: 2px solid #e9ecef;
    padding-left: 15px;
    margin-bottom: 10px;
    position: relative;
}

.timeline-sm .timeline-item::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #6c757d;
    border-radius: 50%;
    position: absolute;
    left: -5px;
    top: 5px;
}

.nav-tabs .nav-link {
    border-radius: 0.375rem 0.375rem 0 0;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.priority-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.priority-normal { background-color: #28a745; }
.priority-urgent { background-color: #ffc107; }
.priority-emergency { background-color: #dc3545; }

.card-gradient {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.status-badge-ghost-ghost {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
}
</style>

<div class="modal fade" id="modalEditService" tabindex="-1" aria-labelledby="modalEditServiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditServiceLabel">
                    <i class="fas fa-edit"></i> Editar Servicio Domiciliario #<?= $service->id ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editServiceForm" method="POST">
                <input type="hidden" name="service_id" value="<?= $service->id ?>">
                <?= csrf_field() ?>
                
                <div class="modal-body">
                    <!-- Estado actual del servicio -->
                    <div class="alert alert-<?= getStatusColor($service->status) ?> mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="fas fa-flag"></i> Estado Actual: 
                                    <span class="status-badge-ghost-ghost bg-<?= getStatusColor($service->status) ?> text-white">
                                        <?= getStatusText($service->status) ?>
                                    </span>
                                </h6>
                                <small>
                                    <i class="fas fa-clock"></i>
                                    Última actualización: <?= $service->updated_at ? date('m/d/Y h:i A', strtotime($service->updated_at)) : 'No disponible' ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="button" class="btn-ghost btn-ghost-dark btn-sm" onclick="openChangeStatusModal(<?= $service->id ?>)">
                                    <i class="fas fa-flag"></i> Cambiar Estado
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs de navegación -->
                    <ul class="nav nav-tabs mb-4" id="editServiceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="service-tab" data-bs-toggle="tab" data-bs-target="#service" type="button" role="tab">
                                <i class="fas fa-medical-bag"></i> Servicio
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="patient-tab" data-bs-toggle="tab" data-bs-target="#patient" type="button" role="tab">
                                <i class="fas fa-user"></i> Paciente
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">
                                <i class="fas fa-map-marker-alt"></i> Ubicación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                                <i class="fas fa-clock"></i> Programación
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                                <i class="fas fa-sticky-note"></i> Notas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="editServiceTabContent">
                        <!-- Tab Servicio -->
                        <div class="tab-pane fade show active" id="service" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-medical-bag"></i> Tipo de Servicio *
                                        </label>
                                        <select name="service_type" class="input-ghost" required>
                                            <option value="">Seleccionar tipo...</option>
                                            <option value="Lipid Panel" <?= ($service->service_type ?? '') === 'Lipid Panel' ? 'selected' : '' ?>>
                                                🧪 Lipid Panel
                                            </option>
                                            <option value="Sueros Vitaminados" <?= ($service->service_type ?? '') === 'Sueros Vitaminados' ? 'selected' : '' ?>>
                                                💧 Sueros Vitaminados
                                            </option>
                                            <option value="Chequeo Médico General" <?= ($service->service_type ?? '') === 'Chequeo Médico General' ? 'selected' : '' ?>>
                                                🩺 Chequeo Médico General
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user-md"></i> Proveedor Asignado
                                        </label>
                                        <select name="assigned_provider_id" class="input-ghost select2" style="width: 100%;">
                                            <option value="">Sin asignar</option>
                                            <?php if(isset($providers) && is_array($providers)): ?>
                                                <?php foreach($providers as $provider): ?>
                                                    <option value="<?= $provider->id ?>" <?= $service->assigned_provider_id == $provider->id ? 'selected' : '' ?>>
                                                        👨‍⚕️ <?= $provider->first_name ?> <?= $provider->last_name ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-exclamation-triangle"></i> Prioridad del Servicio
                                        </label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="priority" id="edit_priority_normal" value="normal" <?= ($service->priority ?? 'normal') == 'normal' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="edit_priority_normal">
                                                <i class="fas fa-check-circle"></i> Normal
                                            </label>

                                            <input type="radio" class="btn-check" name="priority" id="edit_priority_priority" value="priority" <?= ($service->priority ?? '') == 'priority' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning" for="edit_priority_priority">
                                                <i class="fas fa-exclamation-circle"></i> Prioritario
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-dollar-sign"></i> Costo del Servicio
                                        </label>
                                        <div class="input-ghost-icon-container">
                                            <span class="input-ghost-icon">$</span>
                                            <input type="number" name="service_cost" class="input-ghost input-ghost-with-icon " 
                                                   value="<?= $service->service_cost ?? '0.00' ?>" 
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card card-gradient">
                                        <div class="card-body">
                                            <h6><i class="fas fa-info-circle text-primary"></i> Información del Servicio</h6>
                                            <div class="info-item mb-2">
                                                <strong>ID:</strong> #<?= $service->id ?>
                                            </div>
                                            <div class="info-item mb-2">
                                                <strong>Cita Relacionada:</strong> 
                                                <?php if($service->appointment_id): ?>
                                                    <a href="#" onclick="viewAppointment(<?= $service->appointment_id ?>)" class="text-primary">
                                                        #<?= $service->appointment_id ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="info-item mb-2">
                                                <strong>Creado:</strong> <?= date('m/d/Y h:i A', strtotime($service->created_at)) ?>
                                            </div>
                                            <div class="info-item mb-2">
                                                <strong>Estado:</strong> 
                                                <span class="priority-indicator priority-<?= $service->status ?>"></span>
                                                <?= getStatusText($service->status) ?>
                                            </div>
                                            <?php if($service->completion_date): ?>
                                            <div class="info-item mb-2">
                                                <strong>Finalizado:</strong> <?= date('m/d/Y h:i A', strtotime($service->completion_date)) ?>
                                            </div>
                                            <?php endif; ?>
                                            <div class="info-item">
                                                <strong>Creado por:</strong> 
                                                <span class="text-muted"><?= $service->created_by_name ?? 'Sistema' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Paciente -->
                        <div class="tab-pane fade" id="patient" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card border-primary">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-user"></i> Información del Paciente</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Nombre Completo:</strong><br>
                                                        <span class="text-primary"><?= $appointment->patient_name ?? 'N/A' ?></span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Teléfono:</strong><br>
                                                        <?php if($appointment->phone ?? null): ?>
                                                            <a href="tel:<?= $appointment->patient_phone ?>" class="text-success">
                                                                <i class="fas fa-phone"></i> <?= $appointment->phone ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No disponible</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <strong>Email:</strong><br>
                                                        <?php if($appointment->email ?? null): ?>
                                                            <a href="mailto:<?= $appointment->patient_email ?>" class="text-info">
                                                                <i class="fas fa-envelope"></i> <?= $appointment->email ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No disponible</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="mb-3 d-none">
                                                        <strong>Edad:</strong><br>
                                                        <span class="text-muted"><?= $appointment->patient_age ?? 'N/A' ?> años</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if($appointment->patient_id ?? null): ?>
                                            <div class="text-center">
                                                <button type="button" class="btn btn-outline-primary btn-sm d-none" onclick="viewPatientHistory(<?= $appointment->patient_id ?>)">
                                                    <i class="fas fa-history"></i> Ver Historial Médico
                                                </button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="fas fa-notes-medical"></i> Notas del Paciente</h6>
                                            <textarea class="input-ghost" rows="8" name="patient_notes" 
                                                      placeholder="Observaciones especiales del paciente..."><?= $service->patient_notes ?? '' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Ubicación -->
                        <div class="tab-pane fade" id="location" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-flag-usa"></i> Estado *
                                        </label>
                                        <select name="patient_state" class="input-ghost" required>
                                            <option value="">Seleccionar estado...</option>
                                            <?php if(isset($states) && is_array($states)): ?>
                                                <?php foreach($states as $state): ?>
                                                    <option value="<?= $state->code ?>" <?= $service->patient_state == $state->code ? 'selected' : '' ?>>
                                                        <?= $state->name ?> (<?= $state->code ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-city"></i> Ciudad *
                                        </label>
                                        <input type="text" name="patient_city" class="input-ghost" 
                                               value="<?= $service->patient_city ?>" required 
                                               placeholder="Ej: Miami">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-mail-bulk"></i> Código Postal
                                        </label>
                                        <input type="text" name="patient_zipcode" class="input-ghost" 
                                               value="<?= $service->patient_zipcode ?>" 
                                               placeholder="Ej: 33101">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-map-marker-alt"></i> Dirección Completa *
                                        </label>
                                        <textarea name="patient_address" class="input-ghost" rows="4" required 
                                                  placeholder="Ingrese la dirección completa..."><?= $service->patient_address ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-home"></i> Instrucciones de Ubicación
                                        </label>
                                        <textarea name="location_instructions" class="input-ghost" rows="3" 
                                                  placeholder="Puntos de referencia, edificio, apartamento, etc..."><?= $service->location_instructions ?? '' ?></textarea>
                                    </div>

                                    <?php if($service->patient_address): ?>
                                    <div class="d-grid gap-2">
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($service->patient_address . ', ' . $service->patient_city . ', ' . $service->patient_state . ' ' . $service->patient_zipcode) ?>" 
                                           target="_blank" class="btn btn-outline-primary">
                                            <i class="fas fa-map"></i> Ver en Google Maps
                                        </a>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($service->patient_address . ', ' . $service->patient_city . ', ' . $service->patient_state . ' ' . $service->patient_zipcode) ?>" 
                                           target="_blank" class="btn btn-outline-success">
                                            <i class="fas fa-directions"></i> Obtener Direcciones
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Programación -->
                        <div class="tab-pane fade" id="schedule" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="fas fa-calendar"></i> Fecha Programada
                                            </label>
                                            <input type="date" name="scheduled_date" class="input-ghost" 
                                                   value="<?= $service->scheduled_date ?>" min="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <i class="fas fa-clock"></i> Hora Programada
                                            </label>
                                            <input type="time" name="scheduled_time" class="input-ghost" 
                                                   value="<?= $service->scheduled_time ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-hourglass-half"></i> Duración Estimada (minutos)
                                        </label>
                                        <select name="estimated_duration" class="input-ghost">
                                            <option value="">Seleccionar duración...</option>
                                            <option value="30" <?= ($service->estimated_duration ?? '') == '30' ? 'selected' : '' ?>>30 minutos</option>
                                            <option value="45" <?= ($service->estimated_duration ?? '') == '45' ? 'selected' : '' ?>>45 minutos</option>
                                            <option value="60" <?= ($service->estimated_duration ?? '') == '60' ? 'selected' : '' ?>>1 hora</option>
                                            <option value="90" <?= ($service->estimated_duration ?? '') == '90' ? 'selected' : '' ?>>1.5 horas</option>
                                            <option value="120" <?= ($service->estimated_duration ?? '') == '120' ? 'selected' : '' ?>>2 horas</option>
                                        </select>
                                    </div>

                                    <div class="mb-3 d-none">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requires_equipment" 
                                                   id="editRequiresEquipment" value="1" 
                                                   <?= ($service->requires_equipment ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="editRequiresEquipment">
                                                <i class="fas fa-medical-bag"></i> Requiere equipo médico especial
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requires_preparation" 
                                                   id="editRequiresPreparation" value="1"
                                                   <?= ($service->requires_preparation ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="editRequiresPreparation">
                                                <i class="fas fa-clock"></i> Requiere preparación previa del paciente
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_follow_up" 
                                                   id="editIsFollowUp" value="1"
                                                   <?= ($service->is_follow_up ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="editIsFollowUp">
                                                <i class="fas fa-redo"></i> Es un servicio de seguimiento
                                            </label>
                                        </div>
                                    </div>

                                    <div id="providerAvailability" class="alert d-none"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-gradient">
                                        <div class="card-body">
                                            <h6><i class="fas fa-calendar-check"></i> Historial de Cambios</h6>
                                            <div class="timeline-sm">
                                                <div class="timeline-item">
                                                    <small class="text-muted">
                                                        <strong>Creado:</strong><br>
                                                        <?= date('m/d/Y h:i A', strtotime($service->created_at)) ?>
                                                    </small>
                                                </div>
                                                <?php if($service->updated_at && $service->updated_at != $service->created_at): ?>
                                                <div class="timeline-item">
                                                    <small class="text-muted">
                                                        <strong>Última modificación:</strong><br>
                                                        <?= date('m/d/Y h:i A', strtotime($service->updated_at)) ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                                <?php if($service->completion_date): ?>
                                                <div class="timeline-item">
                                                    <small class="text-success">
                                                        <strong>Finalizado:</strong><br>
                                                        <?= date('m/d/Y h:i A', strtotime($service->completion_date)) ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                                onclick="checkProviderAvailability()">
                                            <i class="fas fa-search"></i> Verificar Disponibilidad
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Notas -->
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-sticky-note"></i> Notas del Servicio
                                        </label>
                                        <textarea name="service_notes" class="input-ghost" rows="4" 
                                                  placeholder="Instrucciones especiales, observaciones, etc..."><?= $service->service_notes ?? '' ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-exclamation-triangle"></i> Precauciones Especiales
                                        </label>
                                        <textarea name="special_precautions" class="input-ghost" rows="3" 
                                                  placeholder="Alergias, condiciones médicas especiales, etc..."><?= $service->special_precautions ?? '' ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tools"></i> Equipos Requeridos
                                        </label>
                                        <textarea name="required_equipment" class="input-ghost" rows="3" 
                                                  placeholder="Lista de equipos médicos necesarios..."><?= $service->required_equipment ?? '' ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-file-medical-alt"></i> Resultados del Servicio
                                        </label>
                                        <textarea name="service_results" class="input-ghost" rows="4" 
                                                  placeholder="Resultados obtenidos, diagnósticos, recomendaciones..."><?= $service->service_results ?? '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="fas fa-paperclip"></i> Archivos Adjuntos</h6>
                                            
                                            <?php if(isset($service->attachments) && !empty($service->attachments)): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted">Archivos existentes:</small>
                                                    <?php foreach($service->attachments as $file): ?>
                                                        <div class="d-flex justify-content-between align-items-center mt-2 p-2 bg-white rounded">
                                                            <span class="small">
                                                                <i class="fas fa-file"></i> <?= $file->name ?>
                                                            </span>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="removeAttachment(<?= $file->id ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <label class="form-label small">Subir nuevos archivos:</label>
                                                <input type="file" name="service_attachments[]" class="input-ghost input-ghost-sm" 
                                                       multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                <small class="text-muted">PDF, imágenes, documentos</small>
                                            </div>

                                            <hr>

                                            <h6><i class="fas fa-comments"></i> Comunicación</h6>
                                            <div class="mb-2">
                                                <button type="button" class="btn btn-outline-success btn-sm w-100 mb-2" 
                                                        onclick="sendWhatsAppMessage()">
                                                    <i class="fab fa-whatsapp"></i> Enviar WhatsApp
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" 
                                                        onclick="sendEmailNotification()">
                                                    <i class="fas fa-envelope"></i> Enviar Email
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-sm w-100" 
                                                        onclick="callPatient()">
                                                    <i class="fas fa-phone"></i> Llamar Paciente
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card bg-warning bg-opacity-10 mt-3">
                                        <div class="card-body">
                                            <h6><i class="fas fa-lightbulb text-warning"></i> Recordatorios</h6>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="send_reminder" 
                                                       id="editSendReminder" <?= ($service->send_reminder ?? false) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="editSendReminder">
                                                    Enviar recordatorio 1 hora antes
                                                </label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="send_confirmation" 
                                                       id="editSendConfirmation" <?= ($service->send_confirmation ?? false) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="editSendConfirmation">
                                                    Solicitar confirmación del paciente
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <?php if($service->status != 'completado' && $service->status != 'cancelado'): ?>
                                <button type="button" class="btn-ghost btn-ghost-danger d-none" onclick="cancelService(<?= $service->id ?>)">
                                    <i class="fas fa-times-circle"></i> Cancelar Servicio
                                </button>
                            <?php endif; ?>
                            
                            <?php if($service->status == 'pendiente'): ?>
                                <button type="button" class="btn-ghost btn-ghost-info d-none" onclick="startService(<?= $service->id ?>)">
                                    <i class="fas fa-play"></i> Iniciar Servicio
                                </button>
                            <?php endif; ?>
                            
                            <?php if($service->status == 'en_progreso'): ?>
                                <button type="button" class="btn-ghost btn-ghost-success d-none" onclick="completeService(<?= $service->id ?>)">
                                    <i class="fas fa-check-circle"></i> Marcar Completado
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                            <button type="submit" class="btn-ghost btn-ghost-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccionar...',
            allowClear: true
        });
    }

    // Inicializar Flatpickr para fechas
    if (typeof flatpickr !== 'undefined') {
        flatpickr('input[type="date"]', {
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });
    }

    // Manejar envío del formulario
    $('#editServiceForm').on('submit', function(e) {
        e.preventDefault();
        saveServiceChanges();
    });

    // Validación en tiempo real
    $('input[required], select[required], textarea[required]').on('blur', function() {
        validateField(this);
    });

    // Verificar disponibilidad cuando cambia el proveedor o la fecha/hora
    $('select[name="assigned_provider_id"], input[name="scheduled_date"], input[name="scheduled_time"]').on('change', function() {
        checkProviderAvailability();
    });
});

// Función para guardar cambios del servicio
async function saveServiceChanges() {
    const form = document.getElementById('editServiceForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
        // Mostrar loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;

        const response = await fetch('<?= get_uri("home_services/update_service") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            // Mostrar mensaje de éxito
            
            showSuccess('Servicio actualizado correctamente');
            
            // Cerrar modal
            $('#modalEditService').modal('hide');
            location.reload();
            // Recargar tabla si existe
            if (typeof homeServicesTable !== 'undefined') {
                homeServicesTable.ajax.reload();
            }
        } else {
            throw new Error(result.message || 'Error al actualizar el servicio');
        }
    } catch (error) {
        console.error('Error:', error);
        showError(error.message || 'Error de conexión');
    } finally {
        // Restaurar botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Función para validar campos
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    
    // Remover clases previas
    field.classList.remove('is-valid', 'is-invalid');
    
    if (isRequired && !value) {
        field.classList.add('is-invalid');
        return false;
    } else if (value) {
        field.classList.add('is-valid');
    }
    
    return true;
}

// Función para verificar disponibilidad del proveedor
async function checkProviderAvailability() {
    const providerId = document.querySelector('select[name="assigned_provider_id"]').value;
    const date = document.querySelector('input[name="scheduled_date"]').value;
    const time = document.querySelector('input[name="scheduled_time"]').value;
    const serviceId = document.querySelector('input[name="service_id"]').value;
    
    const availabilityDiv = document.getElementById('providerAvailability');
    
    if (!providerId || !date || !time) {
        availabilityDiv.classList.add('d-none');
        return;
    }

    try {
        availabilityDiv.className = 'alert alert-info';
        availabilityDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando disponibilidad...';

        const response = await fetch(`<?= get_uri("home_services/check_provider_availability") ?>?provider_id=${providerId}&date=${date}&time=${time}&exclude_service=${serviceId}`);
        const result = await response.json();

        if (result.available) {
            availabilityDiv.className = 'alert alert-success';
            availabilityDiv.innerHTML = '<i class="fas fa-check-circle"></i> Proveedor disponible en este horario';
        } else {
            availabilityDiv.className = 'alert alert-warning';
            availabilityDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + (result.message || 'Proveedor no disponible en este horario');
        }
    } catch (error) {
        availabilityDiv.className = 'alert alert-danger';
        availabilityDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error al verificar disponibilidad';
    }
}

// Funciones para cambiar estado del servicio
async function startService(serviceId) {
    if (confirm('¿Confirmar que se ha iniciado el servicio?')) {
        await updateServiceStatus(serviceId, 'en_progreso');
    }
}

async function completeService(serviceId) {
    if (confirm('¿Confirmar que el servicio ha sido completado?')) {
        await updateServiceStatus(serviceId, 'completado');
    }
}

async function cancelService(serviceId) {
    const reason = prompt('Motivo de cancelación (opcional):');
    if (reason !== null) {
        await updateServiceStatus(serviceId, 'cancelado', reason);
    }
}

async function updateServiceStatus(serviceId, newStatus, reason = '') {
    try {
        const formData = new FormData();
        formData.append('service_id', serviceId);
        formData.append('status', newStatus);
        formData.append('reason', reason);

        const response = await fetch('<?= get_uri("home_services/update_service_status") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccess('Estado actualizado correctamente');
            $('#modalEditService').modal('hide');
            if (typeof homeServicesTable !== 'undefined') {
                homeServicesTable.ajax.reload();
            }
        } else {
            throw new Error(result.message || 'Error al actualizar estado');
        }
    } catch (error) {
        showError(error.message, 'error');
    }
}

// Funciones de comunicación
function sendWhatsAppMessage() {
    const phone = '<?= $appointment->patient_phone ?? "" ?>';
    if (!phone) {
        showError('No hay número de teléfono disponible');
        return;
    }
    
    const message = `Hola, le escribimos desde la clínica para confirmar su servicio domiciliario programado.`;
    const whatsappUrl = `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

function sendEmailNotification() {
    const email = '<?= $appointment->patient_email ?? "" ?>';
    if (!email) {
        showError('No hay email disponible');
        return;
    }
    
    const subject = 'Confirmación de Servicio Domiciliario';
    const body = 'Estimado paciente, le escribimos para confirmar su servicio domiciliario programado.';
    const mailtoUrl = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoUrl;
}

function callPatient() {
    const phone = '<?= $appointment->patient_phone ?? "" ?>';
    if (!phone) {
        showError('No hay número de teléfono disponible');
        return;
    }
    
    window.location.href = `tel:${phone}`;
}

// Función para ver historial del paciente
function viewPatientHistory(patientId) {
    //window.open(`<?= site_url('patients/view/') ?>${patientId}`, '_blank');
}

// Función para ver cita relacionada
function viewAppointment(appointmentId) {
    //window.open(`<?= site_url('appointments/view/') ?>${appointmentId}`, '_blank');
}

// Función para remover archivos adjuntos
async function removeAttachment(attachmentId) {
    if (confirm('¿Eliminar este archivo?')) {
        try {
            const response = await fetch('<?= get_uri("home_services/remove_attachment") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ attachment_id: attachmentId })
            });

            const result = await response.json();
            
            if (result.success) {
                location.reload(); // Recargar para mostrar cambios
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            showError('Error al eliminar archivo');
        }
    }
}

// Función para abrir modal de cambio de estado
function openChangeStatusModal(serviceId) {
    // Esta función se implementaría en el controlador principal
    // para abrir un modal específico de cambio de estado
    console.log('Opening status change modal for service:', serviceId);
}

$(document).on('submit', '#editServiceForm', function(e) {
    e.preventDefault();
    saveServiceChanges();  // tu función AJAX
});
</script>