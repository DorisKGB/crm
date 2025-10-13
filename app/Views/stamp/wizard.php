<style>
  .step {
    display: none;
    animation: fadeIn 0.3s ease;
  }

  .step.active {
    display: block;
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

  .mode-card {
    width: 140px;
    border-radius: .5rem;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
  }

  .mode-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .mode-card.selected {
    background: #f0ebeb !important;
  }

  .btn-scan {
    border-radius: 2rem;
    padding: .5rem 1.5rem;
  }

  #previewContainer {
    border: 2px dashed #ced4da;
    border-radius: .5rem;
    min-height: 200px;
    position: relative;
    overflow: auto;
  }

  #docCanvasWrapper {
    border: 1px solid #ddd;
    border-radius: .5rem;
    height: 300px;
    overflow: auto;
    position: relative;
  }

  .progress {
    height: .5rem;
    border-radius: .25rem;
  }

  .progress-bar {
    transition: width .3s ease;
  }

  .template-item.selected {
    border: 2px solid #0d6efd !important;
    background: #f0ebeb !important;
    border-radius: 15px;
  }

  /* contenedor para la vista previa */
  .preview-box {
    height: 70vh;
    border: 5px solid #c3c1c1;
    border-radius: 15px;
    overflow: hidden;
    /* recorta el exceso cuando hace zoom   */
    display: flex;
    /* centra la imagen verticalmente       */
    align-items: center;
  }

  /* imagen con efecto zoom */
  .preview-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    /* la imagen completa siempre visible   */
    transition: transform .4s ease;
    cursor: zoom-in;
    /* icono de lupa                        */
  }

  .preview-box:hover img {
    transform: scale(1.25);
    /* ¡zoom! cambia el factor a tu gusto   */
  }

  .interactive-btn {
    display: block;
    width: 100%;
    padding: 1rem 0;
    /* más grande verticalmente */
    background: #333;
    /* fondo oscuro para contraste */
    color: #eee;
    /* texto claro */
    font-size: 1.25rem;
    /* texto más grande */
    font-weight: bold;
    border: none;
    border-radius: .75rem;
    cursor: pointer;
    overflow: hidden;
    filter: grayscale(100%);
    /* escala de grises */
    animation:
      float 4s ease-in-out infinite,
      /* movimiento vertical */
      pulse 2s infinite;
    /* brillo sutil (puedes quitar pulse si no lo quieres) */
    transition: filter .3s ease;
  }

  .interactive-btn:hover {
    filter: grayscale(0%);
    /* al pasar el ratón recupera el color */
  }

  /* Movimiento vertical suave */
  @keyframes float {
    0% {
      transform: translateY(0px);
    }

    50% {
      transform: translateY(-10px);
    }

    100% {
      transform: translateY(0px);
    }
  }

  /* Brillo sutil alrededor (opcional) */
  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 rgba(255, 255, 255, 0.2);
    }

    50% {
      box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
    }

    100% {
      box-shadow: 0 0 0 rgba(255, 255, 255, 0.2);
    }
  }

  #canvasWrapper {
    position: relative;
    max-width: 90vw;
    /* o 816px si quieres exacto carta */
    max-height: 80vh;
    /* adapta al alto de tu modal */
    border: 1px solid #ddd;
    overflow: auto;
    margin: 0 auto;
  }

  /* Quita estas líneas para que el canvas no se estire al 100% */
  #canvasWrapper canvas {
    display: block !important;
    width: auto !important;
    height: auto !important;
  }

  #selected-template-image-container {
    position: relative;
    display: inline-block;
    /* igual al displayW */
  }

  #selected-template-image-container img {
    display: block;
    width: 100%;
    height: auto;
  }

  #selected-template-image-container .sig-dot {
    position: absolute;
    width: 12px;
    height: 12px;
    background: red;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
  }
</style>

