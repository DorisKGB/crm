<?php
load_css([
    "assets/css/button.css",
    "assets/js/flatpickr/flatpickr.min.css"
]);

load_js([
    "assets/js/flatpickr/flatpickr.min.js"
]);
?>

<div id="page-content" class="page-wrapper clearfix">
    <div class="card-ghost">
        <div class="card-ghost-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4><b>Agenda de Servicios Domiciliarios</b></h4>
                    <p class="text-muted mb-0">Programación y seguimiento de servicios por fecha</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('home_services') ?>" class="btn-ghost btn-ghost-primary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="<?= site_url('home_services/daily_map') ?>" class="btn-ghost btn-ghost-warning d-none">
                        <i class="fas fa-map-marked-alt"></i> Mapa
                    </a>
                    <a href="<?= site_url('home_services/unscheduled_services') ?>" class="btn-ghost btn-ghost-secondary">
                        <i class="fas fa-calendar-minus"></i> Sin Programar
                    </a>
                    <button class="btn-ghost btn-ghost-success" onclick="openCreateServiceModal()">
                        <i class="fas fa-plus"></i> Nuevo Servicio
                    </button>
                </div>
            </div>
        </div>

        <div class="card-ghost-body">
            <!-- Selector de Fecha -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="input-group-ghost">
                        <span class="input-group-ghost-text">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <input type="text" id="datePicker" class="input-ghost-grouped flatpickr" placeholder="Seleccionar fecha">
                        <button class="btn-ghost btn-ghost-primary btn-ghost-grouped" onclick="loadTodaySchedule()">
                            Hoy
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="providerFilter" class="input-ghost d-none">
                        <option value="">Todos los Proveedores</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="statusFilterSchedule" class="input-ghost d-none">
                        <option value="">Todos los Estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_progreso">En Progreso</option>
                        <option value="completado">Completado</option>
                        <option value="no_encontrado">No Encontrado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Estadísticas del Día -->
            <!-- Estadísticas del Día - Versión Delicada -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card-ghost card-ghost-flat shadow-sm">
            <div class="card-ghost-body p-4">
                <!-- Header elegante -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle p-2 me-3" style="background-color: #f0f7ff;">
                            <i class="fas fa-chart-pie" style="color: #7bb3f0;"></i>
                        </div>
                        <div>
                            <h6 class="card-ghost-title mb-1 fw-semibold">Resumen del Día</h6>
                            <small class="text-muted">Estadísticas de servicios programados</small>
                        </div>
                    </div>
                    <span class="px-3 py-2 rounded-pill" style="background-color: #f0f7ff; color: #6ba3d6; border: 1px solid #e8f2ff;" id="selectedDateDisplay">
                        Hoy
                    </span>
                </div>

                <!-- Grid de estadísticas mejorado -->
                <div class="row g-3">
                    <!-- Total -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #e8f2ff !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #a8c8ec;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #f0f7ff;">
                                        <i class="fas fa-tasks fs-6" style="color: #7bb3f0;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #6ba3d6;" id="dayStats-total">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total</small>
                            </div>
                        </div>
                    </div>

                    <!-- Pendientes -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #fff4e6 !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #f5c842;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #fffbf0;">
                                        <i class="fas fa-clock fs-6" style="color: #e6b800;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #d4a843;" id="dayStats-pendiente">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pendientes</small>
                            </div>
                        </div>
                    </div>

                    <!-- En Progreso -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #e6f7ff !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #87ceeb;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #f0faff;">
                                        <i class="fas fa-spinner fs-6" style="color: #5dade2;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #5499c7;" id="dayStats-en_progreso">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">En Progreso</small>
                            </div>
                        </div>
                    </div>

                    <!-- Completados -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #e8f5e8 !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #7dd87d;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #f0fff0;">
                                        <i class="fas fa-check-circle fs-6" style="color: #52c41a;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #5cb85c;" id="dayStats-completado">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Completados</small>
                            </div>
                        </div>
                    </div>

                    <!-- No Encontrado -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #ffebee !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #f48fb1;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #fff5f5;">
                                        <i class="fas fa-exclamation-triangle fs-6" style="color: #ec7063;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #d7727d;" id="dayStats-no_encontrado">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">No Encontrado</small>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelados -->
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="position-relative">
                            <div class="bg-white rounded-3 border p-3 text-center h-100 shadow-sm" style="border-color: #f5f5f5 !important;">
                                <div class="position-absolute top-0 start-0 w-100 rounded-top" style="height: 3px; background-color: #b0bec5;"></div>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="rounded-circle p-2 me-2" style="background-color: #fafafa;">
                                        <i class="fas fa-times-circle fs-6" style="color: #90a4ae;"></i>
                                    </div>
                                </div>
                                <h4 class="mb-1 fw-bold" style="color: #8d9db6;" id="dayStats-cancelado">0</h4>
                                <small class="text-muted text-uppercase fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Cancelados</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Línea divisoria sutil -->
                <hr class="my-4 border-0" style="height: 1px; background: linear-gradient(90deg, transparent, #dee2e6, transparent);">
                
                <!-- Información adicional -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Actualización automática cada 2 minutos</small>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex align-items-center justify-content-end text-muted">
                            <i class="fas fa-clock me-2"></i>
                            <small id="lastUpdate">Última actualización: Ahora</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


            <!-- Timeline de Servicios -->
            <div id="scheduleContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando agenda...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div id="modalContainer"></div>

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
        margin-bottom: 30px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #dee2e6;
    }

    .timeline-item.status-pendiente::before {
        background: #ffc107;
        box-shadow: 0 0 0 3px #ffc107;
    }

    .timeline-item.status-en_progreso::before {
        background: #17a2b8;
        box-shadow: 0 0 0 3px #17a2b8;
    }

    .timeline-item.status-completado::before {
        background: #28a745;
        box-shadow: 0 0 0 3px #28a745;
    }

    .timeline-item.status-no_encontrado::before {
        background: #dc3545;
        box-shadow: 0 0 0 3px #dc3545;
    }

    .timeline-item.status-cancelado::before {
        background: #6c757d;
        box-shadow: 0 0 0 3px #6c757d;
    }

    .timeline-time {
        font-weight: bold;
        color: #495057;
        font-size: 1.1em;
    }

    .service-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .service-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .patient-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        margin: 10px 0;
    }

    .provider-badge {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .time-badge {
        background: #e9ecef;
        color: #495057;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        font-family: monospace;
    }
</style>
<style>
/* Estilos adicionales para hacer la sección más delicada */
.card-ghost.shadow-sm {
    transition: all 0.3s ease;
}

.card-ghost.shadow-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Animación suave para los números */
.stat-number-animate {
    transition: all 0.3s ease;
}

/* Hover effects para las tarjetas de estadísticas */
.bg-white.rounded-3 {
    transition: all 0.2s ease;
    cursor: default;
}

.bg-white.rounded-3:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
}

