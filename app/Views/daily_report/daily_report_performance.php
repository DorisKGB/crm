<div class="col-md-12">
    <h4 class="mt-4"><b>Comparación de Rendimiento</b></h4>
    <p> Elija por favor el <b>Rango de Fecha 1</b> para filtrar los datos... Elija por favor el <b>Rango de Fecha 2</b> para realizar la comparacion de rendimiento.</p>
        <div class="row">
            
                
            <div class="col-md-12 row ">
                <div class="col-md-6 p-3">
                  
                    <div class="row p-2"  style="border:1px solid #ddd;text-align:center;">
                        <p>Rango de Fecha 1</p>
                        <div class="col-md-6">
                            <b></b>
                            <input type="text" id="rang_one_1" class="form-control us-date-input-today fm-r" placeholder="MM/DD/YYYY">
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="rang_one_2" class="form-control us-date-input-today fm-r" placeholder="MM/DD/YYYY">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 p-3" >
                 
                    <div class="row p-2" style="border:1px solid #ddd;text-align:center;">
                        <p>Rango de Fecha 2</p>
                        <div class="col-md-6">
                            <b></b>
                            <input type="text" id="rang_two_1" class="form-control us-date-input-today fm-r" placeholder="MM/DD/YYYY">
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="rang_two_2" class="form-control us-date-input-today fm-r" placeholder="MM/DD/YYYY">
                        </div>
                    </div>
                </div>
                

            </div>

            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8">
                            <?php
                        $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
                        echo form_dropdown(
                        "clinic_select",
                        $clinic_options,
                        $selected_clinic_id,
                        'class="select_graph w-100" id="clinic_id_select" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
                        ); ?>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button class="my-button mx-1" onclick="prepareIncrementTable()"><i class="fas fa-chart-line px-1"></i> Graficar</button>
                        <button class="my_button_danger mx-1" onclick="prepareIncrementTableDelete()"><i class="fas fa-eraser px-1"></i>Borrar</button>
                        <a id="downloadPDF"  href="" class="my_button_n my_button_n_success text-center" ><i class="fas fa-file-download px-1"></i>PDF</a>
                        <button id="downloadXLSX" class="my_button_n btn-success mx-1"> <i class="fas fa-file-download"></i> XLSX</button>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-5 d-none" id="indicateIncrement">
                <table class="table table-bordered table-indicated">
                    <thead>
                        <tr >
                            <th class="text-center" style="background-color: #d4f0fc;"><i class="fas fa-chart-area px-1"></i> INDICADOR DE RENDIMIENTO</th>
                            <th class="text-center" style="background-color: #d4f0fc;">
                                Rango de Fecha 1 <br>
                                <b><span id="start_date_range_01">(01/02/2025)</span> - <span id="start_date_range_02">(02/02/2025)</span></b>
                            </th>
                            <th class="text-center" style="background-color: #d4f0fc;"> 
                                Rango de Fecha 2 <br>
                                <b><span id="start_date_range_11">(03/02/2025)</span> - <span id="start_date_range_12">(04/02/2025)</span></b>
                            </th>
                            <th class="text-center" style="background-color: #d4f0fc;"><i class="fas fa-arrow-down px-1"></i> VARIACIÓN</th>
                            <th class="text-center" style="background-color: #d4f0fc;"><i class="fas fa-percentage px-1"></i> VARIACIÓN %</th>

                        </tr>
                    </thead>
                    <tbody class="text-center" id="table_variant">
                        <tr>
                            <td>Pacientes Nuevos</td>
                            <td id="range_1_patient"></td>
                            <td id="range_2_patient"></td>
                            <td id="range_3_patient"></td>
                            <td id="range_4_patient"></td>
                        </tr>

                        <tr>
                            <td>Pacientes De Seguimiento</td>
                            <td id="range_1_follow"></td>
                            <td id="range_2_follow"></td>
                            <td id="range_3_follow"></td>
                            <td id="range_4_follow"></td>
                        </tr>

                        <tr>
                            <td>Ventas</td>
                            <td id="range_1_sales"></td>
                            <td id="range_2_sales"></td>
                            <td id="range_3_sales"></td>
                            <td id="range_4_sales"></td>
                        </tr>

                        <tr>
                            <td>Referidos</td>
                            <td id="range_1_referred"></td>
                            <td id="range_2_referred"></td>
                            <td id="range_3_referred"></td>
                            <td id="range_4_referred"></td>
                        </tr>

                        <tr>
                            <td>Google & Website</td>
                            <td id="range_1_google"></td>
                            <td id="range_2_google"></td>
                            <td id="range_3_google"></td>
                            <td id="range_4_google"></td>
                        </tr>

                        <tr>
                            <td>Tarjetas & Correo Postal</td>
                            <td id="range_1_email"></td>
                            <td id="range_2_email"></td>
                            <td id="range_3_email"></td>
                            <td id="range_4_email"></td>
                        </tr>

                        <tr>
                            <td>Nos vio al pasar</td>
                            <td id="range_1_walkby"></td>
                            <td id="range_2_walkby"></td>
                            <td id="range_3_walkby"></td>
                             <td id="range_4_walkby"></td>
                        </tr>

                        <tr>
                            <td>Facebook</td>
                            <td id="range_1_facebook"></td>
                            <td id="range_2_facebook"></td>
                            <td id="range_3_facebook"></td>
                            <td id="range_4_facebook"></td>
                        </tr>

                        <tr>
                            <td>Instagram</td>
                            <td id="range_1_instagram"></td>
                            <td id="range_2_instagram"></td>
                            <td id="range_3_instagram"></td>
                            <td id="range_4_instagram"></td>
                        </tr>

                        <tr>
                            <td>Eventos</td>
                            <td id="range_1_events"></td>
                            <td id="range_2_events"></td>
                            <td id="range_3_events"></td>
                            <td id="range_4_events"></td>
                        </tr>

                        <tr>
                            <td>Youtube</td>
                            <td id="range_1_youtube"></td>
                            <td id="range_2_youtube"></td>
                            <td id="range_3_youtube"></td>
                            <td id="range_4_youtube"></td>
                        </tr>

                        <tr>
                            <td>TikTok</td>
                            <td id="range_1_tiktok"></td>
                            <td id="range_2_tiktok"></td>
                            <td id="range_3_tiktok"></td>
                            <td id="range_4_tiktok"></td>
                        </tr>

                        <tr>
                            <td>Radio</td>
                            <td id="range_1_radio"></td>
                            <td id="range_2_radio"></td>
                            <td id="range_3_radio"></td>
                            <td id="range_4_radio"></td>
                        </tr>

                        <tr>
                            <td>Periodico</td>
                            <td id="range_1_periodico"></td>
                            <td id="range_2_periodico"></td>
                            <td id="range_3_periodico"></td>
                            <td id="range_4_periodico"></td>
                        </tr>

                        <tr>
                            <td>Televisión</td>
                            <td id="range_1_tv"></td>
                            <td id="range_2_tv"></td>
                            <td id="range_3_tv"></td>
                            <td id="range_4_tv"></td>
                        </tr>

                        <tr>
                            <td># de pacientes nuevos SIN seguro medico</td>
                            <td id="range_1_uninsured_patients"></td>
                            <td id="range_2_uninsured_patients"></td>
                            <td id="range_3_uninsured_patients"></td>
                            <td id="range_4_uninsured_patients"></td>
                        </tr>

                        <tr>
                            <td># de pacientes nuevos CON seguro medico</td>
                            <td id="range_1_insured_patients"></td>
                            <td id="range_2_insured_patients"></td>
                            <td id="range_3_insured_patients"></td>
                            <td id="range_4_insured_patients"></td>
                        </tr>
                    </tbody>
                </table>
                <?php 
                    /*$indicators = [
                        'new_patients_total' => 'Pacientes Nuevos',
                        'followup_patients_total' => 'Pacientes De Seguimiento',
                        'sales_total' => 'Ventas',
                        'referral_total' => 'Referidos',
                        'google_website_total' => 'Google & Website',
                        'email_total' => 'Tarjetas & Correo Postal',
                        'walkby_total' => 'Nos vio al pasar',
                        'facebook_total' => 'Facebook',
                        'instagram_total' => 'Instagram',
                        'events_total' => 'Eventos',
                        'youtube_total' => 'Youtube',
                        'tiktok_total' => 'TikTok',
                        'radio_total' => 'Radio',
                        'newspaper_total' => 'Periodico',
                        'tv_total' => 'Televisión',
                        'uninsured_patients' => '# de pacientes nuevos SIN seguro medico',
                        'insured_patients' => '# de pacientes nuevos CON seguro medico',
                    ];*/

                    $indicators = [
                        'sales_total' => 'Ventas',
                        'referral_total' => 'Referidos',
                    ];
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <canvas id="grap_new_fw2" class="mb-2">

                        </canvas>
                    </div>
                    <?php foreach ($indicators as $key => $label) {  ?>
                        <div class="col-md-4">
                            <canvas id="<?php echo $key  ?>" class="mb-2">

                            </canvas>
                        </div>
                    <?php } ?>

                    <div class="col-md-6">
                        <canvas id="grap_11_f" class="mt-4">

                        </canvas>
                    </div>

                    <div class="col-md-6">
                        <canvas id="grap_seg" class="mt-4">

                        </canvas>
                    </div>
                </div>
            </div>
            <div class="loading-overlay" id="loading-overlay">
                <div class="spinner"></div>
                <p>Cargando...</p>
            </div>
        </div>
