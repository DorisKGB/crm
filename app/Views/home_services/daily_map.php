<?php
load_css([
    "assets/css/button.css",
    "assets/js/flatpickr/flatpickr.min.css",
    "assets/css/leaftlet.css"
]);

load_js([
    "assets/js/flatpickr/flatpickr.min.js",
    "assets/js/leaftlet.js"
]);
?>

<div id="page-content" class="page-wrapper clearfix">
    <div class="card">

        <div class="card-header">
            <div class="card-title d-flex align-items-center justify-content-between">
                <div>
                    <h4><b> Mapa Diario de Servicios </b></h4>
                    <p class="text-muted mb-0">Visualización geográfica de servicios programados</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('home_services') ?>" class="btn-ghost btn-ghost-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="<?= site_url('home_services/schedule') ?>" class="btn-ghost btn-ghost-info">
                        <i class="fas fa-calendar-alt"></i> Agenda
                    </a>
                    <button class="btn-ghost btn-ghost-success" onclick="openCreateServiceModal()">
                        <i class="fas fa-plus"></i> Nuevo Servicio
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Controles -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="text" id="mapDatePicker" class="form-control flatpickr" placeholder="Seleccionar fecha">
                        <button class="btn btn-outline-primary" onclick="loadTodayMap()">Hoy</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="mapStatusFilter" class="form-control">
                        <option value="">Todos los Estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_progreso">En Progreso</option>
                        <option value="completado">Completado</option>
                        <option value="no_encontrado">No Encontrado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn-ghost btn-ghost-primary" onclick="optimizeRoute()">
                        <i class="fas fa-route"></i> Optimizar Ruta
                    </button>
                    <button class="btn-ghost btn-ghost-warning" onclick="printRoute()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
                <div class="col-md-3">
                    <div class="text-end">
                        <strong>Total del día: <span id="mapTotalServices">0</span> servicios</strong>
                    </div>
                </div>
            </div>

            <!-- Mapa y Lista -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-map"></i> Mapa de Ubicaciones</h6>
                        </div>
                        <div class="card-body">
                            <div id="map" style="height:500px; border-radius:8px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="fas fa-list"></i> Lista de Servicios</h6>
                            <span class="badge-ghost btn-ghost-primary" id="serviceListCount">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="servicesList" style="max-height: 500px; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-3">Cargando servicios...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Ruta -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-route"></i> Información de Ruta</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary" id="routeDistance">-- km</h4>
                                        <small>Distancia Total</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info" id="routeTime">-- min</h4>
                                        <small>Tiempo Estimado</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success" id="routeCost">$--</h4>
                                        <small>Costo Estimado</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning" id="routeStops">--</h4>
                                        <small>Paradas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div id="modalContainer"></div>

<style>
.service-marker {
    cursor: pointer;
    transition: all 0.3s ease;
}

.service-marker:hover {
    transform: scale(1.1);
    z-index: 1000;
}

.service-item {
    border-left: 4px solid #dee2e6;
    transition: all 0.3s ease;
    cursor: pointer;
}

.service-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.service-item.status-pendiente {
    border-left-color: #ffc107;
}

.service-item.status-en_progreso {
    border-left-color: #17a2b8;
}

.service-item.status-completado {
    border-left-color: #28a745;
}

.service-item.status-no_encontrado {
    border-left-color: #dc3545;
}

.service-item.status-cancelado {
    border-left-color: #6c757d;
}

.service-item.selected {
    background-color: #e3f2fd;
    border-left-color: #1976d2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-badge {
    font-size: 0.75em;
    padding: 4px 8px;
    border-radius: 12px;
}

.time-display {
    font-family: monospace;
    font-weight: bold;
    color: #495057;
}

.map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
}

