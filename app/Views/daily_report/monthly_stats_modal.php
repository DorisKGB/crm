<!-- Modal de Estadísticas Mensuales -->
<div class="modal fade" id="monthlyStatsModal" tabindex="-1" aria-labelledby="monthlyStatsModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="monthlyStatsModalLabel">
                    <i data-feather="bar-chart-2" class="icon-16"></i> 
                    <?php echo app_lang('monthly_stats_title'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?php echo app_lang('close'); ?>"></button>
            </div>
            <div class="modal-body">
                <!-- Selector de Clínica -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <label for="stats-clinic-select" class="form-label fw-bold">
                                    <i data-feather="home" class="icon-16"></i> <?php echo app_lang('select_clinic'); ?>
                                </label>
                                <select class="form-select form-select-lg" id="stats-clinic-select">
                                    <option value=""><?php echo app_lang('select_clinic_placeholder'); ?></option>
                                    <?php if (isset($clinic_options) && !empty($clinic_options)): ?>
                                        <?php foreach ($clinic_options as $id => $name): ?>
                                            <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selector de Meses (se mostrará después de seleccionar clínica) -->
                <div class="row mb-4 d-none" id="months-selector-container">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <label class="form-label fw-bold">
                                    <i data-feather="calendar" class="icon-16"></i> <?php echo app_lang('filter_by_months'); ?>
                                </label>
                                <p class="text-muted small"><?php echo app_lang('filter_months_description'); ?></p>
                                <div id="months-checkboxes" class="row">
                                    <!-- Los checkboxes se generarán dinámicamente aquí -->
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-months">
                                        <i data-feather="check-square" class="icon-14"></i> <?php echo app_lang('select_all_months'); ?>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-months">
                                        <i data-feather="x-square" class="icon-14"></i> <?php echo app_lang('clear_all_months'); ?>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" id="apply-months-filter">
                                        <i data-feather="filter" class="icon-14"></i> <?php echo app_lang('apply_filter'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Área de Resultados -->
                <div id="stats-results-container" class="d-none">
                    <!-- Resumen del Mejor Mes -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-success" id="best-month-alert" role="alert">
                                <h4 class="alert-heading">
                                    <i data-feather="award" class="icon-20"></i> <?php echo app_lang('best_month'); ?>
                                </h4>
                                <p id="best-month-text" class="mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Estadísticas -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i data-feather="trending-up" class="icon-16"></i> 
                                        <?php echo app_lang('monthly_ranking_title'); ?>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="stats-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 60px;">
                                                        <i data-feather="hash" class="icon-14"></i> #
                                                    </th>
                                                    <th>
                                                        <i data-feather="calendar" class="icon-14"></i> <?php echo app_lang('month'); ?>
                                                    </th>
                                                    <th class="text-end">
                                                        <i data-feather="dollar-sign" class="icon-14"></i> <?php echo app_lang('total_sales'); ?>
                                                    </th>
                                                    <th class="text-end">
                                                        <i data-feather="credit-card" class="icon-14"></i> <?php echo app_lang('cash'); ?>
                                                    </th>
                                                    <th class="text-end">
                                                        <i data-feather="credit-card" class="icon-14"></i> <?php echo app_lang('card'); ?>
                                                    </th>
                                                    <th class="text-end">
                                                        <i data-feather="credit-card" class="icon-14"></i> <?php echo app_lang('others'); ?>
                                                    </th>
                                                    <th class="text-center">
                                                        <i data-feather="users" class="icon-14"></i> <?php echo app_lang('new_patients'); ?>
                                                    </th>
                                                    <th class="text-center">
                                                        <i data-feather="user-check" class="icon-14"></i> <?php echo app_lang('followup'); ?>
                                                    </th>
                                                    <th class="text-end">
                                                        <i data-feather="trending-up" class="icon-14"></i> <?php echo app_lang('daily_average'); ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="stats-table-body">
                                                <!-- Los datos se cargarán dinámicamente aquí -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Tendencias Cronológicas -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i data-feather="activity" class="icon-16"></i> 
                                        <?php echo app_lang('monthly_trends'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthly-trends-chart" style="width: 100%; min-height: 350px;"></canvas>
                                    <div id="no-data-trends-chart" style="display: none;" class="py-5 text-center">
                                        <i data-feather="bar-chart-2" style="width: 60px; height: 60px;" class="text-muted mb-3"></i>
                                        <p class="text-muted"><?php echo app_lang('no_data_to_chart'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="stats-loading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden"><?php echo app_lang('loading'); ?></span>
                    </div>
                    <p class="mt-3 text-muted"><?php echo app_lang('loading_stats'); ?></p>
                </div>

                <!-- Mensaje cuando no hay clínica seleccionada -->
                <div id="stats-empty-state" class="text-center py-5">
                    <i data-feather="bar-chart-2" style="width: 80px; height: 80px;" class="text-muted mb-3"></i>
                    <h5 class="text-muted"><?php echo app_lang('select_clinic_to_view_stats'); ?></h5>
                    <p class="text-muted"><?php echo app_lang('stats_description'); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="icon-14"></i> <?php echo app_lang('close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentClinicId = null;
    let availableMonths = [];
    let trendsChartInstance = null; // Variable para almacenar la instancia de la gráfica
    
    // Traducciones
    const translations = {
        wasBestMonthWithSales: "<?php echo app_lang('was_best_month_with_sales'); ?>",
        dailyAverage: "<?php echo app_lang('daily_average'); ?>"
    };

    // Mejorar el comportamiento del scroll en el modal
    $('#monthlyStatsModal').on('shown.bs.modal', function() {
        const modalDialog = $(this).find('.modal-dialog');
        const modalBody = $(this).find('.modal-body');
        
        // Prevenir scroll del body cuando el mouse está sobre el modal
        modalDialog.on('wheel', function(e) {
            e.stopPropagation();
        });
        
        // Asegurar que el scroll funcione en el modal-body
        modalBody.on('wheel', function(e) {
            const delta = e.originalEvent.deltaY;
            const scrollTop = this.scrollTop;
            const scrollHeight = this.scrollHeight;
            const height = $(this).height();
            const isAtTop = scrollTop === 0;
            const isAtBottom = scrollTop + height >= scrollHeight;
            
            // Prevenir el scroll del fondo cuando estamos en los límites
            if ((isAtTop && delta < 0) || (isAtBottom && delta > 0)) {
                e.preventDefault();
            }
            
            e.stopPropagation();
        });
        
        // Recargar iconos de Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });

    // Limpiar eventos al cerrar el modal
    $('#monthlyStatsModal').on('hidden.bs.modal', function() {
        const modalDialog = $(this).find('.modal-dialog');
        const modalBody = $(this).find('.modal-body');
        
        modalDialog.off('wheel');
        modalBody.off('wheel');
        
        // Destruir gráfica si existe
        if (trendsChartInstance) {
            trendsChartInstance.destroy();
            trendsChartInstance = null;
        }
        
        // Limpiar datos
        $('#stats-clinic-select').val('');
        $('#stats-results-container').addClass('d-none');
        $('#months-selector-container').addClass('d-none');
        $('#stats-loading').addClass('d-none');
        $('#stats-empty-state').removeClass('d-none');
        $('#monthly-trends-chart').hide();
        $('#no-data-trends-chart').hide();
        currentClinicId = null;
        availableMonths = [];
    });

    // Cuando se selecciona una clínica
    $('#stats-clinic-select').on('change', function() {
        currentClinicId = $(this).val();
        
        if (currentClinicId) {
            // Mostrar loading
            $('#stats-empty-state').addClass('d-none');
            $('#stats-results-container').addClass('d-none');
            $('#months-selector-container').addClass('d-none');
            $('#stats-loading').removeClass('d-none');
            
            // Cargar meses disponibles
            loadAvailableMonths(currentClinicId);
        } else {
            // Limpiar todo si no hay clínica seleccionada
            $('#stats-empty-state').removeClass('d-none');
            $('#stats-results-container').addClass('d-none');
            $('#months-selector-container').addClass('d-none');
            $('#stats-loading').addClass('d-none');
        }
    });

    // Cargar meses disponibles para la clínica
    function loadAvailableMonths(clinicId) {
        $.ajax({
            url: '<?php echo get_uri("daily_report/get_available_months"); ?>',
            type: 'GET',
            data: { clinic_id: clinicId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    availableMonths = response.data;
                    renderMonthsCheckboxes(response.data);
                    $('#months-selector-container').removeClass('d-none');
                    // Cargar estadísticas automáticamente (todos los meses)
                    loadMonthlyStats(clinicId, null);
                } else {
                    $('#stats-loading').addClass('d-none');
                    alert('No se encontraron reportes para esta clínica.');
                    $('#stats-empty-state').removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando meses:', error);
                $('#stats-loading').addClass('d-none');
                alert('Error al cargar los meses disponibles');
                $('#stats-empty-state').removeClass('d-none');
            }
        });
    }

    // Renderizar checkboxes de meses
    function renderMonthsCheckboxes(months) {
        let html = '';
        months.forEach(function(month) {
            html += `
                <div class="col-md-4 col-lg-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input month-checkbox" type="checkbox" 
                               value="${month.month_value}" id="month-${month.month_value}">
                        <label class="form-check-label" for="month-${month.month_value}">
                            ${month.month_label}
                        </label>
                    </div>
                </div>
            `;
        });
        $('#months-checkboxes').html(html);
    }

    // Seleccionar todos los meses
    $('#select-all-months').on('click', function() {
        $('.month-checkbox').prop('checked', true);
    });

    // Limpiar selección
    $('#clear-all-months').on('click', function() {
        $('.month-checkbox').prop('checked', false);
    });

    // Aplicar filtro de meses
    $('#apply-months-filter').on('click', function() {
        let selectedMonths = [];
        $('.month-checkbox:checked').each(function() {
            selectedMonths.push($(this).val());
        });
        
        if (currentClinicId) {
            $('#stats-results-container').addClass('d-none');
            $('#stats-loading').removeClass('d-none');
            loadMonthlyStats(currentClinicId, selectedMonths.length > 0 ? selectedMonths : null);
        }
    });

    // Cargar estadísticas mensuales
    function loadMonthlyStats(clinicId, selectedMonths) {
        $.ajax({
            url: '<?php echo get_uri("daily_report/get_monthly_economic_stats"); ?>',
            type: 'POST',
            data: { 
                clinic_id: clinicId,
                selected_months: selectedMonths ? JSON.stringify(selectedMonths) : null
            },
            dataType: 'json',
            success: function(response) {
                $('#stats-loading').addClass('d-none');
                
                if (response.success && response.data.length > 0) {
                    renderStats(response.data);
                    $('#stats-results-container').removeClass('d-none');
                    // Cargar también la gráfica de tendencias
                    loadTrendsChart(clinicId, selectedMonths);
                } else {
                    alert(response.message || 'No se encontraron datos para mostrar');
                    $('#stats-empty-state').removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando estadísticas:', error);
                $('#stats-loading').addClass('d-none');
                alert('Error al cargar las estadísticas');
                $('#stats-empty-state').removeClass('d-none');
            }
        });
    }

    // Renderizar estadísticas en la tabla
    function renderStats(data) {
        // Mostrar el mejor mes
        let bestMonth = data[0];
        $('#best-month-text').html(
            `<strong>${bestMonth.month_name}</strong> ${translations.wasBestMonthWithSales} 
            <strong>$${formatNumber(bestMonth.total_sales)}</strong> 
            (${translations.dailyAverage}: <strong>$${formatNumber(bestMonth.average_daily_sales)}</strong>)`
        );

        // Renderizar tabla
        let html = '';
        data.forEach(function(item, index) {
            let rowClass = index === 0 ? 'table-success' : '';
            let medalIcon = '';
            
            if (index === 0) {
                medalIcon = '<i data-feather="award" class="icon-16 text-warning"></i>';
            } else if (index === 1) {
                medalIcon = '<i data-feather="award" class="icon-16 text-secondary"></i>';
            } else if (index === 2) {
                medalIcon = '<i data-feather="award" class="icon-16" style="color: #CD7F32;"></i>';
            }
            
            html += `
                <tr class="${rowClass}">
                    <td class="text-center fw-bold">${medalIcon} ${index + 1}</td>
                    <td><strong>${item.month_name}</strong></td>
                    <td class="text-end text-success fw-bold">$${formatNumber(item.total_sales)}</td>
                    <td class="text-end">$${formatNumber(item.total_cash)}</td>
                    <td class="text-end">$${formatNumber(item.total_card)}</td>
                    <td class="text-end">$${formatNumber(item.total_other)}</td>
                    <td class="text-center">${item.total_new_patients}</td>
                    <td class="text-center">${item.total_followup_patients}</td>
                    <td class="text-end text-primary">$${formatNumber(item.average_daily_sales)}</td>
                </tr>
            `;
        });
        
        $('#stats-table-body').html(html);
        
        // Recargar iconos de Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Formatear números con separadores de miles
    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Cargar datos de la gráfica de tendencias
    function loadTrendsChart(clinicId, selectedMonths) {
        console.log('Cargando gráfica de tendencias...', clinicId, selectedMonths);
        $.ajax({
            url: '<?php echo get_uri("daily_report/get_monthly_trends_chart_data"); ?>',
            type: 'POST',
            data: { 
                clinic_id: clinicId,
                selected_months: selectedMonths ? JSON.stringify(selectedMonths) : null
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta de tendencias:', response);
                if (response.success && response.data) {
                    renderTrendsChart(response.data);
                } else {
                    console.log('No hay datos para tendencias');
                    showNoDataTrends();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando gráfica de tendencias:', error);
                console.error('XHR:', xhr);
                showNoDataTrends();
            }
        });
    }

    // Renderizar gráfica de tendencias
    function renderTrendsChart(data) {
        console.log('Renderizando gráfica con datos:', data);
        const chartCanvas = document.getElementById('monthly-trends-chart');
        const noDataDiv = document.getElementById('no-data-trends-chart');

        console.log('Canvas:', chartCanvas);
        console.log('NoData div:', noDataDiv);

        if (!data.labels || data.labels.length === 0) {
            console.log('No hay labels, mostrando mensaje sin datos');
            chartCanvas.style.display = 'none';
            noDataDiv.style.display = 'block';
            return;
        }

        chartCanvas.style.display = 'block';
        noDataDiv.style.display = 'none';

        // Destruir gráfica anterior si existe
        if (trendsChartInstance) {
            console.log('Destruyendo gráfica anterior');
            trendsChartInstance.destroy();
        }

        // Formatear labels con nombres de meses
        const formattedLabels = data.labels.map(function(value) {
            const parts = value.split('-');
            const year = parts[0];
            const monthIndex = parseInt(parts[1], 10);
            const monthNames = [
                "<?php echo app_lang('january'); ?>",
                "<?php echo app_lang('february'); ?>",
                "<?php echo app_lang('march'); ?>",
                "<?php echo app_lang('april'); ?>",
                "<?php echo app_lang('may'); ?>",
                "<?php echo app_lang('june'); ?>",
                "<?php echo app_lang('july'); ?>",
                "<?php echo app_lang('august'); ?>",
                "<?php echo app_lang('september'); ?>",
                "<?php echo app_lang('october'); ?>",
                "<?php echo app_lang('november'); ?>",
                "<?php echo app_lang('december'); ?>"
            ];
            const month = monthNames[monthIndex - 1];
            return `${month} ${year}`;
        });

        const config = {
            type: 'line',
            data: {
                labels: formattedLabels,
                datasets: [
                    {
                        label: '<?php echo app_lang("total_sales"); ?>',
                        data: data.sales,
                        borderColor: '#32A483',
                        backgroundColor: 'rgba(50, 164, 131, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#32A483',
                        yAxisID: 'y-axis-sales',
                        lineTension: 0.3
                    },
                    {
                        label: '<?php echo app_lang("new_patients"); ?>',
                        data: data.new_patients,
                        borderColor: '#E05008',
                        backgroundColor: 'rgba(224, 80, 8, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#E05008',
                        yAxisID: 'y-axis-patients',
                        lineTension: 0.3
                    },
                    {
                        label: '<?php echo app_lang("followup"); ?>',
                        data: data.followup_patients,
                        borderColor: '#4E5E6A',
                        backgroundColor: 'rgba(78, 94, 106, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#4E5E6A',
                        yAxisID: 'y-axis-patients',
                        lineTension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo app_lang("monthly_trends"); ?>',
                    fontSize: 16,
                    padding: 20
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let label = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (data.datasets[tooltipItem.datasetIndex].yAxisID === 'y-axis-sales') {
                                label += '$' + tooltipItem.yLabel.toLocaleString('en-US');
                            } else {
                                label += tooltipItem.yLabel;
                            }
                            return label;
                        }
                    }
                },
                hover: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: '<?php echo app_lang("month"); ?>',
                            fontStyle: 'bold'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }],
                    yAxes: [
                        {
                            id: 'y-axis-sales',
                            type: 'linear',
                            display: true,
                            position: 'left',
                            scaleLabel: {
                                display: true,
                                labelString: '<?php echo app_lang("total_sales"); ?> ($)',
                                fontColor: '#32A483',
                                fontStyle: 'bold'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    return '$' + value.toLocaleString('en-US');
                                }
                            }
                        },
                        {
                            id: 'y-axis-patients',
                            type: 'linear',
                            display: true,
                            position: 'right',
                            scaleLabel: {
                                display: true,
                                labelString: '<?php echo app_lang("number_of_patients"); ?>',
                                fontColor: '#E05008',
                                fontStyle: 'bold'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    return Math.floor(value) === value ? value : null;
                                }
                            },
                            gridLines: {
                                drawOnChartArea: false
                            }
                        }
                    ]
                }
            }
        };

        const ctx = chartCanvas.getContext('2d');
        console.log('Creando nueva instancia de Chart con config:', config);
        trendsChartInstance = new Chart(ctx, config);
        console.log('Gráfica creada exitosamente:', trendsChartInstance);

        // Recargar iconos de Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Mostrar mensaje de "sin datos" para la gráfica
    function showNoDataTrends() {
        const chartCanvas = document.getElementById('monthly-trends-chart');
        const noDataDiv = document.getElementById('no-data-trends-chart');
        
        chartCanvas.style.display = 'none';
        noDataDiv.style.display = 'block';

        // Recargar iconos de Feather
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
});
</script>

<style>
/* Configuración del modal para scroll suave */
#monthlyStatsModal .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#monthlyStatsModal .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#monthlyStatsModal .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: calc(90vh - 130px);
    padding: 1.5rem;
    /* Scroll suave */
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

/* Personalizar scrollbar */
#monthlyStatsModal .modal-body::-webkit-scrollbar {
    width: 10px;
}

#monthlyStatsModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#monthlyStatsModal .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#monthlyStatsModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Para Firefox */
#monthlyStatsModal .modal-body {
    scrollbar-width: thin;
    scrollbar-color: #888 #f1f1f1;
}

/* Estilos de la tabla */
#stats-table tbody tr {
    transition: all 0.2s ease;
}

#stats-table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table-success {
    background-color: #d1e7dd !important;
}

#months-checkboxes .form-check {
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

#months-checkboxes .form-check:hover {
    background-color: #f8f9fa;
}

/* Mejorar el header fijo */
#monthlyStatsModal .modal-header {
    flex-shrink: 0;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* Mejorar el footer fijo */
#monthlyStatsModal .modal-footer {
    flex-shrink: 0;
    border-top: 1px solid #dee2e6;
}

/* Animación suave al abrir */
#monthlyStatsModal.show .modal-dialog {
    transform: none;
}
</style>

