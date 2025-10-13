<?= load_css(["assets/js/flatpickr/flatpickr.min.css"]) ?>
<?= load_js(["assets/js/flatpickr/flatpickr.min.js"]) ?>
<div class="modal fade" id="modalAgendarCita" tabindex="-1" aria-labelledby="modalAgendarCitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height: 90vh;">
            <style>
                /* Estilos generales del wizard */
                #wizardForm .wizard-step {
                    animation: fadeInStep 0.3s ease-in-out;
                }

                @keyframes fadeInStep {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                #wizardSteps {
                    list-style: none;
                    padding-left: 0;
                    border-left: 3px solid #eee;
                    position: relative;
                }

                #wizardSteps .step-item {
                    padding-left: 1rem;
                    position: relative;
                    color: #6b7280;
                    font-size: 15px;
                    transition: all 0.3s ease;
                }

                #wizardSteps .step-item::before {
                    content: "";
                    width: 12px;
                    height: 12px;
                    background-color: #d1d5db;
                    border-radius: 50%;
                    position: absolute;
                    left: -9px;
                    top: 5px;
                    z-index: 1;
                    transition: background-color 0.3s ease;
                }

                #wizardSteps .step-item.active {
                    font-weight: 600;
                    color: #4f46e5;
                }

                #wizardSteps .step-item.active::before {
                    background-color: #4f46e5;
                }

                #wizardSteps .step-item.completed::before {
                    background-color: #10b981;
                    content: "✓";
                    color: white;
                    font-size: 8px;
                    text-align: center;
                    line-height: 12px;
                }

                /* Botones de navegación */
                #wizardForm .btn {
                    min-width: 120px;
                    transition: 0.2s ease;
                }

                #wizardForm .btn-outline-secondary:hover {
                    background-color: #f3f4f6;
                    border-color: #d1d5db;
                }

                /* Inputs */
                #wizardForm .form-control {
                    border-radius: 8px;
                    border: 1px solid #d1d5db;
                    transition: border 0.3s ease;
                }

                #wizardForm .form-control:focus {
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
                }

                #wizardForm .form-control.is-invalid {
                    border-color: #dc3545;
                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                }

                /* Mensajes de disponibilidad */
                #provider-availability.alert {
                    border-radius: 8px;
                    font-size: 0.9rem;
                    padding: 10px 15px;
                }

                /* Select2 estilos para mantener coherencia */
                .select2-container--default .select2-selection--single {
                    border-radius: 8px;
                    border: 1px solid #d1d5db;
                    height: 38px;
                    padding: 5px 10px;
                }

                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: 26px;
                    font-size: 14px;
                    color: #374151;
                }

                .select2-container--default .select2-selection--single .select2-selection__arrow {
                    height: 38px;
                }

                /* Animación de transición entre pasos */
                .wizard-step {
                    transition: all 0.3s ease;
                }

                #medico-results {
                    max-height: 200px;
                    overflow-y: auto;
                    display: none;
                }

                #medico-results .list-group-item {
                    cursor: pointer;
                }

                .autocomplete-box {
                    position: absolute;
                    background: white;
                    border: 1px solid #ccc;
                    width: 100%;
                    z-index: 999;
                    max-height: 200px;
                    overflow-y: auto;
                }

                .result-item {
                    padding: 10px;
                    cursor: pointer;
                }

                .result-item:hover {
                    background-color: #f1f1f1;
                }

                #medico-results {
                    position: absolute;
                    width: 100%;
                    z-index: 999;
                    background-color: #fff;
                    border: 1px solid #ccc;
                    border-top: none;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    max-height: 250px;
                    overflow-y: auto;
                    border-radius: 4px;
                }

                .search-result-item {
                    padding: 10px 15px;
                    border-bottom: 1px solid #f1f1f1;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    transition: background-color 0.2s;
                }

                .step-subtext {
                    font-size: 13px;
                    margin-top: 3px;
                    line-height: 1.4;
                }

                .search-result-item:hover {
                    background-color: #f4f4f4;
                }

                .validation-error {
                    color: #dc3545;
                    font-size: 0.875rem;
                    margin-top: 0.25rem;
                    display: none;
                }

                /* Estilos para el nuevo paso 6 */
                @keyframes checkmarkAppear {
                    0% {
                        opacity: 0;
                        transform: scale(0) rotate(-180deg);
                    }

                    100% {
                        opacity: 1;
                        transform: scale(1) rotate(0deg);
                    }
                }

                @keyframes slideInUp {
                    0% {
                        opacity: 0;
                        transform: translateY(30px);
                    }

                    100% {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes float {

                    0%,
                    100% {
                        transform: translateY(0px) rotate(0deg);
                    }

                    50% {
                        transform: translateY(-20px) rotate(5deg);
                    }
                }

                .copy-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
                    background: linear-gradient(135deg, #38a169, #2f855a) !important;
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

                .share-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
                    color: white !important;
                    text-decoration: none !important;
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
            </style>

            <form id="wizardForm" enctype="multipart/form-data" class="h-100 d-flex">
                <?= csrf_field() ?>

                <!-- Sidebar de pasos -->
                <div class="bg-light p-4 border-end w-25 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="mb-4"><i class="fas fa-stethoscope"></i><b> Agendar Teleconsulta</b></h5>
                        <ul class="wizard-menu list-unstyled px-3 pt-3">
                            <li class="mb-4 step-item" data-step="1">
                                <span class="fw-bold text-primary">
                                    <i class="fas fa-stethoscope me-2"></i> Seleccionar Nurse Practitioner
                                </span>
                                <div id="wizard-selected-provider" class="step-subtext text-muted small ps-4"></div>
                            </li>
                            <li class="mb-4 step-item" data-step="2">
                                <span class="fw-bold text-muted">
                                    <i class="fas fa-user me-2"></i> Seleccionar Paciente
                                </span>
                                <div id="wizard-selected-patient" class="step-subtext text-muted small ps-4"></div>
                            </li>
                            <li class="mb-4 step-item" data-step="3">
                                <span class="fw-bold text-muted">
                                    <i class="fas fa-calendar-alt me-2"></i> Fecha y Hora
                                </span>
                                <div id="wizard-selected-date" class="step-subtext text-muted small ps-4"></div>
                            </li>
                            <li class="mb-4 step-item" data-step="4">
                                <span class="fw-bold text-muted">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> Costo y Referencia
                                </span>
                                <div id="wizard-selected-cost" class="step-subtext text-muted small ps-4"></div>
                            </li>
                            <li class="step-item" data-step="5">
                                <span class="fw-bold text-muted">
                                    <i class="fas fa-check-circle me-2"></i> Confirmar
                                </span>
                                <div id="wizard-confirm-info" class="step-subtext text-muted small ps-4"></div>
                            </li>

                            <li class="step-item" data-step="6">
                                <span class="fw-bold text-muted">
                                    <i class="fas fa-video me-2"></i> Link Generado
                                </span>
                                <div id="wizard-link-info" class="step-subtext text-muted small ps-4"></div>
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>

                <!-- Contenido del wizard -->
                <div class="p-4 flex-fill overflow-auto">

                    <!-- Paso 1: Selección de Médico -->
                    <div class="wizard-step step-1">
                        <div class="form-group position-relative">
                            <h4><b>Selecciona Nurse Practitioner</b></h4>
                            <input type="text" class="form-control" id="medico-search" placeholder="Buscar Nurse Practitioner...">
                            <div class="validation-error" id="medico-error"></div>
                            <button type="button" class="btn-ghost btn-ghost-warning mt-2" id="btn-change-provider">
                                <i class="fas fa-sync-alt"></i> Cambiar Nurse Practitioner
                            </button>
                            <div id="medico-results" class="list-group position-absolute w-100" style="z-index: 9999;"></div>
                            <input type="hidden" id="medico-id" name="provider_id">
                        </div>

                        <div id="medico-info" class="mt-4 border-top pt-3 d-none">
                            <h6 class="text-muted"><i class="fas fa-user-md"></i> Información del Nurse Practitioner</h6>
                            <div id="medico-info-content" class="small text-secondary"></div>
                        </div>
                    </div>

                    <!-- Paso 2: Selección de Paciente -->
                    <div class="wizard-step step-2 d-none">
                        <div class="form-group position-relative">
                            <h4><b>Selecciona el Paciente</b></h4>
                            <input type="text" id="search-patient" placeholder="Buscar paciente..." class="form-control">
                            <div class="validation-error" id="patient-error"></div>
                            <input type="hidden" id="patient-id" name="patient_id">
                            <div id="results-patients" class="autocomplete-box"></div>
                            <button type="button" class="btn-ghost btn-ghost-danger mt-2" onclick="changePatient()">
                                <i class="fas fa-sync-alt"></i> Cambiar Paciente
                            </button>
                        </div>

                        <!-- Información del paciente -->
                        <div id="paciente-info" class="mt-4 border-top pt-3 d-none">
                            <h6 class="text-muted"><i class="fas fa-user"></i> Información del Paciente</h6>
                            <div id="paciente-info-content" class="small text-secondary"></div>
                        </div>
                    </div>

                    <!-- Paso 3: Fecha y Hora -->
                    <div class="wizard-step step-3 d-none">
                        <h4><b>Selecciona la Fecha y Hora de la Teleconsulta</b></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label><b>Fecha</b></label>
                                <input type="text" class="form-control flatpickr" name="date" id="appointment_date" placeholder="MM/DD/YYYY" required>
                                <div class="validation-error" id="date-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label><b>Hora</b></label>
                                <input type="time" name="time" id="appointment_time" class="form-control" required>
                                <div class="validation-error" id="time-error"></div>
                            </div>
                        </div>
                        <div id="provider-availability" class="alert mt-3 d-none"></div>
                        <div class="mt-3">
                            <label><b>Duración</b> (min)</label>
                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" value="30" min="15" max="120">
                        </div>
                    </div>

                    <!-- Paso 4: Costo y Referencias -->
                    <div class="wizard-step step-4 d-none">
                        <h4><b>Selecciona el costo de la Teleconsulta</b></h4>
                        <label><b>Costo de la Consulta</b></label>
                        <input type="number" step="0.01" name="price" id="price" value="25" class="form-control" placeholder="Ej: 45.00" min="0">
                        <div class="validation-error" id="price-error"></div>

                        <label class="mt-3"><b>Referencia / Imagen o PDF</b></label>
                        <div id="upload-area" class="border p-3 rounded text-center" style="cursor:pointer; background:#f9f9f9;">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
                            <p class="mb-0 mt-2">Haz clic o arrastra un archivo (JPG, PNG, PDF)</p>
                            <input type="file" name="reference_image" id="reference_file" accept="image/*,.pdf" class="d-none">
                        </div>
                        <div id="preview-area" class="mt-3 text-center d-none">
                            <div id="preview-content"></div>
                            <button type="button" class="btn btn-sm btn-danger mt-2" id="remove-file">
                                <i class="fas fa-times-circle"></i> Quitar archivo
                            </button>
                        </div>

                        <label class="mt-3"><b>Comentario</b> (opcional)</label>
                        <textarea name="comment" id="comment" class="form-control" placeholder="Ej: Seguimiento de resultado..."></textarea>
                    </div>

                    <!-- Paso 5: Confirmación -->
                    <div class="wizard-step step-5 d-none text-center">
                        <i class="fas fa-video fa-3x text-success mb-3"></i>
                        <h4>¡Listo! Presiona Guardar para generar la videollamada.</h4>
                        <p>Se verificará disponibilidad y se generará un link seguro automáticamente.</p>

                        <!-- Resumen de la cita -->
                        <div id="appointment-summary" class="mt-4 p-3 bg-light rounded text-left">
                            <h6><b>Resumen de la Teleconsulta:</b></h6>
                            <div id="summary-content"></div>
                        </div>

                        <button type="submit" class="btn-button btn-button-success mt-3">
                            <i class="fas fa-check-circle"></i> Confirmar y Agendar
                        </button>
                    </div>

                    <div class="wizard-step step-6 d-none">
                        <div class="text-center" style="padding: 30px;">
                            <div class="success-checkmark mb-4" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #48bb78, #38a169); display: flex; align-items: center; justify-content: center; margin: 0 auto; animation: checkmarkAppear 1s ease-out; box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);">
                                <i class="fas fa-check fa-2x text-white"></i>
                            </div>

                            <h3 class="success-title mb-3" style="color: #2d3748; font-weight: 700; animation: slideInUp 0.8s ease-out 0.2s both;">
                                ¡Teleconsulta Creada Exitosamente!
                            </h3>
                            <p class="success-subtitle mb-4" style="color: #718096; animation: slideInUp 0.8s ease-out 0.4s both;">
                                El link de la videollamada ha sido generado. Compártelo con el paciente.
                            </p>

                            <!-- Contenedor del Link con efectos -->
                            <div class="link-container" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 25px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin: 20px 0;">
                                <div class="floating-icons" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; pointer-events: none; z-index: 1;">
                                    <i class="fas fa-video floating-icon fa-lg" style="position: absolute; color: rgba(255,255,255,0.2); top: 20%; left: 10%; animation: float 4s ease-in-out infinite;"></i>
                                    <i class="fas fa-stethoscope floating-icon fa-lg" style="position: absolute; color: rgba(255,255,255,0.2); top: 60%; right: 15%; animation: float 4s ease-in-out infinite 1s;"></i>
                                    <i class="fas fa-heartbeat floating-icon fa-lg" style="position: absolute; color: rgba(255,255,255,0.2); bottom: 30%; left: 20%; animation: float 4s ease-in-out infinite 2s;"></i>
                                    <i class="fas fa-user-md floating-icon fa-lg" style="position: absolute; color: rgba(255,255,255,0.2); top: 40%; right: 30%; animation: float 4s ease-in-out infinite 3s;"></i>
                                </div>

                                <div class="link-display" style="background: rgba(255,255,255,0.95); border-radius: 10px; padding: 15px; position: relative; z-index: 2; border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            <i class="fas fa-link me-2"></i>Link de Teleconsulta
                                        </h6>
                                        <div class="badge bg-success">
                                            <i class="fas fa-shield-alt me-1"></i>Seguro
                                        </div>
                                    </div>

                                    <p class="link-text mb-3" id="teleconsulta-link" style="font-family: 'Courier New', monospace; font-size: 14px; color: #2d3748; word-break: break-all; line-height: 1.4; margin: 0; padding: 10px; background: #f7fafc; border-radius: 8px; border-left: 4px solid #4299e1;">
                                        Generando link...
                                    </p>

                                    <div class="d-flex gap-2">
                                        <button class="copy-btn flex-grow-1" id="copy-link-btn" style="background: linear-gradient(135deg, #48bb78, #38a169); border: none; color: white; padding: 12px 20px; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                                            <i class="fas fa-copy me-2"></i>
                                            <span>Copiar Link</span>
                                        </button>
                                        <button class="btn btn-outline-primary" id="qr-toggle-btn">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Código QR (inicialmente oculto) -->
                            <div class="qr-code-container d-none" id="qr-container" style="background: white; padding: 20px; border-radius: 12px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <h6 class="text-center text-muted mb-3">
                                    <i class="fas fa-qrcode me-2"></i>Código QR para acceso rápido
                                </h6>
                                <div class="text-center" id="qr-code">
                                    <div style="width: 150px; height: 150px; background: #f0f0f0; margin: 0 auto; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;">
                                        <i class="fas fa-qrcode fa-3x text-muted"></i>
                                    </div>
                                </div>
                                <p class="small text-muted text-center mt-2 mb-0">
                                    El paciente puede escanear este código para acceder directamente
                                </p>
                            </div>

                            <!-- Botones de compartir -->
                            <div class="share-buttons d-flex gap-3 justify-content-center mt-4">
                                <a href="#" class="share-btn whatsapp btn text-white text-decoration-none" id="share-whatsapp" style="background: linear-gradient(135deg, #25d366, #1da851); padding: 12px 16px; border-radius: 8px; transition: all 0.3s ease;">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                                <a href="#" class="share-btn email btn text-white text-decoration-none d-none" id="share-email" style="background: linear-gradient(135deg, #ea4335, #d33b2c); padding: 12px 16px; border-radius: 8px; transition: all 0.3s ease;">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                                <a href="#" class="share-btn sms btn text-white text-decoration-none d-none" id="share-sms" style="background: linear-gradient(135deg, #007aff, #0056cc); padding: 12px 16px; border-radius: 8px; transition: all 0.3s ease;">
                                    <i class="fas fa-sms me-2"></i>SMS
                                </a>
                            </div>

                            <!-- Lista de características -->
                            <div class="feature-list mt-4" style="text-align: left; max-width: 400px; margin: 25px auto;">
                                <div class="feature-item d-flex align-items-center gap-3 py-2" style="color: #4a5568;">
                                    <div class="feature-icon" style="width: 24px; height: 24px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <span>El link estará activo 15 minutos antes de la cita</span>
                                </div>
                                <div class="feature-item d-flex align-items-center gap-3 py-2" style="color: #4a5568;">
                                    <div class="feature-icon" style="width: 24px; height: 24px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <span>Conexión segura y encriptada</span>
                                </div>
                                <div class="feature-item d-flex align-items-center gap-3 py-2" style="color: #4a5568;">
                                    <div class="feature-icon" style="width: 24px; height: 24px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <span>Compatible con dispositivos móviles</span>
                                </div>
                                <div class="feature-item d-flex align-items-center gap-3 py-2" style="color: #4a5568;">
                                    <div class="feature-icon" style="width: 24px; height: 24px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <span>No requiere instalación de software</span>
                                </div>
                            </div>

                            <!-- Botón para finalizar -->
                            <button type="button" class="btn btn-success btn-lg mt-4" id="finish-wizard-btn">
                                <i class="fas fa-check-circle me-2"></i>Finalizar
                            </button>
                        </div>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn-ghost btn-ghost-danger" id="btnPrev" disabled>
                            <i class="fas fa-arrow-left"></i> Atrás
                        </button>
                        <button type="button" class="btn-ghost btn-ghost-primary" id="btnNext">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
                        showError('medico-error', 'Debes seleccionar un Nurse Practitioner');
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
                    <strong>Profesional:</strong> ${provider}<br>
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
                <strong>Nombre:</strong> ${res.full_name}<br>
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
                    const teleconsultaLink = `https://teleconsulta.clinicahispanarubymed.com/consulta.html?token=${res.token}&type=3847629051`;
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

        // Email
        $('#share-email').attr('href', `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(message)}`);

        // SMS
        $('#share-sms').attr('href', `sms:?body=${encodeURIComponent(message)}`);
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
</script>