</div>

<script>

/*const indicators = {
    'new_patients_total': 'Pacientes Nuevos',
    'followup_patients_total': 'Pacientes De Seguimiento',
    'sales_total': 'Ventas',
    'referral_total': 'Referidos',
    'google_website_total': 'Google & Website',
    'email_total': 'Tarjetas & Correo Postal',
    'walkby_total': 'Nos vio al pasar',
    'facebook_total': 'Facebook',
    'instagram_total': 'Instagram',
    'events_total': 'Eventos',
    'youtube_total': 'Youtube',
    'tiktok_total': 'TikTok',
    'radio_total': 'Radio',
    'newspaper_total': 'Periodico',
    'tv_total': 'Televisión',
    'uninsured_patients': '# de pacientes nuevos SIN seguro medico',
    'insured_patients': '# de pacientes nuevos CON seguro medico'
};*/

const indicators = {
    'sales_total': 'Ventas',
    'referral_total': 'Referidos',
};

function prepareDrawChart(data,rang1,rang2){
    const colorPalette = generateColorPalette();
    let i = 0;
    Object.entries(indicators).forEach(([key, value]) => {
        const range1Value = data.range_1[key];
        const range2Value = data.range_2[key];
        const color = colorPalette[i];
        if(key == 'sales_total' || key == 'referral_total'){
            drawChart(key, value,[range1Value, range2Value,0],[color.backgroundColor,color.borderColor],[rang1,rang2]);
        }

        i++;   
    });
}

