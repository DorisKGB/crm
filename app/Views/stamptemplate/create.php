<div id="page-content" class="page-wrapper clearfix grid-button">
    <style>
    #canvasWrapper {
        position: relative;
        display: inline-block;
        border: 1px solid #ccc;
    }

    .marker {
        position: absolute;
        width: 10px;
        height: 10px;
        background: red;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 10;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .checkbox-group {
        margin-bottom: 10px;
    }

    .form-group input[type="text"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        border: none !important;
        background-color: #f5f5f5;
        outline: none;
    }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-book-open"></i> <?php echo app_lang('stamp_template_text') ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="templateImage"><?php echo app_lang('select_image_stamp') ?></label>
                                <input type="file" class="form-control" id="templateImage"
       accept=".pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*">

                            </div>
                            <div class="form-group">
                                <label for=""><?php echo app_lang('select_input_stamp') ?> <small></small></label>
                                <select class="form-control" name="clinic_id" id="clinicSelect">
                                </select>
                                <div class="badge bg-primary"><small> <i class="fas fa-exclamation-triangle"></i>
                                        <?php echo app_lang('message_warning_stamp') ?></small></div>
                            </div>
                            <div class="form-group">
                                <label for="paperSize"><?php echo app_lang('select_size_sheet') ?></label>
                                <select id="paperSize" class="form-control">
                                    <option value="carta"><?php echo app_lang('tam_carta_text') ?></option>
                                    <option value="oficio"><?php echo app_lang('tam_oficio_text') ?></option>
                                    <option value="a4"><?php echo app_lang('tam_a4_text') ?></option>
                                </select>
                            </div>
                            <div id="canvasWrapper">
                                <canvas id="templateCanvas"></canvas>
                                <div id="marker" class="marker" style="display: none;"></div>
                            </div>
                            <br><br>
                            <div class="form-group">
                                <label for="name"><?php echo app_lang('name_stamp') ?></label>
                                <input type="text" class="form-control" id="name"
                                    placeholder="Ej. Plantilla A4 – Firma Superior">
                                <div class="badge bg-primary"><small><i class="fas fa-exclamation-triangle"></i>
                                        <?php echo app_lang('message_warning_stamp') ?></small></div>
                            </div>
                            <br>
                            <a href="<?php echo get_uri('stamp'); ?>" class="btn-rubymed btn-rubymed-secondary-in"><i
                                    class="fas fa-arrow-left"></i> Regresar</a>
                            <button class="btn-rubymed btn-rubymed-primary-in" id="saveTemplate">
                                <i class="fas fa-check"></i> <?php echo app_lang('save_stamp') ?>
                            </button>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://unpkg.com/mammoth/mammoth.browser.min.js"></script>

    <script>
    const csrfName = "<?= csrf_token() ?>";
    const csrfHash = "<?= csrf_hash() ?>";
    const processingModalEl = document.getElementById('processingModal');
    const processingModal = bootstrap.Modal.getOrCreateInstance(processingModalEl);
    const canvas = document.getElementById('templateCanvas');
    const ctx = canvas.getContext('2d');
    const marker = document.getElementById('marker');
    let image = new Image();
    let imageLoaded = false;
    let signatureCoordinates = {
        x: null,
        y: null
    };


    const paperDimensions = {
        carta: {
            width: 816,
            height: 1056
        },
        oficio: {
            width: 816,
            height: 1248
        },
        a4: {
            width: 793,
            height: 1122
        }
    };


    function updateCanvas() {
        const paperSize = document.getElementById('paperSize').value;
        const dims = paperDimensions[paperSize];
        if (!dims || !imageLoaded) return;

        const DPR = window.devicePixelRatio || 1;

        // Canvas físico en alta resolución
        canvas.width = dims.width * DPR;
        canvas.height = dims.height * DPR;

        // Canvas visible al usuario en tamaño lógico
        canvas.style.width = `${dims.width}px`;
        canvas.style.height = `${dims.height}px`;

        // Cada unidad lógica = DPR píxeles físicos
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);

        // Limpia y dibuja
        ctx.clearRect(0, 0, dims.width, dims.height);
        ctx.drawImage(image, 0, 0, dims.width, dims.height);

        hideLoading();
    }

    document.getElementById('templateImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        showLoading('Cargando imagen…');

        const reader = new FileReader();
        reader.onerror = () => showError('Error leyendo el archivo.');

        if (file.type === 'application/pdf') {
            reader.onload = evt => {
                pdfjsLib.getDocument(new Uint8Array(evt.target.result)).promise
                    .then(pdf => pdf.getPage(1))
                    .then(page => {

                        const paperSize = document.getElementById('paperSize').value;
                        const dims = paperDimensions[paperSize];
                        const DPR = window.devicePixelRatio || 1;

                        const vp1 = page.getViewport({
                            scale: 1
                        });
                        const scaleX = (dims.width * DPR) / vp1.width;
                        const scaleY = (dims.height * DPR) / vp1.height;
                        const scale = Math.min(scaleX, scaleY);

                        const vp = page.getViewport({
                            scale
                        });

                        const tmp = document.createElement('canvas');
                        tmp.width = vp.width;
                        tmp.height = vp.height;

                        tmp.style.width = `${dims.width}px`;
                        tmp.style.height = `${dims.height}px`;

                        return page.render({
                                canvasContext: tmp.getContext('2d'),
                                viewport: vp
                            })
                            .promise.then(() => tmp.toDataURL('image/png'));
                    })
                    .then(dataUrl => image.src = dataUrl) // dispara onload u onerror
                    .catch(err => showError('Error procesando PDF: ' + err.message));
            };
            reader.readAsArrayBuffer(file);

        } else if (file.type.startsWith('image/')) {
            reader.onload = evt => image.src = evt.target.result; // dispara onload u onerror
            reader.readAsDataURL(file);

        } else if (file.name.endsWith(".docx") || file.name.endsWith(".doc")) {
            showLoading("Convirtiendo documento Word a imagen...");

            const formData = new FormData();
            formData.append('file', file);

            fetch("https://convert.clinicahispanarubymed.com/docx-converter.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(dataUrl => {
                    console.log("Respuesta cruda del servidor:", dataUrl);
                    if (dataUrl.startsWith("data:image")) {
                        image.src = dataUrl; // Usas la misma variable 'image' que ya tienes
                    } else {
                        showError("El servidor no devolvió una imagen válida.");
                        console.warn("Respuesta del servidor:", dataUrl);
                    }
                })
                .catch(error => {
                    console.error(error);
                    showError("Error al convertir el documento. Intenta con otro archivo.");
                });
        } else {
            showError('Formato no soportado. Selecciona PDF o imagen.');
        }
    });


    // Cuando la imagen se carga, marcamos que está disponible y actualizamos el canvas
    image.onload = () => {
        // Dibuja en el canvas
        console.log("Imagen cargada, refrescando canvas con nuevo tamaño");
        imageLoaded = true;
        updateCanvas();

    };

    image.onerror = function() {
        showError('No se pudo renderizar la imagen seleccionada.');
    };

    // Escuchar cambios en el tamaño de papel para actualizar el canvas
    document.getElementById('paperSize').addEventListener('change', function() {
        console.log("cambiando tamaño.");
        updateCanvas();
    });

    // Capturar el clic en el canvas para obtener las coordenadas
    canvas.addEventListener('click', function(e) {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        signatureCoordinates = {
            x,
            y
        };
        // Posicionar el marcador visual
        marker.style.left = x + 'px';
        marker.style.top = y + 'px';
        marker.style.display = 'block';
    });

    // Enviar la plantilla (imagen, coordenadas, tamaño y opciones) al backend
    document.getElementById('saveTemplate').addEventListener('click', function() {

        const name = document.getElementById('name').value;
        if (!image.src || signatureCoordinates.x === null || name === "") {
            showError(
                'Debes seleccionar una imagen, definir la posición de la firma y poner un nombre a la plantilla'
            );
            return;
        }

        showLoading('Enviando Datos...');
        const paperSize = document.getElementById('paperSize').value;
        const clinic_id = document.getElementById('clinicSelect').value;
        const data = {
            name: name,
            image: image.src,
            coordinates: signatureCoordinates,
            page_size: paperSize,
            //options: templateOptions,
            clinic_id: clinic_id
        };

        console.log(JSON.stringify(data));

        fetch('<?= site_url("stamptemplate/create") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfHash
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                console.log(result);
                if (result.success) {
                    hideLoading();
                    alert('Plantilla guardada correctamente');
                    location.reload(); // Recargar la página
                } else {
                    hideLoading();
                    showError('No se pudo guardar plantilla.');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Error al crear plantilla.');
            });
    });

    const select = document.getElementById('clinicSelect');
    const loadingOption = document.createElement('option');
    fetch('<?= site_url("stamptemplate/clinicsAjax") ?>')
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(result => {
            // Limpiar el “Cargando…”
            <?php if($login_user->is_admin){ ?>
            select.innerHTML = '<option value=""><?php echo app_lang('message_all_clinics') ?></option>';
            <?php } ?>

            if (result.success) {
                result.clinics.forEach(clinic => {
                    const opt = document.createElement('option');
                    opt.value = clinic.id;
                    opt.textContent = clinic.name;
                    select.appendChild(opt);
                });
            } else {
                console.error('API devolvió success=false');

            }
        })
        .catch(error => {
            console.error('No se pudo cargar la lista de clínicas:', error);
            // Opcional: informar al usuario
            select.innerHTML = '<option value="">Error al cargar clínicas</option>';
        });
    </script>


</div>