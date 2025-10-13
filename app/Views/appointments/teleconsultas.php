<div id="page-content" class="page-wrapper clearfix todo-page">
    <?php
    load_css([
        'assets/css/button.css',
    ]);
    ?>

    <div class="card">
         <div class="card-header">
            <div class="card-title d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4>
                        <i class="far fa-heart"></i> Cronograma de Teleconsultas
                    </h4>
                </div>
                <div class="d-flex">
                    <a href="<?= site_url('appointments') ?>" class="btn-ghost btn-ghost-danger">
                        <i class="fas fa-arrow-left"></i> Panel Principal
                    </a>
                </div>
            </div>
        </div>

        <style>
            /* sombra muy discreta */
            .box-shadow-sm {
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
                border-radius: 6px;
                /* opcional, luce mejor */
                padding: 1rem;
                /* separa el texto del borde */
                background: #fff;
                /* para que la sombra se note sobre fondos claros */
                border-radius: 15px;
            }

            .card-timer {
                position: absolute;
                top: 8px;
                right: 12px;
                background: #fff;
                padding: 4px 10px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 2rem;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
                z-index: 2;
            }

            .no-consultations {
                text-align: center;
                padding: 60px 20px;
                color: #64748b;
            }

            .no-consultations i {
                font-size: 64px;
                margin-bottom: 20px;
                opacity: 0.5;
            }
        </style>

        <div class="card-body">
            <div id="teleconsultasContainer">
                <!-- Las teleconsultas se cargarán aquí via AJAX -->
                <div class="text-center" style="padding: 40px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando teleconsultas...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let updateInterval;

        document.addEventListener('DOMContentLoaded', function() {
            loadTeleconsultas();
            
            // Actualizar cada 30 segundos
            updateInterval = setInterval(loadTeleconsultas, 30000);
            
            // Actualizar cronómetros cada segundo
            setInterval(updateTimers, 1000);
        });

        async function loadTeleconsultas() {
            try {
                const response = await fetch('<?= site_url("appointments/get_provider_consultations") ?>', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al cargar teleconsultas');
                }

                const data = await response.json();
                renderTeleconsultas(data.consultations || []);
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('teleconsultasContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al cargar las teleconsultas. <button onclick="loadTeleconsultas()" class="btn btn-sm btn-outline-danger ms-2">Reintentar</button>
                    </div>
                `;
            }
        }

        function renderTeleconsultas(consultations) {
            const container = document.getElementById('teleconsultasContainer');
            
            if (consultations.length === 0) {
                container.innerHTML = `
                    <div class="no-consultations">
                        <i class="fas fa-calendar-times"></i>
                        <h4>No tienes teleconsultas programadas</h4>
                        <p>Cuando tengas citas programadas aparecerán aquí</p>
                    </div>
                `;
                return;
            }

            // Ordenar: próximas primero, luego pendientes, luego finalizadas
            consultations.sort((a, b) => {
                const statusOrder = { 'próxima': 1, 'pendiente': 2, 'finalizada': 3 };
                if (statusOrder[a.status] !== statusOrder[b.status]) {
                    return statusOrder[a.status] - statusOrder[b.status];
                }
                // Si tienen el mismo status, ordenar por fecha/hora
                return new Date(a.appointment_datetime) - new Date(b.appointment_datetime);
            });

            // Dividir en dos columnas
            const leftColumn = [];
            const rightColumn = [];
            
            consultations.forEach((consultation, index) => {
                if (index % 2 === 0) {
                    leftColumn.push(consultation);
                } else {
                    rightColumn.push(consultation);
                }
            });

            const generateConsultationCard = (consultation) => `
                <div class="row list-stamp mb-4" style="position: relative;">
                    <div class="col-md-12">
                        <div class="box-shadow-sm">
                            ${consultation.status === 'próxima' || consultation.status === 'pendiente' ? 
                                `<span class="card-timer" data-countdown="${consultation.appointment_datetime}">00:00:00</span>` : ''
                            }
                            
                            <p><b><i class="fas fa-calendar-alt"></i> Fecha</b>: ${consultation.formatted_date} a las ${consultation.formatted_time}</p>
                            <p><b><i class="fas fa-user"></i> Paciente</b>: <span>${consultation.patient_name}</span></p>
                            <p><b><i class="fas fa-user-md"></i> Provider</b>: <span>${consultation.provider_name || 'Telemedicine Provider'}</span></p>
                            <p class="text-${consultation.status === 'finalizada' ? 'success' : 'warning'}">
                                <b><i class="fas fa-check-double"></i> Estado</b>: 
                                ${consultation.status === 'finalizada' ? 'Finalizada' : 
                                  consultation.status === 'próxima' ? 'Próxima' : 'Pendiente'} 
                               <img src="<?= base_url("assets/images/"); ?>${consultation.status === 'finalizada' ? 'check' : 'pendiente'}.png" width="30" alt="">
                            </p>
                            <p><b><i class="fas fa-hourglass-half"></i> Duración</b>: ${consultation.duration_minutes} min</p>
                            
                            <p>
                                ${consultation.comment ? `
                                    <button class="btn-button btn-button-light" onclick="showComment('${consultation.comment}')">
                                        <i class="fas fa-comment"></i> Ver Comentarios
                                    </button>
                                ` : `
                                    <button class="btn-button btn-button-light">
                                        <i class="fas fa-comment"></i> Ver Comentarios
                                    </button>
                                `}
                                
                                ${consultation.status !== 'finalizada' ? `
                                    <a href="<?= site_url('appointments/consultation_detail/') ?>${consultation.token}" 
                                       class="btn-button btn-button-purple">
                                        <i class="fas fa-video"></i> Unirme a la consulta
                                    </a>
                                ` : ''}
                                
                                <button class="btn-button btn-button-outline-info d-none" 
                                        onclick="viewPatientHistory('${consultation.patient_id}')">
                                    <i class="fas fa-history"></i> Historial
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            `;

            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="cards-container">
                            ${leftColumn.map(generateConsultationCard).join('')}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="cards-container">
                            ${rightColumn.map(generateConsultationCard).join('')}
                        </div>
                    </div>
                </div>
            `;

            container.innerHTML = html;
        }

        /*function updateTimers() {
            document.querySelectorAll('.card-timer').forEach(function(el) {
                const target = new Date(el.dataset.countdown);
                const now = new Date();
                let diff = Math.max(0, target - now);

                const hours = Math.floor(diff / 36e5);
                diff -= hours * 36e5;
                const minutes = Math.floor(diff / 6e4);
                diff -= minutes * 6e4;
                const seconds = Math.floor(diff / 1e3);

                function fmt(n) {
                    return n.toString().padStart(2, '0');
                }

                const timeString = `${fmt(hours)} : ${fmt(minutes)} : ${fmt(seconds)}`;
                el.textContent = timeString;
            });
        }*/

        function updateTimers() {
            document.querySelectorAll('.card-timer').forEach(function(el) {
                const countdownData = el.dataset.countdown;
                
                // Parsear la fecha/hora de manera más precisa
                // Asumiendo que viene en formato: "2025-07-29 22:00:00" o similar
                const dateTimeParts = countdownData.replace(/[-T]/g, ' ').replace(/:/g, ' ').split(' ');
                
                let target;
                if (dateTimeParts.length >= 6) {
                    // Formato: YYYY MM DD HH MM SS
                    target = new Date(
                        parseInt(dateTimeParts[0]), // año
                        parseInt(dateTimeParts[1]) - 1, // mes (0-11)
                        parseInt(dateTimeParts[2]), // día
                        parseInt(dateTimeParts[3]), // hora
                        parseInt(dateTimeParts[4]), // minutos
                        parseInt(dateTimeParts[5]) || 0 // segundos
                    );
                } else if (dateTimeParts.length >= 5) {
                    // Formato: YYYY MM DD HH MM
                    target = new Date(
                        parseInt(dateTimeParts[0]), // año
                        parseInt(dateTimeParts[1]) - 1, // mes (0-11)
                        parseInt(dateTimeParts[2]), // día
                        parseInt(dateTimeParts[3]), // hora
                        parseInt(dateTimeParts[4]), // minutos
                        0 // segundos
                    );
                } else {
                    // Fallback al método original si no se puede parsear
                    target = new Date(countdownData);
                }
                
                const now = new Date();
                let diff = Math.max(0, target - now);

                const hours = Math.floor(diff / 36e5);
                diff -= hours * 36e5;
                const minutes = Math.floor(diff / 6e4);
                diff -= minutes * 6e4;
                const seconds = Math.floor(diff / 1e3);

                function fmt(n) {
                    return n.toString().padStart(2, '0');
                }

                const timeString = `${fmt(hours)} : ${fmt(minutes)} : ${fmt(seconds)}`;
                el.textContent = timeString;
            });
}

        function viewPatientHistory(patientId) {
            // Abrir modal o página con historial del paciente
            window.open(`<?= site_url('patients/history/') ?>${patientId}`, '_blank');
        }

        function showComment(comment) {
            alert(comment); // Puedes reemplazar con un modal más elegante
        }

        // Limpiar interval al salir de la página
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
</div>