function generateColorPalette(numColors = 20) {
    const colors = [];

    for (let i = 0; i < numColors; i++) {
        // Generar valores RGB aleatorios
        const r = Math.floor(Math.random() * 256);
        const g = Math.floor(Math.random() * 256);
        const b = Math.floor(Math.random() * 256);

        // Crear los colores suaves y fuertes
        colors.push({
            backgroundColor: `rgba(${r}, ${g}, ${b}, 0.2)`, // Color suave
            borderColor: `rgba(${r}, ${g}, ${b}, 1)`        // Color fuerte
        });
    }

    return colors;
}


function generateDatasets(labels, dataValues, colors = []) {
    const datasets = labels.map((label, index) => ({
        label: label, // Etiqueta para el conjunto de datos
        data: dataValues[index], // Valores asociados al conjunto
        backgroundColor: colors[index]?.backgroundColor || 'rgba(0, 123, 255, 0.2)', // Color de fondo
        borderColor: colors[index]?.borderColor || 'rgb(0, 123, 255)', // Color del borde
        borderWidth: 1 // Grosor del borde
    }));
    return datasets;
}

function drawChartGrap(idChart,rang,labelDatasets,dataValue){
    const ctx = document.getElementById(idChart).getContext('2d');
    // Ejemplo de uso
    //const labelDatasets = ['Dataset 1', 'Dataset 2'];
        const colors = [
        { backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgb(255, 99, 132)' },
        { backgroundColor: 'rgba(255, 159, 64, 0.2)', borderColor: 'rgb(255, 159, 64)' },
        { backgroundColor: 'rgba(255, 205, 86, 0.2)', borderColor: 'rgb(255, 205, 86)' },
        { backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgb(75, 192, 192)' },
        { backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgb(54, 162, 235)' },
        { backgroundColor: 'rgba(153, 102, 255, 0.2)', borderColor: 'rgb(153, 102, 255)' },
        { backgroundColor: 'rgba(201, 203, 207, 0.2)', borderColor: 'rgb(201, 203, 207)' },
        { backgroundColor: 'rgba(255, 99, 99, 0.2)', borderColor: 'rgb(255, 99, 99)' },
        { backgroundColor: 'rgba(255, 215, 0, 0.2)', borderColor: 'rgb(255, 215, 0)' },
        { backgroundColor: 'rgba(0, 255, 0, 0.2)', borderColor: 'rgb(0, 255, 0)' },
        { backgroundColor: 'rgba(255, 105, 180, 0.2)', borderColor: 'rgb(255, 105, 180)' }
    ];
    const datasets = generateDatasets(labelDatasets, dataValue, colors);

    const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: rang, // Etiquetas del eje X
                datasets: datasets
            },
            options: {
                responsive: true, // Se adapta al tamaño del contenedor
                plugins: {
                    legend: {
                        display: true, // Muestra la leyenda
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true // Activa los tooltips al pasar el mouse
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true // El eje Y comienza desde 0
                    }
                }
            }
        });
}

