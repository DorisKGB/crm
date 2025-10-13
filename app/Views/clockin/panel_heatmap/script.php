<script>
    let heatmapData = null;
    let currentClinicId = <?= isset($_GET['clinic']) ? $_GET['clinic'] : 'null' ?>;

    // Abrir modal del mapa de calor
    function openHeatmapModal() {
        console.log('🔍 Abriendo modal del mapa de calor...');
        const modalElement = document.getElementById('heatmapModal');
        console.log('🔍 Elemento del modal:', modalElement);
        
        if (!modalElement) {
            console.error('❌ No se encontró el elemento heatmapModal');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        console.log('✅ Modal abierto correctamente');
        
        // Cargar automáticamente el mapa con el mes actual
        const monthElement = document.getElementById('heatmapMonth');
        console.log('🔍 Elemento del mes:', monthElement);
        console.log('🔍 Valor del mes:', monthElement ? monthElement.value : 'No encontrado');
        
        if (monthElement && monthElement.value) {
            console.log('🔄 Cargando mapa automáticamente...');
            loadHeatmapFromModal();
        } else {
            console.log('⚠️ No se pudo cargar automáticamente - mes no seleccionado');
        }
    }

    // Funciones de loading
    function showHeatmapLoading() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const heatmapContainer = document.getElementById('heatmapContainer');
        const statsCard = document.getElementById('statsCard');
        
        if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
            loadingSpinner.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <div class="mt-3">
                        <h5>Cargando mapa de calor...</h5>
                        <p class="text-muted">Procesando datos de asistencia</p>
                    </div>
                </div>
            `;
        }
        if (heatmapContainer) heatmapContainer.style.display = 'none';
        if (statsCard) statsCard.style.display = 'none';
    }

    function hideHeatmapLoading() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const heatmapContainer = document.getElementById('heatmapContainer');
        
        if (loadingSpinner) loadingSpinner.style.display = 'none';
        if (heatmapContainer) heatmapContainer.style.display = 'block';
    }

    // Cargar mapa de calor al hacer clic en el botón
    function loadHeatmap() {
        const monthElement = document.getElementById('heatmapMonth');
        const userElement = document.getElementById('heatmapUser');
        
        if (!monthElement) {
            console.error('Elemento heatmapMonth no encontrado');
            return;
        }
        
        const month = monthElement.value;
        const userId = userElement ? userElement.value : '';
        
        if (!month) {
            alert('Por favor selecciona un mes');
            return;
        }
        
        if (!currentClinicId) {
            alert('No se ha seleccionado una clínica');
            return;
        }
        
        showHeatmapLoading();
        
        const url = new URL('<?= site_url("clockin/getHeatmapData") ?>', window.location.origin);
        url.searchParams.set('clinic_id', currentClinicId);
        url.searchParams.set('month', month);
        if (userId) {
            url.searchParams.set('user_id', userId);
        }
        
        console.log('Cargando datos del mapa de calor desde:', url.toString());
        
        // Usar AbortController para cancelar peticiones anteriores
        if (window.heatmapController) {
            window.heatmapController.abort();
        }
        window.heatmapController = new AbortController();
        
        fetch(url, {
            signal: window.heatmapController.signal,
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    heatmapData = data.data;
                    renderHeatmap(data);
                } else {
                    console.error('Error en la respuesta:', data.message);
                    alert(data.message || 'Error al cargar los datos');
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    console.log('Petición cancelada');
                    return;
                }
                console.error('Error en la petición:', error);
                alert('Error al cargar el mapa de calor');
            })
            .finally(() => {
                hideHeatmapLoading();
                window.heatmapController = null;
            });
    }

    // Renderizar el mapa de calor
    function renderHeatmap(data) {
        console.log('Renderizando mapa de calor con datos:', data);
        
        const container = document.getElementById('heatmapContainer');
        
        if (!container) {
            console.error('Contenedor del mapa de calor no encontrado');
            return;
        }
        
        if (!data.data || data.data.length === 0) {
            console.log('No hay datos para mostrar');
            container.innerHTML = '<div class="text-center p-5"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p class="text-muted">No hay datos para el mes seleccionado</p></div>';
            return;
        }
        
        // Formatear el nombre del mes correctamente (solo el mes actual) sin usar Date
        const [year, month] = data.month.split('-');
        const monthNames = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                           'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const monthName = `${monthNames[parseInt(month) - 1]} de ${year}`;
        
        // Calcular estadísticas PRIMERO
        let totalDays = data.data.length;
        let workDays = data.data.filter(day => day.is_work_day).length;
        let totalAttendance = 0;
        let totalEfficiency = 0;
        let efficiencyCount = 0;
        
        data.data.forEach(day => {
            if (day.is_work_day && Array.isArray(day.users)) {
                day.users.forEach(user => {
                    if (user.attended) {
                        totalAttendance++;
                        if (user.efficiency > 0) {
                            totalEfficiency += user.efficiency;
                            efficiencyCount++;
                        }
                    }
                });
            }
        });
        
        // Calcular attendanceRate de forma más segura
        let attendanceRate = 0;
        if (workDays > 0 && data.data.length > 0 && Array.isArray(data.data[0].users)) {
            const totalPossibleAttendance = workDays * data.data[0].users.length;
            attendanceRate = Math.round((totalAttendance / totalPossibleAttendance) * 100);
        }
        const avgEfficiency = efficiencyCount > 0 ? Math.round(totalEfficiency / efficiencyCount) : 0;
        
        let html = `
            <div class="heatmap-controls">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>${monthName}</h4>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Mes:</label>
                        <input type="month" id="heatmapMonthModal" class="form-control" value="${data.month}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Usuario:</label>
                        <select id="heatmapUserModal" class="form-control">
                            <option value="">Todos los usuarios</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="loadHeatmapFromModal()">
                            <i class="fas fa-search me-2"></i>Cargar Mapa
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas del modal -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">${totalDays}</h4>
                            <small>Días del mes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">${workDays}</h4>
                            <small>Días laborales</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Actualizar estadísticas (solo si existen en el DOM)
        const totalDaysEl = document.getElementById('totalDays');
        const workDaysEl = document.getElementById('workDays');
        const attendanceRateEl = document.getElementById('attendanceRate');
        const avgEfficiencyEl = document.getElementById('avgEfficiency');
        
        if (totalDaysEl) totalDaysEl.textContent = totalDays;
        if (workDaysEl) workDaysEl.textContent = workDays;
        if (attendanceRateEl) attendanceRateEl.textContent = attendanceRate + '%';
        if (avgEfficiencyEl) avgEfficiencyEl.textContent = avgEfficiency + '%';
        
        // Mostrar estadísticas (solo si existe en el DOM)
        const statsCard = document.getElementById('statsCard');
        if (statsCard) statsCard.style.display = 'block';
        
        // Generar días de la semana
        html += '<div class="heatmap-weekdays mb-3">';
        const weekdays = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        weekdays.forEach(day => {
            html += `<div class="weekday">${day}</div>`;
        });
        html += '</div>';
        
        // Generar grid del calendario
        html += '<div class="heatmap-grid">';
        
        // Obtener el primer día del mes correctamente (sin problemas de zona horaria)
        const [firstYear, firstMonth] = data.month.split('-');
        const firstDay = new Date(parseInt(firstYear), parseInt(firstMonth) - 1, 1);
        const firstDayOfWeek = firstDay.getDay(); // 0 = Domingo, 1 = Lunes, etc.
        
        console.log('🔍 Primer día del mes:', firstDay);
        console.log('🔍 Día de la semana del primer día:', firstDayOfWeek);
        console.log('🔍 Total de días en data.data:', data.data.length);
        
        // Agregar celdas vacías para alinear el primer día del mes
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="heatmap-day empty"></div>';
        }
        
        // Solo procesar los días del mes actual (data.data ya contiene solo los días del mes)
        data.data.forEach((day, index) => {
            console.log(`🔍 Procesando día ${index + 1}:`, day.date, 'día del mes:', day.day);
            
            // Usar el día que viene del backend para evitar problemas de zona horaria
            const dayNumber = day.day; // Usar el día que ya viene calculado del backend
            const dayOfWeek = new Date(day.date + 'T00:00:00').getDay(); // Forzar hora local
            
            console.log(`🔍 Día ${dayNumber}: fecha=${day.date}, día de la semana=${dayOfWeek} (0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb)`);
            
            // Determinar el estado del día basándose en los horarios de la clínica
            let statusClass = 'non-work';
            let statusIcon = '❌';
            let statusText = 'No laboral';
            let userNames = '';
            
            // Si el día tiene horarios configurados en la clínica, es un día laboral
            if (day.is_work_day && day.expected_hours) {
                // Verificar que day.users existe y es un array
                if (Array.isArray(day.users)) {
                    const usersAttended = day.users.filter(u => u.attended);
                    if (usersAttended.length > 0) {
                        const hasComplete = usersAttended.some(u => u.status === 'complete');
                        const hasOvertime = usersAttended.some(u => u.status === 'overtime');
                        const hasPartial = usersAttended.some(u => u.status === 'partial');
                        
                        if (hasOvertime) {
                            statusClass = 'overtime';
                            statusIcon = '⏰';
                            statusText = 'Horas Extra';
                        } else if (hasComplete) {
                            statusClass = 'complete';
                            statusIcon = '✅';
                            statusText = 'Completo';
                        } else if (hasPartial) {
                            statusClass = 'partial';
                            statusIcon = '⚠️';
                            statusText = 'Parcial';
                        }
                        
                        // Agregar nombres de usuarios debajo
                        userNames = usersAttended.map(user => user.user_name || 'Usuario').join(', ');
                    } else {
                        statusClass = 'absent';
                        statusIcon = '❌';
                        statusText = 'Ausente';
                    }
                } else {
                    console.warn('day.users no es un array para el día:', day.date, day.users);
                    statusClass = 'absent';
                    statusIcon = '❌';
                    statusText = 'Sin datos';
                }
            } else {
                // Día no laboral según horarios de la clínica
                statusClass = 'non-work';
                statusIcon = '🚫';
                statusText = 'No laboral';
            }
            
            console.log(`🔍 Generando HTML para día ${dayNumber}: fecha=${day.date}, onclick="showDayDetails('${day.date}')"`);
            
            html += `
                <div class="heatmap-day ${statusClass}" 
                     onclick="console.log('🖱️ CLIC EN DÍA ${dayNumber}'); showDayDetails('${day.date}')">
                    <div class="day-number">${dayNumber}</div>
                    <div class="day-status">${statusIcon}</div>
                    <div class="day-hours">${statusText}</div>
                    <div class="day-users">${userNames}</div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;

        // Inicializar Flatpickr para el mes en el modal
        const monthModalElement = document.getElementById("heatmapMonthModal");
        if (monthModalElement) {
            flatpickr(monthModalElement, {
                dateFormat: "Y-m",
                defaultDate: data.month,
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        //loadHeatmapFromModal();
                    }
                }
            });
        }

        // Poblar el filtro de usuarios en el modal
        const heatmapUserModal = document.getElementById('heatmapUserModal');
        if (heatmapUserModal) {
            heatmapUserModal.innerHTML = '<option value="">Todos los usuarios</option>';
            const users = <?= json_encode($users ?? []) ?>; // Obtener usuarios del PHP
            
            console.log('🔍 Usuarios recibidos del PHP:', users);
            console.log('🔍 Tipo de usuarios:', typeof users);
            console.log('🔍 Es array usuarios:', Array.isArray(users));
            
            // Verificar que users es un array antes de hacer forEach
            if (Array.isArray(users)) {
                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.first_name + ' ' + user.last_name;
                    heatmapUserModal.appendChild(option);
                });
            } else {
                console.warn('Los usuarios no son un array:', users);
            }
            
            // Seleccionar el usuario actual si está filtrado
            const currentUserId = new URLSearchParams(window.location.search).get('user_id');
            if (currentUserId) {
                heatmapUserModal.value = currentUserId;
            }
        }
    }

    // Mostrar detalles del día
    function showDayDetails(date) {
        console.log('🚀 showDayDetails EJECUTÁNDOSE con fecha:', date);
        console.log('🔍 showDayDetails llamada con fecha:', date);
        console.log('🔍 heatmapData disponible:', heatmapData);
        
        if (!heatmapData) return;
        
        const day = heatmapData.find(d => d.date === date);
        console.log('🔍 Día encontrado:', day);
        console.log('🔍 Fecha buscada:', date);
        console.log('🔍 Fechas disponibles en heatmapData:', heatmapData.map(d => d.date));
        if (!day) return;
        
        const modalElement = document.getElementById('dayDetailModal');
        const content = document.getElementById('dayDetailContent');
        
        if (!modalElement || !content) {
            console.error('Elementos del modal no encontrados');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        
        // Forzar interpretación local para evitar problemas de zona horaria
        const dateObj = new Date(date + 'T00:00:00');
        const dayName = dateObj.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        console.log('🔍 Fecha del objeto encontrado:', day.date);
        console.log('🔍 Día del mes del objeto:', day.day);
        console.log('🔍 Usuarios del día:', day.users);
        console.log('🔍 Fecha pasada a new Date():', date);
        console.log('🔍 dateObj resultante:', dateObj);
        console.log('🔍 dayName formateado:', dayName);
        
        let html = `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-day me-2"></i>${dayName}</h5>
                    <p class="mb-0"><strong>Horario esperado:</strong> ${day.expected_hours || 'No laboral'}</p>
                </div>
                <div class="card-body">
        `;
        
        if (day.users.length === 0) {
            html += '<p class="text-muted">No hay usuarios asignados a este día.</p>';
        } else {
            html += '<div class="table-responsive"><table class="table table-sm">';
            html += '<thead><tr><th>Usuario</th><th>Estado</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Eficiencia</th></tr></thead><tbody>';
            
            day.users.forEach(user => {
                const statusBadge = getStatusBadge(user.status);
                html += `
                    <tr>
                        <td>${user.user_name}</td>
                        <td>${statusBadge}</td>
                        <td>${user.check_in || '--'}</td>
                        <td>${user.check_out || '--'}</td>
                        <td>${user.hours_worked}h</td>
                        <td>${user.efficiency}%</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        html += '</div></div>';
        console.log('🔍 HTML generado para el modal:', html);
        content.innerHTML = html;
        modal.show();
    }

    // Función auxiliar para obtener el badge de estado
    function getStatusBadge(status) {
        const badges = {
            'absent': '<span class="badge bg-danger">Ausente</span>',
            'partial': '<span class="badge bg-warning">Parcial</span>',
            'complete': '<span class="badge bg-success">Completo</span>',
            'overtime': '<span class="badge bg-primary">Horas Extra</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Desconocido</span>';
    }

    // Cargar mapa desde el modal
    function loadHeatmapFromModal() {
        const monthElement = document.getElementById('heatmapMonthModal');
        const userElement = document.getElementById('heatmapUserModal');
        
        if (!monthElement) {
            console.error('Elemento heatmapMonthModal no encontrado');
            return;
        }
        
        const month = monthElement.value;
        const userId = userElement ? userElement.value : '';
        
        if (!month) {
            alert('Por favor selecciona un mes');
            return;
        }
        
        if (!currentClinicId) {
            alert('No se ha seleccionado una clínica');
            return;
        }
        
        showHeatmapLoading();
        
        const url = new URL('<?= site_url("clockin/getHeatmapData") ?>', window.location.origin);
        url.searchParams.set('clinic_id', currentClinicId);
        url.searchParams.set('month', month);
        if (userId) {
            url.searchParams.set('user_id', userId);
        }
        
        console.log('Cargando datos del mapa de calor desde:', url.toString());
        
        // Usar AbortController para cancelar peticiones anteriores
        if (window.heatmapController) {
            window.heatmapController.abort();
        }
        window.heatmapController = new AbortController();
        
        fetch(url, {
            signal: window.heatmapController.signal,
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    heatmapData = data.data;
                    renderHeatmap(data);
                } else {
                    console.error('Error en la respuesta:', data.message);
                    alert(data.message || 'Error al cargar los datos');
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    console.log('Petición cancelada');
                    return;
                }
                console.error('Error en la petición:', error);
                alert('Error al cargar el mapa de calor');
            })
            .finally(() => {
                hideHeatmapLoading();
                window.heatmapController = null;
            });
    }
</script>
