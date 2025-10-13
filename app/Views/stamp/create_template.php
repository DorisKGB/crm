<?php
$permissions2      = $login_user->permissions;
$stamp_permission  = get_array_value($permissions2, "stamp_permission");

// Cargar helper de orientación de documentos
helper('document_orientation');
?>
<div id="page-content" class="page-wrapper clearfix grid-button">
    <button id="openHelpModal" class="btn btn-dark position-fixed" onclick="abrirTutorialTimbre()" style="top: 90px; right: 30px; z-index: 1051;">
        <i class="fas fa-question-circle"></i> Asistente
    </button>
    <style>
        /* ========== WIZARD BASICS ========== */
        #wizard {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: .5rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 60vw;
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

        /* ========== STEP 1: UPLOAD AREA ========== */
        .upload-area {
            width: 100%;
            max-width: 100%;
            background: #fff;
            border: 2px dashed #bfdfff;
            border-radius: 24px;
            padding: 2rem 1.5rem 3rem;
            text-align: center;
            margin-bottom: 1rem;
            box-sizing: border-box;
            position: relative;
        }

        .upload-area__title {
            font-size: 1.5rem;
            margin-bottom: .5rem;
        }

        .upload-area__paragraph {
            font-size: .94rem;
            color: #666;
        }

        .upload-area__tooltip {
            color: #66b;
            cursor: pointer;
        }

        .upload-area__tooltip-data {
            position: absolute;
            top: -.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(-100%);
            background: #fff;
            border: 1px solid #bfdfff;
            padding: .5rem 1rem;
            font-size: .875rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s;
        }

        .upload-area__tooltip:hover .upload-area__tooltip-data {
            opacity: 1;
            visibility: visible;
        }

        .upload-area__drop-zoon {
            height: 11.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #bfdfff;
            border-radius: 15px;
            cursor: pointer;
            transition: border-color .2s;
            position: relative;
            margin-top: 1rem;
        }

        .upload-area__drop-zoon:hover,
        .drop-zoon--over {
            border-color: #0d6efd;
        }

        .drop-zoon__icon {
            font-size: 3rem;
            color: #0d6efd;
        }

        .drop-zoon__paragraph {
            margin-top: .5rem;
            color: #666;
        }

        .drop-zoon__loading-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            color: #0d6efd;
        }

        .drop-zoon__preview-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
            display: none;
            z-index: 10;
        }

        .upload-area__file-details {
            margin-top: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity .3s .3s;
        }

        .file-details--open {
            opacity: 1;
            visibility: visible;
        }

        .uploaded-file {
            display: flex;
            align-items: center;
            opacity: 0;
            transition: opacity .3s .6s;
        }

        .uploaded-file--open {
            opacity: 1;
        }

        .uploaded-file__icon {
            font-size: 3rem;
            color: #0d6efd;
            position: relative;
        }

        .uploaded-file__icon-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: .75rem;
        }

        .uploaded-file__name {
            margin-left: .5rem;
            color: #333;
        }

        .uploaded-file__info {
            position: relative;
            flex-grow: 1;
            margin-left: .5rem;
        }

        .uploaded-file__info::after {
            content: '';
            position: absolute;
            bottom: -.5rem;
            left: 0;
            height: .5rem;
            width: 0;
            background: #0d6efd;
            border-radius: .5rem;
            transition: width .8s .3s ease-in-out;
        }

        .uploaded-file__info--active::after {
            width: 100%;
        }

        /* hide native file input */
        #templateImage,
        #fileInput {
            display: none !important;
        }

        /* ========== STEP 2: CLINIC SELECT ========== */
        .interactive-clinic-select .clinic-card {
            flex: 0 0 48%;
            padding: .75rem;
            border: 1px solid #ddd;
            border-radius: .5rem;
            background: #f9f9f9;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            box-sizing: border-box;
            text-align: center;
        }

        .interactive-clinic-select .clinic-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .interactive-clinic-select .clinic-card.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        /* ========== STEP 3: PAGE SIZE ========== */
        .size-card {
            flex: 1;
            padding: 1rem;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: .5rem;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s, background .2s;
            box-sizing: border-box;
            background: #f5f5f5;
        }

        .size-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .size-card.selected {
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        /* ========== STEP 5: NAME ========== */
        .name-editable {
            min-height: 3rem;
            padding: .5rem;
            border: 1px solid #ddd;
            border-radius: .5rem;
            background: #f5f5f5;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-sizing: border-box;
        }

        .name-editable:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }

        .name-editable:empty:before {
            content: attr(data-placeholder);
            color: #888;
        }

        .name-help {
            font-size: .875rem;
            color: #666;
            margin-top: .3rem;
        }

        /*CANVAS */
        #canvasWrapper {
            max-height: 70vh;
            max-width: 100%;
            overflow: auto;
            position: relative;
            /* para que el .marker quede dentro */
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        #templateCanvas {
            display: block;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: crosshair;
            max-width: 100%;
            max-height: 100%;
        }

        #templateCanvas:hover {
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }

        .marker {
            position: absolute;
            width: 16px;
            height: 16px;
            background: red;
            border: 2px solid white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            animation: markerPulse 0.3s ease-out;
        }

        @keyframes markerPulse {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.8;
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        .wizard-step {
            display: none;
            animation: fadeIn .3s ease;
            max-height: calc(100vh - 300px) !important;
            /* ajustar según header/footer */
            overflow-y: auto;
            padding-right: 1rem;
        }

        .clinic-card {
            position: relative;
            /* tu estilos actuales… */
        }

        .clinic-card .select-circle {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: all .2s ease;
            cursor: pointer;
            z-index: 10;
        }

        .clinic-card .select-circle i {
            font-size: 12px;
            color: transparent;
        }

        .clinic-card.selected .select-circle {
            border-color: #0d6efd;
            background: #0d6efd;
        }

        .clinic-card.selected .select-circle i {
            color: #fff;
        }

        /* Ajustes para documentos landscape */
        .landscape-mode #canvasWrapper {
            max-height: 80vh;
            overflow: auto;
        }
        
        .landscape-mode #templateCanvas {
            max-width: 90%;
            max-height: 90%;
        }
    </style>
    
    <!-- CSS del helper de orientación -->
    <?= document_orientation_css() ?>

    <div class="card" style="min-height:90vh;">
        <div class="card-title d-flex align-items-center">
            <a href="javascript:history.back()" style="margin-left:20px !important;" class="fs-3 me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h3 class="flex-grow-1 text-center mb-0">
                <span class="badge badge-primary">Timbre por Plantilla</span>
                Crear Plantilla
            </h3>
        </div>
        <hr>

        <div id="wizard" class="m-4">
            <!-- HEADER -->
            <div class="wizard-header">
                <h4>
                    Paso <span id="step-number">1</span> de 6:
                    <span id="step-title">Subir documento</span>
                </h4>
            </div>

            <!-- BODY -->
            <div class="wizard-body">
                <form id="templateWizardForm" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- STEP 1: UPLOAD -->
                    <div class="wizard-step active" data-step="1">
                        <div class="upload-area">
                            <h2 class="upload-area__title">1. Subir tu archivo</h2>
                            <p class="upload-area__paragraph">
                                Puede ser PDF, Word o imagen
                                <span class="upload-area__tooltip">
                                    Extensiones:
                                    <span class="upload-area__tooltip-data">.pdf, .docx, .jpg, .png</span>
                                </span>
                            </p>

                            <div id="dropZoon" class="upload-area__drop-zoon">
                                <i class="fas fa-file-upload drop-zoon__icon"></i>
                                <p class="drop-zoon__paragraph">
                                    Arrastra el archivo aquí o haz clic para buscar
                                </p>
                                <span id="loadingText" class="drop-zoon__loading-text">Cargando…</span>
                                <img id="previewImage" class="drop-zoon__preview-image" draggable="false" />
                                <input type="file"
                                    id="fileInput"
                                    accept=".pdf,.doc,.docx,image/*" />
                            </div>

                            <div id="fileDetails" class="upload-area__file-details">
                                <h3 class="file-details__title">Archivo subido:</h3>
                                <div id="uploadedFile" class="uploaded-file">
                                    <div class="uploaded-file__icon">
                                        <i class="fas fa-file-alt"></i>
                                        <span class="uploaded-file__icon-text"></span>
                                    </div>
                                    <div id="uploadedFileInfo" class="uploaded-file__info">
                                        <span class="uploaded-file__name"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: CLÍNICA -->
                    <div class="wizard-step" data-step="2">
                        <div class="form-group interactive-clinic-select">
                            <label>2. Seleccionar clínica</label>
                            <input type="text" id="clinicSearch" class="form-control mb-2" placeholder="🔍 Buscar clínica...">
                            <div id="clinicList" class="d-flex flex-wrap gap-2"></div>
                            <input type="hidden" name="clinic_id" id="clinicSelect">
                        </div>
                    </div>

                    <!-- STEP 3: TAMAÑO -->
                    <div class="wizard-step" data-step="3">
                        <label>3. Seleccionar tamaño de página</label>
                        <div id="sizeContainer" class="d-flex gap-3 my-2">
                            <div class="size-card selected" data-size="carta">Carta</div>
                            <div class="size-card" data-size="oficio">Oficio</div>
                            <div class="size-card" data-size="a4">A4</div>
                        </div>
                        <input type="hidden" id="paperSize" name="page_size" value="carta">
                    </div>

                    <!-- STEP 4: MARCAR FIRMA -->
                    <div class="wizard-step" data-step="4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5><strong>4. Haz clic en el punto donde irá la firma</strong></h5>
                                <small id="orientationInfo" class="text-muted"></small>
                            </div>
                            <button type="button" id="rotateBtn" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-redo"></i> Rotar 90°
                            </button>
                        </div>

                        <!-- flex container - siempre horizontal -->
                        <div id="canvasContainer" class="d-flex align-items-start gap-4">
                            <!-- tu canvas -->
                            <div id="canvasWrapper" style="flex:1; min-width:0;">
                                <canvas id="templateCanvas"></canvas>
                                <div id="marker" class="marker" style="display:none;"></div>
                            </div>

                            <!-- Imagen de guía - siempre al lado -->
                            <div class="signature-guide text-center" style="flex:0 0 280px; min-width:280px;">
                                <img
                                    src="<?= base_url('assets/images/firma.png') ?>"
                                    alt="Guía: firma impresa en el punto seleccionado"
                                    class="img-fluid"
                                    style="max-width: 250px; border:1px dashed #ccc; padding:.5rem; background:#fafafa; border-radius:.5rem;" />
                                <p class="small text-muted mt-2">
                                    <i class="fas fa-info-circle"></i> Guía de posicionamiento de firma
                                </p>
                            </div>
                        </div>
                    </div>


                    <!-- STEP 5: NOMBRE -->
                    <div class="wizard-step" data-step="5">
                        <div class="form-group">
                            <label for="templateNameInput">5. Nombre de la plantilla</label>
                            <input
                                type="text"
                                id="templateNameInput"
                                name="template_name"
                                class="form-control"
                                placeholder="Ej. Plantilla A4 – Firma Superior"
                                maxlength="50"
                                required>
                            <small class="form-text text-muted">
                                Caracteres: <span id="nameCount">0</span>/50
                            </small>
                        </div>
                        <!-- botón oculto para disparar el envío -->
                        <button id="saveTemplate" style="display:none"></button>
                    </div>

                    <!-- STEP 6: PREVIEW + DESCRIPCIÓN -->
                    <div class="wizard-step" data-step="6">
                        <h4>6. Formulario de Solicitud del Timbre</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="previewDesc">Escribe AQUI toda la información del timbre.</label>
                                <textarea id="previewDesc" class="form-control" rows="5" maxlength="200"
                                    placeholder="Aquí va la descripción del timbre…" required style="min-height: 200px;"></textarea>
                                <div class="char-count text-end"><span id="previewDescCount">0</span>/200</div>
                            </div>
                            <div class="col-md-6">
                                <h5>Preview</h5>
                                <div class="preview-box">
                                    <!-- reutilizamos la imagen dibujada en el canvas -->
                                    <img id="finalPreviewImg" src="" width="50%" alt="Vista previa">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- FOOTER -->
            <div class="wizard-footer">
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

