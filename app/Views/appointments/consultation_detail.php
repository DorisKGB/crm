<div id="page-content" class="page-wrapper clearfix todo-page">
    <?php
    load_css([
        'assets/css/button.css',
    ]);
    ?>

    <style>
        :root {
            --primary-color: #2c5aa0;
            --secondary-color: #1e3a8a;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --red-primary: #dc2626;
            --red-secondary: #b91c1c;
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --red-gradient: linear-gradient(135deg, #ef4444 0%, var(--red-primary) 50%, var(--red-secondary) 100%);
        }

        /* Header compacto */
        .header-section {
            background: white;
            color: var(--primary-color);
            padding: 20px 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            border-radius: 20px;
        }


        .consultation-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 30px;
            align-items: center;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .patient-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .patient-details h2 {
            margin-bottom: 3px;
            font-size: 24px;
        }

        .consultation-time {
            color: var(--primary-color);
            font-size: 14px;
            margin-bottom: 2px;
        }

        .timer-section {
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 12px 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            min-width: 140px;
        }

        .countdown-timer {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .timer-label {
            font-size: 11px;
            opacity: 0.9;
        }

        /* Layout principal en grid */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin: 0 auto;
        }

        .left-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .right-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card-header {

            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header h3 {
            margin: 0;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* Información del paciente en grid compacto */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 3px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #e2e8f0;
            transform: translateX(3px);
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .info-content h4 {
            margin: 0 0 2px 0;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-content p {
            margin: 0;
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
        }

        /* Botón principal prominente */
        .join-consultation-btn {
            background: var(--red-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            padding: 18px 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
            width: 100%;
            justify-content: center;
            margin-bottom: 15px;
        }

        .join-consultation-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Acciones secundarias en grid */
        .secondary-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .btn-secondary {
            padding: 10px 15px;
            border: 2px solid var(--accent-color);
            background: transparent;
            color: var(--accent-color);
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-secondary.test-btn {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .btn-secondary.test-btn:hover {
            background: #d97706;
            border-color: #d97706;
        }

        /* Historial compacto */
        .history-item {
            background: #f8fafc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 3px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .history-item:hover {
            border-left-color: var(--accent-color);
            transform: translateX(3px);
            background: #e2e8f0;
        }

        .history-date {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .history-provider {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .history-comment {
            color: #1e293b;
            line-height: 1.4;
            font-size: 13px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .status-badge.pendiente {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .status-badge.finalizada {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .admin-alert {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 1px solid #93c5fd;
            color: #1e40af;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        /* Comentario especial si existe */
        .consultation-comment {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border: 1px solid #f59e0b;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .consultation-comment h4 {
            color: #92400e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .consultation-comment p {
            color: #78350f;
            margin: 0;
            line-height: 1.4;
        }

        /* Loading y VSee */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            max-width: 280px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #vseeBrowserCall {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9998;
            display: none;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .consultation-header {
                grid-template-columns: 1fr;
                gap: 15px;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .patient-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .secondary-actions {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 15px;
            }

            .main-content {
                padding: 0 15px;
            }
        }
    </style>

    <!-- Header compacto -->
    <div class="header-section">
        <div class="container-fluid">
            <?php if ($is_admin): ?>
                <div class="admin-alert">
                    <i class="fas fa-user-shield"></i>
                    <strong>Vista de Administrador:</strong> Entrando como Dr. <?= $consultation->provider_first_name ?> <?= $consultation->provider_last_name ?>
                </div>
            <?php endif; ?>

            <div class="consultation-header">
                <div class="patient-info">
                    <div class="patient-avatar">
                        <?= strtoupper(substr($consultation->patient_name, 0, 1)) ?>
                    </div>
                    <div class="patient-details">
                        <h2><?= $consultation->patient_name ?></h2>
                        <div class="consultation-time">
                            <i class="fas fa-calendar-alt"></i>
                            <?= date('m/d/Y', strtotime($consultation->appointment_date)) ?> a las
                            <?= date('h:i A', strtotime($consultation->appointment_time)) ?>
                        </div>
                        <?php if ($is_admin): ?>
                            <div class="consultation-time">
                                <i class="fas fa-user-md"></i>
                                Dr. <?= $consultation->provider_first_name ?> <?= $consultation->provider_last_name ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="timer-section">
                    <div class="countdown-timer" id="countdownTimer">00:00:00</div>
                    <div class="timer-label">Tiempo restante</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal en grid -->
    <div class="main-content">
        <!-- Columna izquierda -->
        <div class="left-column">
            <!-- Información del paciente -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Información del Paciente</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h4>Email</h4>
                                <p><?= $consultation->patient_email ?: 'No disponible' ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <h4>Teléfono</h4>
                                <p><?= $consultation->patient_phone ?: 'No disponible' ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="info-content">
                                <h4>ID Paciente</h4>
                                <p><?= $consultation->patient_id ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h4>Duración</h4>
                                <p><?= $consultation->duration_minutes ?? 30 ?> minutos</p>
                            </div>
                        </div>
                    </div>

                    <?php if ($consultation->comment): ?>
                        <div class="consultation-comment">
                            <h4><i class="fas fa-comment"></i> Motivo de Consulta</h4>
                            <p><?= $consultation->comment ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial médico -->
            <?php if (!empty($history)): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Historial Médico Reciente</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($history as $item): ?>
                            <div class="history-item">
                                <div class="history-date">
                                    <?= date('m/d/Y', strtotime($item->appointment_date)) ?> -
                                    <?= date('h:i A', strtotime($item->appointment_time)) ?>
                                </div>
                                <div class="history-provider">
                                    Dr. <?= $item->first_name ?> <?= $item->last_name ?>
                                    <span class="status-badge <?= strtolower($item->status) ?>">
                                        <?= $item->status ?>
                                    </span>
                                </div>
                                <?php if ($item->comment): ?>
                                    <div class="history-comment"><?= $item->comment ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha -->
        <div class="right-column">
            <!-- Unirse a consulta -->
            <div class="content-card">
                <div class="card-body">
                    <button class="btn-button btn-button-danger w-100 py-3 text-center" onclick="joinConsultation()">
                        <h4>
                            <i class="fas fa-video"></i>
                            <?php if ($is_admin): ?>
                                Entrar como Dr. <?= explode(' ', $consultation->provider_first_name . ' ' . $consultation->provider_last_name)[0] ?>
                            <?php else: ?>
                                Unirme a la Teleconsulta
                            <?php endif; ?>
                        </h4>
                    </button>
                    <div class="pt-2">
                        <a href="<?= site_url('appointments/teleconsultas') ?>" style="width:100% !important;" class="btn-ghost btn-ghost-danger w-100">
                            <i class="fas fa-arrow-left"></i>
                            Volver
                        </a>
                    </div>

 
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h5>Conectando a la consulta...</h5>
            <p>Por favor espere un momento</p>
        </div>
    </div>

    <!-- VSee Container -->
    <div id="vseeBrowserCall"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src='https://clinic.meet.vsee.com/libs/external_api.min.js' onload="console.log('VSee script cargado')" onerror="console.error('Error cargando VSee script')"></script>

    <script>
        // Verificar que el script se cargó correctamente
        window.addEventListener('load', function() {
            console.log('Página cargada completamente');
            console.log('JitsiMeetExternalAPI disponible:', typeof JitsiMeetExternalAPI !== 'undefined');
            if (typeof JitsiMeetExternalAPI === 'undefined') {
                console.error('VSee API no se cargó correctamente');
            }
        });

        // Datos de la consulta del servidor
        const consultationData = {
            patient_name: "<?= $consultation->patient_name ?>",
            appointment_date: "<?= $consultation->appointment_date ?>",
            appointment_time: "<?= $consultation->appointment_time ?>",
            token: "<?= $consultation->token ?>",
            // Usar las credenciales del provider (no del paciente)
            vsee_username: "<?= $vsee_credentials['username'] ?>",
            vsee_token: "<?= $vsee_credentials['token'] ?>",
            conference_id: "<?= $vsee_credentials['conference_id'] ?>"
        };

        const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
        const providerName = "<?= $consultation->provider_first_name ?> <?= $consultation->provider_last_name ?>";

        // Actualizar cronómetro
        function updateTimer() {
            // Debug: ver qué datos estamos recibiendo
            const appointmentDate = "<?= $consultation->appointment_date ?>";
            const appointmentTime = "<?= $consultation->appointment_time ?>";
            
            console.log('=== DEBUG TIMER ===');
            console.log('Fecha del servidor:', appointmentDate);
            console.log('Hora del servidor:', appointmentTime);
            
            // Parsear fecha y hora por separado
            const [year, month, day] = appointmentDate.split('-');
            const [hours, minutes] = appointmentTime.split(':');
            
            // Crear fecha local
            const appointmentDateTime = new Date(parseInt(year), parseInt(month) - 1, parseInt(day), parseInt(hours), parseInt(minutes), 0);
            const now = new Date();
            
            console.log('Fecha/hora de la cita creada:', appointmentDateTime);
            console.log('Fecha/hora actual:', now);
            console.log('Diferencia en milisegundos:', appointmentDateTime - now);
            
            const diff = Math.max(0, appointmentDateTime - now);

            const hoursLeft = Math.floor(diff / 36e5);
            const minutesLeft = Math.floor((diff % 36e5) / 6e4);
            const secondsLeft = Math.floor((diff % 6e4) / 1e3);

            console.log(`Tiempo calculado: ${hoursLeft}:${minutesLeft}:${secondsLeft}`);

            const timeString = `${hoursLeft.toString().padStart(2, '0')}:${minutesLeft.toString().padStart(2, '0')}:${secondsLeft.toString().padStart(2, '0')}`;
            document.getElementById('countdownTimer').textContent = timeString;

            // Cambiar color si faltan menos de 15 minutos
            const timerElement = document.getElementById('countdownTimer');
            if (hoursLeft === 0 && minutesLeft <= 15) {
                timerElement.style.color = '#ef4444';
            } else if (hoursLeft === 0 && minutesLeft <= 30) {
                timerElement.style.color = '#f59e0b';
            }
        }

        // Función para unirse a la consulta
        function joinConsultation() {
            if (!consultationData.token) {
                alert('Token de videollamada no disponible. Contacte al administrador.');
                return;
            }

            // Nueva URL de redirección
            const baseUrl = "https://www.teleconsulta.clinicahispanarubymed.com/videollamada.html";
            const token = consultationData.token;
            const type = "7194863520";
            
            const redirectUrl = `${baseUrl}?token=${encodeURIComponent(token)}&type=${type}`;
            
            // Mostrar overlay de carga (opcional)
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Pequeño delay para mostrar el loading y luego redirigir
            setTimeout(() => {
                // Redirigir automáticamente a la nueva URL
                //window.location.href = redirectUrl;
                window.open(redirectUrl, '_blank');
            }, 500);
        }

        // Funciones adicionales
        function testVSeeData() {
            console.log('=== TEST DE DATOS VSEE ===');
            console.log('consultationData completo:', consultationData);
            console.log('JitsiMeetExternalAPI disponible:', typeof JitsiMeetExternalAPI !== 'undefined');

            alert(`Datos VSee:
            Username: ${consultationData.vsee_username}
            Token: ${consultationData.vsee_token ? 'PRESENTE (' + consultationData.vsee_token.substring(0, 10) + '...)' : 'FALTANTE'}
            Conference ID: ${consultationData.conference_id}
            API VSee: ${typeof JitsiMeetExternalAPI !== 'undefined' ? 'CARGADA' : 'NO CARGADA'}`);
        }

        function downloadPatientInfo() {
            // Implementar descarga de información del paciente
            alert('Funcionalidad de descarga en desarrollo');
        }

        function sendMessage() {
            // Implementar envío de mensaje al paciente
            alert('Funcionalidad de mensajería en desarrollo');
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            updateTimer();
            setInterval(updateTimer, 1000);
        });

        // Prevenir salida accidental durante videollamada
        window.addEventListener('beforeunload', function(e) {
            if (document.getElementById('vseeBrowserCall').style.display === 'block') {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</div>