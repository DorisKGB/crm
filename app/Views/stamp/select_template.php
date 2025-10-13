<!-- HTML + CSS -->
<style>
    .step {
        display: none;
        animation: fadeIn .3s ease;
        max-height: calc(100vh - 300px);
        /* ajustar según header/footer */
        overflow-y: auto;
        padding-right: 1rem;
    }

    .step.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    /* Plantillas */
    .template-item {
        border: 1px solid #ddd;
        border-radius: .5rem;
        padding: .75rem;
        cursor: pointer;
        text-align: center;
        transition: box-shadow .2s;
    }

    .template-item.selected {
        border-color: #0d6efd;
        background: #e7f1ff;
    }

    .template-item img {
        max-height: 120px;
        object-fit: cover;
        margin-bottom: .5rem;
    }

    /* Clínicas */
    .interactive-clinic-select .clinic-card {
        flex: 0 0 48%;
        padding: .75rem;
        border: 1px solid #ddd;
        border-radius: .5rem;
        background: #f9f9f9;
        cursor: pointer;
        transition: transform .2s, box-shadow .2s;
        text-align: center;
    }

    .interactive-clinic-select .clinic-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
    }

    .interactive-clinic-select .clinic-card.selected {
        border-color: #0d6efd;
        background: #e7f1ff;
    }

    /* Preview */
    .preview-box {
        border: 2px dashed #ced4da;
        border-radius: .5rem;
        height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: zoom-in;
    }

    .preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform .3s ease;
    }

    /* Descripción */
    .char-count {
        font-size: .875rem;
        color: #666;
        text-align: right;
    }

    /* Modal fullscreen */
    .modal-fullscreen .modal-body {
        padding: 0;
        background: rgba(0, 0, 0, .8);
    }

    .modal-fullscreen img {
        width: 100%;
        height: auto;
    }

    .template-item {
        position: relative;
        /* ...tus estilos actuales... */
    }

    .template-item .select-circle {
        position: absolute;
        top: 8px;
        left: 8px;
        width: 24px;
        height: 24px;
        border: 2px solid #ccc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        background: #fff;
        transition: all .2s ease;
        cursor: pointer;
        z-index: 10;
    }

    .template-item .select-circle i {
        font-size: 14px;
    }

    .template-item.selected .select-circle {
        border-color: #0d6efd;
        background: #0d6efd;
    }

    .template-item.selected .select-circle i {
        color: #fff;
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

    .template-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: .25rem;
    }

    .template-actions .btn {
        padding: .25rem .5rem;
        font-size: .75rem;
        border-radius: 50%;
    }

    /* Asegúrate de que en tu CSS tengas esto: */
    #editCanvasWrapper {
        position: relative;
        /* ya lo tienes */
    }

    #editMarker {
        position: absolute;
        width: 12px;
        height: 12px;
        background: red;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        /* ¡muy importante! */
        display: none;
    }

    /* Search bar styles */
    #templateSearch {
        border-radius: 0.5rem 0 0 0.5rem;
        border-right: none;
    }

    #templateSearch:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        border-color: #86b7fe;
    }

    #clearSearch {
        border-radius: 0 0.5rem 0.5rem 0;
        border-left: none;
    }

    #clearSearch:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    /* Pagination styles */
    .pagination-lg .page-link {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 500;
    }

    .pagination-lg .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination-lg .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .pagination-lg .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Template grid responsive */
    @media (max-width: 768px) {
        .template-item {
            margin-bottom: 1rem;
        }
        
        .pagination-lg .page-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
    }

    /* Loading state */
    .template-loading {
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div id="page-content">
    <div class="card m-4">
        <div class="card-title d-flex align-items-center">
            <a href="javascript:history.back()" style="margin-left:20px !important;" class="fs-3 me-3"><i
                    class="fas fa-arrow-left"></i></a>
            <h3 class="flex-grow-1 text-center mb-0">
                <span class="badge badge-primary">Timbre por Plantilla</span>

            </h3>
        </div>
        <hr>
        <div class="card-body">

            <!-- Progress bar -->
            <div class="mb-3">
                <small>Paso <span id="stepIndicator">1</span> de 3</small>
                <div class="progress">
                    <div id="progressBar" class="progress-bar bg-success" style="width:33%"></div>
                </div>
            </div>

            <form id="stampWizardForm" method="post" action="<?= site_url('stamp/create2') ?>" novalidate>
                <?= csrf_field() ?>
                <!-- hidden fields -->
                <input type="hidden" id="templateInput" name="template_id">
                <input type="hidden" id="clinicInput" name="clinic_id">
                <input type="hidden" id="descInput" name="description">
                <input type="hidden" id="dotXInput" name="signature_x">
                <input type="hidden" id="dotYInput" name="signature_y">
                <input type="hidden" id="sizeInput" name="size">
                <input type="hidden" id="pageSizeInput" name="page_size">
                <input type="hidden" id="templateNameInput" name="template_name">
                <input type="hidden" id="templateImageInput" name="template_image">

                <!-- STEP 1: Elige plantilla -->
                <div id="step1" class="step active">
                    <h4>1. Seleccionar una plantilla</h4>
                    
                    <!-- Search Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" id="templateSearch" class="form-control form-control-lg" 
                                       placeholder="<?= lang('search') ?> plantillas..." 
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <!-- Results info -->
                            <div class="mt-2 text-muted" id="resultsInfo">
                                <small id="resultsText">Cargando plantillas...</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div id="templateList" class="row g-3"></div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Paginación de plantillas">
                                    <ul class="pagination pagination-lg" id="templatePagination">
                                        <!-- Pagination will be generated here -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>Preview</h5>
                            <div class="preview-box" id="previewBox1">
                                <img id="previewImg1" src="" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Selecciona clínica -->
                <div id="step2" class="step">
                    <h4>2. Selecciona clínica</h4>
                    <div class="interactive-clinic-select d-flex flex-wrap gap-2">
                        <input type="text" id="clinicSearch" class="form-control mb-2 w-100"
                            placeholder="🔍 Buscar clínica...">
                        <div id="clinicList" class="d-flex flex-wrap gap-2 w-100"></div>
                    </div>
                </div>

                <!-- STEP 3: Descripción -->
                <div id="step3" class="step">
                    <h4>3. Escribe descripción</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description">Descripción</label>
                                <textarea id="description" name="contenido" class="form-control"
                                    style="min-height: 50vh;" rows="5" maxlength="200" placeholder="Describe aquí..."
                                    required></textarea>
                                <div class="char-count"><span id="descCount">0</span>/200</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Preview</h5>
                            <div class="preview-box" id="previewBox2">
                                <img id="previewImg2" src="" alt="">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" id="btnBack" class="btn btn-secondary mr-2" disabled>← Anterior</button>
                    <button type="button" id="btnNext" class="btn btn-primary">Siguiente →</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Modal para preview ampliado -->
<div class="modal fade modal-fullscreen" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <img id="previewModalImg" src="" alt="">
            </div>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="editTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4">
            <div class="modal-header">
                <h5 class="modal-title" id="editTemplateModalLabel">Editar Plantilla</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTemplateForm">
                    <?= csrf_field() ?>
                    <input type="hidden" id="editTemplateId">
                    <!-- Step 1: rename -->
                    <div id="edit-step-1" class="step active">
                        <label>Nombre de plantilla</label>
                        <input type="text" id="editTemplateName" class="form-control" required>
                        <button type="button" class="btn btn-primary mt-3" id="to-step-2">Siguiente →</button>
                    </div>
                    <!-- Step 2: mover puntico -->
                    <div id="edit-step-2" class="step">
                        <p>Arrastra el punto rojo a la nueva posición</p>
                        <div id="editCanvasWrapper" style="border:1px solid #ccc; position:relative; height:400px;">
                            <canvas id="editTemplateCanvas"></canvas>
                            <div id="editMarker" class="marker"></div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-3" id="back-to-step-1">← Anterior</button>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="save-template" class="btn btn-success">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que quieres eliminar esta plantilla?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal informativo de bienvenida para seleccionar plantilla -->
<div class="modal fade" id="welcomeTemplateModal" tabindex="-1" aria-labelledby="welcomeTemplateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-center">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title w-100" id="welcomeTemplateLabel">
                    <i class="fas fa-lightbulb"></i> ¿Cómo seleccionar una plantilla?
                </h4>
            </div>
            <div class="modal-body">
                <img src="<?= base_url('assets/images/asistente.png') ?>" width="120" alt="Asistente" class="mb-3">
                <h3 class="fw-bold">🧠 Seleccionar una plantilla es muy fácil</h3>
                <p style="font-size: 1.2rem;" class="text-muted">
                    Solo debes hacer <strong>clic en la plantilla que desees timbrar</strong> y <br>
                    automáticamente la verás en la vista previa del lado derecho.
                </p>
                <p style="font-size: 1.1rem;">Puedes editarla o eliminarla también desde este paso.</p>
                <div class="mt-4">
                    <button class="btn btn-success btn-lg px-4" data-bs-dismiss="modal">
                        <i class="fas fa-thumbs-up"></i> ¡Entendido, empecemos!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // Mostrar el modal informativo automáticamente al cargar
        const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeTemplateModal'));
        welcomeModal.show();


        // Wizard setup
        const steps = ['step1', 'step2', 'step3'];
        let current = 0;
        const btnBack = document.getElementById('btnBack'),
            btnNext = document.getElementById('btnNext'),
            progBar = document.getElementById('progressBar'),
            stepInd = document.getElementById('stepIndicator'),
            form = document.getElementById('stampWizardForm');

        // ** aquí importa **
        const description = document.getElementById('description');
        const descInput = document.getElementById('descInput');

        function showStep() {
            steps.forEach((id, i) => {
                document.getElementById(id).classList.toggle('active', i === current);
            });
            btnBack.disabled = current === 0;
            btnNext.textContent = current < steps.length - 1 ? 'Siguiente →' : 'Guardar Timbre';
            progBar.style.width = `${(current+1)/steps.length*100}%`;
            stepInd.textContent = current + 1;
        }
        btnBack.onclick = () => {
            if (current > 0) {
                current--;
                showStep();
            }
        };

        btnNext.onclick = async () => {
            // Validaciones
            if (current === 0 && !templateInput.value) {
                return alert('Por favor selecciona una plantilla.');
            }
            if (current === 1 && !clinicInput.value) {
                return alert('Por favor selecciona una clínica.');
            }
            if (current < steps.length - 1) {
                current++;
                showStep();
                return;
            }

            // Último paso: validar descripción antes de enviar
            const desc = description.value.trim();
            if (!desc) {
                return alert('Debes escribir una descripción antes de guardar.');
            }

            // Último paso: recoger y enviar
            descInput.value = desc;
            const data = new FormData(form);
            btnNext.disabled = true;
            btnNext.textContent = 'Guardando…';
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();
                if (json.success) {
                    window.location.href = '<?= site_url("stamp/stamp_success") ?>/' + json.stamp;
                } else {
                    alert(json.message || 'Error guardando.');
                    btnNext.disabled = false;
                    btnNext.textContent = 'Guardar Timbre';
                }
            } catch (e) {
                console.error(e);
                alert('Error de red.');
                btnNext.disabled = false;
                btnNext.textContent = 'Guardar Timbre';
            }
        };
        showStep();

        // Descripción contador
        /*const description = document.getElementById('description'),
            countEl = document.getElementById('descCount');
        description.addEventListener('input', () => countEl.textContent = description.value.length);*/

        // Preview modal
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal')),
            modalImg = document.getElementById('previewModalImg');
        ['previewImg1', 'previewImg2'].forEach(id => {
            document.getElementById(id).onclick = () => {
                modalImg.src = document.getElementById(id).src;
                previewModal.show();
            };
        });

        // Cargar plantillas con paginación y búsqueda
        const tplList = document.getElementById('templateList'),
            tplInput = document.getElementById('templateInput'),
            preview1 = document.getElementById('previewImg1'),
            preview2 = document.getElementById('previewImg2'),
            sizeInput = document.getElementById('sizeInput'),
            pageSizeInput = document.getElementById('pageSizeInput'),
            nameInput = document.getElementById('templateNameInput'),
            imgInput = document.getElementById('templateImageInput'),
            dotXInput = document.getElementById('dotXInput'),
            dotYInput = document.getElementById('dotYInput'),
            templateSearch = document.getElementById('templateSearch'),
            clearSearch = document.getElementById('clearSearch'),
            templatePagination = document.getElementById('templatePagination'),
            resultsText = document.getElementById('resultsText');

        // Variables de paginación
        let allTemplates = [];
        let filteredTemplates = [];
        let currentPage = 1;
        const templatesPerPage = 12;

        // Función para actualizar información de resultados
        function updateResultsInfo(templates, page = 1) {
            const totalTemplates = templates.length;
            const startIndex = (page - 1) * templatesPerPage + 1;
            const endIndex = Math.min(page * templatesPerPage, totalTemplates);
            
            if (totalTemplates === 0) {
                resultsText.textContent = 'No se encontraron plantillas';
            } else if (totalTemplates <= templatesPerPage) {
                resultsText.textContent = `Mostrando ${totalTemplates} plantilla${totalTemplates !== 1 ? 's' : ''}`;
            } else {
                resultsText.textContent = `Mostrando ${startIndex}-${endIndex} de ${totalTemplates} plantillas`;
            }
        }

        // Función para renderizar plantillas
        function renderTemplates(templates, page = 1) {
            const startIndex = (page - 1) * templatesPerPage;
            const endIndex = startIndex + templatesPerPage;
            const pageTemplates = templates.slice(startIndex, endIndex);
            
            tplList.innerHTML = '';
            
            // Actualizar información de resultados
            updateResultsInfo(templates, page);
            
            if (pageTemplates.length === 0) {
                tplList.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron plantillas</h5>
                        <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    </div>
                `;
                return;
            }

            pageTemplates.forEach(t => {
                const col = document.createElement('div');
                col.className = 'col-md-4';
                col.innerHTML = `
                    <div class="template-item" data-id="${t.id}"
                         data-image="<?= rtrim(site_url(), '/') ?>/${t.image}"
                         data-name="${t.name}"
                         data-size="${t.page_size}"
                         data-sigx="${t.signature_x}"
                         data-sigy="${t.signature_y}">
                        <img src="<?= rtrim(site_url(), '/') ?>/${t.image}" alt="">
                        <div class="select-circle"><i class="fas fa-check"></i></div>
                        <div class="template-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit"><i class="fas fa-pencil-alt"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete"><i class="fas fa-trash"></i></button>
                        </div>
                        <div><strong>${t.name}</strong></div>
                    </div>`;
                tplList.append(col);

                // Event listener para editar
                const editBtn = col.querySelector('.btn-edit');
                editBtn.addEventListener('click', async e => {
                    e.preventDefault();
                    const id = t.id;
                    const res = await fetch(`<?= site_url('stamptemplate/getAjax') ?>/${id}`);
                    const json = await res.json();
                    if (!json.success) return alert('No se encontró la plantilla');
                    const tpl = json.template;

                    document.getElementById('editTemplateModalLabel').textContent = tpl.name;
                    document.getElementById('editTemplateId').value = tpl.id;

                    const canvas = document.getElementById('editTemplateCanvas');
                    const ctx = canvas.getContext('2d');
                    const marker = document.getElementById('editMarker');
                    const img = new Image();
                    img.onload = () => {
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        ctx.drawImage(img, 0, 0);
                        marker.style.display = 'block';
                        marker.style.left = tpl.signature_x + 'px';
                        marker.style.top = tpl.signature_y + 'px';
                    };
                    img.src = `<?= rtrim(site_url(), '/') ?>/${tpl.image}`;

                    const editModalEl = document.getElementById('editTemplateModal');
                    new bootstrap.Modal(editModalEl).show();
                });
            });
        }

        // Función para renderizar paginación
        function renderPagination(templates, currentPage) {
            const totalPages = Math.ceil(templates.length / templatesPerPage);
            templatePagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Botón anterior
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}"><?= lang('previous') ?></a>`;
            templatePagination.appendChild(prevLi);

            // Números de página
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                const firstLi = document.createElement('li');
                firstLi.className = 'page-item';
                firstLi.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
                templatePagination.appendChild(firstLi);
                if (startPage > 2) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">...</span>`;
                    templatePagination.appendChild(dotsLi);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                templatePagination.appendChild(li);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">...</span>`;
                    templatePagination.appendChild(dotsLi);
                }
                const lastLi = document.createElement('li');
                lastLi.className = 'page-item';
                lastLi.innerHTML = `<a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>`;
                templatePagination.appendChild(lastLi);
            }

            // Botón siguiente
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}"><?= lang('next') ?></a>`;
            templatePagination.appendChild(nextLi);
        }

        // Función para filtrar plantillas
        function filterTemplates() {
            const searchTerm = templateSearch.value.toLowerCase().trim();
            if (searchTerm === '') {
                filteredTemplates = [...allTemplates];
            } else {
                filteredTemplates = allTemplates.filter(template => 
                    template.name.toLowerCase().includes(searchTerm)
                );
            }
            currentPage = 1;
            renderTemplates(filteredTemplates, currentPage);
            renderPagination(filteredTemplates, currentPage);
        }

        // Cargar plantillas inicialmente
        tplList.innerHTML = `<div class="text-center w-100"><div class="spinner-border"></div></div>`;
        fetch('<?= site_url("stamptemplate/listAjax") ?>')
            .then(r => r.json())
            .then(json => {
                allTemplates = json.templates;
                filteredTemplates = [...allTemplates];
                renderTemplates(filteredTemplates, currentPage);
                renderPagination(filteredTemplates, currentPage);
            });

        // Event listeners para búsqueda
        templateSearch.addEventListener('input', filterTemplates);
        clearSearch.addEventListener('click', () => {
            templateSearch.value = '';
            filterTemplates();
        });

        // Event listener para paginación
        templatePagination.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.classList.contains('page-link') && !e.target.closest('.disabled')) {
                const page = parseInt(e.target.dataset.page);
                if (page && page !== currentPage) {
                    currentPage = page;
                    renderTemplates(filteredTemplates, currentPage);
                    renderPagination(filteredTemplates, currentPage);
                    // Scroll to top of template list
                    tplList.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });


        // Cargar clínicas
        const clinicsData = <?= json_encode($clinics) ?>,
            cliList = document.getElementById('clinicList'),
            cliInput = document.getElementById('clinicInput'),
            cliSearch = document.getElementById('clinicSearch');

        function renderClinics(list) {
            cliList.innerHTML = '';
            list.forEach(c => {
                const card = document.createElement('div');
                card.className = 'clinic-card';
                card.innerHTML = `
      <div class="select-circle"><i class="fas fa-check"></i></div>
      ${c.name}
    `;
                card.dataset.id = c.id;
                card.onclick = () => {
                    cliList.querySelectorAll('.clinic-card')
                        .forEach(x => x.classList.remove('selected'));
                    card.classList.add('selected');
                    cliInput.value = c.id;
                };
                cliList.append(card);
            });
        }
        cliSearch.oninput = () => {
            const q = cliSearch.value.toLowerCase();
            renderClinics(clinicsData.filter(c => c.name.toLowerCase().includes(q)));
        };
        renderClinics(clinicsData);
    });


    document.addEventListener('DOMContentLoaded', () => {
        const editModalEl = document.getElementById('editTemplateModal');
        const editModal = new bootstrap.Modal(editModalEl);
        const step1 = document.getElementById('edit-step-1');
        const step2 = document.getElementById('edit-step-2');
        const toStep2 = document.getElementById('to-step-2');
        const backTo1 = document.getElementById('back-to-step-1');
        const saveBtn = document.getElementById('save-template');
        const tplIdInput = document.getElementById('editTemplateId');
        const nameInput = document.getElementById('editTemplateName');
        const canvas = document.getElementById('editTemplateCanvas');
        const marker = document.getElementById('editMarker');
        const ctx = canvas.getContext('2d');
        let img, sigX, sigY;

        // event delegation para selección de plantilla
        const tplListEl = document.getElementById('templateList');
        tplListEl.addEventListener('click', e => {
            // si el click vino de un botón editar o eliminar, salimos
            if (e.target.closest('.btn-edit') || e.target.closest('.btn-delete')) return;

            // buscamos la tarjeta .template-item más cercana
            const item = e.target.closest('.template-item');
            if (!item) return;

            // 1) destildo todas y marco esta
            tplListEl.querySelectorAll('.template-item').forEach(x => x.classList.remove('selected'));
            item.classList.add('selected');

            // 2) relleno los hidden inputs y previews
            document.getElementById('templateInput').value = item.dataset.id;
            document.getElementById('templateNameInput').value = item.dataset.name;
            // Normaliza la ruta de la imagen
            let rawPath = item.dataset.image;

            // Si viene como URL completa, extraemos solo el path
            try {
                const url = new URL(rawPath);
                rawPath = url.pathname;
            } catch (e) {
                // ya es relativa
            }

            // Elimina "/crm/", "/index.php", y cualquier slash inicial
            rawPath = rawPath.replace(/^\/?(crm\/)?(index\.php\/)?/, '');

            // Asegura que no comience con slash
            rawPath = rawPath.replace(/^\/+/, '');

            // Asegura que tenga extensión .png (si no tiene ninguna)
            if (!rawPath.match(/\.(png|jpg|jpeg|gif)$/i)) {
                rawPath += '.png';
            }

            // ✅ Asigna al input como: uploads/stamp_templates/stamp_xxx.png
            document.getElementById('templateImageInput').value = rawPath;


            document.getElementById('sizeInput').value = item.dataset.size;
            document.getElementById('pageSizeInput').value = item.dataset.size;
            document.getElementById('dotXInput').value = item.dataset.sigx;
            document.getElementById('dotYInput').value = item.dataset.sigy;

            const imgUrl = item.dataset.image;
            document.getElementById('previewImg1').src = imgUrl;
            document.getElementById('previewImg2').src = imgUrl;
        });


        // 1) Handler “Editar”

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', async e => {
                e.preventDefault();

                // 1) fetch de la plantilla
                const card = e.currentTarget.closest('.template-item');
                const id = card.dataset.id;
                const res = await fetch(`<?= site_url('stamptemplate/getAjax') ?>/${id}`);
                const json = await res.json();
                if (!json.success) return alert('No se encontró plantilla');
                const t = json.template;

                // 2) título y hidden
                document.getElementById('editTemplateModalLabel').textContent = t.name;
                document.getElementById('editTemplateId').value = t.id;

                // 3) dimensiones según tamaño de página
                const paperDims = {
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
                } [t.page_size] || {
                    w: 816,
                    h: 1056
                };
                const DPR = window.devicePixelRatio || 1;

                // 4) prepara canvas y marcador
                const wrapper = document.getElementById('editCanvasWrapper');
                const canvas = document.getElementById('editTemplateCanvas');
                const marker = document.getElementById('editMarker');
                const ctx = canvas.getContext('2d');

                // carga y dibuja la imagen a escala
                const img = new Image();
                img.onload = () => {
                    canvas.width = paperDims.w * DPR;
                    canvas.height = paperDims.h * DPR;
                    canvas.style.width = paperDims.w + 'px';
                    canvas.style.height = paperDims.h + 'px';
                    ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
                    ctx.clearRect(0, 0, paperDims.w, paperDims.h);
                    ctx.drawImage(img, 0, 0, paperDims.w, paperDims.h);

                    // coloca el punto rojo donde estaba
                    marker.style.display = 'block';
                    marker.style.left = t.signature_x + 'px';
                    marker.style.top = t.signature_y + 'px';
                };
                img.src = `<?= rtrim(site_url(), '/') ?>/${t.image}`;

                // 5) click en todo el wrapper para mover punto
                wrapper.replaceWith(wrapper.cloneNode(true));
                const freshWrapper = document.getElementById('editCanvasWrapper');
                freshWrapper.addEventListener('click', ev => {
                    const rect = freshWrapper.getBoundingClientRect();
                    const x = ev.clientX - rect.left;
                    const y = ev.clientY - rect.top;
                    marker.style.left = x + 'px';
                    marker.style.top = y + 'px';
                    // si quieres guardar, también actualiza tu sigX/sigY aquí
                });

                // 6) abre modal
                editModal.show();
            });
        });


        // 2) avanzar a paso 2
        toStep2.addEventListener('click', () => {
            if (!nameInput.value.trim()) return alert('Escribe un nombre');
            step1.classList.remove('active');
            step2.classList.add('active');
        });
        backTo1.addEventListener('click', () => {
            step2.classList.remove('active');
            step1.classList.add('active');
        });

        // 3) canvas draggable
        canvas.addEventListener('click', e => {
            const rect = canvas.getBoundingClientRect();
            sigX = e.clientX - rect.left;
            sigY = e.clientY - rect.top;
            marker.style.left = sigX + 'px';
            marker.style.top = sigY + 'px';
        });

        // 4) Guardar cambios
        saveBtn.addEventListener('click', async () => {
            showLoading();
            saveBtn.disabled = true;
            const payload = {
                name: nameInput.value.trim(),
                signature_x: sigX,
                signature_y: sigY
            };
            const csrfToken = '<?= csrf_hash() ?>';
            const id = tplIdInput.value;
            const res = await fetch(`<?= site_url('stamptemplate/updateAjax') ?>/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // <-- esto es crítico
                },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.success) {
                editModal.hide();
                // recarga lista o actualiza sólo el nombre/marker en la card…
                showSuccess('✅ Plantilla actualizada satisfactoriamente.', 'success');

                setTimeout(() => window.location.reload(), 1000);
                editModalEl.addEventListener('hidden.bs.modal', () => {
                    document.body.classList.remove('modal-open');
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                });

            } else {
                alert(json.message || 'Error al guardar');
            }
            saveBtn.disabled = false;
        });


        const deleteModalEl = document.getElementById('confirmDeleteModal');
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        let templateToDelete = null;

        // 1) Al pinchar en el botón de borrar, abrimos el modal
        document.getElementById('templateList').addEventListener('click', e => {
            const btn = e.target.closest('.btn-delete');
            if (!btn) return;
            e.preventDefault();

            // guardamos el id de la plantilla
            const card = btn.closest('.template-item');
            templateToDelete = card.dataset.id;

            deleteModal.show();
        });

        // 2) Al confirmar eliminación
        document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
            if (!templateToDelete) return;
            // bloqueamos el botón
            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.textContent = 'Eliminando…';

            try {
                const csrfToken = '<?= csrf_hash() ?>';
                const res = await fetch(`<?= site_url('stamptemplate/deleteAjax') ?>/${templateToDelete}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // <-- esto es crítico
                    }
                });
                const json = await res.json();
                if (json.success) {
                    // cerramos modal y limpiamos backdrop
                    deleteModal.hide();
                    deleteModalEl.addEventListener('hidden.bs.modal', () => {
                        document.body.classList.remove('modal-open');
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    });
                    // feedback
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-warning position-fixed top-0 end-0 m-3';
                    toast.textContent = '✅ Plantilla eliminada.';
                    document.body.append(toast);
                    setTimeout(() => toast.classList.add('show'), 10);
                    setTimeout(() => toast.remove(), 3000);

                    // recarga lista o página
                    if (typeof loadTemplates === 'function') {
                        loadTemplates();
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(json.message || 'No se pudo eliminar.');
                }
            } catch (err) {
                console.error(err);
                alert('Error al eliminar.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Eliminar';
                templateToDelete = null;
            }
        });
    });
</script>