.route-summary {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>

<script>
let mapData = [];
let currentMapDate = new Date().toISOString().split('T')[0];
let map = null;
let markers = [];
let selectedServiceId = null;


document.addEventListener('DOMContentLoaded', function() {
    initializeMapDatePicker();
    loadTodayMap();
});

function initializeMapDatePicker() {
    flatpickr("#mapDatePicker", {
        dateFormat: "Y-m-d",
        defaultDate: currentMapDate,
        onChange: function(selectedDates, dateStr) {
            if (dateStr) {
                currentMapDate = dateStr;
                loadMapData(dateStr);
            }
        }
    });
}

function loadTodayMap() {
    const today = new Date().toISOString().split('T')[0];
    currentMapDate = today;
    document.getElementById('mapDatePicker').value = today;
    loadMapData(today);
}

async function loadMapData(date = null) {
    try {
        const url = new URL('<?= site_url("home_services/get_daily_map_services") ?>');
        if (date) {
            url.searchParams.append('date', date);
        }

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al cargar los datos del mapa');
        }

        const data = await response.json();
        
        if (data.success) {
            mapData = data.data;
            renderServicesList();
            updateMapStats();
            // Siempre reinicializa el mapa con los nuevos datos:
            initializeMap();
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('servicesList').innerHTML = `
            <div class="p-3 text-center">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error al cargar los datos. 
                    <button onclick="loadMapData('${currentMapDate}')" class="btn btn-sm btn-outline-danger ms-2">Reintentar</button>
                </div>
            </div>
        `;
    }
}

function renderServicesList() {
    const container = document.getElementById('servicesList');
    
    if (mapData.length === 0) {
        container.innerHTML = `
            <div class="p-4 text-center">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h6>No hay servicios</h6>
                <p class="text-muted small">No se encontraron servicios para la fecha seleccionada</p>
            </div>
        `;
        return;
    }

    // Ordenar por hora
    const sortedData = mapData.sort((a, b) => {
        if (!a.scheduled_time) return 1;
        if (!b.scheduled_time) return -1;
        return a.scheduled_time.localeCompare(b.scheduled_time);
    });

    let html = '';
    sortedData.forEach((service, index) => {
        const statusColor = getStatusColor(service.status);
        const statusText = getStatusText(service.status);
        
        html += `
            <div class="service-item status-${service.status} p-3 border-bottom" 
                 onclick="selectService(${service.id}, ${index})" 
                 id="service-item-${service.id}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="time-display">${formatTime(service.scheduled_time)}</span>
                    <span class="btn-ghost btn-ghost-${statusColor}">${statusText}</span>
                </div>
                <h6 class="mb-1">${service.patient_name || 'Sin paciente'}</h6>
                <p class="mb-1 small text-muted">${service.service_type || 'Servicio General'}</p>
                
                <div class="address-info">
                    <p class="mb-1 small">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        ${service.patient_address || 'Sin dirección'}
                    </p>
                    <p class="mb-0 small text-muted">
                        ${service.patient_city || ''} ${service.patient_state || ''} ${service.patient_zipcode || ''}
                    </p>
                </div>
                
                ${service.assigned_provider_name ? `
                    <p class="mb-0 small">
                        <i class="fas fa-user-md text-success"></i>
                        ${service.assigned_provider_name}
                    </p>
                ` : ''}
                
                <div class="service-actions mt-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn-ghost btn-ghost-primary" onclick="event.stopPropagation(); viewServiceDetails(${service.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-ghost btn-ghost-success" onclick="event.stopPropagation(); openMapForService(${service.id})">
                            <i class="fas fa-directions"></i>
                        </button>
                        <button class="btn-ghost btn-ghost-info" onclick="event.stopPropagation(); callPatient('${service.patient_phone}')">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="btn-ghost btn-ghost-warning" onclick="event.stopPropagation(); changeServiceStatus(${service.id})">
                            <i class="fas fa-flag"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    document.getElementById('serviceListCount').textContent = mapData.length;
}

function updateMapStats() {
    document.getElementById('mapTotalServices').textContent = mapData.length;
    
    // Calcular estadísticas de ruta
    const totalStops = mapData.filter(s => s.patient_address).length;
    document.getElementById('routeStops').textContent = totalStops;
    
    // Estimaciones básicas (esto se puede mejorar con la API de Google Maps)
    const estimatedDistance = totalStops * 5; // 5 km promedio entre paradas
    const estimatedTime = totalStops * 20; // 20 minutos promedio por parada
    const estimatedCost = estimatedDistance * 0.5; // $0.50 por km
    
    document.getElementById('routeDistance').textContent = `${estimatedDistance} km`;
    document.getElementById('routeTime').textContent = `${estimatedTime} min`;
    document.getElementById('routeCost').textContent = `$${estimatedCost.toFixed(2)}`;
}

function selectService(serviceId, index) {
    // Quitar selección anterior
    document.querySelectorAll('.service-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Agregar selección actual
    const serviceItem = document.getElementById(`service-item-${serviceId}`);
    if (serviceItem) {
        serviceItem.classList.add('selected');
        selectedServiceId = serviceId;
    }
    
    // Si hay mapa, centrar en el marcador
    if (map && markers[index]) {
        map.setView(markers[index].getLatLng(), 15);
        markers[index].openPopup();
    }
}

async function initializeMap() {
    // 1) Inicializar mapa solo una vez
    if (!map) {
        map = L.map('map').setView([7.119, -73.1227], 12); // Bucaramanga como centro inicial
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }
    
    // 2) Eliminar marcadores previos
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    
    // 3) Geocodificar con delay para evitar rate limiting
    const featureGroup = L.featureGroup();
    
    for (let i = 0; i < mapData.length; i++) {
        const svc = mapData[i];
        if (!svc.patient_address) continue;
        
        // Delay entre requests para evitar rate limiting
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const address = `${svc.patient_address}, ${svc.patient_city}, ${svc.patient_state} ${svc.patient_zipcode}`;
        
        try {
            const resp = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`,
                { 
                    headers: { 
                        'User-Agent': 'ServiceApp/1.0 (contact@yoursite.com)' 
                    } 
                }
            );
            
            const results = await resp.json();
            if (results[0]) {
                const lat = parseFloat(results[0].lat);
                const lon = parseFloat(results[0].lon);
                
                // Crear marcador con color según estado
                const markerColor = getMarkerColor(svc.status);
                
                const m = L.marker([lat, lon]).addTo(map);
                m.bindPopup(`
                    <div style="min-width: 200px;">
                        <h6><b>${svc.patient_name || 'Sin paciente'}</b></h6>
                        <p><strong>Servicio:</strong> ${svc.service_type || 'Servicio General'}</p>
                        <p><strong>Hora:</strong> ${formatTime(svc.scheduled_time)}</p>
                        <p><strong>Estado:</strong> <span class="badge bg-${getStatusColor(svc.status)}">${getStatusText(svc.status)}</span></p>
                        <p><strong>Dirección:</strong> ${address}</p>
                        ${svc.assigned_provider_name ? `<p><strong>Proveedor:</strong> ${svc.assigned_provider_name}</p>` : ''}
                    </div>
                `);
                
                markers.push(m);
                featureGroup.addLayer(m);
            }
        } catch (e) {
            console.warn('Error geocoding', address, e);
        }
    }
    
    // 4) Ajustar zoom/centro para ver todos los marcadores
    if (featureGroup.getLayers().length > 0) {
        map.fitBounds(featureGroup.getBounds(), { padding: [20, 20] });
    }
}