<!-- Modal explicativo didáctico para Crear Plantilla y Timbrar -->
<div class="modal fade" id="tutorialTimbreModal" tabindex="-1" aria-labelledby="tutorialTimbreLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tutorialTimbreLabel">
                    <i class="fas fa-graduation-cap"></i> Asistente: ¿Cómo Crear una Plantilla y Timbrar?
                </h5>
            </div>
            <div class="modal-body">
                <img src="<?= base_url('assets/images/asistente.png') ?>" width="100" class="mb-3" alt="Asistente Rubymed">
                <ol class="text-start px-4">
                    <li><strong>Sube tu documento</strong> en PDF, Word o imagen.</li>
                    <li><strong>Selecciona la clínica</strong> donde se usará esta plantilla.</li>
                    <li><strong>Elige el tamaño</strong> del documento (Carta, A4 u Oficio).</li>
                    <li><strong>Marca con un clic</strong> el punto donde debe ir la firma digital.</li>
                    <li><strong>Asigna un nombre</strong> a la plantilla que estás creando.</li>
                    <li><strong>Escribe la descripción del timbre</strong> y guarda el documento.</li>
                </ol>
                <p class="mt-3 text-muted">Este asistente te guiará paso a paso. Puedes regresar en cualquier momento si te equivocas.</p>
                <div class="mt-4">
                    <button class="btn btn-success btn-lg px-4" id="btnTutorial" data-bs-dismiss="modal">
                        <i class="fas fa-play"></i> Empezar ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="asistenteConfirmacionPlantilla" tabindex="-1" aria-labelledby="asistenteConfirmacionPlantillaLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="asistenteConfirmacionPlantillaLabel">
                    <i class="fas fa-robot"></i> ¡Plantilla creada exitosamente!
                </h5>
            </div>
            <div class="modal-body text-center">
                <img src="<?= base_url('assets/images/asistente.png') ?>" width="90" alt="Asistente Rubymed" class="mb-3">
                <p class="mb-3">Esta plantilla fue guardada correctamente. La próxima vez que desees crear un timbre usando esta plantilla, la encontrarás disponible en <strong>"Seleccionar plantilla"</strong>.</p>
                <p class="text-muted">No necesitas volver a subirla ni repetir el proceso. ¡Solo selecciona y continúa!</p>
                <button class="btn btn-success mt-3" data-bs-dismiss="modal" id="continuarTrasAsistente">
                    <i class="fas fa-check-circle"></i> Entendido, continuar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.17/mammoth.browser.min.js"></script>

