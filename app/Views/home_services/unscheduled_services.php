<?php
// home_services/unscheduled_services.php
load_css([
    "assets/css/button.css",
    "assets/js/flatpickr/flatpickr.min.css"
]);

load_js([
    "assets/js/flatpickr/flatpickr.min.js"
]);
?>

<div id="page-content" class="page-wrapper clearfix">
    <style>
  /* Solo mantener estos estilos mínimos */

.service-card {
    border-left: 4px solid #ffc107;
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.service-card.scheduled {
    border-left-color: #28a745;
}

.service-card.overdue {
    border-left-color: #dc3545;
}

.service-priority {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 500;
    z-index: 10;
}

.priority-normal { 
    background: #28a745; 
    color: white; 
}

.priority-urgent { 
    background: #ffc107; 
    color: #212529; 
}

.priority-emergency { 
    background: #dc3545; 
    color: white; 
}

.quick-schedule-form {
    display: none;
}

.quick-schedule-form.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { 
        opacity: 0; 
        transform: translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}
    </style>
    <div class="card-ghost">
        <div class="card-ghost-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><b>Servicios Sin Programar</b></h4>
                    <p class="text-muted mb-0">Gestión de servicios pendientes de asignación de fecha y hora</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('home_services') ?>" class="btn-ghost btn-ghost-primary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button class="btn-ghost btn-ghost-success d-none" onclick="scheduleAllServices()">
                        <i class="fas fa-calendar-plus"></i> Programar Todos
                    </button>
                </div>
            </div>
        </div>

        <div class="card-ghost-body">
            <!-- Estadísticas -->
             <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="position-relative">
                <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #fff4e6 !important;">
                    <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #f5c842;"></div>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle p-2 me-2" style="background-color: #fffbf0;">
                            <i class="fas fa-calendar-minus fs-6" style="color: #e6b800;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold" style="color: #d4a843;" id="totalUnscheduled">0</h3>
                    <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sin Programar</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="position-relative">
                <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #e6f7ff !important;">
                    <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #87ceeb;"></div>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle p-2 me-2" style="background-color: #f0faff;">
                            <i class="fas fa-calendar-day fs-6" style="color: #5dade2;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold" style="color: #5499c7;" id="totalScheduledToday">0</h3>
                    <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Programados Hoy</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="position-relative">
                <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #ffebee !important;">
                    <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #f48fb1;"></div>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle p-2 me-2" style="background-color: #fff5f5;">
                            <i class="fas fa-clock fs-6" style="color: #ec7063;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold" style="color: #d7727d;" id="totalOverdue">0</h3>
                    <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Atrasados</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="position-relative">
                <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #e8f5e8 !important;">
                    <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #7dd87d;"></div>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle p-2 me-2" style="background-color: #f0fff0;">
                            <i class="fas fa-calendar-week fs-6" style="color: #52c41a;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold" style="color: #5cb85c;" id="totalScheduledThisWeek">0</h3>
                    <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Esta Semana</small>
                </div>
            </div>
        </div>
    </div>

            <!-- Herramientas de Programación -->
            <div class="row mb-4 d-none">
                <div class="col-md-12">
                    <div class="card-ghost">
                        <div class="card-ghost-header">
                            <h6><i class="fas fa-tools"></i> Herramientas de Programación Rápida</h6>
                        </div>
                        <div class="card-ghost-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Base</label>
                                    <input type="date" id="bulkScheduleDate" class="input-ghost" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hora Inicio</label>
                                    <input type="time" id="bulkScheduleTime" class="input-ghost" value="08:00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Intervalo (min)</label>
                                    <select id="bulkScheduleInterval" class="input-ghost">
                                        <option value="30">30 minutos</option>
                                        <option value="45" selected>45 minutos</option>
                                        <option value="60">60 minutos</option>
                                        <option value="90">90 minutos</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn-ghost btn-ghost-primary w-100" onclick="applyBulkSchedule()">
                                        <i class="fas fa-magic"></i> Aplicar Programación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Servicios -->
            <div id="unscheduledContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando servicios sin programar...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div id="modalContainer"></div>

<style>
.service-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #ffc107;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.service-card.scheduled {
    border-left-color: #28a745;
}

.service-card.overdue {
    border-left-color: #dc3545;
}

.quick-schedule-form {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
    display: none;
}