/* Iconos con animación sutil */
.bg-opacity-10 i {
    transition: transform 0.2s ease;
}

.bg-white.rounded-3:hover .bg-opacity-10 i {
    transform: scale(1.1);
}

/* Badge del header más elegante */
.badge.bg-primary.bg-opacity-15 {
    backdrop-filter: blur(10px);
    border: 1px solid rgba(13, 110, 253, 0.2);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .card-ghost-body {
        padding: 1.5rem !important;
    }
    
    .bg-white.rounded-3 {
        padding: 1rem !important;
    }
    
    .bg-white.rounded-3 h4 {
        font-size: 1.5rem !important;
    }
}
</style>
<script>
    let scheduleData = [];
    let currentDate = new Date().toISOString().split('T')[0];

    document.addEventListener('DOMContentLoaded', function() {
        initializeDatePicker();
        loadTodaySchedule();
        loadProviders();
    });

    function initializeDatePicker() {
        flatpickr("#datePicker", {
            dateFormat: "Y-m-d",
            defaultDate: currentDate,
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    currentDate = dateStr;
                    loadSchedule(dateStr);
                    updateSelectedDateDisplay(dateStr);
                }
            }
        });
    }

    function loadTodaySchedule() {
        const today = new Date().toISOString().split('T')[0];
        currentDate = today;
        document.getElementById('datePicker').value = today;
        loadSchedule(today);
        updateSelectedDateDisplay(today);
    }

    function updateSelectedDateDisplay(dateStr) {
        // parseo manual para evitar desfase por UTC
        const [year, month, day] = dateStr.split('-').map(Number);
        const date = new Date(year, month - 1, day);

        const formattedDate = date.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('selectedDateDisplay').textContent = formattedDate;
    }

    async function loadSchedule(date = null) {
        try {
            const dateStr = date || document.getElementById('datePicker').value;
            const url = new URL('<?= site_url("home_services/get_schedule_services") ?>');
            url.searchParams.append('date', dateStr);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Error al cargar la agenda');
            }

            const data = await response.json();

            if (data.success) {
                scheduleData = data.data;
                renderSchedule();
                updateDayStatistics();
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('scheduleContainer').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar la agenda. 
                <button onclick="loadSchedule('${currentDate}')" class="btn btn-sm btn-outline-danger ms-2">Reintentar</button>
            </div>
        `;
        }
    }

    async function loadProviders() {
        try {
            // Extraer proveedores únicos de los datos
            const uniqueProviders = new Set();
            scheduleData.forEach(service => {
                if (service.assigned_provider_name) {
                    uniqueProviders.add(service.assigned_provider_name);
                }
            });

            const providerFilter = document.getElementById('providerFilter');
            uniqueProviders.forEach(provider => {
                const option = document.createElement('option');
                option.value = provider;
                option.textContent = provider;
                providerFilter.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading providers:', error);
        }
    }

    function renderSchedule() {
        const container = document.getElementById('scheduleContainer');

        if (scheduleData.length === 0) {
            container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4>No hay servicios programados</h4>
                <p>No se encontraron servicios para la fecha seleccionada</p>
                <button class="btn-ghost btn-ghost-primary" onclick="openCreateServiceModal()">
                    <i class="fas fa-plus"></i> Programar Servicio
                </button>
            </div>
        `;
            return;
        }

        // Agrupar servicios por hora
        const servicesByTime = {};
        scheduleData.forEach(service => {
            const time = service.scheduled_time || 'Sin hora';
            if (!servicesByTime[time]) {
                servicesByTime[time] = [];
            }
            servicesByTime[time].push(service);
        });

        // Ordenar horas
        const sortedTimes = Object.keys(servicesByTime).sort((a, b) => {
            if (a === 'Sin hora') return 1;
            if (b === 'Sin hora') return -1;
            return a.localeCompare(b);
        });

        let html = '<div class="timeline">';

        sortedTimes.forEach(time => {
            const services = servicesByTime[time];

            services.forEach(service => {
                const statusColor = getStatusColor(service.status);
                const statusText = getStatusText(service.status);

                html += `
                <div class="timeline-item status-${service.status}">
                    <div class="service-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="time-badge">${formatTime(time)}</div>
                            <span class="badge-ghost badge-ghost-${statusColor}">${statusText}</span>
                        </div>
                        
                        <div class="patient-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        <i class="fas fa-user"></i> ${service.patient_name || 'Sin paciente'}
                                    </h6>
                                    <p class="mb-1 text-muted">
                                        <i class="fas fa-phone"></i> ${service.patient_phone || 'Sin teléfono'}
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        ${service.patient_address || 'Dirección no especificada'}<br>
                                        <small class="text-muted">
                                            ${service.patient_city || ''} ${service.patient_state || ''} ${service.patient_zipcode || ''}
                                        </small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    ${service.assigned_provider_name ? `
                                        <span class="provider-badge">
                                            <i class="fas fa-user-md"></i> ${service.assigned_provider_name}
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        
                        <div class="service-details">
                            <h6 class="text-primary">${service.service_type || 'Servicio General'}</h6>
                            ${service.service_notes ? `<p class="text-muted small">${service.service_notes}</p>` : ''}
                        </div>
                        
                        <div class="service-actions mt-3">
                            <div class="btn-group" role="group">
                                <button class="btn-ghost btn-ghost-info " onclick="viewServiceDetails(${service.id})">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn-ghost btn-ghost-primary " onclick="editService(${service.id})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn-ghost btn-ghost-warning " onclick="changeServiceStatus(${service.id})">
                                    <i class="fas fa-flag"></i> Estado
                                </button>
                                ${service.patient_address ? `
                                    <button class="btn-ghost btn-ghost-success " onclick="openMapForService(${service.id}, '${service.patient_address}', '${service.patient_city}', '${service.patient_state}', '${service.patient_zipcode}')">
                                        <i class="fas fa-map"></i> Mapa
                                    </button>
                                ` : ''}
                                <button class="btn-ghost btn-ghost-danger btn-ghost-sm d-none" onclick="callPatient('${service.patient_phone}')">
                                    <i class="fas fa-phone"></i> Llamar
                                </button>
                                <button class="btn-ghost btn-ghost-danger " onclick="confirmDeleteService(${service.id}, '${service.patient_name}', '${service.service_type}')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });
        });

        html += '</div>';
        container.innerHTML = html;

        // Reload providers after rendering
        setTimeout(loadProviders, 100);
    }

    function confirmDeleteService(serviceId, patientName, serviceType) {
        const modalHtml = `
            <div class="modal fade" id="modalDeleteService" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-trash-alt fa-3x text-danger"></i>
                                </div>
                                <h6>¿Estás seguro de que deseas eliminar este servicio?</h6>
                                <div class="alert alert-light mt-3">
                                    <strong>Paciente:</strong> ${patientName}<br>
                                    <strong>Servicio:</strong> ${serviceType}<br>
                                    <strong>ID:</strong> #${serviceId}
                                </div>
                                <p class="text-muted small">
                                    <i class="fas fa-warning"></i> 
                                    Esta acción no se puede deshacer. El servicio será eliminado permanentemente.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-ghost btn-ghost-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn-ghost btn-ghost-danger" onclick="deleteService(${serviceId})">
                                <i class="fas fa-trash"></i> Eliminar Servicio
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar modal al container
        document.getElementById('modalContainer').innerHTML = modalHtml;
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalDeleteService'));
        modal.show();
    }

    async function deleteService(serviceId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalDeleteService'));
        const deleteBtn = document.querySelector('#modalDeleteService .btn-ghost-danger');
        const originalText = deleteBtn.innerHTML;
        
        try {
            // Mostrar loading
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
            deleteBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('service_id', serviceId);
            
            // Agregar token CSRF
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            
            const response = await fetch('<?= get_uri("home_services/delete_service") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Cerrar modal
                modal.hide();
                
                // Mostrar mensaje de éxito
                showSuccess('Servicio eliminado correctamente');
                
                // Recargar agenda
                loadSchedule(currentDate);
                
            } else {
                throw new Error(result.message || 'Error al eliminar el servicio');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'Error de conexión');
            
            // Restaurar botón
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    }


    function updateDayStatistics() {
        const stats = {
            total: 0,
            pendiente: 0,
            en_progreso: 0,
            completado: 0,
            no_encontrado: 0,
            cancelado: 0
        };

        scheduleData.forEach(service => {
            stats.total++;
            if (stats[service.status] !== undefined) {
                stats[service.status]++;
            }
        });

        Object.keys(stats).forEach(status => {
            const element = document.getElementById(`dayStats-${status}`);
            if (element) {
                element.textContent = stats[status];
            }
        });
    }

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

    function formatTime(timeString) {
        if (!timeString || timeString === 'Sin hora') return 'Sin hora programada';

        try {
            const [hours, minutes] = timeString.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes));
            return date.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return timeString;
        }
    }

    // Funciones de acción
    function openCreateServiceModal() {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_create_service") ?>', function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalCreateService'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal');
        });
    }

    function editService(id) {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_edit_service") ?>', {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalEditService'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal');
        });
    }

    function changeServiceStatus(id) {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_change_status") ?>', {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalChangeStatus'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal');
        });
    }

    function viewServiceDetails(id) {
        $('#modalContainer').empty();
        $.get('<?= get_uri("home_services/modal_service_details") ?>', {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalServiceDetails'));
            modal.show();
        }).fail(function() {
            alert('Error al cargar el modal');
        });
    }

    function openMapForService(id, address, city, state, zipcode) {
        const fullAddress = `${address}, ${city}, ${state} ${zipcode}`;
        const encodedAddress = encodeURIComponent(fullAddress);
        window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
    }

    function callPatient(phone) {
        if (phone && phone !== 'Sin teléfono') {
            window.open(`tel:${phone}`);
        } else {
            alert('No hay número de teléfono disponible');
        }
    }

    // Filtros
    document.getElementById('providerFilter').addEventListener('change', applyScheduleFilters);
    document.getElementById('statusFilterSchedule').addEventListener('change', applyScheduleFilters);

    function applyScheduleFilters() {
        const providerFilter = document.getElementById('providerFilter').value;
        const statusFilter = document.getElementById('statusFilterSchedule').value;

        // Recargar datos con filtros
        loadSchedule(currentDate);
    }

    // Actualizar cada 2 minutos
    setInterval(() => {
        loadSchedule(currentDate);
    }, 120000);
</script>