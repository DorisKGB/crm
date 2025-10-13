<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="d-flex row pb-4 align-items-end gap-2">
            <p class="fw-bold pb-2"><?php echo app_lang("filter_by_date"); ?></p>

            <div class="col-md-4">
                <?php
                $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
                $clinic_options = ['' => app_lang("select_clinic_one")] + $clinic_options;
                echo form_dropdown(
                    "clinic_select",
                    $clinic_options,
                    $selected_clinic_id,
                    'class="select_graph w-100 form-control" id="clinic_select" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
                ); ?>
            </div>

            <div class="col-md-2">
                <label for="start_date"><?php echo app_lang("start_date"); ?></label>
                <input type="text" id="start_date" class="form-control us-date-input-today" placeholder="MM/DD/YYYY">
            </div>

            <div class="col-md-2">
                <label for="end_date"><?php echo app_lang("end_date"); ?></label>
                <input type="text" id="end_date" class="form-control us-date-input-today" placeholder="MM/DD/YYYY">
            </div>

            <div class="col-md-2">
                <button id="filter_dates" class="btn btn-primary"><i class="fas fa-chart-line px-1"></i> <?php echo app_lang("filter"); ?></button>
                <button id="filter_clean" class="btn btn-danger">
                    <i class="fas fa-eraser px-1"></i>
                </button>
            </div>

        </div>

        <div class="bg-danger text-white p-2 mb-2" role="alert"
            style="display:none; color:#fff !important;border-radius:5px;" id="date_error">
            <small><?php echo app_lang("date_start_error"); ?></small>
        </div>

        <div id="daily-report-list" class="table-responsive">
            <table id="daily-report-table" class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo app_lang("report_id"); ?></th>
                        <th><?php echo app_lang("report_date"); ?></th>
                        <th><?php echo app_lang("clinic_id"); ?></th>
                        <th><?php echo app_lang("clinic_name"); ?></th>
                        <th><?php echo app_lang("depositor"); ?></th>
                        <th><?php echo app_lang("depositor_amount"); ?></th>
                        <th><?php echo app_lang("report_date_start"); ?></th>
                        <th><?php echo app_lang("report_date_end"); ?></th>
                        <th><?php echo app_lang("register_electronic"); ?></th>
                        <th><?php echo app_lang("deposit_receipt_scan"); ?></th>

                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se llenarán a través de DataTables -->
                </tbody>

            </table>
        </div>
    </div>
</div>
<style>
    .large-input {
        width: 100px;
        /* Hace el input tan ancho como la celda */
        height: 40px;
        /* Establece la altura */
        font-size: 16px;
        /* Tamaño de la fuente */
        padding: 8px;
        text-align: center;
        /* Relleno interno para hacerlo más espacioso */
    }

    .signature-column {
        background-color: #b9b9b9 !important;
    }

    .signature-icon {
        cursor: pointer;
        color: #007bff;
        font-size: 18px;
    }

    .signature-icon:hover {
        color: #0056b3;
    }
    .signature_electronic{
    text-align: center;
    font-size: 20px !important;
    color:rgb(62, 63, 63);
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
    font-style: italic;
  }
</style>

<!-- Modal -->
<div id="signatureModal" class="modal fade" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Firma Electrónica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <p id="signatureText" class="signature_electronic"></p> <!-- Aquí se mostrará el texto de la firma electrónica -->
            </div>
        </div>
    </div>
</div>


<div class="container">
    <table class="table ">
        <tfoot>
            <tr>
                <th class="text-center" style="background-color:rgb(238, 241, 243);" colspan="2"><?php echo app_lang("num_deposit"); ?></th>
                <th class="text-center" id="num_deposit"></th>
                <th class="text-center" style="background-color:rgb(238, 241, 243);" colspan="2"><?php echo app_lang('total_amount_deposit'); ?></th>
                <th class="text-center" id="total_amount_deposit"></th>
            </tr>
        </tfoot>
    </table>
</div>

