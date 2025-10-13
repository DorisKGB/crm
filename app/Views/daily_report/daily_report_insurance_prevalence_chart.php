<div class="pt-3 ps-3">
    <div class="pt-2"><?php echo app_lang("chart_insurance_prevalence"); ?></div>
    <canvas id="insurance-prevalence-chart"
        style="width: 100%; min-height: 60px; margin-left: -10px; display: none;"></canvas>
    <div id="no-data-insurance-prevalence" style="display: none;" class="py-5 mt-4 text-center border">
        <?php echo app_lang("no_data_to_chart"); ?></div>
</div>


<script type="text/javascript">
var initInsurancePrevalenceChart = function(insuranceData) {
    var insurancePrevalenceChart = document.getElementById("insurance-prevalence-chart");
    var noDataInsurancePrevalence = document.getElementById("no-data-insurance-prevalence");

    console.log(insuranceData);
    // Verificación y manejo de valores null
    if ((insuranceData.insuredSum == null && insuranceData.uninsuredSum == null) || Object.values(insuranceData)
        .every(item => item === 0)) {


        insurancePrevalenceChart.style.display = 'none';
        noDataInsurancePrevalence.style.display = 'block';
        return;
    } else {
        insurancePrevalenceChart.style.display = 'block';
        noDataInsurancePrevalence.style.display = 'none';
    }

    // Destruir cualquier gráfico previo si existe
    if (window.insurancePrevalenceChartContent) {
        window.insurancePrevalenceChartContent.destroy();
    }

    // Colores para cada categoría
    const bgColors = ['rgba(243, 81, 202, 0.27)', 'rgba(105, 9, 116, 0.28)'];
    const borderColors = ['rgba(233, 120, 205, 0.56)', 'rgba(230, 131, 241, 0.72)'];

    // Datos del gráfico
    const data = {
        labels: ['Insured Patients', 'Uninsured Patients'], // Etiquetas para las categorías
        datasets: [{
                label: 'Insured Patients',
                data: [insuranceData.insuredSum || 0, null], // Datos para asegurados
                backgroundColor: bgColors[0],
                borderColor: borderColors[0],
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false,
            },
            {
                label: 'Uninsured Patients',
                data: [null, insuranceData.uninsuredSum || 0], // Datos para no asegurados
                backgroundColor: bgColors[1],
                borderColor: borderColors[1],
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false,
            }
        ]
    };

    const maxValue = Math.max(insuranceData.insuredSum || 0, insuranceData.uninsuredSum || 0);
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
                    display: true, // Mostrar la leyenda
                    position: 'top', // Mostrar la leyenda arriba
                    labels: {
                        usePointStyle: true // Usar punto en lugar de cajas
                    }
                },
                title: {
                    display: true,
                    text: 'Insurance Prevalence' // Título del gráfico
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Insurance Status' // Título del eje X
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Number of Patients' // Título del eje Y
                    },
                    ticks: {
                        beginAtZero: true,
                        max: maxValue + (maxValue * 0.1), // Añadir un 10% extra al valor máximo
                        callback: function(value) {
                            return Math.floor(value) === value ? value : null;
                        }
                    }
                }
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
                                // Dibujar el texto en negro, con la fuente especificada
                                ctx.fillStyle = 'rgb(78, 94, 106)';
                                var fontSize = 12;
                                var fontStyle = 'normal';
                                var fontFamily = 'Open Sans';
                                ctx.font = Chart.helpers.fontString(fontSize,
                                    fontStyle, fontFamily);

                                // Convertir a string
                                var dataString = dataset.data[index].toString();

                                // Asegurar que la alineación sea correcta
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
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

    window.insurancePrevalenceChartContent = new Chart(insurancePrevalenceChart, config);
};

var prepareInsurancePrevalenceChart = function() {
    var start_date = document.getElementById("start_date").value;
    var end_date = document.getElementById("end_date").value;
    var clinicId = document.getElementById("clinic_select").value;
    if (start_date && end_date) {
        start_date = formatDateToISO(start_date);
        end_date = formatDateToISO(end_date);
    }

    $.ajax({
        url: "<?php echo get_uri('daily_report/getInsurancePrevalenceData1'); ?>",
        type: "GET",
        data: {
            clinic_id: clinicId,
            start_date: start_date,
            end_date: end_date
        },
        dataType: "json",
        success: function(response) {
            // Verifica que la respuesta tiene los datos esperados
            if (response && response.insuranceData && response.insuranceData.length === 2) {
                // Asumiendo que insuranceData contiene los valores de asegurados y no asegurados
                var insuranceData = response.insuranceData;

                // Llamar a la función para inicializar el gráfico con los datos
                initInsurancePrevalenceChart({
                    insuredSum: insuranceData[0], // Asegurados
                    uninsuredSum: insuranceData[1] // No asegurados
                });
            } else {
                console.error("Datos inesperados recibidos:", response);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX:", textStatus, errorThrown);
        }
    });
};
</script>