<!-- Modal fullscreen -->
<div class="modal fade" id="createStampModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header border-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formTimbrado" method="post" enctype="multipart/form-data" action="<?= site_url('stamp/save') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="mode" id="modeInput">
          <input type="hidden" name="template_id" id="templateInput">
          <input type="hidden" name="dot_x" id="dotXInput">
          <input type="hidden" name="dot_y" id="dotYInput">

          <!-- Progress + Paso -->
          <div class="mb-2">
            <small class="text-muted">Paso <span id="wizardStep">1</span> de 5</small>
            <div class="progress">
              <div id="progressBar" class="progress-bar bg-success" style="width:0%"></div>
            </div>
          </div>

          <!-- STEP 1: Modo -->
          <div id="step1" class="step active">
            <h2 class="text-center mb-3">¿Cómo deseas timbrar?</h2>
            <div class="row">

              <div class="col-md-6">
                <div class="card mode-card" data-mode="directo" style="width: 100%;">
                  <div class="card-body text-center p-3">
                    <img src="<?= base_url('assets/images/stamp_directo.png') ?>" style="border-radius:15px;" width="300" height="300" class=" mb-2" alt="Directo">
                    <h5>Directo</h5>
                    <p class="text-muted">Escanea documento físico y coloca firma.</p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card mode-card" data-mode="plantilla" style="width: 100%;">
                  <div class="card-body text-center p-3">
                    <img src="<?= base_url('assets/images/stamp_temas.png') ?>" style="border-radius:15px;" width="300" height="300" class=" mb-2" alt="Plantilla">
                    <h5>Por Plantilla</h5>
                    <p class="text-muted">Selecciona o crea plantilla.</p>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- STEP 2A: Directo -->
          <div id="stepDirecto" class="step">
            <p class="lead">Escanea tu documento:</p>
            <div class="row">
              <div class="col-md-4">
                <img src="<?= base_url('assets/images/escaner.png')
                          ?>" class="img-fluid rounded mb-3 mx-auto d-block" style="height:80%;">
              </div>
              <div class="col-md-6">

                <div class="text-center mb-3">
                  <button type="button" id="btnScan" class="btn-rubymed btn-rubymed-primary btn-scan w-100" data-bs-toggle="tooltip" title="Lanza diálogo de escaneo">
                    <i class="fas fa-camera-alt"></i> Sube tu documento escaneado AQUI!
                  </button>
                  <input type="file" id="fileScan" name="scanned_file" hidden>
                </div>

              </div>
            </div>
          </div>

          <!-- STEP 2B: Plantilla -->
          <div id="stepPlantilla" class="step">
            <p class="lead">Elige la plantilla:</p>
            <div id="templateLoader" class="text-center my-3" style="display:none;">
              <div class="spinner-border"></div>
            </div>
            <div id="templateList" class="row"></div>
            <div class="text-center mt-3 d-none">
              <button type="button" id="btnNewTemplate" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-plus-circle"></i> Nueva Plantilla
              </button>
            </div>

            <div class="row">
              <div class="col-md-6">
                <!-- Carrusel -->
                <div id="templateCarousel" class="carousel slide" data-bs-ride="false" style="border: 5px solid #c3c1c1;border-radius:15px;height:70vh !important;">
                  <div class="carousel-inner" id="carouselInner" style="height:70vh !important;"></div>

                  <!-- Controles -->
                  <button class="carousel-control-prev" type="button"
                    data-bs-target="#templateCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true" style="background-color: #0d6efd !important;border-radius:50%;"></span>
                    <span class="visually-hidden">Anterior</span>
                  </button>
                  <button class="carousel-control-next" type="button"
                    data-bs-target="#templateCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" style="background-color: #0d6efd !important;border-radius:50%;" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                  </button>
                </div>
              </div>

              <!-- Vista previa -->
              <div class="col-md-4 d-flex align-items-center preview-box" style="height:70vh ;overflow-y: scroll !important;border: 5px solid #c3c1c1;border-radius:15px;">
                <img id="previewImg" class="w-100 border rounded" style="width:100%;height:100%;" alt="Vista previa plantilla">
              </div>
            </div>

          </div>

          <!-- STEP 3: Selección tamaño y colocar firma (solo Directo) -->
          <div id="stepMeta" class="step">
            <p class="lead">Selecciona tamaño de papel:</p>
            <div class="btn-group mb-3" role="group" aria-label="Tamaño papel">
              <button type="button" id="btnCarta" class="btn btn-outline-primary">Carta</button>
              <button type="button" id="btnOficio" class="btn btn-outline-primary">Oficio</button>
            </div>

            <div id="canvasWrapper" style="position:relative; border:1px solid #ddd; overflow:auto; margin:0 auto;">
              <canvas id="templateCanvas"></canvas>
              <div id="marker"
                style="display:none;position:absolute;width:16px;height:16px;
                background:red;border-radius:50%;transform:translate(-50%,-50%);"></div>
            </div>
          </div>
          <!-- STEP 4: Datos y formulario final -->
          <div id="stepForm" class="step">

            <?= csrf_field() ?>
            <div class="row">
              <div class="col-md-6 mx-auto">
                <div class="card">
                  <div class="card-header">
                    <h4><i class="fas fa-signature"></i> Descripción del Timbre</h4>
                  </div>
                  <div class="card-body">
                    <div class="form-group mb-3">
                      <label><?php echo app_lang('clinic_emit_document') ?></label>
                      <select name="clinic_select" class="form-control" id="clinic_select" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($clinics as $c): ?>
                          <option value="<?= $c->id ?>"><?= $c->name ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group mb-3">
                      <label><?php echo app_lang('tam_doc_text') ?></label>
                      <div class="d-flex gap-3">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="size" id="size1" value="<?= app_lang('tam_carta_text') ?>" required>
                          <label class="form-check-label" for="size1"><i class="far fa-sticky-note"></i> <?= app_lang('tam_carta_text') ?></label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="size" id="size2" value="<?= app_lang('tam_oficio_text') ?>">
                          <label class="form-check-label" for="size2"><i class="far fa-sticky-note"></i> <?= app_lang('tam_oficio_text') ?></label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="size" id="size3" value="<?= app_lang('tam_a4_text') ?>">
                          <label class="form-check-label" for="size3"><i class="far fa-sticky-note"></i> <?= app_lang('tam_a4_text') ?></label>
                        </div>
                      </div>
                    </div>

                    <div class="form-group mb-3">
                      <label><?php echo app_lang('description_document_timbrar_text') ?></label>
                      <textarea name="contenido" class="form-control" rows="4" id="contenido" required style="min-height: 200px;"></textarea>
                    </div>

                    <button type="submit" class="interactive-btn">
                      <i class="fas fa-arrow-right"></i> Solicitar timbre
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group mb-4 mt-5">
                  <span>Plantilla Seleccionada : <span id="selected-template-name"></span></span>
                  <div id="selected-template-image-container" width="500" height="500"></div>
                </div>


              </div>
            </div>

        </form>
      </div>

    </div>
    <div class="modal-footer border-0">
      <button id="btnBack" type="button" class="btn btn-secondary" disabled><i class="fas fa-arrow-left me-1"></i> Anterior</button>
      <button id="btnNext" type="button" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
    </div>
  </div>
