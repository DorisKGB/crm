<div class="pt-2 ps-3">
    <div class="pt-2"><?php echo app_lang("deposit_moth"); ?></div>
    <canvas id="total-deposits-chart"
        style="width: 100%; min-height: 60px; margin-left: -10px; display: none;"></canvas>
    <div id="no-data-total-deposits" style="display: none;" class="py-5 mt-4 text-center border">
        <?php echo app_lang("no_data_to_chart"); ?></div>
</div>

<script type="text/javascript">
var totalDepositsChartContent;

var initTotalDepositsChart = function(monthlyDeposits, labels) {
    var totalDepositsChart = document.getElementById("total-deposits-chart");
    var noDataTotalDeposits = document.getElementById("no-data-total-deposits");

    // Comprobar si hay datos disponibles
    if (!monthlyDeposits || monthlyDeposits.length === 0 || !labels || labels.length === 0) {
        totalDepositsChart.style.display = 'none';
        noDataTotalDeposits.style.display = 'block';
        return;
    } else {
        totalDepositsChart.style.display = 'block';
        noDataTotalDeposits.style.display = 'none';
    }

    // Si ya existe un gráfico, destruirlo para crear uno nuevo
    if (totalDepositsChartContent) {
        totalDepositsChartContent.destroy();
    }

    // Configurar los datos para el gráfico
    const data = {
        labels: labels, // Usar las etiquetas como las recibimos
        datasets: [{
            label: "<?php echo app_lang('deposit_moth'); ?>",
            data: monthlyDeposits || [0],
            borderColor: 'rgba(6, 105, 39, 0.76)',
            backgroundColor: 'rgba(8, 224, 80, 0.25)',
            borderWidth: 2,
            borderRadius: 5,
            borderSkipped: false,
        }]
    };

    // Obtener el valor máximo de los depósitos
    const maxValue = Math.max(...(monthlyDeposits || [0]));
    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            layout: {
                padding: {
                    top: 20
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    padding: {
                        top: 2
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo app_lang("chart_total_deposits"); ?>'
                },
            },
            scales: {
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
                        labelString: '<?php echo app_lang("number_of_deposits"); ?>'
                    },
                    ticks: {
                        beginAtZero: true,
                        max: maxValue + (maxValue * 0.1),
                        callback: function(value) {
                            return Math.floor(value) === value ? value : null;
                        }
                    }
                }]
            }
        }
    };

    // Crear el gráfico
    totalDepositsChartContent = new Chart(totalDepositsChart, config);
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
        url: "<?php echo get_uri('deposit_report/getTotalPatientsData1'); ?>",
        type: 'GET',
        data: {
            clinic_id: clinicId,
            start_date: start_date,
            end_date: end_date
        },
        dataType: "json",
        success: function(response) {
            console.log(response);
            initTotalDepositsChart(response.monthlyDeposit, response.labels);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la llamada AJAX (Depósitos):", textStatus, errorThrown);
        }
    });
};
</script>
