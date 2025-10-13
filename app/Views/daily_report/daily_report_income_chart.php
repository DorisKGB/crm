<div class="pt-2 ps-3">
    <div class="pt-2"><?php echo app_lang("chart_total_income"); ?></div>
    <canvas id="total-income-chart" style="width: 100%; min-height: 60px; margin-left: -10px; display: none;"></canvas>
    <div id="no-data-total-income" style="display: none;" class="py-5 mt-4 text-center border">
        <?php echo app_lang("no_data_to_chart"); ?></div>
</div>

<script type="text/javascript">
var totalIncomeChartContent;

var initTotalIncomeChart = function(dailyIncome, labels) {

    var totalIncomeChart = document.getElementById("total-income-chart");
    var noDataTotalIncome = document.getElementById("no-data-total-income");

    // Verificación de valores null y arreglo vacío
    if (!dailyIncome || dailyIncome.length === 0 || !labels || labels.length === 0) {
        totalIncomeChart.style.display = 'none';
        noDataTotalIncome.style.display = 'block';
        return;
    } else {
        totalIncomeChart.style.display = 'block';
        noDataTotalIncome.style.display = 'none';
    }

    if (totalIncomeChartContent) {
        totalIncomeChartContent.destroy();
    }

    // Ordenar las fechas y los datos de acuerdo a las fechas
    const orderedData = labels.map((label, index) => ({
        label,
        data: dailyIncome[index]
    }));

    // Ordena las fechas y los datos en orden ascendente
    orderedData.sort((a, b) => new Date(a.label) - new Date(b.label));

    // Después de ordenar, separa los valores ordenados
    const sortedLabels = orderedData.map(item => item.label);
    const sortedData = orderedData.map(item => item.data);

    const data = {
        labels: labels.map(function(value) {
            const parts = value.split('-');
            const year = parts[0];
            const monthIndex = parseInt(parts[1], 10);
            const day = parseInt(parts[2], 10); // Convertir el día a entero para manipularlo

            // Eliminar la línea que resta un día
            const date = new Date(year, monthIndex - 1, day);


            //Obtener el dia
            const dayOfWeek = new Intl.DateTimeFormat('es-ES', { weekday: 'long' }).format(date);

            // Reconvertir a formato 'DD MMM YYYY'
            const dayAdjusted = String(date.getDate()).padStart(2, '0');
            const monthNames = [
                "<?php echo app_lang('short_january'); ?>",
                "<?php echo app_lang('short_february'); ?>",
                "<?php echo app_lang('short_march'); ?>",
                "<?php echo app_lang('short_april'); ?>",
                "<?php echo app_lang('short_may'); ?>",
                "<?php echo app_lang('short_june'); ?>",
                "<?php echo app_lang('short_july'); ?>",
                "<?php echo app_lang('short_august'); ?>",
                "<?php echo app_lang('short_september'); ?>",
                "<?php echo app_lang('short_october'); ?>",
                "<?php echo app_lang('short_november'); ?>",
                "<?php echo app_lang('short_december'); ?>"
            ];
            const month = monthNames[monthIndex - 1];
            const dayFormatted = dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1);

            // Devolver la fecha ajustada
            return `${dayFormatted}, ${dayAdjusted} ${month} ${year}`;
        }),
        datasets: [{
            label: "<?php echo app_lang('daily_income'); ?>",
            borderColor: '#32A483',
            backgroundColor: 'rgba(50, 164, 131, 0.2)',
            borderWidth: 2,
            fill: true,
            data: dailyIncome,
            pointRadius: 3,
            pointBackgroundColor: '#32A483'
        }]
    };

    const maxValue = Math.max(...dailyIncome || [0]);

    const config = {
        type: 'line',
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
                        top: 2 // Añadir padding superior
                    }
                },
                title: {
                    display: true,
                    text: '<?php echo app_lang("daily_income"); ?>'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            // Formateo el valor en dólares con el signo y separador de miles
                            var value = context.parsed.y;
                            label += '$' + value.toLocaleString('en-US');
                            return label;
                        }
                    }
                }
            },
            scales: {
            x: {
                type: 'time', // Define el tipo de eje como tiempo
                time: {
                    unit: 'day', // Puedes ajustar la unidad (día, mes, año) según tus necesidades
                    tooltipFormat: 'll', // Formato de la fecha en la vista previa del tooltip
                },
                display: true,
                title: {
                    display: true,
                    text: '<?php echo app_lang("date"); ?>'
                },
                ticks: {
                    autoSkip: true,
                    maxRotation: 0
                }
            },
            y: {
                display: true,
                title: {
                    display: true,
                    text: '<?php echo app_lang("total_income"); ?>'
                },
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {
                        // Aseguramos que el valor sea un número y formateamos con el signo $ y separador de miles
                        if (typeof value === 'number') {
                            return '$' + value.toLocaleString('en-US');
                        }
                        return value;
                    }
                }
            }
            }
        }
    };

    totalIncomeChartContent = new Chart(totalIncomeChart, config);
};

var prepareTotalIncomeChart = function() {
    var start_date = document.getElementById("start_date").value;
    var end_date = document.getElementById("end_date").value;
    var clinicId = document.getElementById("clinic_select").value;
    if (start_date != "" && end_date != "") {
        start_date = formatDateToISO(start_date);
        end_date = formatDateToISO(end_date);
    }

    $.ajax({
        url: "<?php echo get_uri('daily_report/getTotalIncomeData1'); ?>",
        type: 'GET',
        data: {
            clinic_id: clinicId,
            start_date: start_date,
            end_date: end_date
        },
        dataType: "json",
        success: function(response) {
            initTotalIncomeChart(response.dailyIncome, response.labels);
            console.log(response.labels);
            console.log(response.dailyIncome);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la llamada AJAX (Ingresos):", textStatus, errorThrown);
        }
    });
};
</script>

<style>
@media only screen and (max-width: 768px) {
    #total-income-chart {
        min-height: 300px;
        /* Ajusta este valor según sea necesario */
    }
}
</style>