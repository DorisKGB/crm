  let currentStep = 1;
    const totalSteps = 6;
    let stepData = {
        provider: null,
        patient: null,
        date: null,
        time: null,
        cost: null,
        comment: null
    };

    // Funciones de validación
    function validateStep(step) {
        try {
            clearErrors();
            let isValid = true;

            switch (step) {
                case 1:
                    const medicoId = $('#medico-id').val();
                    if (!medicoId || medicoId.trim() === '') {
                        showError('medico-error', 'Debes seleccionar un médico');
                        $('#medico-search').addClass('is-invalid');
                        isValid = false;
                    }
                    break;

                case 2:
                    const patientId = $('#patient-id').val();
                    if (!patientId || patientId.trim() === '') {
                        showError('patient-error', 'Debes seleccionar un paciente');
                        $('#search-patient').addClass('is-invalid');
                        isValid = false;
                    }
                    break;

                case 3:
                    const date = $('#appointment_date').val();
                    const time = $('#appointment_time').val();

                    if (!date || date.trim() === '') {
                        showError('date-error', 'Debes seleccionar una fecha');
                        $('#appointment_date').addClass('is-invalid');
                        isValid = false;
                    }
                    if (!time || time.trim() === '') {
                        showError('time-error', 'Debes seleccionar una hora');
                        $('#appointment_time').addClass('is-invalid');
                        isValid = false;
                    }
                    break;

                case 4:
                    const price = $('#price').val();
                    if (!price || parseFloat(price) <= 0) {
                        showError('price-error', 'El costo debe ser mayor a 0');
                        $('#price').addClass('is-invalid');
                        isValid = false;
                    }
                    break;
            }

            return isValid;
        } catch (e) {
            console.error('Error in validateStep:', e);
            return false;
        }
    }

    // 5. FUNCIÓN SEGURA PARA MOSTRAR ERRORES:
    function showError(elementId, message) {
        try {
            const element = $(`#${elementId}`);
            if (element.length > 0) {
                element.text(message).show();
            } else {
                console.warn(`Element #${elementId} not found for error message:`, message);
            }
        } catch (e) {
            console.error('Error showing validation message:', e);
        }
    }

    function clearErrors() {
        try {
            $('.validation-error').text('').hide();
            $('.form-control').removeClass('is-invalid');
        } catch (e) {
            console.error('Error clearing validation:', e);
        }
    }

    /*function updateWizardSteps() {
        $('.wizard-step').addClass('d-none');
        $(`.step-${currentStep}`).removeClass('d-none');

        // Actualizar sidebar
        $('.step-item').each(function(index) {
            const stepNum = index + 1;
            $(this).removeClass('active completed text-primary text-muted');

            if (stepNum < currentStep) {
                $(this).addClass('completed');
                $(this).find('span.fw-bold').addClass('text-success');
            } else if (stepNum === currentStep) {
                $(this).addClass('active');
                $(this).find('span.fw-bold').addClass('text-primary');
            } else {
                $(this).find('span.fw-bold').addClass('text-muted');
            }
        });

        // Botones de navegación
        $('#btnPrev').prop('disabled', currentStep === 1);
        $('#btnNext').toggle(currentStep < totalSteps);

        // Mostrar resumen en el último paso
        if (currentStep === totalSteps) {
            updateAppointmentSummary();
        }
    }*/


    function updateWizardSteps() {
        $('.wizard-step').addClass('d-none');
        $(`.step-${currentStep}`).removeClass('d-none');

        // Actualizar sidebar
        $('.step-item').each(function(index) {
            const stepNum = index + 1;
            $(this).removeClass('active completed text-primary text-muted');

            if (stepNum < currentStep) {
                $(this).addClass('completed');
                $(this).find('span.fw-bold').addClass('text-success');
            } else if (stepNum === currentStep) {
                $(this).addClass('active');
                $(this).find('span.fw-bold').addClass('text-primary');
            } else {
                $(this).find('span.fw-bold').addClass('text-muted');
            }
        });

        // Botones de navegación - MODIFICAR ESTA PARTE
        $('#btnPrev').prop('disabled', currentStep === 1);

        // Ocultar botones de navegación en el paso 6
        if (currentStep === 6) {
            $('#btnNext, #btnPrev').hide();
        } else {
            $('#btnNext').toggle(currentStep < totalSteps - 1); // Mostrar hasta el paso 5
            $('#btnPrev').show();
        }

        // Mostrar resumen en el paso 5
        if (currentStep === 5) {
            updateAppointmentSummary();
        }

        // Inicializar funciones del paso 6
        if (currentStep === 6) {
            initializeStep6();
        }
    }

    function updateAppointmentSummary() {
        const provider = $('#medico-search').val();
        const patient = $('#search-patient').val();
        const date = $('#appointment_date').val();
        const time = $('#appointment_time').val();
        const cost = $('#price').val();
        const duration = $('#duration_minutes').val();
        const comment = $('#comment').val();

        let summaryHtml = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Médico:</strong> ${provider}<br>
                    <strong>Paciente:</strong> ${patient}<br>
                    <strong>Fecha:</strong> ${date}<br>
                </div>
                <div class="col-md-6">
                    <strong>Hora:</strong> ${time}<br>
                    <strong>Duración:</strong> ${duration} min<br>
                    <strong>Costo:</strong> $${cost}<br>
                </div>
            </div>
        `;

        if (comment) {
            summaryHtml += `<div class="mt-2"><strong>Comentario:</strong> ${comment}</div>`;
        }

        $('#summary-content').html(summaryHtml);
    }

    // Navegación del wizard
    $('#btnNext').on('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardSteps();

                // Actualizar datos del wizard en el sidebar
                updateWizardSelections();
            }
        }
    });

    $('#btnPrev').on('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateWizardSteps();
        }
    });

    // Inicializar wizard
    updateWizardSteps();

    // Inicializar Flatpickr
    $('.flatpickr').flatpickr({
        dateFormat: "m/d/Y",
        minDate: "today"
    });

    // Búsqueda de médicos
    $('#medico-search').on('input', function() {
        const query = $(this).val();
        if (query.length < 2) {
            $('#medico-results').hide().empty();
            return;
        }

        $.ajax({
            url: '<?= get_uri("appointments/search_providers") ?>',
            method: 'GET',
            data: {
                q: query
            },
            success: function(data) {
                let html = '';
                data.forEach(item => {
                    html += `
                        <div class="search-result-item" data-id="${item.id}" data-name="${item.name}">
                            <i class="fas fa-user-md me-2 text-primary"></i> ${item.name}
                        </div>
                    `;
                });
                $('#medico-results').html(html).show();
            }
        });
    });

    // Seleccionar médico
    $('#medico-results').on('click', '.search-result-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        $('#medico-id').val(id);
        $('#medico-search').val(name).removeClass('is-invalid');
        $('#medico-results').hide().empty();
        clearErrors();

        // Obtener información del médico
        $.get('<?= get_uri("appointments/provider_info") ?>', {
            id
        }, function(res) {
            $('#medico-info-content').html(`
                <strong>Nombre:</strong> ${res.name}<br>
                <strong>Teléfono:</strong> ${res.phone || 'N/A'}<br>
                <strong>Email:</strong> ${res.email || 'N/A'}<br>
                <strong>Dirección:</strong> ${res.address || 'N/A'}<br>
            `);
            $('#medico-info').removeClass('d-none');
        });
    });

    // Cambiar médico
    $('#btn-change-provider').on('click', function() {
        $('#medico-search').val('').prop('disabled', false).removeClass('is-invalid').focus();
        $('#medico-id').val('');
        $('#medico-results').empty().hide();
        $('#medico-info').addClass('d-none');
        clearErrors();
    });

    // Búsqueda de pacientes
    $('#search-patient').on('keyup', function() {
        let query = $(this).val();

        if (query.length < 2) {
            $('#results-patients').empty().hide();
            return;
        }

        $.ajax({
            url: '<?= get_uri("patients/search_patients") ?>',
            method: 'GET',
            data: {
                q: query
            },
            success: function(data) {
                let html = '';
                if (data && data.length > 0) {
                    data.forEach(p => {
                        html += `<div class="result-item" data-id="${p.id}" data-name="${p.name}">${p.name}</div>`;
                    });
                    $('#results-patients').html(html).show();
                } else {
                    $('#results-patients').html('<div class="result-item text-muted">No se encontraron pacientes</div>').show();
                }
            },
            error: function() {
                $('#results-patients').html('<div class="result-item text-danger">Error al buscar pacientes</div>').show();
            }
        });
    });

    // Seleccionar paciente
    $(document).on('click', '.result-item', function() {
        const id = $(this).data('id');
        const name = $(this).text();

        $('#search-patient').val(name).removeClass('is-invalid');
        $('#patient-id').val(id);
        $('#results-patients').empty();
        clearErrors();

        // Obtener información del paciente
        $.get('<?= get_uri("patients/get_info") ?>', {
            id
        }, function(res) {
            $('#paciente-info-content').html(`
                <strong>Nombre:</strong> ${res.name}<br>
                <strong>Correo:</strong> ${res.email || 'N/A'}<br>
                <strong>Teléfono:</strong> ${res.phone || 'N/A'}<br>
            `);
            $('#paciente-info').removeClass('d-none');
        });
    });

    // Cambiar paciente
    function changePatient() {
        $('#search-patient').val('').prop('disabled', false).removeClass('is-invalid');
        $('#patient-id').val('');
        $('#paciente-info').addClass('d-none');
        $('#paciente-info-content').empty();
        clearErrors();
    }

    // Verificar disponibilidad
    $('#appointment_date, #appointment_time').off('change').on('change', function() {
        const provider = $('#medico-id').val();
        const date = $('#appointment_date').val();
        const time = $('#appointment_time').val();

        if (provider && date && time) {
            // Agregar loading
            $('#provider-availability').removeClass('d-none alert-success alert-danger alert-warning')
                .addClass('alert-info').text('⏳ Verificando disponibilidad...');

            $.ajax({
                url: '<?= get_uri("appointments/check_availability") ?>',
                method: 'GET',
                data: {
                    provider: provider,
                    date: date,
                    time: time
                },
                timeout: 10000, // 10 segundos timeout
                success: function(res) {
                    const el = $('#provider-availability');

                    // Verificar que la respuesta tenga la estructura esperada
                    if (res && typeof res.available !== 'undefined') {
                        if (res.available === true) {
                            el.removeClass('alert-info alert-danger alert-warning')
                                .addClass('alert-success')
                                .text("✅ Proveedor disponible");
                        } else {
                            el.removeClass('alert-info alert-success alert-warning')
                                .addClass('alert-danger')
                                .text("❌ El proveedor ya tiene una cita en ese horario");
                        }
                    } else {
                        // Respuesta malformada
                        el.removeClass('alert-info alert-success alert-danger')
                            .addClass('alert-warning')
                            .text("⚠️ Respuesta inesperada del servidor");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking availability:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    const el = $('#provider-availability');
                    let errorMessage = "⚠️ No se pudo verificar disponibilidad";

                    // Mensajes más específicos según el error
                    if (xhr.status === 400) {
                        errorMessage = "⚠️ Datos inválidos enviados";
                    } else if (xhr.status === 500) {
                        errorMessage = "⚠️ Error del servidor";
                    } else if (status === 'timeout') {
                        errorMessage = "⚠️ Tiempo de espera agotado";
                    }

                    el.removeClass('alert-info alert-success alert-danger')
                        .addClass('alert-warning')
                        .text(errorMessage);
                }
            });
        } else {
            $('#provider-availability').addClass('d-none');
        }
    });



    // Manejo de archivos
    document.getElementById("upload-area").addEventListener("click", function() {
        document.getElementById("reference_file").click();
    });

    $('#upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('background', '#eef');
    });

    $('#upload-area').on('dragleave drop', function(e) {
        e.preventDefault();
        $(this).css('background', '#f9f9f9');
    });

    $('#upload-area').on('drop', function(e) {
        e.preventDefault();
        const file = e.originalEvent.dataTransfer.files[0];
        $('#reference_file')[0].files = e.originalEvent.dataTransfer.files;
        showPreview(file);
    });

    $('#reference_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            showPreview(file);
        }
    });

    function showPreview(file) {
        const preview = $('#preview-content');
        preview.empty();

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html(`<img src="${e.target.result}" class="img-fluid rounded shadow" style="max-height: 200px;">`);
            };
            reader.readAsDataURL(file);
        } else if (file.type === "application/pdf") {
            preview.html(`<i class="fas fa-file-pdf fa-3x text-danger"></i><p>${file.name}</p>`);
        } else {
            preview.html(`<p class="text-danger">Formato no soportado</p>`);
            return;
        }

        $('#preview-area').removeClass('d-none');
    }

    $('#remove-file').on('click', function() {
        $('#reference_file').val('');
        $('#preview-area').addClass('d-none');
        $('#preview-content').empty();
    });

    // Actualizar información del sidebar
    function updateWizardSelections() {
        const provider = $('#medico-search').val();
        const patient = $('#search-patient').val();
        const date = $('#appointment_date').val();
        const time = $('#appointment_time').val();
        const cost = $('#price').val();
        const comment = $('#comment').val();

        if (provider) {
            $('#wizard-selected-provider').text(provider);
        }
        if (patient) {
            $('#wizard-selected-patient').text(patient);
        }
        if (date && time) {
            $('#wizard-selected-date').text(`${date} — ${time}`);
        }
        if (cost) {
            let costText = `Costo: $${cost}`;
            if (comment && comment.length > 0) {
                costText += `\nComentario: ${comment.substring(0, 30)}${comment.length > 30 ? '...' : ''}`;
            }
            $('#wizard-selected-cost').text(costText);
        }
    }

    // Envío del formulario
    /*$('#wizardForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateStep(4)) {
            return;
        }

        const formData = new FormData(this);
        formData.append('vsee_link', "https://teleconsulta.rubymed.org/meet/" + Math.random().toString(36).substr(2, 8));

        // Mostrar loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);

        $.ajax({
            url: '<?= get_uri("appointments/save") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#modalAgendarCita').modal('hide');
                    appAlert.success("✅ Teleconsulta agendada correctamente");
                    $('#appointments-table').DataTable().ajax.reload();
                    showSuccess("✅ Teleconsulta agendada correctamente");
                    resetWizard();

                    //RESPUESTA
                    //res.token 
                    // https://teleconsulta.clinicahispanarubymed.com/?token={res.token}
                } else {
                    showError(`${res.message}`);
                    appAlert.error(res.message || "Error al guardar.");
                }
            },
            error: function() {
                appAlert.error("Error de conexión. Intenta nuevamente.");
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });*/

    // 6. MODIFICAR EL ENVÍO DEL FORMULARIO PARA IR AL PASO 6
    $('#wizardForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateStep(4)) {
            return;
        }

        const formData = new FormData(this);
        formData.append('vsee_link', "https://teleconsulta.rubymed.org/meet/" + Math.random().toString(36).substr(2, 8));

        // Mostrar loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...').prop('disabled', true);

        $.ajax({
            url: '<?= get_uri("appointments/save") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    // EN LUGAR DE CERRAR EL MODAL, IR AL PASO 6
                    currentStep = 6;
                    updateWizardSteps();

                    // Actualizar el link con el token real
                    const teleconsultaLink = `https://teleconsulta.clinicahispanarubymed.com/?token=${res.token}`;
                    $('#teleconsulta-link').text(teleconsultaLink);
                    updateShareLinks(teleconsultaLink);

                    // Actualizar información en el sidebar
                    $('#wizard-link-info').text('Link generado exitosamente');

                    appAlert.success("✅ Teleconsulta agendada correctamente");
                    $('#appointments-table').DataTable().ajax.reload();
                } else {
                    appAlert.error(res.message || "Error al guardar.");
                }
            },
            error: function() {
                appAlert.error("Error de conexión. Intenta nuevamente.");
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    function initializeStep6() {
        // Función para copiar al portapapeles
        $('#copy-link-btn').off('click').on('click', async function() {
            const button = $(this);
            const originalContent = button.html();
            const link = $('#teleconsulta-link').text();

            // Animación de loading
            button.html('<i class="fas fa-spinner fa-spin me-2"></i><span>Copiando...</span>');
            button.prop('disabled', true);

            try {
                await navigator.clipboard.writeText(link);

                // Éxito
                button.addClass('copied');
                button.html('<i class="fas fa-check me-2"></i><span>¡Copiado!</span>');
                showNotification('¡Link copiado al portapapeles!');
            } catch (err) {
                // Fallback para navegadores más antiguos
                const textArea = document.createElement('textarea');
                textArea.value = link;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();

                try {
                    document.execCommand('copy');
                    document.body.removeChild(textArea);

                    button.addClass('copied');
                    button.html('<i class="fas fa-check me-2"></i><span>¡Copiado!</span>');
                    showNotification('¡Link copiado al portapapeles!');
                } catch (err) {
                    document.body.removeChild(textArea);
                    button.html('<i class="fas fa-times me-2"></i><span>Error</span>');
                    showNotification('Error al copiar. Inténtalo manualmente.', 'error');
                }
            }

            // Restaurar botón después de 2 segundos
            setTimeout(() => {
                button.removeClass('copied');
                button.html(originalContent);
                button.prop('disabled', false);
            }, 2000);
        });

        // Toggle QR Code
        $('#qr-toggle-btn').off('click').on('click', function() {
            const qrContainer = $('#qr-container');
            const button = $(this);

            if (qrContainer.hasClass('d-none')) {
                qrContainer.removeClass('d-none');
                button.html('<i class="fas fa-times"></i>');
                button.removeClass('btn-outline-primary').addClass('btn-outline-danger');
            } else {
                qrContainer.addClass('d-none');
                button.html('<i class="fas fa-qrcode"></i>');
                button.removeClass('btn-outline-danger').addClass('btn-outline-primary');
            }
        });

        // Botón finalizar
        $('#finish-wizard-btn').off('click').on('click', function() {
            $('#modalAgendarCita').modal('hide');
            resetWizard();
        });
    }

    // Función para actualizar links de compartir
    function updateShareLinks(teleconsultaLink) {
        const patientName = $('#search-patient').val();
        const appointmentDate = $('#appointment_date').val();
        const appointmentTime = $('#appointment_time').val();

        const message = `¡Hola ${patientName}! Tu teleconsulta está programada para el ${appointmentDate} a las ${appointmentTime}.\n\nAccede aquí: ${teleconsultaLink}\n\n¡Te esperamos!`;
        const subject = `Teleconsulta programada - ${appointmentDate}`;

        // WhatsApp
        $('#share-whatsapp').attr('href', `https://wa.me/?text=${encodeURIComponent(message)}`);
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        // Crear toast si no existe
        if ($('#copy-toast').length === 0) {
            $('body').append(`
            <div class="notification-toast" id="copy-toast">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);
        }

        const toast = $('#copy-toast');
        const icon = toast.find('i');
        const text = toast.find('span');

        // Actualizar contenido
        text.text(message);
        icon.removeClass().addClass(type === 'success' ? 'fas fa-check-circle text-success me-2' : 'fas fa-exclamation-circle text-danger me-2');

        // Mostrar toast
        toast.addClass('show');

        // Ocultar después de 3 segundos
        setTimeout(() => {
            toast.removeClass('show');
        }, 3000);
    }



    // Función para resetear el wizard
    /*function resetWizard() {
        currentStep = 1;

        // Limpiar todos los campos
        $('#wizardForm')[0].reset();
        $('#medico-id, #patient-id').val('');
        $('#medico-search, #search-patient').prop('disabled', false);

        // Ocultar elementos
        $('#medico-info, #paciente-info, #preview-area, #provider-availability').addClass('d-none');
        $('#medico-results, #results-patients').empty().hide();

        // Limpiar sidebar
        $('.step-subtext').text('');

        // Limpiar errores
        clearErrors();

        // Actualizar vista
        updateWizardSteps();
    }*/

    function resetWizard() {
        currentStep = 1;

        // Limpiar todos los campos
        $('#wizardForm')[0].reset();
        $('#medico-id, #patient-id').val('');
        $('#medico-search, #search-patient').prop('disabled', false);

        // Ocultar elementos
        $('#medico-info, #paciente-info, #preview-area, #provider-availability, #qr-container').addClass('d-none');
        $('#medico-results, #results-patients').empty().hide();

        // Limpiar sidebar
        $('.step-subtext').text('');

        // Limpiar paso 6
        $('#teleconsulta-link').text('Generando link...');

        // Limpiar errores
        clearErrors();

        // Actualizar vista
        updateWizardSteps();
    }

    // Actualizar información cuando cambian los campos
    $('#appointment_date, #appointment_time').on('change', updateWizardSelections);
    $('#price, #comment').on('input', updateWizardSelections);

    // Cerrar resultados al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#medico-search, #medico-results').length) {
            $('#medico-results').hide();
        }
        if (!$(e.target).closest('#search-patient, #results-patients').length) {
            $('#results-patients').empty();
        }
    });

    // Resetear wizard al abrir modal
    $('#modalAgendarCita').on('show.bs.modal', function() {
        resetWizard();
    });

    $(document).ready(function() {
        try {
            // Inicializar wizard solo si los elementos existen
            if ($('#wizardForm').length > 0) {
                updateWizardSteps();
            }

            // Inicializar Flatpickr solo si existe
            if ($('.flatpickr').length > 0) {
                $('.flatpickr').flatpickr({
                    dateFormat: "m/d/Y",
                    minDate: "today"
                });
            }

            console.log('✅ Wizard inicializado correctamente');
        } catch (e) {
            console.error('❌ Error inicializando wizard:', e);
        }
    });

    window.onerror = function(msg, url, line, col, error) {
        if (msg.includes('unrecognized expression')) {
            console.error('🔍 Selector problemático detectado:', {
                message: msg,
                line: line,
                column: col,
                url: url
            });
        }
        return false;
    };

    // 8. VERIFICAR QUE NO HAYA IDs DUPLICADOS:
    function checkDuplicateIds() {
        const ids = {};
        $('[id]').each(function() {
            const id = this.id;
            if (ids[id]) {
                console.error('❌ ID duplicado encontrado:', id);
            }
            ids[id] = true;
        });
    }

    // Ejecutar verificación en desarrollo
    if (typeof DEBUG !== 'undefined' && DEBUG) {
        checkDuplicateIds();
    }