<!-- JavaScript del helper de orientación -->
<?= document_orientation_js([
    'auto_rotate' => false, // No rotar automáticamente en create_template
    'show_rotate_button' => true,
    'show_orientation_info' => true,
    'threshold' => 1.2
]) ?>

<?= add_orientation_detection_to_upload() ?>

<script>
    function abrirAsistenteConfirmacion() {
        const asistenteModal = new bootstrap.Modal(document.getElementById("asistenteConfirmacionPlantilla"));
        asistenteModal.show();
        document.getElementById("continuarTrasAsistente").onclick = () => {
            currentStep++;
            showStep(currentStep);
        };
    }

    function abrirTutorialTimbre() {
        const tutorialModal = new bootstrap.Modal(document.getElementById("tutorialTimbreModal"));
        tutorialModal.show();
        const btn = document.getElementById("btnTutorial");
        btn.innerHTML = '<i class="fas fa-check-circle"></i> OK';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // — SCROLL TO CANVAS ON RESIZE IF NEEDED — (opcional)
        const tutorialModal = new bootstrap.Modal(document.getElementById("tutorialTimbreModal"));
        setTimeout(() => tutorialModal.show(), 800); // lo muestra tras 800ms

        // Variables de wizard
        const steps = document.querySelectorAll('.wizard-step');
        const titles = ["Subir documento", "Seleccionar clínica", "Seleccionar tamaño", "Marcar firma", "Nombre plantilla", "Vista previa y descripción"];
        let currentStep = 1;
        const totalSteps = steps.length;
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const stepNum = document.getElementById('step-number');
        const stepTitle = document.getElementById('step-title');
        // STEP 1: Upload Area
        const templateNameInput = document.getElementById('templateNameInput');
        const nameCount = document.getElementById('nameCount');
        templateNameInput.addEventListener('input', () => {
            const len = templateNameInput.value.length;
            nameCount.textContent = len;
        });

        function showStep(n) {
            if (n < 1 || n > totalSteps) return;
            steps.forEach(s => s.classList.remove('active'));
            const el = document.querySelector(`.wizard-step[data-step="${n}"]`);
            el?.classList.add('active');
            stepNum.textContent = n;
            stepTitle.textContent = titles[n - 1];
            prevBtn.disabled = (n === 1);
            nextBtn.innerHTML = n === totalSteps ?
                'Guardar <i class="fas fa-check"></i>' :
                'Siguiente <i class="fas fa-arrow-right"></i>';

            if (n === 6 && imageLoaded) {
                // 1) Asegurarnos de que el canvas tiene la última versión (imagen + marcador)
                updateCanvas(); // ya la tienes definida

                // 2) Sacar el dataURL
                const dataUrl = document.getElementById('templateCanvas')
                    .toDataURL('image/png');

                // 3) Ponerlo en el <img>
                const finalImg = document.getElementById('finalPreviewImg');
                finalImg.src = dataUrl;
                finalImg.style.display = 'block'; // en caso de que estuviera oculto
            }
        }
        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
        nextBtn.addEventListener('click', async () => {
            // Validaciones
            if (currentStep === 1 && !imageLoaded) return alert('Sube un archivo.');
            if (currentStep === 2 && !document.getElementById('clinicSelect').value)
                return alert('Selecciona una clínica.');
            if (currentStep === 4 && marker.style.display === 'none')
                return alert('Marca la posición de la firma.');
            if (currentStep === 5) {
                const name = templateNameInput.value.trim();
                if (!name) {
                    return alert('Escribe un nombre para la plantilla.');
                }
                showLoading('Guardando plantilla…');

                try {
                    console.log('🚀 Enviando datos de plantilla...');
                    
                    const requestData = {
                        name: document.getElementById('templateNameInput').value.trim(),
                        image: image.src,
                        coordinates: {
                            x: signatureCoordinates.x,
                            y: signatureCoordinates.y
                        },
                        page_size: document.getElementById('paperSize').value,
                        clinic_id: document.getElementById('clinicSelect').value,
                        orientation_data: window.documentOrientation ? window.documentOrientation.getBackendOrientationData() : null
                    };
                    
                    console.log('📤 Datos enviados:', {
                        name: requestData.name,
                        imageSize: requestData.image.length,
                        coordinates: requestData.coordinates,
                        page_size: requestData.page_size,
                        clinic_id: requestData.clinic_id,
                        orientation_data: requestData.orientation_data
                    });

                    const res = await fetch('<?= site_url("stamptemplate/create") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: JSON.stringify(requestData)
                    });
                    
                    console.log('📡 Respuesta del servidor:', {
                        status: res.status,
                        statusText: res.statusText,
                        headers: Object.fromEntries(res.headers.entries())
                    });
                    
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    }
                    
                    const data = await res.json();
                    console.log('✅ Datos recibidos:', data);
                    
                    hideLoading();
                    
                    if (!data.success) {
                        console.error('❌ Error del servidor:', data.message);
                        return showError(data.message || 'No se pudo guardar la plantilla.');
                    }
                    
                    console.log('🎉 Plantilla guardada exitosamente');
                    
                    // Mostrar asistente antes de pasar al paso 6
                    abrirAsistenteConfirmacion();

                    // avanzamos al siguiente paso
                    currentStep++;
                    showStep(currentStep);
                } catch (err) {
                    console.error('💥 Error al guardar plantilla:', err);
                    hideLoading();
                    
                    let errorMessage = 'Error de red al guardar la plantilla.';
                    if (err.message.includes('HTTP')) {
                        errorMessage = `Error del servidor: ${err.message}`;
                    } else if (err.message.includes('JSON')) {
                        errorMessage = 'Error al procesar la respuesta del servidor.';
                    }
                    
                    showError(errorMessage + '\n\nDetalles: ' + err.message);
                }
                return;

            }
            if (currentStep === 6) {
                // Validar descripción
                const desc = document.getElementById('previewDesc').value.trim();
                if (!desc) return alert('Escribe una descripción para el timbre.');

                showLoading('Guardando timbre…');
                const formData = new FormData();
                formData.append('clinic_id', document.getElementById('clinicSelect').value);
                formData.append('clinic_select', document.querySelector('.clinic-card.selected').textContent.trim());
                formData.append('size', document.getElementById('paperSize').value);
                formData.append('page_size', document.getElementById('paperSize').value);
                formData.append('contenido', document.getElementById('previewDesc').value.trim());
                let imagePath = image.src;

                // Si es una URL absoluta, la convertimos a ruta relativa sin extensión
                if (!imagePath.startsWith('data:image/')) {
                    const url = new URL(imagePath);
                    imagePath = url.pathname.replace('/index.php', ''); // Elimina /index.php si está
                    imagePath = imagePath.replace(/^\/+/, ''); // Quita slashes iniciales
                    imagePath = imagePath.replace(/\.(png|jpg|jpeg|gif)$/i, ''); // Quita extensión
                }

                formData.append('template_image', imagePath);

                formData.append('template_name', document.getElementById('templateNameInput').value.trim());
                formData.append('signature_x', signatureCoordinates.x);
                formData.append('signature_y', signatureCoordinates.y);
                
                // ✅ AGREGAR: Campos de orientación usando la nueva función del helper
                if (window.documentOrientation) {
                    const orientationData = window.documentOrientation.getBackendOrientationData();
                    console.log('✅ Datos de orientación obtenidos del helper:', orientationData);
                    
                    formData.append('orientation', orientationData.orientation);
                    formData.append('is_horizontal', orientationData.is_horizontal);
                    formData.append('rotation', orientationData.rotation);
                    formData.append('aspect_ratio', orientationData.aspect_ratio);
                    
                    console.log('📤 Campos de orientación enviados:');
                    console.log('  - orientation:', orientationData.orientation);
                    console.log('  - is_horizontal:', orientationData.is_horizontal);
                    console.log('  - rotation:', orientationData.rotation);
                    console.log('  - aspect_ratio:', orientationData.aspect_ratio);
                } else {
                    console.log('⚠️ Helper no disponible, enviando valores por defecto');
                    formData.append('orientation', 'portrait');
                    formData.append('is_horizontal', '0');
                    formData.append('rotation', '0');
                    formData.append('aspect_ratio', '1.00');
                }

                try {
                    const res = await fetch('<?= site_url("stamp/create2") ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: formData
                    });
                    const result = await res.json();
                    hideLoading();
                    if (result.success) {
                        window.location.href = '<?= site_url("stamp") ?>/stamp_success/' + result.stamp;
                    } else {
                        showError(result.message || 'No se pudo guardar el timbre.');
                    }
                } catch (err) {
                    hideLoading();
                    showError('Error de red al guardar el timbre.');
                }
                return;

            }

            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                document.getElementById('saveTemplate').click();
            }
        });
        showStep(1);


        const dropZone = document.getElementById('dropZoon');
        const fileInput = document.getElementById('fileInput');
        const loadingText = document.getElementById('loadingText');
        const previewImg = document.getElementById('previewImage');
        const detailsBox = document.getElementById('fileDetails');
        const uploaded = document.getElementById('uploadedFile');
        const nameSpan = uploaded.querySelector('.uploaded-file__name');
        const iconSpan = uploaded.querySelector('.uploaded-file__icon-text');
        const validExt = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];
        let imageLoaded = false,
            image = new Image(),
            signatureCoordinates = {
                x: null,
                y: null
            };

        // Hacer disponibles globalmente para el helper
        window.image = image;
        window.signatureCoordinates = signatureCoordinates;

        async function uploadFile(file) {
            const ext = file.name.toLowerCase().split('.').pop();
            if (!validExt.includes(ext)) return alert('Formato no soportado.');
            showLoading('Procesando…');
            try {
                // PDF
                if (ext === 'pdf') {
                    const buf = await file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument(new Uint8Array(buf)).promise;
                    const page = await pdf.getPage(1);
                    const pSize = document.getElementById('paperSize').value;
                    const dims = {
                        carta: {
                            w: 816,
                            h: 1056
                        },
                        oficio: {
                            w: 816,
                            h: 1248
                        },
                        a4: {
                            w: 793,
                            h: 1122
                        }
                    } [pSize];
                    const DPR = window.devicePixelRatio || 1;
                    const vp1 = page.getViewport({
                        scale: 1
                    });
                    const scale = Math.min((dims.w * DPR) / vp1.width, (dims.h * DPR) / vp1.height);
                    const vp = page.getViewport({
                        scale
                    });
                    const tmp = document.createElement('canvas');


                    tmp.width = vp.width;
                    tmp.height = vp.height;
                    await page.render({
                        canvasContext: tmp.getContext('2d'),
                        viewport: vp
                    }).promise;
                    image.src = tmp.toDataURL('image/png');
                }
                // Word
                else if (ext === 'doc' || ext === 'docx') {
                    const fd = new FormData();
                    fd.append('file', file);
                    const res = await fetch('https://convert.clinicahispanarubymed.com/docx-converter.php', {
                        method: 'POST',
                        body: fd
                    });
                    const d = await res.text();
                    if (!d.startsWith('data:image')) throw new Error('Conversión fallida');
                    image.src = d;
                }
                // Imágenes
                else {
                    const r = new FileReader();
                    r.onload = () => image.src = r.result;
                    r.readAsDataURL(file);
                }
                nameSpan.textContent = file.name;
                iconSpan.textContent = ext;
                detailsBox.classList.remove('file-details--open');
                uploaded.classList.remove('uploaded-file--open');
            } catch (err) {
                hideLoading();
                showError(err.message);
                return;
            }
        }
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('drop-zoon--over');
        });
        dropZone.addEventListener('dragleave', e => {
            e.preventDefault();
            dropZone.classList.remove('drop-zoon--over');
        });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('drop-zoon--over');
            uploadFile(e.dataTransfer.files[0]);
        });
        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', () => uploadFile(fileInput.files[0]));

        image.onload = () => {
            imageLoaded = true;
            
            // Procesar orientación usando el helper
            if (window.processImageWithOrientation) {
                processImageWithOrientation(image);
            }
            
            updateCanvas();
            previewImg.src = image.src;
            previewImg.style.display = 'block';
            detailsBox.classList.add('file-details--open');
            uploaded.classList.add('uploaded-file--open');
            document.getElementById('uploadedFileInfo').classList.add('uploaded-file__info--active');
            hideLoading();
        };
        image.onerror = () => {
            hideLoading();
            showError('No se pudo renderizar el archivo.');
        };

        // STEP 4: Canvas
        const canvas = document.getElementById('templateCanvas');
        const ctx = canvas.getContext('2d');
        const marker = document.getElementById('marker');

        const canvasWrapper = document.getElementById('canvasWrapper');
        canvasWrapper.addEventListener('wheel', function(e) {
            // Evita que la página padre también haga scroll
            e.preventDefault();

            // Desplaza el propio wrapper
            canvasWrapper.scrollTop += e.deltaY;
        });
        canvasWrapper.style.position = 'relative'; // refuerza el position: relative
        const paperDimensions = {
            carta: {
                portrait: { width: 816, height: 1056 },
                landscape: { width: 1056, height: 816 }
            },
            oficio: {
                portrait: { width: 816, height: 1248 },
                landscape: { width: 1248, height: 816 }
            },
            a4: {
                portrait: { width: 793, height: 1122 },
                landscape: { width: 1122, height: 793 }
            }
        };

        function updateCanvas() {
            const size = document.getElementById('paperSize').value;
            if (!imageLoaded) return;
            
            // Determinar orientación usando el helper o fallback
            let orientation = 'portrait';
            if (window.documentOrientation) {
                const originalImage = window.originalImageForDetection || window.documentOrientation.originalImage || image;
                const orientationData = window.documentOrientation.detectOrientation(originalImage);
                orientation = orientationData.orientation || 'portrait';
                console.log('✅ Orientación detectada para canvas:', orientation);
            } else {
                // Fallback: usar aspect ratio de la imagen
                const imgWidth = image.naturalWidth || image.width;
                const imgHeight = image.naturalHeight || image.height;
                const imgAspectRatio = imgWidth / imgHeight;
                if (imgAspectRatio > 1.2) {
                    orientation = 'landscape';
                }
                console.log('⚠️ Usando fallback para orientación:', orientation);
            }
            
            const d = paperDimensions[size][orientation];
            if (!d) {
                console.error('No se encontraron dimensiones para:', size, orientation);
                return;
            }
            
            const DPR = window.devicePixelRatio || 1;
            
            // Usar las dimensiones correctas del papel según la orientación
            const canvasWidth = d.width;
            const canvasHeight = d.height;
            
            console.log('Canvas dimensions:', { width: canvasWidth, height: canvasHeight, orientation });
            
            // Aplicar clase CSS para landscape
            const canvasContainer = document.getElementById('canvasContainer');
            if (orientation === 'landscape') {
                canvasContainer.classList.add('landscape-mode');
            } else {
                canvasContainer.classList.remove('landscape-mode');
            }
            
            canvas.width = canvasWidth * DPR;
            canvas.height = canvasHeight * DPR;
            canvas.style.width = canvasWidth + 'px';
            canvas.style.height = canvasHeight + 'px';
            ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
            ctx.clearRect(0, 0, canvasWidth, canvasHeight);
            ctx.drawImage(image, 0, 0, canvasWidth, canvasHeight);
        }
        document.getElementById('paperSize').addEventListener('change', updateCanvas);
        canvas.addEventListener('click', function(e) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Obtener las coordenadas relativas al contenedor del canvas
            const wrapperRect = canvasWrapper.getBoundingClientRect();
            
            // Considerar el scroll del contenedor
            const scrollLeft = canvasWrapper.scrollLeft;
            const scrollTop = canvasWrapper.scrollTop;
            
            const markerX = e.clientX - wrapperRect.left + scrollLeft;
            const markerY = e.clientY - wrapperRect.top + scrollTop;

            // posiciona el marcador exactamente donde está el cursor
            marker.style.left = markerX + 'px';
            marker.style.top = markerY + 'px';
            marker.style.display = 'block';

            console.log(`Clic en canvas: x=${x}, y=${y}`);
            console.log(`Marcador posicionado en: x=${markerX}, y=${markerY}`);
            console.log(`Scroll: left=${scrollLeft}, top=${scrollTop}`);

            // guarda coordenadas para el envío (relativas al canvas)
            signatureCoordinates = {
                x,
                y
            };
        });
        // STEP 2: Clinicas
        let clinics = [];
        fetch('<?= site_url("stamptemplate/clinicsAjax") ?>').then(r => r.json()).then(j => {
            if (j.success) {
                clinics = j.clinics;
                renderClinics(clinics);
            }
        });

        function renderClinics(list) {
            const ctn = document.getElementById('clinicList');
            ctn.innerHTML = '';
            list.forEach(c => {
                const card = document.createElement('div');
                card.className = 'clinic-card';
                card.innerHTML = `
      <div class="select-circle"><i class="fas fa-check"></i></div>
      ${c.name}
    `;
                card.dataset.id = c.id;
                card.onclick = () => {
                    ctn.querySelectorAll('.clinic-card').forEach(x => x.classList.remove('selected'));
                    card.classList.add('selected');
                    document.getElementById('clinicSelect').value = c.id;
                };
                ctn.append(card);
            });
        }
        document.getElementById('clinicSearch').addEventListener('input', e => {
            const q = e.target.value.toLowerCase();
            renderClinics(clinics.filter(c => c.name.toLowerCase().includes(q)));
        });

        // STEP 3: Tamaño
        document.querySelectorAll('.size-card').forEach(card => {
            card.onclick = () => {
                document.querySelectorAll('.size-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                document.getElementById('paperSize').value = card.dataset.size;
                updateCanvas();
            };
        });

        // STEP 5: Nombre


        // SUBMIT
        document.getElementById('saveTemplate').addEventListener('click', async function(e) {
            e.preventDefault();

            const name = document.getElementById('templateNameInput').value.trim();
            if (!image.src || signatureCoordinates.x === null || !name) {
                return showError('Debes subir un archivo, colocar la firma y poner nombre.');
            }

            showLoading('Enviando Datos...');
            const                 payload = {
                name: name, // <— coincide con $json->name
                image: image.src, // <— coincide con $json->image
                coordinates: {
                    x: signatureCoordinates.x,
                    y: signatureCoordinates.y
                }, // <— coincide con $json->coordinates->x / ->y
                page_size: document.getElementById('paperSize').value,
                clinic_id: document.getElementById('clinicSelect').value,
                                            // ✅ AGREGAR: Información de orientación para el backend
                            orientation_data: window.documentOrientation ? window.documentOrientation.getBackendOrientationData() : null
            };

            try {
                const res = await fetch('<?= site_url("stamptemplate/create") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                hideLoading();

                if (result.success) {
                    window.location.href = '<?= site_url("stamp/stamp_template?request=new.solicitud") ?>';
                } else {
                    showError(result.message || 'No se pudo guardar plantilla.');
                }
            } catch (err) {
                hideLoading();
                showError('Error de red al crear plantilla.');
                console.error(err);
            }
        });



    }); // end DOMContentLoaded
</script>