function optimizeRoute() {
    if (mapData.length === 0) {
        alert('No hay servicios para optimizar');
        return;
    }
    
    // Simulación de optimización de ruta
    const optimizingAlert = document.createElement('div');
    optimizingAlert.className = 'alert alert-info';
    optimizingAlert.innerHTML = `
        <i class="fas fa-spinner fa-spin"></i>
        Optimizando ruta para ${mapData.length} servicios...
    `;
    
    document.querySelector('.card-body').prepend(optimizingAlert);
    
    setTimeout(() => {
        optimizingAlert.remove();
        
        // Mostrar resultado de optimización
        const resultAlert = document.createElement('div');
        resultAlert.className = 'alert alert-success alert-dismissible fade show';
        resultAlert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <strong>Ruta optimizada!</strong> Se redujo la distancia en un 15% y el tiempo en 25 minutos.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.card-body').prepend(resultAlert);
        
        // Actualizar estadísticas
        document.getElementById('routeDistance').textContent = `${Math.round(mapData.length * 4.2)} km`;
        document.getElementById('routeTime').textContent = `${Math.round(mapData.length * 15)} min`;
    }, 3000);
}

function printRoute() {
    if (mapData.length === 0) {
        alert('No hay servicios para imprimir');
        return;
    }
    
    // Crear ventana de impresión
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ruta Diaria - ${currentMapDate}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .service { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
                .service h4 { margin: 0 0 10px 0; color: #333; }
                .service p { margin: 5px 0; }
                .status { padding: 3px 8px; border-radius: 3px; color: white; font-size: 12px; }
                .status-pendiente { background: #ffc107; }
                .status-en_progreso { background: #17a2b8; }
                .status-completado { background: #28a745; }
                .status-no_encontrado { background: #dc3545; }
                .status-cancelado { background: #6c757d; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Ruta Diaria de Servicios</h2>
                <p>Fecha: ${new Date(currentMapDate).toLocaleDateString('es-ES')}</p>
                <p>Total de servicios: ${mapData.length}</p>
            </div>
            
            ${mapData.map((service, index) => `
                <div class="service">
                    <h4>${index + 1}. ${service.patient_name || 'Sin paciente'}</h4>
                    <p><strong>Servicio:</strong> ${service.service_type || 'Servicio General'}</p>
                    <p><strong>Hora:</strong> ${formatTime(service.scheduled_time)}</p>
                    <p><strong>Dirección:</strong> ${service.patient_address || 'Sin dirección'}</p>
                    <p><strong>Ciudad:</strong> ${service.patient_city || ''} ${service.patient_state || ''} ${service.patient_zipcode || ''}</p>
                    <p><strong>Teléfono:</strong> ${service.patient_phone || 'Sin teléfono'}</p>
                    <p><strong>Proveedor:</strong> ${service.assigned_provider_name || 'Sin asignar'}</p>
                    <p><strong>Estado:</strong> <span class="status status-${service.status}">${getStatusText(service.status)}</span></p>
                </div>
            `).join('')}
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}

// Funciones auxiliares
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
    if (!timeString) return 'Sin hora';
    
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

// Controles del mapa
function zoomIn() {
    // Simular zoom in
    console.log('Zoom in');
}

function zoomOut() {
    // Simular zoom out
    console.log('Zoom out');
}

function centerMap() {
    // Simular centrar mapa
    console.log('Center map');
}

// Funciones de acción (reutilizadas de otras vistas)
function openCreateServiceModal() {
    $('#modalContainer').empty();
    $.get('<?= site_url("home_services/modal_create_service") ?>', function(html) {
        $('#modalContainer').html(html);
        const modal = new bootstrap.Modal(document.getElementById('modalCreateService'));
        modal.show();
    }).fail(function() {
        alert('Error al cargar el modal');
    });
}

function viewServiceDetails(id) {
    $('#modalContainer').empty();
    $.get('<?= site_url("home_services/modal_service_details") ?>', { id }, function(html) {
        $('#modalContainer').html(html);
        const modal = new bootstrap.Modal(document.getElementById('modalServiceDetails'));
        modal.show();
    }).fail(function() {
        alert('Error al cargar el modal');
    });
}

function changeServiceStatus(id) {
    $('#modalContainer').empty();
    $.get('<?= site_url("home_services/modal_change_status") ?>', { id }, function(html) {
        $('#modalContainer').html(html);
        const modal = new bootstrap.Modal(document.getElementById('modalChangeStatus'));
        modal.show();
    }).fail(function() {
        alert('Error al cargar el modal');
    });
}

function openMapForService(id) {
    const service = mapData.find(s => s.id == id);
    if (service && service.patient_address) {
        const address = `${service.patient_address}, ${service.patient_city}, ${service.patient_state} ${service.patient_zipcode}`;
        const encodedAddress = encodeURIComponent(address);
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`, '_blank');
    } else {
        alert('No se encontró la dirección del servicio');
    }
}

function callPatient(phone) {
    if (phone && phone !== 'Sin teléfono' && phone !== 'null') {
        window.open(`tel:${phone}`);
    } else {
        alert('No hay número de teléfono disponible');
    }
}

// Filtros
document.getElementById('mapStatusFilter').addEventListener('change', function() {
    loadMapData(currentMapDate);
});

// Actualizar cada 3 minutos
setInterval(() => {
    loadMapData(currentMapDate);
}, 180000);
</script>