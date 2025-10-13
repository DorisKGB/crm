<?php
$permissions2      = $login_user->permissions;
$stamp_permission  = get_array_value($permissions2, "stamp_permission");
?>
<div id="page-content" class="page-wrapper clearfix grid-button">

    <style>
        /* ========== WIZARD BASICS ========== */
        #wizard {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: .5rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 60%;
        }

        #wizard .wizard-header {
            padding: 1rem;
            background: rgb(176, 199, 234);
            color: rgb(2, 51, 123) !important;
            font-weight: bold;
            color: #fff;
        }

        #wizard .wizard-header h4 {
            margin: 0;
            font-weight: normal;
        }

        #wizard .wizard-body {
            padding: 1rem;
            overflow-x: hidden;
            box-sizing: border-box;

            max-height: calc(100vh - 200px);
            /* ajusta 200px según el alto de header+footer */
            overflow-y: auto;
        }

        .wizard-step {
            display: none;
            animation: fadeIn .3s ease;
        }

        .wizard-step .step-text {
            flex: 1;
        }

        .wizard-step:nth-of-type(even) {
            flex-direction: row-reverse;
        }

        .wizard-step.active {
            display: block;
        }

        #wizard .wizard-footer {
            padding: 1rem;
            background: #f8f9fa;
            text-align: right;
            border-top: 1px solid #ddd;
        }

        #wizard .wizard-footer button {
            min-width: 120px;
            margin-left: .5rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wizard-step .step-img img {
            max-width: 350px;
            height: auto;
            display: block;
            border-radius: .5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Contenedor de flecha */
        .arrow-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation-delay: 0.5s;
        }

        /* Flecha animada */
        .arrow {
            width: 30px;
            height: 30px;
            border-left: 4px solid #555;
            border-bottom: 4px solid #555;
            transform: rotate(-45deg);
            animation: bounce 1.5s infinite;
            margin-top: 10px;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: rotate(-45deg) translateY(0);
            }

            50% {
                transform: rotate(-45deg) translateY(10px);
            }
        }

        /* Responsive y espaciado */
        .scan-intro {
            flex-wrap: wrap;
            text-align: center;
        }
    </style>
    <div class="card" style="min-height:90vh;">
        <div class="card-title d-flex align-items-center">
            <a href="javascript:history.back()" style="margin-left:20px !important;" class="fs-3 me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="flex-grow-1 text-center mb-0">
                <span class="badge badge-primary"><i class="fas fa-robot"></i> Asistente de Escaneo</span>
            </h3>
        </div>
        <hr>

        <div class="d-flex justify-content-center align-item-center">

            <div id="wizard" class="m-4">

                <!-- HEADER -->
                <div class="wizard-header">
                    <h4>
                        Paso <span id="step-number">1</span> de 14:
                        <span id="step-title">Conectar cable USB</span>
                    </h4>
                </div>

                <!-- BODY -->
                <div class="wizard-body">
                    <form id="scanWizardForm">
                        <!-- STEP 1 -->
                        <div class="wizard-step" data-step="1">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="step-text">
                                        <h2><strong><span class="badge badge-secondary">Conectar cable USB</span></strong></h2>
                                        <p>🔌 Inserta el cable USB del escáner en un puerto de tu PC.</p>
                                    </div>
                                    <div class="step-img">
                                        <img src="<?= base_url('assets/images/paso0.png') ?>" width="80%" alt="Paso 1: Conectar cable USB">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <img src="<?= base_url('assets/images/asistente.png') ?>" width="100%" alt="Asistente Rubymed">
                                </div>
                            </div>
                        </div>

                        <?php
                        $steps = [
                            ["Encender escáner", "🔦 Presiona el botón de encendido del escáner y verifica que la luz esté activa.", "paso1.png"],
                            ["Colocar documento", "📄 Abre el escáner y coloca el documento que desees escanear.", "paso2.png"],
                            ["Abrir menú Inicio", "🖥️ Pulsa la tecla <code>⊞ Windows</code> en tu teclado.", "paso3.png"],
                            ["Buscar \"Escanear\"", "✏️ Escribe <strong>Escanear</strong> en la barra de búsqueda.", "paso4.png"],
                            ["Abrir Windows Scan", "📂 Haz clic en la aplicación <strong>Windows Scan</strong>.", "paso5.png"],
                            ["Seleccionar dispositivo", "🔽 En el campo <strong>Dispositivo</strong>, elige tu escáner de la lista.", "paso6.png"],
                            ["Seleccionar formato: PDF", "📄 Marca la opción <strong>PDF</strong> para múltiples páginas.", "paso7.png"],
                            ["Pulsar \"Escanear\"", "📸 Haz clic en el botón <strong>Escanear</strong> para iniciar.", "paso8.png"],
                            ["Esperar a que termine", "⏳ No muevas el documento mientras el escaneo finaliza.", "paso9.png"],
                            ["Pulsar \"Guardar como...\"", "💾 Haz clic en <strong>Guardar como...</strong> para elegir ubicación.", "paso10.png"],
                            ["Elegir carpeta", "📁 Selecciona carpeta (p.ej. Escritorio &gt; Mis Escaneos).", "paso11.png"],
                            ["Nombrar archivo", "✏️ Escribe un nombre descriptivo, p.ej. <code>Documento_2025-07-01.pdf</code>.", "paso12.png"],
                            ["Confirmar guardar", "✅ Pulsa <strong>Guardar</strong> para finalizar.", "paso13.png"],
                        ];

                        $stepNum = 2;
                        foreach ($steps as $step) {
                            [$title, $desc, $img] = $step;
                        ?>
                            <!-- STEP <?= $stepNum ?> -->
                            <div class="wizard-step" data-step="<?= $stepNum ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="step-text">
                                            <h2><strong><span class="badge badge-secondary"><?= $title ?></span></strong></h2>
                                            <p><?= $desc ?></p>
                                        </div>
                                        <div class="step-img">
                                            <img src="<?= base_url("assets/images/{$img}") ?>" width="80%" alt="Paso <?= $stepNum ?>: <?= $title ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <img src="<?= base_url('assets/images/asistente.png') ?>" width="100%" alt="Asistente Rubymed">
                                    </div>
                                </div>
                            </div>
                        <?php
                            $stepNum++;
                        }
                        ?>
                    </form>
                </div>

                <!-- FOOTER -->
                <div class="wizard-footer">
                    <button id="skipToEndBtn" class="btn-rubymed btn-rubymed-warning">
                        <i class="fas fa-file-alt"></i> Ya tengo el documento escaneado
                    </button>


                    <button id="prevBtn" class="btn btn-secondary" disabled>
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button id="nextBtn" class="btn btn-primary">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>


        </div>
    </div>
    <!-- MODAL DE BIENVENIDA -->
    <div class="modal fade" id="welcomeWizardModal" tabindex="-1" aria-labelledby="welcomeWizardLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content text-center">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title w-100" id="welcomeWizardLabel">
                        <i class="fas fa-info-circle"></i> Bienvenido al Asistente de Escaneo
                    </h4>
                </div>
                <div class="modal-body">
                    <img src="<?= base_url('assets/images/asistente.png') ?>" width="120" alt="Asistente" class="mb-3">
                    <h3 class="fw-bold">📄 ¡Vamos a escanear tu documento!</h3>
                    <p style="font-size: 1.3rem;" class="text-muted">Por favor sigue todos los pasos en pantalla.<br>Es muy fácil y te iré guiando paso a paso.</p>
                    <div class="mt-4">
                        <button class="btn btn-success btn-lg px-4" data-bs-dismiss="modal">
                            <i class="fas fa-check-circle"></i> Estoy listo, empecemos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- MODAL CONFIRMACIÓN ESCANEO -->
    <div class="modal fade" id="scanConfirmModal" tabindex="-1" aria-labelledby="scanConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="scanConfirmLabel">
                        <i class="fas fa-robot"></i> Rubymed ItSupport
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <img src="<?= base_url('assets/images/asistente.png') ?>" alt="Asistente" width="120">
                    <h5 class="mt-3">¿Escaneaste y guardaste el documento correctamente?</h5>
                    <p class="text-muted">Esta acción es necesaria para continuar con el timbrado.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-success" id="confirmYesBtn"><i class="fas fa-check"></i> Sí, continuar</button>
                    <button class="btn btn-danger" id="confirmNoBtn"><i class="fas fa-redo"></i> No, repetir pasos</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.17/mammoth.browser.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Mostrar bienvenida al cargar
        $('#welcomeWizardModal').modal('show');


        const steps = document.querySelectorAll('.wizard-step');
        const titles = [
            'Conectar cable USB',
            'Encender escáner',
            'Colocar documento en el escáner',
            'Abrir menú Inicio',
            'Buscar "Escanear"',
            'Abrir Windows Scan',
            'Seleccionar dispositivo',
            'Seleccionar formato: PDF',
            'Pulsar "Escanear"',
            'Esperar a que termine',
            'Pulsar "Guardar como..."',
            'Elegir carpeta',
            'Nombrar archivo',
            'Confirmar guardar'
        ];
        let currentStep = 1;
        const totalSteps = steps.length;
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const stepNum = document.getElementById('step-number');
        const stepTitle = document.getElementById('step-title');

        function showStep(n) {
            steps.forEach(s => s.classList.remove('active'));
            document.querySelector(`.wizard-step[data-step="${n}"]`).classList.add('active');
            stepNum.textContent = n;
            stepTitle.textContent = titles[n - 1];
            prevBtn.disabled = n === 1;
            nextBtn.innerHTML = n === totalSteps ? 'Finalizar' : 'Siguiente <i class="fas fa-arrow-right"></i>';
        }

        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
        nextBtn.addEventListener('click', () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                $('#scanConfirmModal').modal('show');
            }
        });

        showStep(1);

        // Redirecciones según respuesta
        document.getElementById('confirmYesBtn').addEventListener('click', () => {
            window.location.href = "<?= site_url('stamp/stamp_direct') ?>"; // Cambia esta ruta si deseas otro destino
        });

        document.getElementById('confirmNoBtn').addEventListener('click', () => {
            $('#scanConfirmModal').modal('hide');
            currentStep = 1;
            showStep(currentStep);
        });

        document.getElementById('skipToEndBtn').addEventListener('click', () => {
            $('#scanConfirmModal').modal('show');
        });
    });
</script>