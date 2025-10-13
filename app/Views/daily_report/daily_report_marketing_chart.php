<div class="pt-3 ps-3">
    <div class="pt-2"><?php echo app_lang("chart_marketing_report_redes"); ?></div>
    <canvas id="total-platforms-chart-especific"
        style="width: 100%; min-height: 60px; margin-left: -10px; display: none;"></canvas>
    <div id="no-data-total-platforms-especific" style="display: none;" class="py-5 mt-4 text-center border">
        <?php echo app_lang("no_data_to_chart"); ?></div>
</div>

<script type="text/javascript">
var totalPlatformsChartContentEspecific;

var initTotalPlatformsChartEspecific = function(platformsData) {
    var totalPlatformsChart = document.getElementById("total-platforms-chart-especific");
    var noDataTotalPlatforms = document.getElementById("no-data-total-platforms-especific");

    // Verificación y manejo de valores null
    if (!platformsData || Object.values(platformsData).every(item => item === 0)) {
        totalPlatformsChart.style.display = 'none';
        noDataTotalPlatforms.style.display = 'block';
        return;
    } else {
        totalPlatformsChart.style.display = 'block';
        noDataTotalPlatforms.style.display = 'none';
    }

    // Destruir cualquier gráfico previo
    if (totalPlatformsChartContentEspecific) {
        totalPlatformsChartContentEspecific.destroy();
    }

    const labels = ['google','mail','facebook','instagram','youtube','tiktok','radio','newspaper'];
    const data = {
    labels: labels,
    datasets: [{
        label: '<?php echo app_lang("social_link_text"); ?>',
        data: [
        platformsData.google, 
        platformsData.mail,
        platformsData.facebook,
        platformsData.instagram,
        platformsData.youtube,
        platformsData.tiktok,
        platformsData.radio,
        platformsData.newspaper,
        0
    ],
        backgroundColor: [
        'rgba(255, 99, 132, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(255, 205, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(201, 203, 207, 0.2)',
        'rgba(0, 0, 0, 0.2)'
        ],
        borderColor: [
        'rgb(255, 99, 132)',
        'rgb(255, 159, 64)',
        'rgb(255, 205, 86)',
        'rgb(75, 192, 192)',
        'rgb(54, 162, 235)',
        'rgb(153, 102, 255)',
        'rgb(201, 203, 207)',
        'rgb(0, 0, 0)',
        ],
        borderWidth: 1
    }]
    };

    const maxValue = Math.max(platformsData.internetSum || 0, platformsData.walkingSum || 0, platformsData
        .referredSum || 0);
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
                    display: true, // Mostrar la leyenda con los nombres de las categorías
                },
                title: {
                    display: true,
                    text: '<?php echo app_lang("chart_total_platforms"); ?>'
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: '<?php echo app_lang("platforms"); ?>'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: '<?php echo app_lang("number_of_patients"); ?>'
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

    totalPlatformsChartContentEspecific = new Chart(totalPlatformsChart, config);
    console.log("Gráfica de plataformas inicializada.");
};

var prepareTotalPlatformsChartEspecific = function() {
    var start_date = document.getElementById("start_date").value;
    var end_date = document.getElementById("end_date").value;
    var clinicId = document.getElementById("clinic_select").value;
    if (start_date && end_date) {
        start_date = formatDateToISO(start_date);
        end_date = formatDateToISO(end_date);
    }

    $.ajax({
        url: "<?php echo get_uri('daily_report/getPlatformsDataEspecific'); ?>",
        type: 'GET',
        data: {
            clinic_id: clinicId,
            start_date: start_date,
            end_date: end_date
        },
        dataType: "json",
        success: function(response) {
            console.log("Especific");
            console.log(response);
            initTotalPlatformsChartEspecific(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la llamada AJAX (Plataformas):", textStatus, errorThrown);
        }
    });
};

/*$(document).ready(function() {
  prepareTotalPlatformsChart();
  document.getElementById("clinic_select").addEventListener("change", prepareTotalPlatformsChart);
});*/
</script>