<script type="text/javascript">
    function showSignatureModal(element) {
        let signatureText = element.getAttribute("data-signature"); // Obtiene el texto de la firma
        document.getElementById("signatureText").innerText = signatureText; // Coloca el texto en el modal
        let modal = new bootstrap.Modal(document.getElementById("signatureModal"));
        modal.show();
    }

    var DATATABLE_FILL = [];
    $(document).ready(function() {
        var isMobile = window.matchMedia("only screen and (max-width: 768px)").matches;

        var table = $("#daily-report-table").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo_uri('deposit_report/list_data') ?>",
                "type": "POST",
                "data": function(d) {
                    const startDate = $('#start_date').val();
                    const endDate = $('#end_date').val();
                    if (startDate && endDate) {
                        d.startDate = formatDateToISO(startDate);
                        d.endDate = formatDateToISO(endDate);
                    }
                    d.clinicId = $('#clinic_select').val();
                },
                "dataSrc": function(json) {
                    const amount_total = json.totalData.data;

                    $("#total_amount_deposit").text("$" + Number(amount_total[0]).toLocaleString('en-US'));
                    $("#num_deposit").text(amount_total[1]);
                    return json.data; // Asegúrate de devolver los datos correctamente
                }
            },
            "paging": true,
            "lengthMenu": [5, 10, 20, 50, 100],
            "pageLength": isMobile ? 10 : 5,
            "scrollX": true,
            "scrollY": isMobile ? "400px" : "",
            "order": [
                [1, "desc"] // Asegúrate de que ordenas por la columna correcta (ej. columna de fecha)
            ],
            "columns": [

                {
                    "data": "report_id"
                }, // Nombre del depositante
                {
                    "data": "deposit_datetime",
                    "render": formatDateColumn2
                },
                {
                    "data": "clinic_id"
                }, // ID del reporte
                {
                    "data": "clinic_name"
                }, // Nombre de la clínica
                {
                    "data": "depositor"
                }, // ID de la clínica
                {
                    "data": "deposit_amount",
                    "render": formatCurrencyColumn
                }, // Monto del depósito
                {
                    "data": "deposit_start_date",
                    "render": formatDateColumn
                },
                {
                    "data": "deposit_end_date",
                    "render": formatDateColumn
                },
                {
                    "data": "signature_electronic",
                    "render": function(data, type, row) {
                        return `
                        <span class="signature-icon" data-signature="${data}" onclick="showSignatureModal(this)">
                            <i class="fas fa-signature"></i> <!-- Ícono de firma de FontAwesome -->
                        </span>
                    `;
                    }
                }, // Firma electrónica
                {
                    "data": "deposit_receipt_scan",
                    "render": function(data) {
                        return renderLinkColumn(data); // Enlaza el archivo si es necesario
                    }
                }
            ],
            "columnDefs": [{
                "targets": [0, 1, 2, 3, 5], // No editable (ID reporte, ID clínica, nombre clínica, archivo)
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('contenteditable', false);
                }
            }],
            "createdRow": function(row, data, dataIndex) {
                $('td', row).each(function(index) {
                    var columnName = $('#daily-report-table').DataTable().column(index).dataSrc();
                    $(this).attr('data-column', columnName); // Agregar el atributo a cada celda
                    $(this).attr('data-column-idx', index);
                });
            }
        });



        // Función para convertir la fecha en formato MM/DD/YYYY
        function formatDateColumn(data, type, row) {
            var date = new Date(data);
            var day = ("0" + date.getUTCDate()).slice(-2);
            var month = ("0" + (date.getUTCMonth() + 1)).slice(-2);
            var year = date.getUTCFullYear();
            return month + "/" + day + "/" + year;
        }

        function formatDateColumn2(data, type, row) {
            var date = new Date(data);

            var daysOfWeek = [
                "domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"
            ];

            var months = [
                "enero", "febrero", "marzo", "abril", "mayo", "junio",
                "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
            ];

            var dayOfWeek = daysOfWeek[date.getUTCDay()];
            var day = date.getUTCDate();
            var month = months[date.getUTCMonth()];
            var hours = date.getUTCHours();
            var year = date.getUTCFullYear();
            var minutes = ("0" + date.getUTCMinutes()).slice(-2);

            // Convertir a formato de 12 horas
            var period = hours >= 12 ? "pm" : "am";
            hours = hours % 12 || 12; // Convierte "0" en "12" para el formato de 12 horas

            return `${dayOfWeek} ${day} de ${month} ${year}, a las ${hours}:${minutes}${period}`;
        }


        // Función para convertir los valores en formato moneda
        function formatCurrencyColumn(data, type, row) {
            return "<?php echo app_lang('sign_money'); ?>" + Number(data).toLocaleString('en-US');
        }

        // Función para generar enlaces de archivos
        function renderLinkColumn(data, type, row) {
            return '<a href="' + data + '" target="_blank"><?php echo app_lang("view_file"); ?></a>';
        }

        <?php if ($login_user->is_admin || $login_user->role_id == 7) { ?>

            $('#daily-report-table tbody').on('dblclick', 'td', function() {
                var cell = table.cell(this);
                var originalValue = cell.data();
                var columnIdx = cell.index().column;

                // Columnas no editables
                if ([0, 1, 2, 3].includes(columnIdx)) {
                    return;
                }

                // Convertir fecha si la columna es la de fecha (5)
                if (columnIdx === 5 && originalValue) {
                    // Convertir la fecha al formato MM/DD/YYYY si viene en otro formato
                    let dateObj = new Date(originalValue);
                    if (!isNaN(dateObj)) {
                        originalValue = (dateObj.getMonth() + 1) + "/" + dateObj.getDate() + "/" + dateObj.getFullYear();
                    }
                }

                var inputField;
                if ([7, 8, 9].includes(columnIdx)) { // Columnas de ventas
                    inputField = `<input type="number" class="form-control large-input" value="${originalValue}" style="width: 150px; padding: 5px; font-size: 14px;" />`;
                } else if (columnIdx === 4) { // Columna de fecha
                    var formattedDate = convertToUSFormat(originalValue);
                    inputField = `<input type="text" class="form-control us-date-input-today-now large-input" value="${formattedDate}" style="width: 150px; padding: 5px; font-size: 14px;" />`;

                } else {
                    inputField = `<input type="text" class="form-control large-input" value="${originalValue}" style="width: 200px; padding: 5px; font-size: 14px;" />`;
                }

                // Insertar input en la celda
                $(this).html(inputField);
                var inputElement = $(this).find('input');
                inputElement.focus();



                // Guardar cambios cuando el usuario termine de editar (Enter o perder el foco)
                inputElement.on('blur keypress', function(e) {
                    if (e.type === 'blur' || e.which === 13) { // Enter o perder el foco
                        let input = $(this);
                        let cell2 = input.closest('td');
                        var newValue = $(this).val();

                        var rowData = table.row(cell.index().row).data();
                        let columnName2 = cell2.attr('data-column');
                        let indexColumn = cell2.attr('data-column-idx');

                        if (indexColumn == 4) {
                            newValue = convertToISOFormat2(newValue);
                        }

                        if (indexColumn == 4 && newValue == null) {
                            if (!input.hasClass('error')) {
                                input.addClass('error'); // Agregar una clase para evitar mostrar el alert nuevamente
                                setTimeout(function() {
                                    table.draw();
                                    alert("La fecha no es correcta!");
                                }, 100);
                            }
                            return;
                        }
                        if (indexColumn != 4 && newValue < 0) {
                            if (!input.hasClass('error')) {
                                input.addClass('error'); // Agregar una clase para evitar mostrar el alert nuevamente
                                setTimeout(function() {
                                    table.draw();
                                    alert("No se puede colocar valores negativos.");
                                }, 100);
                            }
                            return;
                        }
                        // Enviar datos al servidor mediante AJAX
                        $.ajax({
                            url: "<?php echo_uri('deposit_report/updateReport') ?>",
                            type: 'POST',
                            data: {
                                id: rowData.id,
                                column: columnName2,
                                value: newValue
                            },
                            success: function(response) {

                                cell.data(newValue).draw();
                            },
                            error: function() {
                                alert('Error de conexión con el servidor.');
                            }
                        });
                    }
                });
            });

        <?php } ?>



        $('#filter_dates').click(function() {
            table.draw();
            //prepareTotalPatientsChart();
        });

        $('#filter_clean').click(function() {
            // Limpiar los campos de fecha y clínica
            $('#start_date').val('');
            $('#end_date').val('');
            $('#clinic_select').val('');

            // También puedes volver a dibujar la tabla si lo deseas
            table.draw();
        });

        selectFill();


        var myModal = new bootstrap.Modal(document.getElementById('confirmModal'), {
            backdrop: 'static', // Evita que se cierre al hacer clic fuera
            keyboard: false // Evita que se cierre con la tecla "Esc"
        });

        // Muestra el modal
        //myModal.show();

        // Cierra el modal solo cuando se presiona el botón "Aceptar"
        $("#btnConfirmModal").click(function() {
            myModal.hide();
        });

    });

    function formatToYMD(dateString) {
        // Dividir la fecha por el separador "/"
        var parts = dateString.split('/');

        // Obtener el año, mes y día
        var month = parts[0]; // El mes está en la primera parte
        var day = parts[1]; // El día está en la segunda parte
        var year = parts[2]; // El año está en la tercera parte

        // Formatear la fecha en formato YYYY-MM-DD
        return year + '-' + month + '-' + day;
    }

    function isValidDate(dateString) {
        // Expresión regular para formato MM/DD/YYYY
        var regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;
        if (!regex.test(dateString)) {
            return false;
        }

        // Verificar si es una fecha real
        var parts = dateString.split('/');
        var month = parseInt(parts[0], 10);
        var day = parseInt(parts[1], 10);
        var year = parseInt(parts[2], 10);

        var dateObj = new Date(year, month - 1, day);
        return (
            dateObj.getFullYear() === year &&
            dateObj.getMonth() + 1 === month &&
            dateObj.getDate() === day
        );
    }


    $('#daily-report-table').on('click', '.btnRow', function() {
        var reportId = $(this).data('id');
        var ROW = getRowById(DATATABLE_FILL, reportId.toString());
    });

    function convertToUSFormat(dateString) {
        // Verificar si la fecha está en el formato correcto (YYYY-MM-DD)
        var dateParts = dateString.split('-');

        if (dateParts.length !== 3) {
            console.error("Fecha inválida. El formato esperado es YYYY-MM-DD.");
            return null;
        }

        var year = dateParts[0];
        var month = dateParts[1];
        var day = dateParts[2];

        // Retornar la fecha en formato MM-DD-YYYY
        return `${month}-${day}-${year}`;
    }

    function convertToISOFormat(dateString) {
        // Verificar si la fecha está en el formato correcto (MM-DD-YYYY)
        var dateParts = dateString.split('-');

        if (dateParts.length !== 3) {
            console.error("Fecha inválida. El formato esperado es MM-DD-YYYY.");
            return null;
        }

        var month = dateParts[0];
        var day = dateParts[1];
        var year = dateParts[2];

        // Retornar la fecha en formato YYYY-MM-DD
        return `${year}-${month}-${day}`;
    }

    function convertToISOFormat2(dateString) {
        // Expresión regular para verificar formato MM-DD-YYYY
        var regex = /^(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])-\d{4}$/;

        if (!regex.test(dateString)) {
            console.error("Fecha inválida. El formato esperado es MM-DD-YYYY.");
            return null;
        }

        // Dividir la fecha
        var dateParts = dateString.split('-');
        var month = parseInt(dateParts[0], 10);
        var day = parseInt(dateParts[1], 10);
        var year = parseInt(dateParts[2], 10);

        // Crear el objeto Date para verificar si la fecha es real
        var dateObj = new Date(year, month - 1, day);
        if (
            dateObj.getFullYear() !== year ||
            dateObj.getMonth() + 1 !== month ||
            dateObj.getDate() !== day
        ) {
            console.error("Fecha inválida. No es una fecha real.");
            return null;
        }

        // Retornar la fecha en formato YYYY-MM-DD
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }
    /*
    //obtener data ID
    $('#daily-report-table').on('click', '.btnRow', function() {
        var reportId = $(this).data('id');
        var ROW = getRowById(DATATABLE_FILL, reportId.toString());
        $(".addReport").click();

        //$("#clinic_id").val(ROW.clinic_id);
        //$("#report_date_new").val(ROW.report_date);
        //$("#report_date").val(ROW.report_date);
        console.log($("#sales_cash").val());

        $('#ajaxModal').on('shown.bs.modal', function () {
            console.log('Cargado');
            var resultElement = searchElementById(document.body, 'total_patients_marketing');
            console.log(resultElement);
        }); 
        //$("#sales_cash").val(ROW.sales_cash);
        /*$("#sales_card").val(ROW.sales_card);
        $("#sales_other").val(ROW.sales_other);

        $("#new_patients_total").val(ROW.new_patients_total);
        $("#followup_patients_total").val(ROW.followup_patients_total);
        $("#referral_google").val(ROW.referral_google);

        $("#referral_google").val(ROW.referral_google);
        $("#referral_referred").val(ROW.referral_referred);
        $("#referral_mail").val(ROW.referral_mail);
        $("#referral_walkby").val(ROW.referral_walkby);
        $("#referral_facebook").val(ROW.referral_facebook);
        $("#referral_events").val(ROW.referral_events);

        $("#referral_instagram").val(ROW.referral_instagram);
        $("#referral_youtube").val(ROW.referral_youtube);
        $("#referral_tiktok").val(ROW.referral_tiktok);
        $("#referral_radio").val(ROW.referral_radio);
        $("#referral_newspaper").val(ROW.referral_newspaper);
        $("#referral_tv").val(ROW.referral_tv);

        $("#uninsured_patients").val(ROW.uninsured_patients);
        $("#insured_patients").val(ROW.insured_patients);
    });*/

    function getRowById(data, id) {
        return data.find(row => row.id === id);
    }

    function selectFill() {
        // Verifica si el parámetro "report" existe en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const reportId = urlParams.get("report");

        if (reportId && reportId.trim() !== "") {

            const table = $("#daily-report-table").DataTable();

            // Asegúrate de que los datos de la tabla estén completamente cargados
            table.on("draw", function() {
                table.rows().every(function(rowIdx) {
                    const data = this.data();

                    if (data.id == reportId) {

                        const rowNode = this.node();
                        $(rowNode).addClass("blink-highlight");
                        table.row(rowIdx).scrollTo(); // Desplazar hasta la fila
                    }
                });
            });
        }
        // Agrega un estilo de animación para el parpadeo
        const style = document.createElement("style");
        style.textContent = `
      @keyframes blink {
          50% { background-color: #ffeb3b; } /* Amarillo */
      }
      .blink-highlight {
          animation: blink 1s infinite;
          border: 2px solid red; /* Resaltar con borde */
      }
  `;
        document.head.appendChild(style);
    }
</script>




<style>
    @media only screen and (max-width: 768px) {

        .dataTables_length,
        .dataTables_filter {
            display: inline-block;
            width: auto;
            vertical-align: middle;
            margin-right: 10px;
            /* Espaciado entre elementos */
        }

        .dataTables_wrapper .dataTables_filter input {
            width: auto;
            display: inline-block;
        }

        .dataTables_wrapper .dataTables_filter {
            float: left;
        }

        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-bottom: 10px;
        }
    }
</style>