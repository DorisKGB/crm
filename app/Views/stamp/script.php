<script>
    let allStamps = []; // aquí guardamos todo
    let currentStatus = 'pending';
    let _stampToPrint = null;
    let seenStampIds = new Set();


    document.addEventListener("DOMContentLoaded", function() {
        // Cargar timbres y clínicas, y configurar eventos

        loadClinics();
        document.getElementById("formTimbrado").addEventListener("submit", submitForm);

        // ----- Código para selección de plantilla -----
        let templates = [];
        window.selectedTemplate = null; // Plantilla seleccionada
        const chooseTemplateBtn = document.getElementById('choose-template-btn');
        const templateModalEl = document.getElementById('Plantilla');
        const templateModal = new bootstrap.Modal(templateModalEl);
        const templateList = document.getElementById('template-list');
        const templateSearch = document.getElementById('template-search');
        const selectedTemplateNameInput = document.getElementById('selected-template-name');
        const selectedTemplateImageContainer = document.getElementById('selected-template-image-container');
        const templateLoader = document.getElementById('template-loader');
        // Modal de previsualización
        const templatePreviewModalEl = document.getElementById('templatePreviewModal');
        const templatePreviewModal = new bootstrap.Modal(templatePreviewModalEl);
        const templatePreviewImageContainer = document.getElementById('template-preview-image-container');
        const choosePreviewBtn = document.getElementById('choose-preview-btn');
        const backToListBtn = document.getElementById('back-to-list-btn');
        let prevApprovedCount = 0;
        let prevDeniedCount = 0;


        // Scroll en listStamp (sin cambios)
        const scrollContainer = document.querySelector('.listStamp');
        scrollContainer.addEventListener('wheel', function(e) {
            const delta = e.deltaY;
            const atTop = scrollContainer.scrollTop === 0;
            const atBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop === scrollContainer
                .clientHeight;
            if ((delta < 0 && atTop) || (delta > 0 && atBottom)) return;
            e.preventDefault();
            scrollContainer.scrollTop += delta;
        }, {
            passive: false
        });

        let baseUrl = "<?= site_url() ?>";
        if (baseUrl.slice(-1) !== "/") {
            baseUrl += "/";
        }

        const isAdmin = <?= $login_user->is_admin ? 'true' : 'false' ?>;
        // Función para renderizar la lista de plantillas (cada item tendrá botones "Visualizar" y "Elegir")
        function renderTemplateList(filter = '') {
            templateList.innerHTML = '';
            templates.filter(t => t.name.toLowerCase().includes(filter.toLowerCase()))
                .forEach(t => {
                    const templateItem = document.createElement('div');
                    templateItem.classList.add('template-item');
                    templateItem.style.cursor = 'pointer';
                    //templateItem.style.border = '1px solid #ccc';
                    templateItem.style.padding = '10px';
                    templateItem.style.textAlign = 'center';
                    templateItem.innerHTML = `
                <img src="${baseUrl}${t.image}" alt="${t.name}" class="img-thumbnail" style="width:100px; height:auto;">
                <p>${t.name}</p>
                <button class="btn btn-sm btn-success choose-btn w-100" style="margin:2px;"><i class="fas fa-window-restore"></i> Elegir</button>
                <button class="btn btn-sm btn-secondary w-100 visualize-btn" style="margin:2px;"><i class="far fa-image"></i> Visualizar</button>
            `;
                    if (isAdmin || t.clinic_id !== null) {
                        templateItem.insertAdjacentHTML('beforeend', `
                    <button class="btn btn-sm btn-primary w-100 mb-1 edit-btn" data-id="${t.id}">
                        <i class="fas fa-pencil-alt"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger w-100 delete-btn" data-id="${t.id}">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                    `);
                    }
                    const editBtn = templateItem.querySelector('.edit-btn');
                    if (editBtn) editBtn.addEventListener('click', () => openEditModal(t.id));

                    const deleteBtn = templateItem.querySelector('.delete-btn');
                    if (deleteBtn) deleteBtn.addEventListener('click', onDeleteTemplate);


                    // Botón Elegir: selecciona la plantilla y cierra el modal
                    templateItem.querySelector('.choose-btn').addEventListener('click', function() {
                        selectTemplate(t);
                        templateModal.hide();
                    });
                    // Botón Visualizar: abre el modal de previsualización
                    templateItem.querySelector('.visualize-btn').addEventListener('click', function() {
                        templatePreviewImageContainer.innerHTML =
                            `<img src="${baseUrl}${t.image}" alt="${t.name}" style="width:100%; max-height:500px; object-fit:contain;" class="zoomable">`;
                        window.previewTemplate = t;
                        // Limpia cualquier imagen previa para evitar duplicados
                        templatePreviewImageContainer.innerHTML = '';
                        templatePreviewImageContainer.innerHTML =
                            `<img src="${baseUrl}${t.image}" alt="${t.name}" style="width:100%; max-height:500px; object-fit:contain;" class="zoomable">`;
                        templateModal.hide();
                        templatePreviewModal.show();
                    });
                    templateList.appendChild(templateItem);
                });
        }

        async function onDeleteTemplate(e) {
            const id = e.currentTarget.dataset.id;
            if (!confirm('¿Seguro que quieres eliminar esta plantilla?')) return;
            try {

                const res = await fetch(`<?= site_url("stamptemplate/deleteAjax") ?>/${id}`, {
                    method: 'GET'
                });
                const data = await res.json();
                if (data.success) {

                    showSuccess('Plantilla eliminada');
                    loadTemplates();
                } else {
                    showError('No se pudo eliminar.');

                }
            } catch (err) {
                console.error(err);
                showError('Error al eliminar.');

            }
        }

        // Función para cargar plantillas vía AJAX con loader
        window.loadTemplates = function() {
            templateLoader.style.display = 'block';
            fetch("<?= site_url('stamptemplate/listAjax') ?>")
                .then(response => response.json())
                .then(data => {
                    templateLoader.style.display = 'none';
                    if (data.success) {
                        templates = data.templates;
                        renderTemplateList();
                    } else {
                        console.error("Error al cargar plantillas:", data.message);
                    }
                })
                .catch(err => {
                    templateLoader.style.display = 'none';
                    console.error("Error en la petición AJAX de plantillas:", err);

                });
        };


        // Al hacer clic en "Elegir Plantilla"
        chooseTemplateBtn.addEventListener('click', function() {
            templateSearch.value = '';
            loadTemplates();
            templateModal.show();
        });

        templateSearch.addEventListener('input', function() {
            renderTemplateList(this.value);
        });

        // Botón Volver atrás: cierra el preview, limpia el contenedor y vuelve al listado
        backToListBtn.addEventListener('click', function() {
            // Ocultamos el modal de previsualización
            templatePreviewModal.hide();
            // Esperamos a que se complete la transición (300 ms, ajustar si es necesario)
            setTimeout(() => {
                templatePreviewImageContainer.innerHTML = '';
                window.previewTemplate = null;
                // Mostramos el modal de listado de plantillas
                templateModal.show();
            }, 300);
        });

        // Botón Elegir en el preview: selecciona la plantilla y cierra el modal de previsualización
        choosePreviewBtn.addEventListener('click', function() {
            if (window.previewTemplate) {
                selectTemplate(window.previewTemplate);
                // Esperamos a que se oculte el modal de previsualización
                templatePreviewModal.hide();
            }
        });



        // Función que selecciona la plantilla y actualiza el formulario
        function selectTemplate(template) {
            window.selectedTemplate = template;
            selectedTemplateNameInput.value = template.name;
            selectedTemplateImageContainer.innerHTML =
                `<img src="${baseUrl}${template.image}" alt="${template.name}" style="width:40px; height:auto; margin-left:10px; cursor:pointer;" onclick="previewSelectedTemplate()">`;
        }

        // Agrega este listener después de cargar el DOM (por ejemplo, dentro del "DOMContentLoaded")
        document.getElementById('template-preview-image-container').addEventListener('click', function(e) {
            const img = e.target;
            if (img.tagName.toLowerCase() === 'img') {
                // Construye el contenido para el modal de zoom
                document.getElementById('zoomModalContent').innerHTML =
                    `<img src="${img.src}" alt="${img.alt}" style="width:100%; max-height:90vh; object-fit:contain;" class="zoomable">`;
                // Cierra el modal de previsualización (si está abierto)
                const previewModalInstance = bootstrap.Modal.getInstance(document.getElementById(
                    'templatePreviewModal'));
                if (previewModalInstance) {
                    previewModalInstance.hide();
                }
                // Abre el modal de zoom
                const zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
                zoomModal.show();
            }
        });

    });


    function previewSelectedTemplate() {
        if (window.selectedTemplate) {
            let baseUrl = "<?= site_url() ?>";
            if (baseUrl.slice(-1) !== "/") {
                baseUrl += "/";
            }
            document.getElementById('zoomModalContent').innerHTML =
                `<img src="${baseUrl}${window.selectedTemplate.image}" alt="${window.selectedTemplate.name}" style="width:100%; max-height:90vh; object-fit:contain;" class="zoomable">`;
            let zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
            zoomModal.show();
        }
    }

    // --------------------------------------------------------------------------
    // Función para cargar la lista de timbres vía AJAX
    async function loadStamp() {
        try {
            const response = await fetch("<?= site_url('stamp/listAjax') ?>");
            const data = await response.json();
            // Siempre limpio antes de repintar
            const container = document.querySelector("#list-stamp .cards-container");
            container.innerHTML = "";
            if (data.success) {
                allStamps = data.stamp || [];
            } else {
                allStamps = [];
            }
            renderFilteredStamps();
        } catch (error) {
            console.error("Error al cargar los timbres:", error);
        }
    }

    async function checkNewStamps() {
        try {
            const res = await fetch("<?= site_url('stamp/listAjax') ?>");
            const data = await res.json();
            if (!data.success) return;

            // Extraemos sólo los IDs que vienen del servidor
            const incomingIds = data.stamp.map(s => s.id);

            // Filtramos los que NO estaban en seenStampIds: ¡nuevos!
            const newIds = incomingIds.filter(id => !seenStampIds.has(id));

            if (newIds.length > 0) {
                // 1) Toca el sonido
                document.getElementById('notif-sound').play().catch(() => {});

                // 2) Añade cada nuevo al Set y lo pintas
                newIds.forEach(id => {
                    const stamp = data.stamp.find(s => s.id === id);
                    renderStampCard(stamp);
                    seenStampIds.add(id);
                });
            }

            // También podrías reordenar o refrescar toda la lista, si lo prefieres:
            // allStamps = data.stamp;
            // renderFilteredStamps();

        } catch (e) {
            console.error("Error checking stamps:", e);
        }
    }


    async function initSeenStamps() {
        try {
            const res = await fetch("<?= site_url('stamp/listAjax') ?>");
            const data = await res.json();
            if (data.success) {
                data.stamp.forEach(s => seenStampIds.add(s.id));
                renderFilteredStamps(); // pinta la primera vez
            }
        } catch (e) {
            console.error("Init stamps error:", e);
        }
    }


    async function checkStampCounts() {
        try {
            const res = await fetch("<?= site_url('stamp/listAjax') ?>");
            const data = await res.json();
            if (data.success) {
                const stamps = data.stamp || [];
                const approved = stamps.filter(s => s.approved == 1).length;
                const denied = stamps.filter(s => s.approved == 0 && !!s.provider_user_id).length;

                // si cambió cualquiera de los conteos, recarga
                if (approved !== prevApprovedCount || denied !== prevDeniedCount) {
                    prevApprovedCount = approved;
                    prevDeniedCount = denied;
                    await loadStamp();
                }
            }
        } catch (e) {
            console.error("Error al comprobar conteos de timbres:", e);
        }
    }


    document.addEventListener("DOMContentLoaded", () => {
        initSeenStamps();
        loadStamp().then(() => {
            // Espera un momento a que allStamps esté poblado
            prevApprovedCount = allStamps.filter(s => s.approved == 1).length;
            prevDeniedCount = allStamps.filter(s => s.approved == 0 && !!s.provider_user_id).length;
        });
        // Cada 10 segundos comprobamos nuevos
        setInterval(checkNewStamps, 10 * 1000);
        setInterval(checkStampCounts, 10 * 1000);
    });


    // Función para enviar el formulario de timbrado e imprimir el documento
    async function submitForm(event) {
        event.preventDefault();
        document.getElementById('loader-laboral').style.display = 'block';
        // Verificar que se haya elegido una plantilla (campo requerido)
        if (!window.selectedTemplate) {
            showError('Seleccione una plantilla.');
            document.getElementById('loader-laboral').style.display = 'none';
            return;
        }
        const form = document.getElementById('formTimbrado');
        const formData = new FormData(form);
        const clinicSelect = document.getElementById('clinic_select');
        const selectedOption = clinicSelect.options[clinicSelect.selectedIndex];
        const clinicId = selectedOption.getAttribute('data-id');
        formData.append('clinic_id', clinicId);
        const sizeInput = document.querySelector('input[name="size"]:checked');
        if (!sizeInput) {
            showError('Seleccione un tamaño de documento.');
            document.getElementById('loader-laboral').style.display = 'none';
            return;
        }
        if (!clinicId) {
            showError('Seleccione una Clínica.');
            document.getElementById('loader-laboral').style.display = 'none';
            return;
        }
        showLoading();
        // Agregar datos de la plantilla seleccionada
        formData.append('template_name', window.selectedTemplate.name);
        formData.append('template_image', window.selectedTemplate.image);
        formData.append('signature_x', window.selectedTemplate.signature_x);
        formData.append('signature_y', window.selectedTemplate.signature_y);
        formData.append('page_size', window.selectedTemplate.page_size);
        try {
            const response = await fetch("<?= site_url('stamp/create') ?>", {
                method: "POST",
                body: formData
            });
            const result = await response.json();
            document.getElementById('loader-laboral').style.display = 'none';
            if (result.success) {
                showSuccess('Solicitud de Timbre Creado Correctamente!')
                form.reset();
                window.selectedTemplate = null;
                document.getElementById('selected-template-name').value = '';
                document.getElementById('selected-template-image-container').innerHTML = '';
                // Aquí se puede llamar a imprimirDocumento(result.stamp) si se desea imprimir
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

    // --------------------------------------------------------------------------
    // Observer para generar el código QR cuando el elemento entre en la vista
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const token = el.dataset.token;
                const qrUrl = el.dataset.url;
                new QRCode(el, {
                    text: qrUrl,
                    width: 80,
                    height: 80,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H,
                });
                obs.unobserve(el);
            }
        });
    });

    function formatearFecha(stamp) {
        // Convertir a UTC sin alteración de zona horaria
        const fechaUTC = new Date(stamp.created_at + " UTC");

        // Extraer los valores manualmente
        const year = fechaUTC.getUTCFullYear();
        const month = String(fechaUTC.getUTCMonth() + 1).padStart(2, "0");
        const day = String(fechaUTC.getUTCDate()).padStart(2, "0");
        let hours = fechaUTC.getUTCHours();
        const minutes = String(fechaUTC.getUTCMinutes()).padStart(2, "0");
        const seconds = String(fechaUTC.getUTCSeconds()).padStart(2, "0");

        // Formateo AM/PM
        const ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12; // Convierte 0 en 12 para formato 12h

        return `${month}/${day}/${year}, ${String(hours).padStart(2, "0")}:${minutes}:${seconds} ${ampm}`;
    }

    // Función para renderizar cada tarjeta de timbre
    function renderStampCard(stamp) {
        const container = document.querySelector("#list-stamp .cards-container");
        const token = stamp.token;
        const qrUrl = `https://www.clinicahispanarubymed.com/excusevalidator?token=${token}`;
        const fechaHora = formatearFecha(stamp);
        const TextTimbrado = (stamp.stamped == 0) ? "NO" : "SI";
        var timbrar = '';
        if (TextTimbrado == "NO" && stamp.provider != "" && stamp.approved != 0) {
            /*timbrar = `<button class="btn-rubymed btn-rubymed-sm btn-rubymed-secondary" onclick="timbrarStamp('${stamp.id}')">
                            <i class="fas fa-signature"></i> Timbrar
                        </button>`;*/
            timbrar = `<button class="btn-rubymed btn-rubymed-sm btn-rubymed-secondary" onclick="showPrinterInstructions('${stamp.id}')">
                        <i class="fas fa-signature"></i> Timbrar
                    </button>`;
        }
        var textAprobacion = '';
        var buttonText = ``;
        if (stamp.approved == 1 && stamp.provider != "") {
            textAprobacion = `
            <p class='text-success'><b><i class="fas fa-check-double"></i> Estado</b>:Aprobado </p>
            <p><b><i class="fas fa-user-cog"></i> Provider</b>:${stamp.provider} </p>
        `;
        } else {
            if (stamp.approved == 0 && stamp.provider == null) {
                textAprobacion = `
                <p class='text-warning'><b><i class="fas fa-clock"></i> Estado</b>: <span>En espera de aprobación..</span> </p>
            `;
                buttonText = `
                <button class="btn-rubymed btn-rubymed-sm btn-rubymed-success" onclick="approveStamp('${stamp.id}', '<?= $login_user->id ?>')">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button class="btn-rubymed btn-rubymed-sm btn-rubymed-danger" onclick="denyStamp('${stamp.id}', '<?= $login_user->id ?>')">
                    <i class="fas fa-times"></i> Denegar
                </button>
            `;
            } else {
                textAprobacion = `
                <p class='text-danger'><b><i class="fas fa-close"></i> Estado</b>:Negado </p>
                <p><b><i class="fas fa-close"></i> Provider</b>:${stamp.provider} </p>
            `;
            }
        }
        const card = `
        <div class="row list-stamp mb-4" id="cardStamp-${stamp.id}" style="position: relative;">
        <div
                style="
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    width: 32px;
                    height: 32px;
                    background: #007bff;
                    color: #fff;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                ">
                ${stamp.id}
            </div>
            <div class="col-auto">
                <div id="qrcode-${token}" class="qrcode-container" data-token="${token}" data-url="${qrUrl}"></div>
            </div>
            <div class="col-md-8">
                <p><b><i class="fas fa-user"></i> Clínica</b>: ${stamp.clinic_select}</p>
                <p><b><i class="fas fa-square"></i> Documento</b>: ${stamp.size}</p>
                <p><b><i class="fas fa-square"></i> Timbrado</b>:${TextTimbrado} </p>
            </div>
            <div class="col-md-8">
                ${textAprobacion}
                <p><b><i class="fas fa-square"></i> Generado Por</b>:${stamp.generate_name} </p>
                <p class='colorFechaHora'><i class="far fa-clock"></i><small> ${fechaHora}</small></p>
                <p>
                    <button class="btn-rubymed btn-rubymed-sm btn-rubymed-primary" onclick="mostrarDescripcionTimbre('${token}')">
                        <i class="fas fa-comment"></i> Descripción
                    </button>
                    <?php if ($stamp_permission == "provider") { ?>
                    ${buttonText}
                    <?php } ?>
                    <?php if ($stamp_permission == "request" || $login_user->is_admin) { ?>
                    ${timbrar}
                     <?php } ?>
                </p>
            </div>
        </div>
        <hr />
        `;
        container.insertAdjacentHTML("afterbegin", card);
        const qrElement = document.getElementById(`qrcode-${token}`);
        observer.observe(qrElement);
    }


    function showPrinterInstructions(id) {
        _stampToPrint = id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('printerInstructionsModal')).show();
    }

    // 2) Al pulsar “¡Tienes todo listo!” en el modal de instrucciones…
    document.getElementById('printerReadyBtn').addEventListener('click', () => {
        // Oculta el modal de instrucciones
        bootstrap.Modal.getInstance(document.getElementById('printerInstructionsModal')).hide();
        // Y abre el modal de confirmación final
        bootstrap.Modal.getOrCreateInstance(document.getElementById('printerConfirmModal')).show();
    });

    // 3) Si cancela en el confirm, simplemente quita el id pendiente
    document.getElementById('printerCancelBtn').addEventListener('click', () => {
        _stampToPrint = null;
    });

    // 4) Si confirma en el segundo modal, ejecuta el timbrado
    document.getElementById('printerConfirmBtn').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('printerConfirmModal')).hide();
        if (_stampToPrint) {
            timbrarStamp(_stampToPrint);
            _stampToPrint = null;
        }
    });
    // --------------------------------------------------------------------------
    // Función para cargar las clínicas vía AJAX
    async function loadClinics() {
        try {
            let response = await fetch('<?= site_url("stamp/clinicsAjax") ?>');
            let data = await response.json();
            if (data.success) {
                let select = document.getElementById('clinic_select');
                select.innerHTML = '<option value="">Seleccione una clínica</option>';
                data.clinics.forEach(clinic => {
                    select.innerHTML +=
                        `<option value="${clinic.name}" data-id="${clinic.id}" data-provider="${clinic.provider_id}" data-address="${clinic.address}" data-phone="${clinic.phone}">${clinic.name}</option>`;
                });
            }
        } catch (error) {
            console.error('Error al cargar clínicas:', error);
        }
    }


    document
        .querySelectorAll('#stampStatusTabs .nav-link')
        .forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                // marca activa
                document
                    .querySelectorAll('#stampStatusTabs .nav-link')
                    .forEach(a => a.classList.remove('active'));
                tab.classList.add('active');
                // cambia filtro y repinta
                currentStatus = tab.dataset.status;
                renderFilteredStamps();
            });
        });

    loadStamp();
    updateStampCounts();


    function updateStampCounts() {
        const pendingCount = allStamps.filter(s => s.approved == 0 && !s.provider_user_id).length;
        const approvedCount = allStamps.filter(s => s.approved == 1).length;
        const deniedCount = allStamps.filter(s => s.approved == 0 && !!s.provider_user_id).length;

        document.querySelector('#stampStatusTabs [data-status="pending"]')
            .textContent = `Pendientes (${pendingCount})`;
        document.querySelector('#stampStatusTabs [data-status="approved"]')
            .textContent = `Aprobadas (${approvedCount})`;
        document.querySelector('#stampStatusTabs [data-status="denied"]')
            .textContent = `Negadas (${deniedCount})`;
    }


    function renderFilteredStamps() {
        updateStampCounts();
        const container = document.querySelector(".cards-container");
        container.innerHTML = '';
        // filtrar según estado
        const filtered = allStamps.filter(stamp => {
            if (currentStatus === 'pending') {
                // no aprobado ni denegado → approved==0 y provider_user_id null
                return stamp.approved == 0 && !stamp.provider_user_id;
            }
            if (currentStatus === 'approved') {
                return stamp.approved == 1;
            }
            if (currentStatus === 'denied') {
                // denegado → approved==0 pero sí tiene provider_user_id
                return stamp.approved == 0 && !!stamp.provider_user_id;
            }
        });
        // pintar cada uno
        filtered.forEach(stamp => renderStampCard(stamp));
    }

    // --------------------------------------------------------------------------
    // Función para filtrar timbres en la lista según el input
    /*document.querySelector('#list-stamp input').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const cards = document.querySelectorAll('#list-stamp .list-stamp');
        cards.forEach(card => {
            const clinic = card.querySelector('p:nth-of-type(1)').textContent.toLowerCase();
            const provider = card.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
            const documentType = card.querySelector('p:nth-of-type(3)').textContent.toLowerCase();
            if (clinic.includes(searchValue) || provider.includes(searchValue) || documentType.includes(
                    searchValue)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });*/

    const searchInput = document.getElementById('search');
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        // Pillamos sólo los cards de la sección activa
        const cardsContainer = document.querySelector('#list-stamp .cards-container');
        const cards = cardsContainer.querySelectorAll('.row.list-stamp');
        cards.forEach(card => {
            // Busca dentro de todo el texto del card
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // --------------------------------------------------------------------------
 

    // --------------------------------------------------------------------------
    // Función para imprimir el documento generado
    function imprimirDocumento(stamp) {
        console.log(stamp);
        const size = stamp.size?.toLowerCase() || 'carta';
        let pageSize;
        if (stamp.page_size) {
            pageSize = stamp.page_size;
        } else {
            switch (size) {
                case 'oficio':
                    pageSize = '8.5in 13in';
                    break;
                case 'a4':
                    pageSize = '8.27in 11.69in';
                    break;
                default:
                    pageSize = '8.5in 11in';
            }
        }
        const contenido = stamp.content || '';
        const currentDateTime = new Date().toLocaleString();
        const token = stamp.token || '';
        const tokenPrefix = token.substring(0, 8).toUpperCase();
        const tokenRest = token.substring(8).toLowerCase();
        const tokenFormatted = tokenPrefix + tokenRest;
        const BASE_URL = "<?= rtrim(site_url(), '/') ?>/";
        const firmaImg = `${BASE_URL}firmas/${stamp.provider_signature}`;
        const qrUrl = `https://www.clinicahispanarubymed.com/excusevalidator?token=${stamp.token}`;
        const signatureX = stamp.signature_x || 0;
        const signatureY = stamp.signature_y || 0;
        const html = `
            <html>
            <head>
                <title>Documento Timbre</title>
                <style>
                @page { size: ${pageSize}; margin: 0; }
                html, body { margin: 0; padding: 0; font-family: Arial, sans-serif; height: 100%; }
                .page-container {
                    position: relative;
                    width: 100%;
                    height: 100%;
                }
                .content {
                    padding: 10px;
                    font-size: 10px;
                    transform: scale(0.5);
                    transform-origin: top left;
                }
                .signature-container {
                    position: absolute;
                    left: ${signatureX-50}px;
                    top: ${signatureY-50}px;
                    text-align: center;
                    font-size: 8px;
                }
                .signature-content {
                    display: flex;
                    align-items: flex-end;
                }
                .signature-text {
                    margin-top: 5px;
                }
                </style>
            </head>
            <body>
                <div class="page-container">
                <div class="content">
                    ${contenido}
                </div>
                <div class="signature-container">
                    <div class="signature-content">
                    <img src="${firmaImg}" style="height:70px; margin:0; padding:0;">
                    <div id="qr-container" style="margin-left:10px;"></div>
                    </div>
                    <div class="signature-text">
                    Electronic Signature Ref <b>${tokenFormatted}</b> ${currentDateTime}<br>
                    <span style="font-size:7px;">Provider: ${stamp.provider || ''}</span>
                    </div>
                </div>
                </div>
            </body>
            </html>`;
        const ventana = window.open('', '_blank');
        ventana.document.write(html);
        ventana.document.close();
        ventana.addEventListener("load", () => {
            const qrScript = ventana.document.createElement("script");
            qrScript.src = "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
            qrScript.onload = () => {
                new ventana.QRCode(ventana.document.getElementById("qr-container"), {
                    text: qrUrl,
                    width: 40,
                    height: 40,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: ventana.QRCode.CorrectLevel.H
                });
                const imgs = ventana.document.images;
                let loaded = 0;
                const checkPrint = () => {
                    loaded++;
                    if (loaded === imgs.length) {
                        setTimeout(() => {
                            ventana.focus();
                            ventana.print();
                            ventana.close();
                        }, 300);
                    }
                };
                if (imgs.length === 0) {
                    ventana.focus();
                    ventana.print();
                    ventana.close();
                    return;
                }
                for (let img of imgs) {
                    if (img.complete) {
                        checkPrint();
                    } else {
                        img.onload = img.onerror = checkPrint;
                    }
                }
            };
            ventana.document.head.appendChild(qrScript);
        });
    }

    // Función para aprobar el timbre (con loader)
    async function approveStamp(id, userId) {
        if (confirm('¿Aprobar este timbre?')) {
            showLoading();
            document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/approveAjax') ?>/" + id + "/" + userId, {
                    method: "GET"
                });
                const result = await response.json();
                document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    showSuccess();
                    await loadStamp(); // recarga inmediata
                } else {
                    showError('Hubo un error');
                    alert("Error al aprobar.");
                }
            } catch (error) {
                showError('Hubo un error');
                document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en approveStamp:", error);
            }
        }
    }

    // Función para denegar el timbre (con loader)
    async function denyStamp(id, userId) {
        if (confirm('¿Denegar este timbre?')) {
            showLoading();
            document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/denyAjax') ?>/" + id + "/" + userId, {
                    method: "GET"
                });
                const result = await response.json();
                document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    showSuccess();
                    await loadStamp(); // recarga inmediata
                } else {
                    showError('Hubo un error');
                    alert("Error al denegar.");
                }
            } catch (error) {
                showError('Hubo un error');
                document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en denyStamp:", error);
            }
        }
    }

    // Función para timbrar el documento (llama al endpoint timbrarAjax y luego imprime el HTML devuelto)
    async function timbrarStamp(id) {
        if (confirm('¿Timbrar este documento?')) {
            document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/timbrarAjax') ?>/" + id, {
                    method: "GET"
                });
                const result = await response.json();
                document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    imprimirDocumento(result.stamp);
                    loadStamp();
                } else {
                    alert("Error al timbrar el documento.");
                }
            } catch (error) {
                document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en timbrarStamp:", error);
            }
        }
    }

    const editModalEl = document.getElementById('editTemplateModal');
    const editModal = new bootstrap.Modal(editModalEl);
    const canvas = document.getElementById('editTemplateCanvas');
    const ctx = canvas.getContext('2d');
    const marker = document.getElementById('editMarker');
    let editImg = new Image();
    let currentTemplate = null; // guardamos los datos
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


    window.openEditModal = async function(templateId) {
        // 1) Obtén la plantilla vía AJAX
        const res = await fetch(`<?= site_url('stamptemplate/getAjax') ?>/${templateId}`);
        const {
            success,
            template
        } = await res.json();
        if (!success) return alert('No se encontró la plantilla.');

        currentTemplate = {
            ...template,
            // cargamos coordenadas originales para guardado inmediato
            coordinates: {
                x: template.signature_x,
                y: template.signature_y
            }
        };

        document.getElementById('edit-template-id').value = template.id;
        document.getElementById('editClinicName').value = template.clinic_name ||
            '<?php echo app_lang('message_all_clinics'); ?>';
        document.getElementById('editTemplateName').value = template.name;
        document.getElementById('editTamSheet').value = template.page_size;
        // 2) Carga la imagen, luego pinta
        const page = template.page_size;
        console.log(page);

        editImg.onload = () => {
            const dims = {
                width: paperDimensions[page].width,
                height: paperDimensions[page].height
            };
            canvas.width = dims.width;
            canvas.height = dims.height;
            canvas.style.width = dims.css_width + 'px';
            canvas.style.height = dims.css_height + 'px';
            ctx.clearRect(0, 0, dims.width, dims.height);
            ctx.drawImage(editImg, 0, 0, dims.width, dims.height);
            // Posiciona el marker
            marker.style.left = `${template.signature_x}px`;
            marker.style.top = `${template.signature_y}px`;
            marker.style.display = 'block';
        };
        // config tamaño en tu controlador al traer
        // aquí asumimos que template contiene .canvas_width y .css_width, etc.
        editImg.src = template.image;

        editModal.show();
    };

    canvas.addEventListener('click', e => {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        marker.style.left = `${x}px`;
        marker.style.top = `${y}px`;
        currentTemplate.coordinates = {
            x,
            y
        };
    });

    // Guardar cambios
    document.getElementById('saveEditTemplate').addEventListener('click', async () => {
        showLoading("Cargando..");
        const id = document.getElementById('edit-template-id').value;
        const name = document.getElementById('editTemplateName').value.trim();
        if (!name) return alert('El nombre es obligatorio.');

        const payload = {
            name,
            signature_x: currentTemplate.coordinates.x,
            signature_y: currentTemplate.coordinates.y,
        };

        try {
            const res = await fetch(`<?= site_url('stamptemplate/updateAjax') ?>/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            console.log(result);
            if (result.success) {
                loadTemplates();
                editModal.hide();
                showSuccess('Plantilla actualizada');
                //loadTemplates(); // refresca el listado
            } else {
                showError('No se pudo actualizar.');
            }
        } catch (err) {
            console.error(err);
            showError('Error al actualizar.');
        }
    });
</script>