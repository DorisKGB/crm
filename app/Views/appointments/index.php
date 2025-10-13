<?php
load_css([
    "assets/js/fullcalendar/fullcalendar.min.css",
    "assets/css/button.css"
]);

load_js([
    "assets/js/fullcalendar/fullcalendar.min.js",
    "assets/js/fullcalendar/locales-all.min.js"
]);
?>

<div id="page-content" class="page-wrapper clearfix">
    <style>
        #appointments-table tbody tr {
            transition: 0.3s ease;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        #appointments-table tbody tr:hover {

            background-color: #f8f9fa;
        }

        #appointments-table td {
            vertical-align: middle;
        }

        .btn-sm {
            border-radius: 8px !important;
        }

        #calendar-container {
            min-height: 600px;
            background-color: white;
            border-radius: 12px;
            padding: 10px;
            overflow: hidden;
        }

        .fc {
            font-size: 14px;
        }

        /* Fondo limpio y espacio entre eventos */
        .fc-event {
            background-color: #f5f5f5 !important;
            color: #333 !important;
            border: none !important;
            padding: 5px 8px !important;
            border-radius: 8px;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 4px;
        }

        /* Hover para evento */
        .fc-event:hover {
            background-color: #e0e0e0 !important;
            cursor: pointer;
        }

        /* Mejora visual del día actual */
        .fc-day-today {
            background-color: #f0f8ff !important;
        }

        /* Cambiar fuente del título de evento */
        .fc-event-title {
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Espaciado entre líneas */
        .fc .fc-daygrid-day-events {
            margin-top: 6px;
        }

        /* Encabezado bonito */
        .fc-toolbar-title {
            font-weight: bold;
            font-size: 1.3rem;
        }

        .fc-button {
            border-radius: 6px !important;
            font-size: 0.85rem;
            padding: 4px 10px;
        }

        .fc-listWeek-view .fc-toolbar-title {
            font-size: 22px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 10px;
        }

        /* Día: martes, miércoles... */
        .fc .fc-list-day {
            background-color: #F3F4F6;
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            margin-top: 10px;
        }

        /* Fecha tipo 15 de julio de 2025 */
        .fc .fc-list-day-side-text {
            font-size: 13px;
            font-weight: 400;
            color: #6B7280;
        }

        /* Contenedor del evento */
        .fc .fc-list-event {
            border-left: 4px solid #6366F1;
            /* Morado suave */
            background-color: #F9FAFB;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 6px 0;
            transition: 0.2s ease;
        }

        /* Efecto al pasar el mouse */
        .fc .fc-list-event:hover {
            background-color: #EEF2FF;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        /* Hora del evento */
        .fc .fc-list-event-time {
            font-weight: bold;
            color: #4B5563;
            font-size: 14px;
        }

        /* Título del evento */
        .fc .fc-list-event-title {
            font-size: 15px;
            color: #111827;
        }

        /* Punto azul */
        .fc .fc-event-dot {
            background-color: #6366F1 !important;
        }

        /* Botones Mes, Semana, Agenda */
        .fc .fc-button {
            border-radius: 8px !important;
            font-weight: 600;
            padding: 6px 14px;
            text-transform: capitalize;
            background-color: #E5E7EB;
            color: #111827;
            border: none;
        }

        .fc .fc-button-active {
            background-color: #6366F1 !important;
            color: white !important;
        }

        .fc .fc-button:hover {
            background-color: #4F46E5 !important;
            color: white !important;
        }

        .btn-link-teleconsulta {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-link-teleconsulta:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Estilos para el modal de teleconsulta */
        .modal-teleconsulta .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-teleconsulta .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 25px 30px;
            position: relative;
        }

        .modal-teleconsulta .modal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .modal-teleconsulta .modal-title {
            font-weight: 700;
            font-size: 1.4rem;
            z-index: 2;
            position: relative;
        }

        .modal-teleconsulta .btn-close {
            filter: invert(1);
            z-index: 2;
            position: relative;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #48bb78, #38a169);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: successPulse 2s infinite;
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
        }

        @keyframes successPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .link-container {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 15px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            margin: 20px 0;
        }

        .link-display {
            background: white;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #e2e8f0;
        }

        .link-text {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #2d3748;
            word-break: break-all;
            line-height: 1.4;
            margin: 0;
            padding: 12px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
        }

        .copy-btn {
            background: linear-gradient(135deg, #48bb78, #38a169);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
            background: linear-gradient(135deg, #38a169, #2f855a);
            color: white;
        }

        .copy-btn.copied {
            background: linear-gradient(135deg, #4299e1, #3182ce) !important;
            animation: copySuccess 0.6s ease;
        }

        @keyframes copySuccess {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .share-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .share-btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .share-btn.whatsapp {
            background: linear-gradient(135deg, #25d366, #1da851);
        }

        .share-btn.email {
            background: linear-gradient(135deg, #ea4335, #d33b2c);
        }

        .share-btn.sms {
            background: linear-gradient(135deg, #007aff, #0056cc);
        }

        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .feature-list {
            margin-top: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            color: #4a5568;
            font-size: 14px;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
        }

        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-left: 4px solid #48bb78;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .appointment-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    </style>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h4><b><i class="far fa-heart"></i> Teleconsultas</b></h4>
                <div class="d-flex">
                    <button class="btn-ghost btn-ghost-primary" onclick="openAppointmentForm()">
                        <i class="fa fa-plus"></i> Nueva Teleconsulta
                    </button>

                    <a href="<?= site_url('patients') ?>" class="btn-ghost btn-ghost-danger"><i class="fas fa-user"></i> Nuevo Paciente</a>
                    <a href="<?= site_url('appointments/teleconsultas') ?>" class="btn-ghost btn-ghost-success"><i class="far fa-calendar"></i> Cronograma</a>
                </div>

            </div>

            <ul class="nav nav-tabs mb-3" id="appointmentTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="table-tab" data-bs-toggle="tab" href="#datatable" role="tab">Tabla</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab">Calendario</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade " id="calendar" role="tabpanel">
                    <div id="calendar-container"></div>
                </div>
                <div class="tab-pane fade show active" id="datatable" role="tabpanel">
                    <table id="appointments-table" class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Nurse Practitioner</th>
                                <th>Estado</th>
                                <th>Comentario</th>
                                <th>Videollamada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalContainer"></div>


<!-- Modal para mostrar link de teleconsulta -->
<div class="modal fade modal-teleconsulta" id="modalLinkTeleconsulta" tabindex="-1" aria-labelledby="modalLinkTeleconsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLinkTeleconsultaLabel">
                    <i class="fas fa-video me-2"></i>Link de Teleconsulta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center fade-in-up">
                <!-- Icono de éxito -->
                <div class="success-icon">
                    <i class="fas fa-link fa-lg text-white"></i>
                </div>

                <!-- Información de la cita -->
                <div class="appointment-info">
                    <div class="info-row">
                        <span class="info-label">Paciente:</span>
                        <span class="info-value" id="modal-patient-name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Médico:</span>
                        <span class="info-value" id="modal-provider-name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha:</span>
                        <span class="info-value" id="modal-appointment-date">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Hora:</span>
                        <span class="info-value" id="modal-appointment-time">-</span>
                    </div>
                </div>

                <!-- Contenedor del Link -->
                <div class="link-container">
                    <div class="link-display">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-primary fw-bold">
                                <i class="fas fa-link me-2"></i>Link de Acceso
                            </h6>
                            <div class="badge bg-success text-white">
                                <i class="fas fa-shield-alt me-1"></i>Seguro
                            </div>
                        </div>

                        <p class="link-text mb-3" id="modal-teleconsulta-link">
                            Cargando link...
                        </p>

                        <div class="d-flex gap-2">
                            <button class="copy-btn flex-grow-1" id="modal-copy-btn">
                                <i class="fas fa-copy me-2"></i>
                                <span>Copiar Link</span>
                            </button>
                            <a href="#" class="btn btn-outline-primary" id="modal-open-link" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Botones de compartir -->
                <div class="share-buttons">
                    <a href="#" class="share-btn whatsapp" id="modal-share-whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </a>
                </div>

                <!-- Lista de características -->
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span>El link estará activo 15 minutos antes de la cita</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <span>Conexión segura y encriptada</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <span>Compatible con dispositivos móviles</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast de notificación -->
<div class="notification-toast" id="modal-copy-toast">
    <div class="d-flex align-items-center">
        <i class="fas fa-check-circle text-success me-2"></i>
        <span>¡Link copiado al portapapeles!</span>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let calendar;

        document.getElementById("calendar-tab").addEventListener("click", function() {
            setTimeout(() => {
                if (!calendar) {
                    let calendarEl = document.getElementById('calendar-container');
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'es',
                        initialView: 'listWeek',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        selectable: true,
                        dateClick: function(info) {
                            openAppointmentForm(info.dateStr);
                        },
                        events: "<?= get_uri('appointments/calendar_events') ?>"
                    });
                    calendar.render();
                }
            }, 200); // Espera para que se active el tab
        });


         $('#appointments-table').DataTable({
            ajax: "<?= get_uri('appointments/list_data') ?>",
            order: [
                [1, 'desc']
            ],
            columns: [{
                    data: "id"
                },
                {
                    data: "date"
                },
                {
                    data: "time"
                },
                {
                    data: "patient_name"
                },
                {
                    data: "provider_name"
                },
                {
                    data: "status" // Nueva columna
                },
                {
                    data: "comment"
                },
                {
                    data: "video_link"
                },
                {
                    data: "actions"
                }
            ],
            columnDefs: [{
                targets: 2, // Columna 'time'
                render: function(data, type, row, meta) {
                    return '<span ><b><i class="far fa-clock"></i>' + data + '</b></span>';
                }
            }],
            language: {
                url: "<?= base_url('assets/js/datatable/es-ES.json') ?>"
            }
        });
        
    });


     // Funciones para los nuevos modales
    function openStatusModal(id) {
        $('#modalContainer').empty();
        $.get("<?= get_uri('appointments/modal_cambiar_estado') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
            modal.show();
        });
    }

    function openRescheduleModal(id) {
        $('#modalContainer').empty();
        $.get("<?= get_uri('appointments/modal_reprogramar') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalReprogramar'));
            modal.show();
        });
    }

    function openAppointmentForm(date = "") {
        $.get("<?= get_uri('appointments/modal_agendar_cita') ?>", {
            date
        }, function(html) {
            $('#modalContainer').html(html);
            $('#modalAgendarCita').modal('show');
        });
    }


    window.openDetail = function(id) {
        $('#modalContainer').empty(); // Limpiamos contenedor
        $.get("<?= get_uri('patients/view_modal') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalVerPaciente'));
            modal.show();
        });
    }

    function showComment(id) {
        $.get("<?= get_uri('appointments/modal_comentario') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalComentarioCita'));
            modal.show();
        });
    }

    function openEditAppointment(id) {
        $('#modalContainer').empty(); // Limpiamos contenedor por si hay otro modal cargado
        $.get("<?= get_uri('appointments/modal_editar_cita') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalEditarCita'));
            modal.show();
        });
    }

    function openDeleteAppointment(id) {
        $('#modalContainer').empty(); // Limpiamos contenedor por si hay otro modal cargado
        $.get("<?= get_uri('appointments/modal_eliminar_cita') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalEliminarCita'));
            modal.show();
        });
    }

    function showTeleconsultaLink(appointmentId) {
        // Mostrar modal inmediatamente
        const modal = new bootstrap.Modal(document.getElementById('modalLinkTeleconsulta'));
        modal.show();

        // Obtener información de la cita
        $.ajax({
            url: '<?= get_uri("appointments/get_appointment_link") ?>',
            method: 'GET',
            data: {
                id: appointmentId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Actualizar información de la cita
                    $('#modal-patient-name').text(data.patient_name || '-');
                    $('#modal-provider-name').text(data.provider_name || '-');
                    $('#modal-appointment-date').text(data.appointment_date || '-');
                    $('#modal-appointment-time').text(data.appointment_time || '-');

                    // Generar link de teleconsulta
                    const teleconsultaLink = `https://teleconsulta.clinicahispanarubymed.com/consulta.html?token=${data.token}&type=3847629051`;
                    $('#modal-teleconsulta-link').text(teleconsultaLink);
                    $('#modal-open-link').attr('href', teleconsultaLink);

                    // Actualizar links de compartir
                    updateModalShareLinks(teleconsultaLink, data);

                } else {
                    $('#modal-teleconsulta-link').text('Error al cargar el link');
                    showModalNotification('Error al cargar la información', 'error');
                }
            },
            error: function() {
                $('#modal-teleconsulta-link').text('Error de conexión');
                showModalNotification('Error de conexión', 'error');
            }
        });
    }

    // Función para actualizar links de compartir
    function updateModalShareLinks(teleconsultaLink, data) {
        const message = `¡Hola ${data.patient_name}! Tu teleconsulta está programada para el ${data.appointment_date} a las ${data.appointment_time}.\n\nAccede aquí: ${teleconsultaLink}\n\n¡Te esperamos!`;
        const subject = `Teleconsulta programada - ${data.appointment_date}`;

        // WhatsApp
        $('#modal-share-whatsapp').attr('href', `https://wa.me/?text=${encodeURIComponent(message)}`);
    }

    // Función para copiar al portapapeles
    async function copyModalToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            // Fallback para navegadores más antiguos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
                document.body.removeChild(textArea);
                return true;
            } catch (err) {
                document.body.removeChild(textArea);
                return false;
            }
        }
    }

    // Función para mostrar notificaciones
    function showModalNotification(message, type = 'success') {
        const toast = $('#modal-copy-toast');
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

    // Event listener para el botón copiar
    $(document).on('click', '#modal-copy-btn', async function() {
        const button = $(this);
        const originalContent = button.html();
        const link = $('#modal-teleconsulta-link').text();

        // Animación de loading
        button.html('<i class="fas fa-spinner fa-spin me-2"></i><span>Copiando...</span>');
        button.prop('disabled', true);

        // Intentar copiar
        const success = await copyModalToClipboard(link);

        if (success) {
            // Éxito
            button.addClass('copied');
            button.html('<i class="fas fa-check me-2"></i><span>¡Copiado!</span>');
            showModalNotification('¡Link copiado al portapapeles!');
        } else {
            // Error
            button.html('<i class="fas fa-times me-2"></i><span>Error</span>');
            showModalNotification('Error al copiar. Inténtalo manualmente.', 'error');
        }

        // Restaurar botón después de 2 segundos
        setTimeout(() => {
            button.removeClass('copied');
            button.html(originalContent);
            button.prop('disabled', false);
        }, 2000);
    });
</script>