function drawChart(chartId,labelName,dataLabel,colors,rang) {
      const data = {
        labels: [rang[0], rang[1]],
        datasets: [{
          label: labelName,
          data: dataLabel, //[12,34]
          backgroundColor: colors[0], //rgba(255, 99, 132, 0.2)
          borderColor: colors[1], //rgba(255, 99, 132, 1)
          borderWidth: 1
        }]
      };

      const config = {
        type: 'bar', // Puedes cambiar a 'line', 'pie', etc.
        data: data,
        options: {
          scales: {
            y: {
                min: 0,
                ticks: {
                    stepSize: 1 // Opcional, define el incremento entre ticks
                }
            }
          }
        }
      };

      const ctx = document.getElementById(chartId).getContext('2d');
      new Chart(ctx, config);
}
    
      // Función para mostrar la ventana de carga
function startLoading() {
    const overlay = document.getElementById("loading-overlay");
    if (overlay) {
        overlay.classList.add("active");
    }
}

// Función para detener la ventana de carga
function stopLoading() {
    const overlay = document.getElementById("loading-overlay");
    if (overlay) {
        overlay.classList.remove("active");
    }
}


    var textIndicatePerformance = function (sum,sign = false) {

        
   
        if(sum === "" || sum == null || sum == undefined){
            
            return "<span><b>0</b></span>";
        }

        if(sum > 0){
            if(sign){
                return "<span class='text-success'><b>+$"+Number(sum).toLocaleString('en-US')+"</b></span>";
            }
            return "<span class='text-success'><b>+"+sum+"</b></span>";
        }

        
        if(sign){
            return "<span class='text-danger'><b>$"+Number(sum).toLocaleString('en-US')+"</b></span>";
        }
        return "<span class='text-danger'><b>"+sum+"</b></span>";
    }


    var prepareIncrementTableDelete = function (){
        startLoading();
        clearTableFields();
        $("#indicateIncrement").addClass('d-none');
        document.getElementById('rang_one_1').value = "";
        document.getElementById('rang_one_2').value = "";
        document.getElementById('rang_two_1').value = "";
        document.getElementById('rang_two_2').value = "";
        stopLoading();
    };


    document.getElementById('downloadPDF').addEventListener('click', function(event) {
    // Evitar que el enlace se ejecute por defecto
    event.preventDefault();

    // Obtener los valores de los campos de entrada
    const r1 = document.getElementById('rang_one_1').value;
    const r2 = document.getElementById('rang_one_2').value;
    const r3 = document.getElementById('rang_two_1').value;
    const r4 = document.getElementById('rang_two_2').value;

    // Validar que todas las fechas estén completas
    if (!r1 || !r2 || !r3 || !r4) {
        alert('Por favor, complete todas las fechas antes de continuar.');
        return; // Detener la ejecución si alguna fecha está incompleta
    }

    // Convertir las fechas al formato ISO
    const r1ISO = formatDateToISO(r1);
    const r2ISO = formatDateToISO(r2);
    const r3ISO = formatDateToISO(r3);
    const r4ISO = formatDateToISO(r4);

    const clinic_id = document.getElementById("clinic_id_select").value;

    // Crear la nueva URL
    const newUrl = `<?php echo get_uri("daily_report/generarPdf") ?>?rang_one_1=${encodeURIComponent(r1ISO)}&rang_one_2=${encodeURIComponent(r2ISO)}&rang_two_1=${encodeURIComponent(r3ISO)}&rang_two_2=${encodeURIComponent(r4ISO)}&clinic_id=${encodeURIComponent(clinic_id)}`;

    // Asignar la nueva URL al enlace
    window.location.href = newUrl;
});

    var  convertDateToAmericanFormat =  function (date) {
        const [year, month, day] = date.split("-");
        return `${month}/${day}/${year}`;
    }

    function formatDateRange(startDate, endDate) {
        // Crear objetos Date a partir de los strings recibidos
        const start = new Date(startDate);
        const end = new Date(endDate);

        // Validar si las fechas son válidas
        if (isNaN(start) || isNaN(end)) {
            throw new Error("Una o ambas fechas no son válidas. Usa el formato YYYY-MM-DD.");
        }

        // Array de meses abreviados
        const months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

        // Formatear las fechas
        const startMonth = months[start.getMonth()];
        const startDay = start.getDate();
        const startYear = start.getFullYear();

        const endMonth = months[end.getMonth()];
        const endDay = end.getDate();
        const endYear = end.getFullYear();

        // Comprobar si las dos fechas están en el mismo año
        if (startYear === endYear) {
            return `${startMonth} ${startDay} - ${endMonth} ${endDay} (${startYear})`;
        } else {
            return `${startMonth} ${startDay} (${startYear}) - ${endMonth} ${endDay} (${endYear})`;
        }
    }
            
    var prepareIncrementTable = function() {

        const r1 = document.getElementById('rang_one_1').value;
        const r2 = document.getElementById('rang_one_2').value;
        const r3 = document.getElementById('rang_two_1').value;
        const r4 = document.getElementById('rang_two_2').value;

        console.log(r1);
        //console.log("Fecha 1 Seleccionada : " + document.getElementById('rang_one_1').value);

        $("#start_date_range_01").text(r1);
        $("#start_date_range_02").text(r2);
        $("#start_date_range_11").text(r3);
        $("#start_date_range_12").text(r4);

        var rang_one_1 = new Date(formatDateToISO(r1));
        var rang_one_2 = new Date(formatDateToISO(r2));
        var rang_two_1 = new Date(formatDateToISO(r3));
        var rang_two_2 = new Date(formatDateToISO(r4));
        var clinic_id = document.getElementById("clinic_id_select").value;

        // Validar los rangos
        if (isNaN(rang_one_1) || isNaN(rang_one_2) || isNaN(rang_two_1) || isNaN(rang_two_2)) {
            alert("Por favor ingrese fechas válidas en todos los campos.");
            return;
        }

        if (rang_one_1 >= rang_one_2) {
            alert("En el Rango de Fecha 1, la fecha inicial debe ser menor que la fecha final.");
            return;
        }

        if (rang_two_1 >= rang_two_2) {
            alert("En el Rango de Fecha 2, la fecha inicial debe ser menor que la fecha final.");
            return;
        }

        if (rang_one_2 >= rang_two_1) {
            alert("El Rango de Fecha 1 debe ser anterior al Rango de Fecha 2.");
            return;
        }

        // Si todas las validaciones son correctas, proceder con la lógica AJAX

        startLoading();
        $.ajax({
            url: "<?php echo get_uri('daily_report/getIndicatePerformance'); ?>",
            type: 'GET',
            data: {
                rang_one_1: rang_one_1.toISOString(),
                rang_one_2: rang_one_2.toISOString(),
                rang_two_1: rang_two_1.toISOString(),
                rang_two_2: rang_two_2.toISOString(),
                clinic_id: clinic_id
            },
            dataType: "json",
            success: function(response) {
                 //initTotalIncomeChart(response.dailyIncome, response.labels);
                 const range1 = response.range_1;
                const range2 = response.range_2;
                //console.log();
                $("#range_1_patient").text(range1.new_patients_total);
                $("#range_2_patient").text(range2.new_patients_total);
                $("#range_3_patient").html(textIndicatePerformance(range2.new_patients_total - range1.new_patients_total));
                $("#range_4_patient").html(textIndicatePerformancePercent(range1.new_patients_total, range2.new_patients_total));

                $("#range_1_sales").text("$"+Number(range1.sales_total).toLocaleString('en-US'));
                $("#range_2_sales").text("$"+Number(range2.sales_total).toLocaleString('en-US'));
                $("#range_3_sales").html(textIndicatePerformance(range2.sales_total - range1.sales_total,true));
                $("#range_4_sales").html(textIndicatePerformancePercent(range1.sales_total, range2.sales_total));

                $("#range_1_follow").text(range1.followup_patients_total);
                $("#range_2_follow").text(range2.followup_patients_total);
                $("#range_3_follow").html(textIndicatePerformance(range2.followup_patients_total - range1.followup_patients_total));
                $("#range_4_follow").html(textIndicatePerformancePercent(range1.followup_patients_total, range2.followup_patients_total));


                $("#range_1_referred").text(range1.referral_total);
                $("#range_2_referred").text(range2.referral_total);
                $("#range_3_referred").html(textIndicatePerformance(range2.referral_total - range1.referral_total));
                $("#range_4_referred").html(textIndicatePerformancePercent(range1.referral_total, range2.referral_total));

                $("#range_1_google").text(range1.google_website_total);
                $("#range_2_google").text(range2.google_website_total);
                $("#range_3_google").html(textIndicatePerformance(range2.google_website_total - range1.google_website_total));
                $("#range_4_google").html(textIndicatePerformancePercent(range1.google_website_total, range2.google_website_total));

                $("#range_1_email").text(range1.email_total);
                $("#range_2_email").text(range2.email_total);
                $("#range_3_email").html(textIndicatePerformance(range2.email_total - range1.email_total));
                $("#range_4_email").html(textIndicatePerformancePercent(range1.email_total, range2.email_total));

                $("#range_1_walkby").text(range1.walkby_total);
                $("#range_2_walkby").text(range2.walkby_total);
                $("#range_3_walkby").html(textIndicatePerformance(range2.walkby_total - range1.walkby_total));
                $("#range_4_walkby").html(textIndicatePerformancePercent(range1.walkby_total, range2.walkby_total));

                $("#range_1_facebook").text(range1.facebook_total);
                $("#range_2_facebook").text(range2.facebook_total);
                $("#range_3_facebook").html(textIndicatePerformance(range2.facebook_total - range1.facebook_total));
                $("#range_4_facebook").html(textIndicatePerformancePercent(range1.facebook_total, range2.facebook_total));

                $("#range_1_instagram").text(range1.instagram_total);
                $("#range_2_instagram").text(range2.instagram_total);
                $("#range_3_instagram").html(textIndicatePerformance(range2.instagram_total - range1.instagram_total));
                $("#range_4_instagram").html(textIndicatePerformancePercent(range1.instagram_total, range2.instagram_total));

                $("#range_1_events").text(range1.events_total);
                $("#range_2_events").text(range2.events_total);
                $("#range_3_events").html(textIndicatePerformance(range2.events_total - range1.events_total));
                $("#range_4_events").html(textIndicatePerformancePercent(range1.events_total, range2.events_total));

                $("#range_1_youtube").text(range1.youtube_total);
                $("#range_2_youtube").text(range2.youtube_total);
                $("#range_3_youtube").html(textIndicatePerformance(range2.youtube_total - range1.youtube_total));
                $("#range_4_youtube").html(textIndicatePerformancePercent(range1.youtube_total, range2.youtube_total));

                $("#range_1_tiktok").text(range1.tiktok_total);
                $("#range_2_tiktok").text(range2.tiktok_total);
                $("#range_3_tiktok").html(textIndicatePerformance(range2.tiktok_total - range1.tiktok_total));
                $("#range_4_tiktok").html(textIndicatePerformancePercent(range1.tiktok_total, range2.tiktok_total));

                $("#range_1_radio").text(range1.radio_total);
                $("#range_2_radio").text(range2.radio_total);
                $("#range_3_radio").html(textIndicatePerformance(range2.radio_total - range1.radio_total));
                $("#range_4_radio").html(textIndicatePerformancePercent(range1.radio_total, range2.radio_total));

                $("#range_1_tv").text(range1.tv_total);
                $("#range_2_tv").text(range2.tv_total);
                $("#range_3_tv").html(textIndicatePerformance(range2.tv_total - range1.tv_total));
                $("#range_4_tv").html(textIndicatePerformancePercent(range1.tv_total, range2.tv_total));

                $("#range_1_periodico").text(range1.newspaper_total);
                $("#range_2_periodico").text(range2.newspaper_total);
                $("#range_3_periodico").html(textIndicatePerformance(range2.newspaper_total - range1.newspaper_total));
                $("#range_4_periodico").html(textIndicatePerformancePercent(range1.newspaper_total, range2.newspaper_total));

                $("#range_1_uninsured_patients").text(range1.uninsured_patients);
                $("#range_2_uninsured_patients").text(range2.uninsured_patients);
                $("#range_3_uninsured_patients").html(textIndicatePerformance(range2.uninsured_patients - range1.uninsured_patients));
                $("#range_4_uninsured_patients").html(textIndicatePerformancePercent(range1.uninsured_patients, range2.uninsured_patients));

                
                $("#range_1_insured_patients").text(range1.insured_patients);
                $("#range_2_insured_patients").text(range2.insured_patients);
                $("#range_3_insured_patients").html(textIndicatePerformance(range2.insured_patients - range1.insured_patients));
                $("#range_4_insured_patients").html(textIndicatePerformancePercent(range1.insured_patients, range2.insured_patients));

                //mostrar fecha
         
                /*$("#start_date_range_01").text(convertDateToAmericanFormat($('#rang_one_1').value));
                $("#start_date_range_02").text(convertDateToAmericanFormat($('#rang_one_2').value));
                $("#start_date_range_11").text(convertDateToAmericanFormat($('#rang_two_1').value));
                $("#start_date_range_12").text(convertDateToAmericanFormat($('#rang_two_2').value));*/

                const rang1 = formatDateRange(r1,r2);
                const rang2 = formatDateRange(r3,r4);
                prepareDrawChart(response,rang1,rang2); 
               
                $("#indicateIncrement").removeClass('d-none');

                //Grafica Pacientes Nuevos y Seguimientos grap_11_f
                const dataValue = [
                    [range1.new_patients_total,range2.new_patients_total,0],
                    [range1.followup_patients_total,range2.followup_patients_total,0]
                ];
                drawChartGrap('grap_new_fw2',[rang1,rang2],['Pacientes Nuevos' , 'Paciente Seguimiento'],dataValue);

                //Grafica de Facebook hasta Tv
                const dataValueFacebook = [
                    [range1.google_website_total,range2.google_website_total,0],
                    [range1.email_total,range2.email_total,0],
                    [range1.walkby_total,range2.walkby_total,0],
                    [range1.facebook_total,range2.facebook_total,0],
                    [range1.instagram_total,range2.instagram_total,0],
                    [range1.events_total,range2.events_total,0],
                    [range1.youtube_total,range2.youtube_total,0],
                    [range1.tiktok_total,range2.tiktok_total,0],
                    [range1.radio_total,range2.radio_total,0],
                    [range1.newspaper_total,range2.newspaper_total,0],
                    [range1.tv_total,range2.tv_total,0],
                ];

                const LabelValue = [
                    'Google & Website',
                    'Tarjetas & Correo Postal	',
                    'Nos vio al pasar	',
                    'Facebook',
                    'Instagram',
                    'Eventos',
                    'Youtube',
                    'TikTok',
                    'Radio',
                    'Periodico',
                    'Televisión'
                ];

                drawChartGrap('grap_11_f',[rang1,rang2],LabelValue,dataValueFacebook);


                //Grafica de Pacientes con Seguros y Sin Seguros
                //Grafica de Facebook hasta Tv
                const dataValueSecurity = [
                    [range1.uninsured_patients,range2.uninsured_patients,0],
                    [range1.insured_patients,range2.insured_patients,0],
                ];

                const LabelValueSecurity = [
                    '# de pacientes nuevos SIN seguro medico',
                    '# de pacientes nuevos CON seguro medico	',
                ];

                drawChartGrap('grap_seg',[rang1,rang2],LabelValueSecurity,dataValueSecurity);

                stopLoading();

            },
            error: function(error) {
                alert("Hubo un problema al procesar los datos. Inténtelo de nuevo.");
            }
        });
    };



    function clearCanvasById(canvasIds) {
        canvasIds.forEach(canvasId => {
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        });
    }

    function formatDateToISO(mdY) {
        if (!mdY) return "";
        const [m, d, y] = mdY.split("/");
        if (!y || !m || !d) return "";
        return `${y.padStart(4,"0")}-${m.padStart(2,"0")}-${d.padStart(2,"0")}`;
    }

    function clearTableFields() {
        // Limpia los campos de la tabla
        const tbody = document.getElementById('table_variant');
        const tableFields = tbody.querySelectorAll('[id]');
        
        tableFields.forEach(field => {
            field.textContent = ''; // Limpia el contenido del elemento
        });

        // Limpia los lienzos de la tabla y los lienzos específicos
        const canvasIds = ['grap_new_fw2', 'grap_11_f', 'grap_seg'];
        clearCanvasById(canvasIds);

        // Limpia los lienzos de los indicadores
        Object.keys(indicators).forEach(key => {
            clearCanvasById([key]);
        });
    }

        document.getElementById('downloadXLSX').addEventListener('click', function(event) {
        // Obtener los valores de los inputs
        var rang_one_1 = document.getElementById('rang_one_1').value;
        var rang_one_2 = document.getElementById('rang_one_2').value;
        var rang_two_1 = document.getElementById('rang_two_1').value;
        var rang_two_2 = document.getElementById('rang_two_2').value;
        var clinic_id = document.getElementById('clinic_id_select').value;

        // Validar que todos los campos estén llenos
        if (rang_one_1 === "" || rang_one_2 === "" || rang_two_1 === "" || rang_two_2 === "" || clinic_id === "") {
            // Si algún campo está vacío, mostrar un mensaje y evitar que la acción se realice
            alert("Por favor, completa todos los campos antes de continuar.");
            event.preventDefault();  // Detener la acción del click
        } else {
            // Si todos los campos están llenos, construir la URL con los parámetros GET
            //var url = "http://localhost/crm/index.php/daily_report/exportExcelPerformanceComparison";
            var url = "./daily_report/exportExcelPerformanceComparison";
            url += "?rang_one_1=" + formatDateToISO(rang_one_1);
            url += "&rang_one_2=" + formatDateToISO(rang_one_2);
            url += "&rang_two_1=" + formatDateToISO(rang_two_1);
            url += "&rang_two_2=" + formatDateToISO(rang_two_2);
            url += "&clinic_id=" + clinic_id;

            // Redirigir a la URL para iniciar la descarga
            window.location.href = url;
        }
    });

    /*var textIndicatePerformancePercent = function (value1, value2) {
        if (value1 === 0 || value1 === "" || value1 == null) {
            if (value2 > 0) return "<span class='text-success'><b>+∞%</b></span>";
            return "<span><b>0.0%</b></span>";
        }
        
        const percent = ((value2 - value1) / value1) * 100;
        const roundedPercent = percent.toFixed(1);
        
        if (roundedPercent > 0) {
            return "<span class='text-success'><b>+" + roundedPercent + "%</b></span>";
        } else if (roundedPercent < 0) {
            return "<span class='text-danger'><b>" + roundedPercent + "%</b></span>";
        }
        return "<span><b>0.0%</b></span>";
    }*/

     /*var textIndicatePerformancePercent = function (value1, value2) {
        // Normalizar valores
        value1 = parseFloat(value1) || 0;
        value2 = parseFloat(value2) || 0;

        // Caso especial: antes era 0 y ahora hay valor
        if (value1 === 0 && value2 > 0) {
            return "<span class='text-success'><b>Nuevo</b></span>";
        }

        // Caso especial: antes y ahora es 0
        if (value1 === 0 && value2 === 0) {
            return "<span><b>0.0%</b></span>";
        }

        // Calcular variación en %
        const percent = ((value2 - value1) / value1) * 100;
        const roundedPercent = percent.toFixed(1);

        // Formato visual
        if (roundedPercent > 0) {
            return "<span class='text-success'><b>+" + roundedPercent + "%</b></span>";
        } else if (roundedPercent < 0) {
            return "<span class='text-danger'><b>" + roundedPercent + "%</b></span>";
        }
        return "<span><b>0.0%</b></span>";
    }*/

    var textIndicatePerformancePercent = function (value1, value2) {
        // Normalizar valores
        value1 = parseFloat(value1) || 0;
        value2 = parseFloat(value2) || 0;

        // Caso especial: antes era 0 y ahora hay valor
        if (value1 === 0 && value2 > 0) {
            return "<span class='text-success'><b>+100%</b></span>"; // Tope máximo 100%
        }

        // Caso especial: antes y ahora es 0
        if (value1 === 0 && value2 === 0) {
            return "<span><b>0.0%</b></span>";
        }

        // Calcular variación en %
        let percent = ((value2 - value1) / value1) * 100;

        // Limitar a máximo 100%
        if (percent > 100) {
            percent = 100;
        }

        const roundedPercent = percent.toFixed(1);

        // Formato visual
        if (roundedPercent > 0) {
            return "<span class='text-success'><b>+" + roundedPercent + "%</b></span>";
        } else if (roundedPercent < 0) {
            return "<span class='text-danger'><b>" + roundedPercent + "%</b></span>";
        }
        return "<span><b>0.0%</b></span>";
    }

   

</script>