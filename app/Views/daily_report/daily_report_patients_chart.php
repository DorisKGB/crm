<div class="pt-2 ps-3">
    <div class="pt-2"><?php echo app_lang("chart_total_patients"); ?></div>
    <canvas id="total-patients-chart"
        style="width: 100%; min-height: 60px; margin-left: -10px; display: none;"></canvas>
    <div id="no-data-total-patients" style="display: none;" class="py-5 mt-4 text-center border">
        <?php echo app_lang("no_data_to_chart"); ?></div>
</div>

<script type="text/javascript">
var totalPatientsChartContent;

var initTotalPatientsChart = function(monthlyPatients, labels) {
    var totalPatientsChart = document.getElementById("total-patients-chart");
    var noDataTotalPatients = document.getElementById("no-data-total-patients");

    // Verificación de valores null y arreglo vacío
    if (!monthlyPatients || monthlyPatients.length === 0 || !labels || labels.length === 0) {
        totalPatientsChart.style.display = 'none';
        noDataTotalPatients.style.display = 'block';
        return;
    } else {
        totalPatientsChart.style.display = 'block';
        noDataTotalPatients.style.display = 'none';
    }

    if (totalPatientsChartContent) {
        totalPatientsChartContent.destroy();
    }

    const data = {
        labels: labels.map(function(value) {
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
        }),
        datasets: [{
            label: "<?php echo app_lang('monthly_patients'); ?>",
            data: monthlyPatients || [0], // Inicializar con 0 si es null
            borderColor: 'rgba(105, 39, 6, 0.76)',
            backgroundColor: 'rgba(224, 80, 8, 0.25)',
            borderWidth: 2,
            borderRadius: 5,
            borderSkipped: false,
        }]
    };

    const maxValue = Math.max(...(monthlyPatients || [
    0])); // Manejar maxValue con datos inicializados a 0 si es necesario
    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            layout: {
                padding: {
                    top: 20 // Añadir padding superior
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    padding: {
                        top: 2 // Añadir padding superior
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo app_lang("chart_total_patients"); ?>'
                },
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'month', // Puedes ajustar según la unidad (día, mes, año)
                        tooltipFormat: 'll', // Formato de la fecha en la vista previa
                    }
                    },
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '<?php echo app_lang("month"); ?>'
                    },
                    ticks: {
                        autoSkip: true,
                        maxRotation: 0
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '<?php echo app_lang("number_of_patients"); ?>'
                    },
                    ticks: {
                        beginAtZero: true,
                        max: maxValue + (maxValue * 0.1), // Añadir un 10% extra al valor máximo
                        callback: function(value) {
                            return Math.floor(value) === value ? value : null;
                        }
                    }
                }]
            }
        },
        plugins: [{
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;

                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    if (!meta.hidden) {
                        meta.data.forEach(function(element, index) {
                            // Verificar si el dato es definido antes de usar toString()
                            if (dataset.data[index] !== undefined && dataset.data[
                                    index] !== null) {
                                ctx.fillStyle = 'rgb(78, 94, 106)';
                                var fontSize = 12;
                                var fontStyle = 'semibold';
                                var fontFamily = 'Open Sans';
                                ctx.font = Chart.helpers.fontString(fontSize,
                                    fontStyle, fontFamily);

                                // Convertir a string
                                var dataString = dataset.data[index].toString();

                                // Asegurar que la alineación sea correcta
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle'
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - (
                                    fontSize / 2));
                            }
                        });
                    }
                });
            }
        }]
    };

    totalPatientsChartContent = new Chart(totalPatientsChart, config);
};

var prepareTotalPatientsChart = function() {
    var start_date = document.getElementById("start_date").value;
    var end_date = document.getElementById("end_date").value;
    var clinicId = document.getElementById("clinic_select").value;
    if (start_date && end_date) {
        start_date = formatDateToISO(start_date);
        end_date = formatDateToISO(end_date);
    }

    $.ajax({
        url: "<?php echo get_uri('daily_report/getTotalPatientsData1'); ?>",
        type: 'GET',
        data: {
            clinic_id: clinicId,
            start_date: start_date,
            end_date: end_date
        },
        dataType: "json",
        success: function(response) {
            initTotalPatientsChart(response.monthlyPatients, response.labels);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la llamada AJAX (Pacientes):", textStatus, errorThrown);
        }
    });
};
</script>