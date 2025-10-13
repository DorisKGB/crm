<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="d-flex row pb-4 align-items-end gap-2">
            <div class="d-flex justify-content-between pb-4 align-items-center">
                <p class="fw-bold mb-0"><?php echo app_lang("filter_by_date"); ?></p>

                <?php 
                    $permissions2 = $login_user->permissions;
                    $access_daily = get_array_value($permissions2, "daily_permission");  
                    ?>
                <?php if(!$login_user->is_admin || $access_daily == "all"){ ?>
                <p class="text-muted mb-0 badge" style="background:#e8e8e8;"><b id="attempts_available"><?php echo $num_attemps . "</b> " . app_lang('text_attemps_edit'); ?></p>
                <?php } ?>
            </div>


            <div class="col-md-4">
                <?php
                $selected_clinic_id = isset($model_info) && $model_info !== null ? $model_info->clinic_id : '';
                $clinic_options = ['' => app_lang("select_clinic_one")] + $clinic_options;
                echo form_dropdown(
                    "clinic_select",
                    $clinic_options,
                    $selected_clinic_id,
                    'class="select_graph w-100" id="clinic_select" required aria-required="true" aria-label="' . app_lang('clinic_list') . '"'
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

            <div class="col-md-3">
                <button id="filter_dates" class="btn btn-primary"><i class="fas fa-chart-line px-1"></i> <?php echo app_lang("filter"); ?></button>
                <button id="filter_clean" class="btn btn-danger">
                    <i class="fas fa-eraser px-1"></i>
                </button>
                <button id="downloadPDFFilter" class="btn my_button_n_success"> <i class="fas fa-file-download"></i> PDF</button>
                <button id="downloadXLSXFilter" class="btn btn-success"> <i class="fas fa-file-download"></i> XLSX</button>
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
                        <th><?php echo app_lang("reported_by"); ?></th>
                        <th><?php echo app_lang("report_id"); ?></th>
                        <th><?php echo app_lang("clinic_id"); ?></th>
                        <th><?php echo app_lang("clinic_name"); ?></th>
                        <th><?php echo app_lang("report_date"); ?></th>
                        <th><?php echo app_lang("file"); ?></th>
                        <th><?php echo app_lang("cash_sales"); ?></th>
                        <th><?php echo app_lang("card_sales"); ?></th>
                        <th><?php echo app_lang("other_sales"); ?></th>
                        <th><?php echo app_lang("new_patients"); ?></th>
                        <th><?php echo app_lang("followup_patients"); ?></th>
                        <th><?php echo app_lang("referral_google"); ?></th>
                        <th><?php echo app_lang("referral_referred"); ?></th>
                        <th><?php echo app_lang("referral_mail"); ?></th>
                        <th><?php echo app_lang("referral_walkby"); ?></th>
                        <th><?php echo app_lang("referral_facebook"); ?></th>
                        <th><?php echo app_lang("referral_events"); ?></th>
                        <th><?php echo app_lang("referral_instagram"); ?></th>
                        <th><?php echo app_lang("referral_youtube"); ?></th>
                        <th><?php echo app_lang("referral_tiktok"); ?></th>
                        <th><?php echo app_lang("referral_radio"); ?></th>
                        <th><?php echo app_lang("referral_newspaper"); ?></th>
                        <th><?php echo app_lang("referral_tv"); ?></th>
                        <th><?php echo app_lang("uninsured_patients"); ?></th>
                        <th><?php echo app_lang("insured_patients"); ?></th>
                        <th><?php echo app_lang("actions"); ?></th>
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
</style>
<div class="container">
    <table class="table ">
        <thead>
            <tr style="background-color:rgb(238, 241, 243);">
                <th class="text-center"><?php echo app_lang("sales_total_money"); ?></th>
                <th class="text-center"><?php echo app_lang("sales_total_card"); ?></th>
                <th class="text-center"><?php echo app_lang("sales_total_other"); ?></th>
                <th class="text-center"><?php echo app_lang("pacient_total"); ?></th>
                <th class="text-center"><?php echo app_lang("pacient_total_insured"); ?></th>
                <th class="text-center"><?php echo app_lang("pacient_total_insurance"); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" id="total_sales_cash"></td>
                <td class="text-center" id="total_sales_card"></td>
                <td class="text-center" id="total_sales_other"></td>
                <td class="text-center" id="total_new_patients"></td>
                <td class="text-center" id="total_insured_patients"></td>
                <td class="text-center" id="total_uninsured_Patients"></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-center" style="background-color:rgb(238, 241, 243);" colspan="2">Ventas Totales</th>
                <th class="text-center" id="total_sales"></th>
                <th class="text-center" style="background-color:rgb(238, 241, 243);" colspan="2">Pacientes Totales</th>
                <th class="text-center" id="total_patients"></th>
            </tr>
        </tfoot>
    </table>
</div>

<script type="text/javascript">
    var DATATABLE_FILL = [];
    var isProcessing = false;
    $(document).ready(function() {
        var isMobile = window.matchMedia("only screen and (max-width: 768px)").matches;

        var table = $("#daily-report-table").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo_uri('daily_report/list_data') ?>",
                "type": "POST",
                "data": function(d) {
                    const startDate = $('#start_date').val();
                    const endDate = $('#end_date').val();
                    if (startDate && endDate) {

                        d.startDate = formatDateToISO(startDate);
                        d.endDate = formatDateToISO(endDate);
                    }
                    d.clinicId = $('#clinic_select').val();

                }
            },
            "paging": true,
            "lengthMenu": [5, 10, 20, 50, 100],
            "pageLength": isMobile ? 10 : 5,
            "scrollX": true,
            "scrollY": isMobile ? "400px" : "",
            "order": [
                [4, "desc"]
            ],
            "columns": [{
                    "data": "submitted_by"
                },
                {
                    "data": "id"
                },
                {
                    "data": "clinic_id"
                },
                {
                    "data": "clinic_name"
                },
                {
                    "data": "report_date",
                    "render": formatDateColumn
                },
                {
                    "data": "report_file",
                    "render": renderLinkColumn
                },
                {
                    "data": "sales_cash",
                    "render": formatCurrencyColumn
                },
                {
                    "data": "sales_card",
                    "render": formatCurrencyColumn
                },
                {
                    "data": "sales_other",
                    "render": formatCurrencyColumn
                },
                {
                    "data": "new_patients_total"
                },
                {
                    "data": "followup_patients_total"
                },
                {
                    "data": "referral_google"
                },
                {
                    "data": "referral_referred"
                },
                {
                    "data": "referral_mail"
                },
                {
                    "data": "referral_walkby"
                },
                {
                    "data": "referral_facebook"
                },
                {
                    "data": "referral_events"
                },
                {
                    "data": "referral_instagram"
                },
                {
                    "data": "referral_youtube"
                },
                {
                    "data": "referral_tiktok"
                },
                {
                    "data": "referral_radio"
                },
                {
                    "data": "referral_newspaper"
                },
                {
                    "data": "referral_tv"
                },
                {
                    "data": "uninsured_patients"
                },
                {
                    "data": "insured_patients"
                },
                {
                    "data": "id",
                    "orderable": false,
                    "searchable": false,
                    "render": function(data, type, row) {
                        var actions = '';
                        <?php if($login_user->is_admin){ ?> //$login_user->is_admin || $access_daily == "all"
                            actions += '<button type="button" class="btn btn-danger btn-sm delete-report-btn me-1" data-id="' + data + '" title="<?php echo app_lang("delete"); ?>">';
                            actions += '<i class="fas fa-trash"></i>';
                            actions += '</button>';
                        <?php } ?>
                        return actions;
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

        // Función para convertir los valores en formato moneda
        function formatCurrencyColumn(data, type, row) {
            return "<?php echo app_lang('sign_money'); ?>" + Number(data).toLocaleString('en-US');
        }

        // Función para generar enlaces de archivos
        /*function renderLinkColumn(data, type, row) {
            return '<a href="' + data + '" target="_blank"><?php echo app_lang("view_file"); ?></a>';
        }*/
        function renderLinkColumn(data, type, row) {
            return '<a href="#" onclick="openReportPopup(\'' + data + '\'); return false;"><?php echo app_lang("view_file"); ?></a>';
        }





        $('#daily-report-table tbody').on('dblclick', 'td', function() {
            var cell = table.cell(this);
            var originalValue = cell.data();
            var columnIdx = cell.index().column;

            // Columnas no editables
            if ([0, 1, 2, 3, 5].includes(columnIdx)) {
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
            let blurTriggered = false; // Bandera para verificar si el blur fue forzado

            inputElement.on('blur keypress', function(e) {
                if (e.type === 'keypress' && e.which === 13) {
                    e.preventDefault(); // Evita que Enter dispare también el blur
                    if (!blurTriggered) {
                        $(this).blur(); // Forza el blur manualmente sin ejecutar doble evento
                        blurTriggered = true; // Marca que el blur fue forzado
                    }
                    return;
                }

                if (e.type === 'blur') {
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
                            input.addClass('error');
                            setTimeout(function() {
                                table.draw();
                                alert("La fecha no es correcta!");
                            }, 100);
                        }
                        return;
                    }
                    if (indexColumn != 4 && newValue < 0) {
                        if (!input.hasClass('error')) {
                            input.addClass('error');
                            setTimeout(function() {
                                table.draw();
                                alert("No se puede colocar valores negativos.");
                            }, 100);
                        }
                        return;
                    }

                    if (isProcessing) return; // Evita doble llamada
                    isProcessing = true;

                    $.ajax({
                        url: "<?php echo_uri('daily_report/updateDailyReport') ?>",
                        type: 'POST',
                        data: {
                            id: rowData.id,
                            column: columnName2,
                            value: newValue,
                            user_id: <?php echo $login_user->id; ?>
                        },
                        success: function(response) {
                            isProcessing = false; // Desbloquea el envío
                            console.log(response);
                            $("#attempts_available").text(response.attempt_available);

                            if (response.status == 'success') {

                                //limpiando
                                $("#btnConfirmModal").removeClass('btn-warning');
                                $("#modalM_logo").removeClass('warningModal fas fa-exclamation-triangle');

                                $("#btnConfirmModal").addClass('btn-success');
                                $("#modalM_logo").addClass('successModal far fa-check-circle');
                                $("#modalM_title").text("Excelente!");
                                $("#modalM_description").text("Muy bien, Reporte actualizado correctamente.");
                            } else {

                                //limpiando
                                $("#btnConfirmModal").removeClass('btn-success');
                                $("#modalM_logo").removeClass('successModal far fa-check-circle');

                                $("#btnConfirmModal").addClass('btn-warning');
                                $("#modalM_logo").addClass('warningModal fas fa-exclamation-triangle');
                                $("#modalM_title").text("Error");
                                $("#modalM_description").text(response.message);
                            }

                            $("#confirmModal").modal("show");
                            cell.data(newValue).draw();
                        },
                        error: function() {
                            isProcessing = false; // Desbloquea el envío
                            alert('Error de conexión con el servidor.');
                        }
                    });

                    blurTriggered = false; // Restablece la bandera
                }
            });
        });

        // Agregar el manejador de eventos para el botón de eliminar
        $(document).on('click', '.delete-report-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var reportId = $(this).data('id');
            var button = $(this);
            
            // Usar un modal único y específico
            var modalId = 'deleteReportModal_' + reportId;
            
            // Eliminar modal previo si existe
            $('#' + modalId).remove();
            
            // Deshabilitar botón temporalmente
            button.prop('disabled', true);
            
            $.ajax({
                url: "<?php echo_uri('daily_report/delete_confirmation_modal/') ?>" + reportId,
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    // Crear modal con ID único y backdrop
                    var modalHtml = '<div class="modal fade" id="' + modalId + '" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="' + modalId + 'Label" aria-hidden="true">' +
                                   '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">' +
                                   response + '</div></div></div>';
                    
                    $('body').append(modalHtml);
                    $('#' + modalId).modal('show');
                    
                    // Rehabilitar botón
                    button.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading delete modal:', error);
                    showError('Error al cargar el modal de confirmación');
                    button.prop('disabled', false);
                }
            });
        });
        
        $(document).on('hidden.bs.modal', '[id^="deleteReportModal_"]', function () {
            $(this).remove();
        });
        
        function reloadDailyReportTable() {
            if (typeof $('#daily-report-table').DataTable === 'function') {
                $('#daily-report-table').DataTable().draw();
            }
        }


        $('#filter_dates').click(function() {
            table.draw();
            prepareTotalPatientsChart();
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

            // Función para abrir en ventana emergente
        function openReportPopup(url) {
            window.open(
                url,
                "Reporte",
                "width=900,height=600,resizable=yes,scrollbars=yes,status=yes"
            );
        }

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
            return null;
        }

        // Retornar la fecha en formato YYYY-MM-DD
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }

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

    document.getElementById('downloadPDFFilter').addEventListener('click', function(e) {
        e.preventDefault();

        // Obtener valores de los campos seleccionados
        var clinic_id = document.getElementById('clinic_select').value;
        var start_date = document.getElementById('start_date').value;
        var end_date = document.getElementById('end_date').value;

        // Validar que todos los campos necesarios estén seleccionados
        if (!clinic_id || !start_date || !end_date) {
            alert('Por favor, selecciona todos los campos.');
            return;
        }

        // Crear la URL con los parámetros
        var url = '<?php echo get_uri("daily_report/generatePDFRangeClinic") ?>?clinic_id=' + clinic_id + '&rang_one_1=' + formatDateToISO(start_date) + '&rang_one_2=' + formatDateToISO(end_date);

        // Redirigir a la URL generada
        window.location.href = url;
    });

    document.getElementById('downloadXLSXFilter').addEventListener('click', function(e) {
        e.preventDefault();

        // Obtener valores de los campos seleccionados
        var clinic_id = document.getElementById('clinic_select').value;
        var start_date = document.getElementById('start_date').value;
        var end_date = document.getElementById('end_date').value;

        // Validar que todos los campos necesarios estén seleccionados
        if (!clinic_id || !start_date || !end_date) {
            alert('Por favor, selecciona todos los campos.');
            return;
        }

        // Crear la URL con los parámetros
        var url = '<?php echo get_uri("daily_report/exportExcelRangeClinic") ?>?clinic_id=' + clinic_id + '&rang_one_1=' + formatDateToISO(start_date) + '&rang_one_2=' + formatDateToISO(end_date);

        // Redirigir a la URL generada
        window.location.href = url;
    });
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