.quick-schedule-form.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.service-priority {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.priority-normal { background: #28a745; color: white; }
.priority-urgent { background: #ffc107; color: #212529; }
.priority-emergency { background: #dc3545; color: white; }

.bulk-selection {
    position: sticky;
    top: 0;
    background: white;
    z-index: 100;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 0;
    margin-bottom: 20px;
}
</style>

<script>
let unscheduledServices = [];
let selectedServices = [];

document.addEventListener('DOMContentLoaded', function() {
    loadUnscheduledServices();
    updateStatistics();
    
    // Inicializar flatpickr
    flatpickr("#bulkScheduleDate", {
        dateFormat: "Y-m-d",
        minDate: "today"
    });
});

function getNextWorkingDay() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        // Si es fin de semana, mover al lunes
        if (tomorrow.getDay() === 0) { // Domingo
            tomorrow.setDate(tomorrow.getDate() + 1);
        } else if (tomorrow.getDay() === 6) { // Sábado
            tomorrow.setDate(tomorrow.getDate() + 2);
        }

        return tomorrow;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const nextDay = getNextWorkingDay();

        flatpickr('.usa-datepicker', {
            dateFormat: 'm/d/Y',
            defaultDate: nextDay
        });
    });

async function loadUnscheduledServices() {
    try {
        const response = await fetch('<?= site_url("home_services/get_unscheduled_services") ?>', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar servicios');
        }

        const data = await response.json();
        console.log(data);
        if (data.success) {
            
            unscheduledServices = data.data;
            renderUnscheduledServices();
            updateStatistics();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('unscheduledContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar servicios. 
                <button onclick="loadUnscheduledServices()" class="btn btn-sm btn-outline-danger ms-2">Reintentar</button>
            </div>
        `;
    }
}

function renderUnscheduledServices() {
    const container = document.getElementById('unscheduledContainer');

    if (unscheduledServices.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-calendar-check fa-4x text-success mb-3"></i>
                <h4>¡Excelente!</h4>
                <p>Todos los servicios están programados</p>
                <a href="<?= site_url('home_services') ?>" class="btn-ghost btn-ghost-primary">
                    <i class="fas fa-arrow-left"></i> Volver a Servicios
                </a>
            </div>
        `;
        return;
    }

    let html = `
        <div class="bulk-selection d-none">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleSelectAll()">
                    <label for="selectAll" class="form-check-label ms-2">
                        Seleccionar todos (<span id="selectedCount">0</span>)
                    </label>
                </div>
                <div id="bulkActions" style="display: none;">
                    <button class="btn-ghost btn-ghost-primary btn-sm" onclick="showBulkScheduleForm()">
                        <i class="fas fa-calendar-plus"></i> Programar Seleccionados
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
    `;

   unscheduledServices.forEach(service => {
    const isOverdue = new Date(service.appointment_date) < new Date();
    const cardClass = isOverdue ? 'service-card overdue' : 'service-card';
    const priorityClass = `priority-${service.priority || 'normal'}`;

    html += `
        <div class="col-md-6 mb-3">
            <div class="card ${cardClass} h-100 border-0 shadow-sm position-relative">
                <!-- Priority Badge -->
                <div class="service-priority ${priorityClass}">
                    ${getPriorityText(service.priority || 'normal')}
                </div>
                
                <!-- Header -->
                <div class="card-header bg-light border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="form-check mb-0">
                            <input class="form-check-input service-checkbox" type="checkbox" 
                                   value="${service.id}" onchange="updateSelection()">
                            <label class="form-check-label fw-semibold text-dark">
                                Servicio #${service.id}
                            </label>
                        </div>
                        ${isOverdue ? '<span class="badge bg-danger d-none">Atrasado</span>' : ''}
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="mb-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Paciente</small>
                                        <span class="fw-medium">${service.patient_name || 'Sin paciente'}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    <span class="text-muted">${service.patient_phone || 'Sin teléfono'}</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-info me-2"></i>
                                    <span class="text-muted small">${service.patient_address || 'Sin dirección'}</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-warning me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Teleconsulta</small>
                                        <span class="fw-medium">${formatDate(service.appointment_date)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Type -->
                    <div class="mb-3">
                        <span class="badge-ghost bg-ghost-primary bg-opacity-10 text-primary px-3 py-2">
                            ${service.service_type || 'Servicio General'}
                        </span>
                    </div>

                    <!-- Quick Schedule Form -->
                    <div class="quick-schedule-form bg-light rounded p-3 border" id="quickForm_${service.id}">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <h6 class="mb-0 text-primary">Programar servicio</h6>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-medium">Fecha</label>
                                <input type="text" id="date_${service.id}" 
                                       class="form-control form-control-sm usa-datepicker">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-medium">Hora</label>
                                <input type="time" id="time_${service.id}" 
                                       class="form-control form-control-sm" value="08:00">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn-ghost btn-ghost-success btn-sm flex-fill" 
                                    onclick="scheduleService(${service.id})">
                                <i class="fas fa-check me-1"></i> Programar
                            </button>
                            <button class="btn-ghost btn-ghost-secondary btn-sm" 
                                    onclick="hideQuickForm(${service.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-grid mb-2">
                        <button class="btn-ghost btn-ghost-primary btn-sm" 
                                onclick="showQuickForm(${service.id})">
                            <i class="fas fa-calendar-plus me-2"></i>Programar Ahora
                        </button>
                    </div>
                    <div class="btn-group w-100" role="group">
                        <button class="btn-ghost btn-ghost-info btn-sm" 
                                onclick="viewServiceDetails(${service.id})">
                            <i class="fas fa-eye me-1"></i> Ver
                        </button>
                        <button class="btn-ghost btn-ghost-warning btn-sm" 
                                onclick="editService(${service.id})">
                            <i class="fas fa-edit me-1"></i> Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
});

    html += '</div>';
    container.innerHTML = html;

      // IMPORTANTE: Inicializar datepickers después de insertar el HTML
    setTimeout(() => {
        initializeDatePickers();
    }, 100);
}

function getPriorityText(priority) {
    const priorities = {
        'normal': 'Normal',
        'urgent': 'Urgente',
        'emergency': 'Emergencia'
    };
    return priorities[priority] || 'Normal';
}

function formatDate(dateString) {
    if (!dateString) return 'Sin fecha';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US');
}

function showQuickForm(serviceId) {
    // Ocultar todos los demás formularios
    document.querySelectorAll('.quick-schedule-form').forEach(form => {
        form.classList.remove('show');
    });
    
    // Mostrar el formulario específico
    const form = document.getElementById(`quickForm_${serviceId}`);
    form.classList.add('show');

    const dateInput = document.getElementById(`date_${serviceId}`);
    if (dateInput && !dateInput._flatpickr) { // Verificar que no esté ya inicializado
        const nextDay = getNextWorkingDay();
        
        flatpickr(dateInput, {
            dateFormat: "m/d/Y",
            defaultDate: nextDay,
            minDate: "today",
            locale: {
                firstDayOfWeek: 1 // Lunes como primer día
            }
        });
    }
}

function initializeDatePickers() {
    // Buscar todos los elementos con clase usa-datepicker que no estén inicializados
    document.querySelectorAll('.usa-datepicker:not([data-flatpickr-initialized])').forEach(element => {
        const nextDay = getNextWorkingDay();
        
        flatpickr(element, {
            dateFormat: "m/d/Y",
            defaultDate: nextDay,
            minDate: "today",
            locale: {
                firstDayOfWeek: 1
            }
        });
        
        // Marcar como inicializado
        element.setAttribute('data-flatpickr-initialized', 'true');
    });
}

function hideQuickForm(serviceId) {
    const form = document.getElementById(`quickForm_${serviceId}`);
    form.classList.remove('show');
}

async function scheduleService(serviceId) {
    const dateInput = document.getElementById(`date_${serviceId}`);
    const timeInput = document.getElementById(`time_${serviceId}`);
    
    const dateValue = dateInput.value;
    const timeValue = timeInput.value;

    if (!dateValue || !timeValue) {
        alert('Por favor selecciona fecha y hora');
        return;
    }

    // Convertir la fecha al formato correcto (Y-m-d)
    let formattedDate;
    try {
        // Si flatpickr tiene una instancia, usar su selectedDates
        if (dateInput._flatpickr && dateInput._flatpickr.selectedDates.length > 0) {
            const selectedDate = dateInput._flatpickr.selectedDates[0];
            formattedDate = selectedDate.toISOString().split('T')[0]; // Formato Y-m-d
        } else {
            // Fallback: convertir manualmente desde m/d/Y
            const dateParts = dateValue.split('/');
            if (dateParts.length === 3) {
                const month = dateParts[0].padStart(2, '0');
                const day = dateParts[1].padStart(2, '0');
                const year = dateParts[2];
                formattedDate = `${year}-${month}-${day}`;
            } else {
                // Si ya está en formato Y-m-d, usarlo directamente
                formattedDate = dateValue;
            }
        }
    } catch (error) {
        console.error('Error al formatear fecha:', error);
        alert('Error en el formato de fecha. Por favor selecciona una fecha válida.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('service_id', serviceId);
        formData.append('scheduled_date', formattedDate); // Usar fecha formateada
        formData.append('scheduled_time', timeValue);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch('<?= get_uri("home_services/assign_schedule") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            showSuccessMessage(result.message);
            hideQuickForm(serviceId);
            loadUnscheduledServices();
        } else {
            alert(`Error al programar servicio: ${result.message}`);
        }
    } catch (error) {
        console.error('Error de red:', error);
        alert('Error de red o servidor al programar servicio');
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.service-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.service-checkbox:checked');
    selectedServices = Array.from(checkboxes).map(cb => cb.value);
    
    document.getElementById('selectedCount').textContent = selectedServices.length;
    document.getElementById('bulkActions').style.display = 
        selectedServices.length > 0 ? 'block' : 'none';
    
    // Actualizar estado del checkbox "Seleccionar todos"
    const totalCheckboxes = document.querySelectorAll('.service-checkbox').length;
    const selectAll = document.getElementById('selectAll');
    selectAll.indeterminate = selectedServices.length > 0 && selectedServices.length < totalCheckboxes;
    selectAll.checked = selectedServices.length === totalCheckboxes;
}

async function applyBulkSchedule() {
    if (selectedServices.length === 0) {
        alert('Selecciona al menos un servicio');
        return;
    }

    const baseDate = document.getElementById('bulkScheduleDate').value;
    const baseTime = document.getElementById('bulkScheduleTime').value;
    const interval = parseInt(document.getElementById('bulkScheduleInterval').value);

    if (!baseDate || !baseTime) {
        alert('Selecciona fecha y hora base');
        return;
    }

    let currentTime = baseTime;
    let successCount = 0;

    for (let i = 0; i < selectedServices.length; i++) {
        try {
            const formData = new FormData();
            formData.append('service_id', selectedServices[i]);
            formData.append('scheduled_date', baseDate);
            formData.append('scheduled_time', currentTime);

            const response = await fetch('<?= site_url("home_services/assign_schedule") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                successCount++;
            }

            // Calcular siguiente hora
            const timeArray = currentTime.split(':');
            const timeDate = new Date();
            timeDate.setHours(parseInt(timeArray[0]), parseInt(timeArray[1]), 0);
            timeDate.setMinutes(timeDate.getMinutes() + interval);
            currentTime = timeDate.toTimeString().slice(0, 5);

        } catch (error) {
            console.error('Error scheduling service:', selectedServices[i], error);
        }
    }

    showSuccessMessage(`${successCount} servicios programados exitosamente`);
    loadUnscheduledServices();
}

function updateStatistics() {
    // Implementar cálculos de estadísticas
    const totalUnscheduled = unscheduledServices.length;
    document.getElementById('totalUnscheduled').textContent = totalUnscheduled;
    
    // Aquí puedes agregar más cálculos de estadísticas
}

function viewServiceDetails(id) {
    $('#modalContainer').empty();
    $.get('<?= get_uri("home_services/modal_service_details") ?>', { id }, function(html) {
        $('#modalContainer').html(html);
        const modal = new bootstrap.Modal(document.getElementById('modalServiceDetails'));
        modal.show();
    }).fail(function() {
        alert('Error al cargar detalles del servicio');
    });
}

function editService(id) {
    $('#modalContainer').empty();
    $.get('<?= get_uri("home_services/modal_edit_service") ?>', { id }, function(html) {
        $('#modalContainer').html(html);
        const modal = new bootstrap.Modal(document.getElementById('modalEditService'));
        modal.show();
    }).fail(function() {
        alert('Error al cargar el modal de edición');
    });
}

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

    function getNextWorkingDay() {
        const today = new Date();
        const nextDay = new Date(today);
        nextDay.setDate(today.getDate() + 1);

        // Si es sábado o domingo, saltar al lunes
        if (nextDay.getDay() === 6) { // Sábado
            nextDay.setDate(nextDay.getDate() + 2);
        } else if (nextDay.getDay() === 0) { // Domingo
            nextDay.setDate(nextDay.getDate() + 1);
        }

        return nextDay;
    }


</script>