</div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {

    const modalEl = document.getElementById('createStampModal');
    const modal = new bootstrap.Modal(modalEl);
    const steps = ['step1', 'stepDirecto', 'stepPlantilla', 'stepMeta', 'stepForm'];
    let current = 0,
      chosenMode = null,
      chosenTemplate = null,
      dotX = null,
      dotY = null;
    const backBtn = document.getElementById('btnBack');
    const nextBtn = document.getElementById('btnNext');
    const progBar = document.getElementById('progressBar');
    const stepIndicator = document.getElementById('wizardStep');
    const fileScan = document.getElementById('fileScan');
    const preview = document.getElementById('previewContainer');
    const templateList = document.getElementById('templateList');
    const loader = document.getElementById('templateLoader');
    const canvas = document.getElementById('templateCanvas');
    const marker = document.getElementById('marker');
    const ctx = canvas.getContext('2d');

    const paperDimensions = {
      Carta: {
        width: 612,
        height: 792
      }, // 8.5"x11"
      Oficio: {
        width: 612,
        height: 1008
      } // 8.5"x14"
    };
    let selectedPaper = null;

    const btnCarta = document.getElementById('btnCarta');
    const btnOficio = document.getElementById('btnOficio');

    function resizeWrapper(size) {
      const wrapper = document.getElementById('canvasWrapper');
      if (size === 'Carta') {
        wrapper.style.width = '816px';
        wrapper.style.height = '1056px';
      } else { // Oficio
        wrapper.style.width = '816px';
        wrapper.style.height = '1344px';
      }
    }

    // Calcula la altura disponible y la aplica al wrapper
    /*function resizeCanvasWrapper() {
      const headerH = modalEl.querySelector('.modal-header').offsetHeight;
      const footerH = modalEl.querySelector('.modal-footer').offsetHeight;
      // si tu .modal-body tiene padding vertical, súmalo aquí (p.ej. 32px)
      const bodyPadding = 32;
      const available = window.innerHeight - headerH - footerH - bodyPadding;
      wrapper.style.maxHeight = available + 'px';
    }*/

    function resizeCanvasWrapper() {
      const wrapper = document.getElementById('canvasWrapper'); // ✅ AGREGAR ESTA LÍNEA
      if (!wrapper) return; // ✅ VERIFICACIÓN DE SEGURIDAD
      
      const headerH = modalEl.querySelector('.modal-header').offsetHeight;
      const footerH = modalEl.querySelector('.modal-footer').offsetHeight;
      const bodyPadding = 32;
      const available = window.innerHeight - headerH - footerH - bodyPadding;
      wrapper.style.maxHeight = available + 'px';
    }

    // Redimensiona al abrir el modal y cuando cambie el tamaño de la ventana
    modalEl.addEventListener('shown.bs.modal', resizeCanvasWrapper);
    window.addEventListener('resize', resizeCanvasWrapper);

    function selectPaper(size) {
      selectedPaper = size;
      resizeWrapper(size);

      const dims = paperDimensions[size]; // { width:612, height:792 } ó {612,1008}
      canvas.width = dims.width;
      canvas.height = dims.height;
      canvas.style.width = dims.width + 'px';
      canvas.style.height = dims.height + 'px';

      // redibuja la imagen maestra al tamaño interno del canvas
      const img = window._loadedDocumentImage;
      if (img && img.complete) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(
          img,
          0, 0, img.naturalWidth, img.naturalHeight,
          0, 0, canvas.width, canvas.height
        );
      }

      marker.style.display = 'none';
      dotX = dotY = null;
    }



    // eventos de los botones
    btnCarta.onclick = () => selectPaper('Carta');
    btnOficio.onclick = () => selectPaper('Oficio');


    //const loader = document.getElementById('loader'); // <div id="loader">
    const templateInput = document.getElementById('templateInput'); // <input hidden ...>
    const selectedTemplateName = document.getElementById('selected-template-name'); // <input hidden ...>
    const selectedTemplateImageContainer = document.getElementById('selected-template-image-container');

    const carouselInner = document.getElementById('carouselInner');
    const previewImg = document.getElementById('previewImg');

    const BASE_URL = "<?= rtrim(site_url(), '/') ?>/";

    // Selección modo
    document.querySelectorAll('.mode-card').forEach(card => {
      card.onclick = () => {
        console.log("click");
        chosenMode = card.dataset.mode;
        document.getElementById('modeInput').value = chosenMode;
        document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');

        current = (chosenMode === 'directo' ? 1 : 2);
        showStep();

        if (chosenMode === 'directo') {
          //fileScan.click();
        }
      };
    });

    backBtn.onclick = () => {
      console.log("Siguiente");
      // Ir atrás y omitir firma si plantilla
      if (chosenMode === 'plantilla' && current === 4) current = 2;
      else if (current > 0) current--;
      showStep();
    };

    nextBtn.onclick = () => {

      // Directo: forzar escaneo y posicionar
      if (steps[current] === 'stepDirecto') {
        if (!fileScan.files.length) return fileScan.click();
        current++;
        return showStep();
      }
      // Plantilla: elegir

      if (steps[current] === 'stepPlantilla') {
        if (!chosenTemplate) return alert('Seleccione plantilla.');
        // saltar pasoMeta
        current = 4;
        return showStep();
      }

      if (steps[current] === 'stepMeta') {
        if (!selectedPaper) return alert('Seleccione tamaño de papel.');
        if (dotX === null) return alert('Marque posición de firma.');
        current++;
        selectPaper('Carta');
        return showStep();
      }


      // Después de firma directo, avanzar a formulario
      if (current === 2 && chosenMode === 'directo') {
        console.log("Es el paso a 2");
        return showStep();
      }
      // Entre firma y formulario
      if (current === 3) {
        console.log("Es el paso a 3");
        current = 4;
        return showStep();
      }
      // De formulario, nada: submit con botón propio
      // ** NUEVO: al llegar al formulario **

      // Avanzar normalmente
      if (current < steps.length - 1) {
        console.log("Es el paso a 4");
        current++;
        showStep();
      }

      if (current === 4) {
        const cont = document.getElementById('selected-template-image-container');
        const nameEl = document.getElementById('selected-template-name');
        cont.innerHTML = '';
        nameEl.textContent = '';

        if (chosenMode === 'directo' && window._loadedDocumentImage) {
          // tu código de DIRECTO (ya lo tienes)
          nameEl.textContent = 'Documento Escaneado';
          cont.innerHTML = `
        <img src="${window._loadedDocumentImage.src}" />
        <div class="sig-dot"
             style="left:${dotX*scale}px; top:${dotY*scale}px;"></div>
      `;
        } else if (chosenMode === 'plantilla' && chosenTemplate) {
          // NUEVO: PREVIEW de PLANTILLA
          const displayW = 200;
          // asumo que canvas.width ya iguala al tamaño “real” de página
          const scale = displayW / canvas.width;

          nameEl.textContent = chosenTemplate.name;
          cont.innerHTML = `
        <img src="${BASE_URL}${chosenTemplate.image}" />
        <div class="sig-dot"
             style="
               left:${chosenTemplate.signature_x * scale}px;
               top :${chosenTemplate.signature_y * scale}px;
             "></div>
      `;
        }
      }
    };

    function showStep() {
      // 1) activar/desactivar .step
      steps.forEach((id, i) => {
        document.getElementById(id)
          .classList.toggle('active', i === current);
      });

      // 2) actualizar botones, barra de progreso…
      backBtn.disabled = current === 0;
      nextBtn.innerHTML = current === steps.length - 1 ?
        '' :
        'Siguiente <i class="fas fa-arrow-right ms-1"></i>';
      progBar.style.width = `${100 * current/(steps.length-1)}%`;
      stepIndicator.textContent = current + 1;

      // 3) si estás en “Plantilla” carga plantillas
      if (steps[current] === 'stepPlantilla') loadTemplates();

      // 4) si entras en paso Directo, fuerza file picker
      if (steps[current] === 'stepDirecto' && !fileScan.files.length) {
        //fileScan.click();
      }

      // 5) ** NUEVO: si llegas al formulario (stepForm / índice 4) **
      if (current === 4) {
        const cont = document.getElementById('selected-template-image-container');
        const nameEl = document.getElementById('selected-template-name');

        cont.innerHTML = '';
        nameEl.textContent = '';

        if (chosenMode === 'directo' && window._loadedDocumentImage) {
          // tu código actual para directo…
          const displayW = 200;
          const scale = displayW / canvas.width;
          const displayH = canvas.height * scale;

          nameEl.textContent = 'Documento Escaneado';
          cont.innerHTML = `
      <div >
        <img src="${window._loadedDocumentImage.src}"
             style="width:100%; height:100%; display:block;" />
      </div>
    `;
        } else if (chosenMode === 'plantilla' && chosenTemplate) {
          // ¡aquí va el preview de plantilla!
          const imgUrl = BASE_URL + chosenTemplate.image;
          const displayW = 200; // ancho deseado
          const scale = displayW / canvas.width; // canvas.width = tamaño real “puntos”
          const displayH = canvas.height * scale;

          nameEl.textContent = chosenTemplate.name;
          cont.innerHTML = `
      <div>
        <img src="${imgUrl}"
             style="width:100%; height:100%; display:block;" />
      </div>
    `;
        }
      }
    }



    // Escaneo directo y posicionamiento
    //document.getElementById('btnScan').onclick = () => fileScan.click();

    // Cuando el usuario selecciona el archivo...
    fileScan.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      showLoading('Cargando documento…');
      marker.style.display = 'none'; // oculta marcador previo

      // Lectura de PDF
      if (file.type === 'application/pdf') {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument(new Uint8Array(arrayBuffer)).promise;
        const page = await pdf.getPage(1);

        // Ajusta escala al tamaño del canvas
        const viewport = page.getViewport({
          scale: 1
        });
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        await page.render({
          canvasContext: ctx,
          viewport
        }).promise;

        // ← Aquí, justo tras renderizar:
        const dataURL = canvas.toDataURL();
        const loadedImg = new Image();
        loadedImg.src = dataURL;
        window._loadedDocumentImage = loadedImg;

        hideLoading();

        // Lectura de imagen
      } else if (file.type.startsWith('image/')) {
        const img = new Image();
        img.onload = () => {
          canvas.width = img.naturalWidth;
          canvas.height = img.naturalHeight;
          ctx.drawImage(img, 0, 0);

          // ← Aquí, justo tras dibujar la imagen:
          const dataURL = canvas.toDataURL();
          const loadedImg = new Image();
          loadedImg.src = dataURL;
          window._loadedDocumentImage = loadedImg;

          hideLoading();
        };
        img.src = URL.createObjectURL(file);

        // Word → imagen (igual que antes)
      } else if (/\.(docx?|DOCX?)$/.test(file.name)) {
        const formData = new FormData();
        formData.append('file', file);
        const res = await fetch('https://convert.clinicahispanarubymed.com/docx-converter.php', {
          method: 'POST',
          body: formData
        });
        const dataUrl = await res.text();
        hideLoading();
        if (dataUrl.startsWith('data:image')) {
          const img = new Image();
          img.onload = () => {
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            ctx.drawImage(img, 0, 0);

            // ← Aquí, justo tras dibujar la imagen:
            const dataURL = canvas.toDataURL();
            const loadedImg = new Image();
            loadedImg.src = dataURL;
            window._loadedDocumentImage = loadedImg;
          };
          img.src = dataUrl;
        } else {
          showError('No se pudo convertir el documento.');
        }

      } else {
        hideLoading();
        showError('Formato no soportado. Usa PDF o imagen.');
      }

      dotX = null; // aún no hay posición
      current = steps.indexOf('stepMeta');
      showStep();
    });

    // Ahora captura clics sobre el canvas para colocar el marcador
    canvas.addEventListener('click', e => {
      const wrapper = document.getElementById('canvasWrapper');
      canvasWrapper.addEventListener('wheel', e => {
        e.preventDefault(); // evita el scroll de la página
        canvasWrapper.scrollTop += e.deltaY;
        canvasWrapper.scrollLeft += e.deltaX;
      });
      const rect = canvas.getBoundingClientRect();

      // coordenada CSS dentro del canvas visible
      const cssX = e.clientX - rect.left;
      const cssY = e.clientY - rect.top;

      // escala interna real
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;
      dotX = cssX * scaleX;
      dotY = cssY * scaleY;

      // coloca el marcador **dentro** del wrapper
      marker.style.left = `${cssX - marker.offsetWidth/2}px`;
      marker.style.top = `${cssY - marker.offsetHeight/2}px`;
      marker.style.display = 'block';

      document.getElementById('dotXInput').value = dotX;
      document.getElementById('dotYInput').value = dotY;
    });


    // Carga plantillas

    async function loadTemplates() {
      carouselInner.innerHTML = '';
      try {
        const res = await fetch(`<?= site_url("stamptemplate/listAjax") ?>`);
        const data = await res.json();
        const templates = data.templates;

        // Itera de 2 en 2
        for (let i = 0; i < templates.length; i += 2) {
          const slide = document.createElement('div');
          slide.className = `carousel-item ${i === 0 ? 'active' : ''}`;

          // Crea un contenedor flex para dos items
          const container = document.createElement('div');
          container.className = 'd-flex';

          // Toma este chunk de hasta 2 plantillas
          templates.slice(i, i + 2).forEach(t => {
            const item = document.createElement('div');
            item.className = 'col-md-6 template-item text-center p-2 flex-fill';
            item.innerHTML = `
          <img src="${BASE_URL}${t.image}" class="d-block w-100"
               style="max-height:60vh;object-fit:cover;" alt="${t.name}">
          <button class="btn-rubymed w-100 btn-sm btn-rubymed-primary choose-btn">
            <i class="fas fa-mouse-pointer"></i> Seleccionar
          </button>
        `;

            // Mantén tu handler tal cual
            item.querySelector('.choose-btn').addEventListener('click', () => {
              chosenTemplate = t;
              window.selectedTemplate = t;
              templateInput.value = t.id;
              selectedTemplateName.textContent = t.name;
              selectedTemplateImageContainer.innerHTML =
                `<img src="${BASE_URL}${t.image}" class="img-fluid" width="80%" height="80%" alt="${t.name}">`;

              previewImg.src = `${BASE_URL}${t.image}`;
              previewImg.alt = t.name;

              document.querySelectorAll('.template-item')
                .forEach(el => el.classList.remove('selected'));
              item.classList.add('selected');
            });
            item.querySelector('img').addEventListener('click', () => {
              previewImg.src = `${BASE_URL}${t.image}`;
              previewImg.alt = t.name;
            });

            container.appendChild(item);
          });

          slide.appendChild(container);
          carouselInner.appendChild(slide);
        }
      } catch (err) {
        carouselInner.innerHTML =
          '<p class="text-danger p-3">Error cargando plantillas.</p>';
        console.error(err);
      }
    }


    // Tooltips
    var tlist = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tlist.map(el => new bootstrap.Tooltip(el));

    const form = document.getElementById("formTimbrado");
    if (form) form.addEventListener("submit", submitForm);

    btnScan.addEventListener('click', () => {
      fileScan.click();
    });
  });

  async function submitForm(event) {
    event.preventDefault();
    showLoading();
    // Verificar que se haya elegido una plantilla (campo requerido)
    const mode = document.getElementById('modeInput').value;
    if (mode === 'plantilla' && !window.selectedTemplate) {
      hideLoading();
      showError('Seleccione una plantilla.');
      return;
    }
    const form = document.getElementById('formTimbrado');
    const formData = new FormData(form);
    const clinicSelect = document.getElementById('clinic_select');
    const selectedOption = clinicSelect.options[clinicSelect.selectedIndex];
    const clinicId = clinicSelect.value;
    formData.append('clinic_id', clinicId);
    const sizeInput = document.querySelector('input[name="size"]:checked');
    if (!sizeInput) {
      showError('Seleccione un tamaño de documento.');

      return;
    }
    if (!clinicId) {
      showError('Seleccione una Clínica.');
      return;
    }
    showLoading();



    const dotX = document.getElementById('dotXInput').value;
    const dotY = document.getElementById('dotYInput').value;
    if (mode === 'directo' && (!dotX || !dotY)) {
      hideLoading();
      return alert('Marque posición de firma.');
    }

    // siempre envío el modo
    formData.set('mode', mode);

    // Agregar datos de la plantilla seleccionada
    if (mode === 'plantilla') {
      // como antes
      formData.set('template_name', window.selectedTemplate.name);
      formData.set('template_image', window.selectedTemplate.image);
      formData.set('signature_x', window.selectedTemplate.signature_x);
      formData.set('signature_y', window.selectedTemplate.signature_y);
      formData.set('page_size', window.selectedTemplate.page_size);
    } else { // directo
      formData.set('template_name', 'Documento Escaneado');
      // mandas la imagen en base64
      formData.set('template_image', window._loadedDocumentImage.src);
      // coordenadas de la firma
      formData.set('signature_x', dotX);
      formData.set('signature_y', dotY);
      // tamaño de página desde el radio seleccionado
      formData.set('page_size', formData.get('size'));
    }


    try {
      const response = await fetch("<?= site_url('stamp/create') ?>", {
        method: "POST",
        body: formData
      });
      const result = await response.json();
      //document.getElementById('loader-laboral').style.display = 'none';
      if (result.success) {
        showSuccess('Solicitud de Timbre Creado Correctamente!')
        form.reset();
        window.selectedTemplate = null;
        document.getElementById('selected-template-name').value = '';
        document.getElementById('selected-template-image-container').innerHTML = '';
        // Aquí se puede llamar a imprimirDocumento(result.stamp) si se desea imprimir

        // ——— Cierra el modal ———
        const modalEl = document.getElementById('createStampModal');
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        bsModal.hide();
        current = 0;

        loadStamp();
      } else {
        showError('No se pudo realizar la solicitud de timbre.');
        alert(result.message || 'Error al guardar.');
      }
    } catch (err) {
      showError('No se pudo realizar la solicitud de timbre.');
      document.getElementById('loader-laboral').style.display = 'none';
      alert("Error en el envío del formulario.");
      console.error(err);
    }
  }
</script>