<?php
$permissions2 = $login_user->permissions;
$stamp_permission = get_array_value($permissions2, "stamp_permission_v1");
?>
<div id="page-content" class="page-wrapper clearfix grid-button">

    <div class="row">
        <?php echo view("stamp/wizard.php");
        ?>
        <!-- Lista de timbres -->
        <?php echo view("stamp/list.php"); ?>
    </div>
    <!-- Modal de descripción -->
    <div class="modal fade" id="modalDescripcionTimbre" tabindex="-1" role="dialog"
        aria-labelledby="modalDescripcionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDescripcionLabel">Información del Timbre</h5>
                    <button type="button" class="close btn btn-light" data-bs-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoModalDescripcion">
                    <!-- Se rellena con JS. Se incluye la imagen de la plantilla (si existe) -->
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de listado de plantillas -->
    <div class="modal fade" id="Plantilla" tabindex="-1" role="dialog" aria-labelledby="modalPlantillaLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 90%; width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPlantillaLabel">Elige una Plantilla <span
                            style="color:red">*</span> <a href="<?php echo get_uri('stamptemplate'); ?>"
                            class="btn btn-success btn-sm">Agregar Plantilla</a></h5>

                    <button type="button" class="close btn btn-light" data-bs-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Loader de plantillas -->
                    <div id="template-loader" class="loader">
                        <p>Cargando plantillas...</p>
                    </div>
                    <!-- Buscador -->
                    <input type="text" id="template-search" class="form-control mb-2" placeholder="Buscar plantilla...">
                    <!-- Aquí se listarán las plantillas -->
                    <div id="template-list" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de previsualización de plantilla -->
    <div class="modal fade" id="templatePreviewModal" tabindex="-1" role="dialog"
        aria-labelledby="templatePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templatePreviewModalLabel">Vista previa de la plantilla</h5>
                    <button type="button" class="close btn btn-light" data-bs-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="templatePreviewContent">
                    <div id="template-preview-image-container" style="text-align:center;">
                        <!-- Se mostrará la imagen en grande con zoom -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="choose-preview-btn" class="btn-rubymed btn-rubymed-success"><i
                            class="fas fa-window-restore"></i> Elegir</button>
                    <button type="button" id="back-to-list-btn" class="btn-rubymed btn-rubymed-primary"><i
                            class="fas fa-arrow-left"></i> Volver atrás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT TEMPLATE MODAL -->
    <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateLabel" aria-hidden="true">
        <div class="modal-dialog " style="max-width: 90%; width: 90%;">
            <div class="modal-content p-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTemplateLabel">Editar Plantilla <span style="color:red">*</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="editTemplateForm">
                        <input type="hidden" id="edit-template-id" />

                        <div class="row">
                            <div class="col-md-9">
                                <!-- Canvas + marker -->
                                <div id="editCanvasWrapper" style="position: relative; border:1px solid #ccc;">
                                    <canvas id="editTemplateCanvas"></canvas>
                                    <div id="editMarker" class="marker" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="editClinicName" class="form-label">Clínica</label>
                                    <input type="text" id="editClinicName" disabled class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="editTemplateName" class="form-label">Nombre de plantilla</label>
                                    <input type="text" id="editTemplateName" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editTemplateName" class="form-label">Tamaño del Documento</label>
                                    <input type="text" id="editTamSheet" disabled class="form-control" required>
                                </div>
                                <div>
                                    <div class="alert alert-warning">
                                        <p>Si deseas cambiar el tamaño, formato o clinica que pertenece este documento,
                                            deberás eliminarlo y volverlo a crear. <b>Solo puedes modificar el marcador
                                                donde va la firma electronica o el nombre del documento.</b></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveEditTemplate" class="btn btn-primary"><i class="fas fa-save"></i>
                        Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .circleNumber {
            padding: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .btn-timbrar-llamativo {
            background: linear-gradient(135deg, #4CAF50, #388E3C);
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 40px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-timbrar-llamativo i {
            margin-right: 8px;
        }

        .btn-timbrar-llamativo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            transition: all 0.5s ease;
            z-index: 0;
        }

        .btn-timbrar-llamativo:hover::before {
            top: 100%;
            left: 100%;
        }

        .btn-timbrar-llamativo:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 128, 0, 0.4);
        }

        .circle-count {
            background-color: #dc3545;
            /* rojo vibrante */
            color: #fff;
            font-weight: bold;
            padding: 6px 10px;
            border-radius: 50%;
            font-size: 14px;
            display: inline-block;
            min-width: 32px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            position: relative;
            top: -5px;
            left: 5px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.15);
            }

            100% {
                transform: scale(1);
            }
        }

         .btn-timbrar-llamativo-red {
            background: linear-gradient(135deg,rgb(175, 76, 86),rgb(135, 25, 25));
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 40px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-timbrar-llamativo-red i {
            margin-right: 8px;
        }

        .btn-timbrar-llamativo-red::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(103, 6, 6, 0.1);
            transform: rotate(45deg);
            transition: all 0.5s ease;
            z-index: 0;
        }

        .btn-timbrar-llamativo-red:hover::before {
            top: 100%;
            left: 100%;
        }

        .btn-timbrar-llamativo-red:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(128, 0, 38, 0.4);
        }

    </style>
    <!-- Modal de Instrucciones de Impresión -->
    <div class="modal fade" id="printerInstructionsModal" tabindex="-1" aria-labelledby="printerInstructionsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 90%; width: 90%;">
            <div class="modal-content p-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="printerInstructionsLabel">
                        <span style="font-weight: 400;"> <i class="fas fa-check-double"></i> Departamento de Tecnología
                            Rubymed </span> <br>
                        <b>Instrucciones de Impresión</b>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <style>
                        .step-img {
                            height: 350px;
                            /* ajusta la altura que quieras */
                            object-fit: contain;
                            /* escala sin recortar ni deformar */
                        }
                    </style>

                     <div class="row align-items-center">
                        <!-- Columna de imágenes -->
                        <div class="col-md-4 d-flex justify-content-around align-items-center">
                            <img src="<?= base_url('assets/images/imprimir.gif') ?>"
                                class="step-img me-1"
                                alt="Imprimir" style="border-radius:20px;" >
                            <img src="<?= base_url('assets/images/imprimir2.gif') ?>"
                                class="step-img"
                                alt="Instrucciones" style="border-radius:20px;">
                        </div>
                        <!-- Columna de texto -->
                        <div class="col-md-6">
                            <h2>¡PASO A PASO!</h2>
                            <p><b><span class="circleNumber bg-primary">1.</span> Enciende tu impresora </b> Asegúrate de presionar el botón de encendido y esperar a que la impresora esté lista (luces fijas).</p>
                            <p><b><span class="circleNumber bg-primary">2.</span> Abre la bandeja de papel </b> Tira suavemente de la bandeja inferior hasta que se detenga.</p>
                            <p><b><span class="circleNumber bg-primary">3.</span> Coloca el documento </b> Inserta la hoja con el contenido hacia arriba y alinea el borde izquierdo con la guía de papel.</p>
                            <p><b><span class="circleNumber bg-primary">4.</span> Haz clic en “Tengo todo listo” </b> En esta misma ventana presiona el botón ¡Tengo todo listo!.. Luego le das Click en <b>SÍ, Timbrar</b> para aplicar la firma electrónica.</p>
                            <p><b><span class="circleNumber bg-primary">5.</span> ¡Listo! </b> Retira el documento impreso con el sello de firma y verifica que aparece el check verde junto a la firma.</p>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" id="printerReadyBtn" class="btn btn-success">¡Tengo todo listo!</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación Final -->
    <div class="modal fade" id="printerConfirmModal" tabindex="-1" aria-labelledby="printerConfirmLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 text-center">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="printerConfirmLabel">¿Listo para timbrar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <img width="50%" style="border-radius: 20px;"
                        src="<?php echo base_url("assets/images/firma_rubymed.png"); ?>" alt="">
                    <h4>¿Cumpliste con todas las instrucciones de carga de la hoja?</h4>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" id="printerCancelBtn" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="printerConfirmBtn" class="btn btn-primary">Sí, timbrar</button>
                </div>
            </div>
        </div>
    </div>

    <audio id="notif-sound-stamp" src="<?= base_url('assets/sounds/campana.mp3') ?>" preload="auto"></audio>


    <!-- Modal de Zoom de Imagen -->
    <div class="modal fade" id="zoomModal" tabindex="-1" role="dialog" aria-labelledby="zoomModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body" id="zoomModalContent" style="padding: 0;">
                    <!-- Se mostrará la imagen en grande con zoom -->
                </div>
            </div>
        </div>
    </div>

     <!-- Contenedor para Bootstrap Toasts -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <div id="newStampToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Nuevo Timbre</strong>
                <small class="text-muted">Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body">
                Tienes un nuevo timbre <span id="toast-stamp-id"></span> en <span id="toast-stamp-clinic"></span>.
            </div>
        </div>
    </div>

    <div id="print-preview" style="display:none;"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let allStamps = []; // aquí guardamos todo
    let currentStatus = 'approved';
    let prevApprovedCount = 0;
    let prevDeniedCount = 0;
    let prevPendingCount  = 0;
    let seenStampIds = new Set();
    let currentPage = 0;
    const pageSize = 20;
    const searchInput = document.getElementById('search');

    document.addEventListener("DOMContentLoaded", () => {
        loadStamp(0);
    });


    document.addEventListener("DOMContentLoaded", () => {
        initSeenStamps();
        loadStamp().then(() => {
            // Espera un momento a que allStamps esté poblado
            prevApprovedCount = allStamps.filter(s => s.approved == 1).length;
            prevDeniedCount = allStamps.filter(s => s.approved == 0 && !!s.provider_user_id).length;
            prevPendingCount  = allStamps.filter(s => s.approved == 0 && !s.provider_user_id).length;
        });
        // Cada 10 segundos comprobamos nuevos
        setInterval(checkNewStamps, 10 * 1000);
        setInterval(checkStampCounts, 10 * 1000);
    });

    async function initSeenStamps() {
    const res  = await fetch("<?= site_url('stamp/listAjax') ?>");
    const data = await res.json();
    if (data.success) {
        data.stamp.forEach(s => seenStampIds.add(s.id));
        renderFilteredStamps(); // opcional, pinta la primera vez
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
                const pending  = stamps.filter(s => s.approved == 0 && !s.provider_user_id).length;
                
                // si cambió cualquiera de los conteos, recarga
                if (approved !== prevApprovedCount || denied !== prevDeniedCount) {
                    prevApprovedCount = approved;
                    prevDeniedCount = denied;
                    prevPendingCount  = pending;
                    await loadStamp();
                }
            }
        } catch (e) {
            console.error("Error al comprobar conteos de timbres:", e);
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
                document.getElementById('notif-sound-stamp').play().catch(() => {});

                // 2) Añade cada nuevo al Set y lo pintas
                newIds.forEach(id => {
                    const stamp = data.stamp.find(s => s.id === id);
                    seenStampIds.add(id);

                    // Rellenamos el contenido del toast
                    document.getElementById('toast-stamp-id').textContent = `#${stamp.id}`;
                    document.getElementById('toast-stamp-clinic').textContent = stamp.clinic_select;

                    // Mostramos el toast
                    newStampToast.show();


                    if (
                        (currentStatus === 'approved' && stamp.approved == 1) ||
                        (currentStatus === 'pending' && stamp.approved == 0 && !stamp.provider_user_id) ||
                        (currentStatus === 'denied' && stamp.approved == 0 && !!stamp.provider_user_id)
                    ) {
                        renderStampCard(stamp);
                    }
                });
                // <-- aquí:
                await loadStamp(currentPage);
            }

            // También podrías reordenar o refrescar toda la lista, si lo prefieres:
            // allStamps = data.stamp;
            // renderFilteredStamps();

        } catch (e) {
            console.error("Error checking stamps:", e);
        }
    }

    // Función para cargar la lista de timbres vía AJAX
    async function loadStamp(page = 0) {
        currentPage = page;
        const offset = page * pageSize;
        const res = await fetch(`<?= site_url('stamp/listAjax2') ?>/${currentStatus}/${offset}/${pageSize}?search=${encodeURIComponent($('#search').val())}`);
        const data = await res.json();
        allStamps = data.stamp || [];
        renderFilteredStamps(); // tu función actual de pintado
        renderPagination(data.totalCount, page);


        // → Nuevo: Actualizar indicador de página
        const totalPages = Math.ceil(data.totalCount / pageSize) || 1;
        const pageIndicator = document.getElementById('pageIndicator');
        pageIndicator.textContent = `Página ${page + 1} de ${totalPages}`;

        // **Actualizo los contadores globales de las pestañas**
        document.querySelector('[data-status="approved"]').textContent = `Aprobadas (${data.approvedCount})`;
        document.querySelector('[data-status="pending"]').textContent = `Pendientes (${data.pendingCount})`;
        document.querySelector('[data-status="denied"]').textContent = `Negadas (${data.deniedCount})`;
    }

    searchInput.addEventListener('input', () => loadStamp(0));


    function renderPagination(totalCount, page) {
        const totalPages = Math.ceil(totalCount / pageSize);
        const ul = document.getElementById('stampPagination');
        ul.innerHTML = '';

        // Helper para crear <li>
        const makeItem = (idx, label, active = false, disabled = false) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.innerHTML = label;
            if (!disabled && !active) {
                a.addEventListener('click', async e => {
                    e.preventDefault();
                    // 1) Cargamos la nueva página
                    await loadStamp(idx);
                    // 2) Hacemos scroll suave al principio de la lista
                    document
                        .querySelector('#list-stamp') // apunta al contenedor
                        .scrollIntoView({
                            behavior: 'smooth'
                        });
                });
            }
            li.appendChild(a);
            return li;
        };

        // “«” Prev
        ul.appendChild(makeItem(page - 1, '<i class="fas fa-angle-left"></i>', false, page === 0));

        // Ventana de páginas (máx 9 visibles)
        const maxVisible = 9;
        let start = Math.max(0, page - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages - 1, start + maxVisible - 1);
        if (end - start < maxVisible - 1) {
            start = Math.max(0, end - maxVisible + 1);
        }

        if (start > 0) {
            ul.appendChild(makeItem(0, '1'));
            if (start > 1) {
                const dots = document.createElement('li');
                dots.className = 'page-item disabled';
                dots.innerHTML = `<span class="page-link">…</span>`;
                ul.appendChild(dots);
            }
        }

        for (let i = start; i <= end; i++) {
            ul.appendChild(makeItem(i, (i + 1).toString(), i === page));
        }

        if (end < totalPages - 1) {
            if (end < totalPages - 2) {
                const dots = document.createElement('li');
                dots.className = 'page-item disabled';
                dots.innerHTML = `<span class="page-link">…</span>`;
                ul.appendChild(dots);
            }
            ul.appendChild(makeItem(totalPages - 1, totalPages.toString()));
        }

        // “»” Next
        ul.appendChild(makeItem(page + 1, '<i class="fas fa-angle-right"></i>', false, page === totalPages - 1));
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

    function renderStampCard(stamp) {
        const container = document.querySelector("#list-stamp .cards-container");
        const token = stamp.token;
        const qrUrl = `https://www.clinicahispanarubymed.com/excusevalidator?token=${token}`;
        const fechaHora = formatearFecha(stamp);
        const TextTimbrado = (stamp.stamped == 0) ? "NO" : "SI";
        var timbrar = '';
        /*if (TextTimbrado == "NO" && stamp.provider != "" && stamp.approved != 0) {
            timbrar = `<button class="btn-rubymed btn-rubymed-sm btn-rubymed-secondary" onclick="showPrinterInstructions('${stamp.id}')">
                        <i class="fas fa-signature"></i> Timbrar
                    </button>`;
        }*/
        if (stamp.provider != "" && stamp.approved != 0 && stamp.stamped < 2) {
            timbrar = `<button class="btn-timbrar-llamativo" onclick="showPrinterInstructions('${stamp.id}')">
    <i class="fas fa-signature"></i> Timbrar
</button>
`;
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
                <button class="btn-timbrar-llamativo" onclick="approveStamp('${stamp.id}', '<?= $login_user->id ?>')">
                    <i class="fas fa-check"></i> Aprobar
                </button>
                <button class="btn-timbrar-llamativo-red" onclick="denyStamp('${stamp.id}', '<?= $login_user->id ?>')">
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
            <div class="col-md-4">
                <p><b><i class="fas fa-user"></i> Clínica</b>: ${stamp.clinic_select}</p>
                <p><b><i class="fas fa-square"></i> Documento</b>: <span style="color: ${stamp.orientation === 'landscape' ? 'red' : 'inherit'}; font-weight: ${stamp.orientation === 'landscape' ? 'bold' : 'normal'};">${stamp.size}</span></p>
                <p><b><i class="fas fa-square"></i> Timbrado</b>:${TextTimbrado} <span class="circle-count">${stamp.stamped ?? 0}</span></p>
            </div>
            <div class="col-md-4">
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
        container.insertAdjacentHTML("beforeend", card);
        const qrElement = document.getElementById(`qrcode-${token}`);
        observer.observe(qrElement);
    }

    function showPrinterInstructions(id) {
        _stampToPrint = id;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('printerInstructionsModal')).show();
    }


    // 4) Si confirma en el segundo modal, ejecuta el timbrado
    document.getElementById('printerConfirmBtn').addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('printerConfirmModal')).hide();
        if (_stampToPrint) {
            timbrarStamp(_stampToPrint);
            _stampToPrint = null;
        }
    });

    async function timbrarStamp(id) {
        if (confirm('¿Timbrar este documento?')) {
            //document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/timbrarAjax2') ?>/" + id, {
                    method: "GET"
                });
                const result = await response.json();
                //document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    console.log(result.stamp);
                    imprimirDocumento(result.stamp);
                    loadStamp();
                } else {
                    alert("Error al timbrar el documento.");
                }
            } catch (error) {
                //document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en timbrarStamp:", error);
            }
        }
    }

    function imprimirDocumento(stamp) {
        const isEscaneado = stamp.template_name === "Documento Escaneado";
        const templateImg = "<?= site_url() ?>/" + stamp.template_image;
        console.log(templateImg);
        console.log(stamp);
        const size = stamp.size?.toLowerCase() || 'carta';
        const orientation = stamp.orientation || 'portrait';
        
        // Calcular pageSize considerando la orientación
        let pageSize;
        if (stamp.page_size) {
            // Si page_size viene como string (ej: "a4"), convertirlo a dimensiones
            const pageSizeStr = stamp.page_size.toLowerCase();
            switch (pageSizeStr) {
                case 'oficio':
                    pageSize = orientation === 'landscape' ? '13in 8.5in' : '8.5in 13in';
                    break;
                case 'a4':
                    pageSize = orientation === 'landscape' ? '11.69in 8.27in' : '8.27in 11.69in';
                    break;
                case 'carta':
                default:
                    pageSize = orientation === 'landscape' ? '11in 8.5in' : '8.5in 11in';
            }
        } else {
            switch (size) {
                case 'oficio':
                    pageSize = orientation === 'landscape' ? '13in 8.5in' : '8.5in 13in';
                    break;
                case 'a4':
                    pageSize = orientation === 'landscape' ? '11.69in 8.27in' : '8.27in 11.69in';
                    break;
                default:
                    pageSize = orientation === 'landscape' ? '11in 8.5in' : '8.5in 11in';
            }
        }
        
        // Debug: mostrar información de orientación y tamaño
        console.log('=== DEBUG IMPRESIÓN ===');
        console.log('Stamp orientation:', stamp.orientation);
        console.log('Stamp page_size:', stamp.page_size);
        console.log('Stamp size:', stamp.size);
        console.log('Final orientation:', orientation);
        console.log('Final pageSize (converted):', pageSize);
        console.log('======================');

        // Calcular dimensiones del contenedor basadas en pageSize (ya orientado correctamente)
        const getPageDimensions = (pageSizeStr) => {
            console.log('getPageDimensions input:', { pageSizeStr });
            const parts = pageSizeStr.split(' ');
            if (parts.length === 2) {
                const width = parseFloat(parts[0]) * 96; // Convertir pulgadas a píxeles (96 DPI)
                const height = parseFloat(parts[1]) * 96;
                
                console.log('Calculated dimensions:', { width, height });
                
                return { width, height };
            }
            return { width: 816, height: 1056 }; // Default carta
        };
        
        const pageDimensions = getPageDimensions(pageSize);
        const containerWidth = pageDimensions.width;
        const containerHeight = pageDimensions.height;
        
        // Debug: mostrar dimensiones calculadas
        console.log('Container dimensions:', { width: containerWidth, height: containerHeight });
        console.log('Is landscape?', orientation === 'landscape');
        console.log('Width > Height?', containerWidth > containerHeight);
        console.log('========================');

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
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
                <meta name="format-detection" content="telephone=no">
                <style>
                @page { 
                    size: ${pageSize}; 
                    margin: 0; 
                    orientation: ${orientation}; 
                }
                html, body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: Arial, sans-serif; 
                    height: 100vh; 
                    width: 100vw;
                    overflow: hidden;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    background: #f0f0f0;
                }
                .page-container {
                    position: relative;
                    width: ${containerWidth}px;
                    height: ${containerHeight}px;
                    display: flex;
                    flex-direction: column;
                    border: 2px solid #333;
                    background: white;
                    box-shadow: 0 0 15px rgba(0,0,0,0.3);
                    transform: ${orientation === 'landscape' ? 'rotate(0deg)' : 'rotate(0deg)'};
                }
                .content {
                    padding: 10px;
                    font-size: 8px;
                    width: 100%;
                    height: 100%;
                    box-sizing: border-box;
                    overflow: hidden;
                }
                .signature-container {
                    position: absolute;
                    z-index: 10;
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
                @media print {
                    html, body {
                        margin: 0;
                        padding: 0;
                        height: auto;
                        width: auto;
                        display: block;
                    }
                    .page-container {
                        width: ${containerWidth}px;
                        height: ${containerHeight}px;
                        border: none;
                        box-shadow: none;
                        margin: 0;
                    }
                }
                </style>
            </head>
            <body>
                <div class="page-container">
               ${isEscaneado
    ? `<img src="${templateImg}" style="width:100%;height:100%;object-fit:contain;position:absolute;top:0;left:0;z-index:0;max-width:100%;max-height:100%;">`
    : `<div class="content">${contenido}</div>`}
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
        
        // Validar que la ventana se abrió correctamente
        if (!ventana) {
            alert('No se pudo abrir la ventana de impresión. Por favor, permite ventanas emergentes para este sitio.');
            return;
        }
        
        ventana.document.write(html);
        ventana.document.close();
        
        // Ajustar el zoom y tamaño de la ventana
        ventana.addEventListener("load", () => {
            // Verificar que la ventana sigue abierta
            if (ventana.closed) {
                return;
            }
            
            
            const qrScript = ventana.document.createElement("script");
            qrScript.src = "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
            qrScript.onload = () => {
                // Verificar que la ventana sigue abierta
                if (ventana.closed) {
                    return;
                }
                
                try {
                    new ventana.QRCode(ventana.document.getElementById("qr-container"), {
                        text: qrUrl,
                        width: 40,
                        height: 40,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: ventana.QRCode.CorrectLevel.H
                    });
                } catch (error) {
                    console.error('Error generando QR:', error);
                }
                
                const imgs = ventana.document.images;
                let loaded = 0;
                const checkPrint = () => {
                    // Verificar que la ventana sigue abierta
                    if (ventana.closed) {
                        return;
                    }
                    
                    loaded++;
                    if (loaded === imgs.length) {
                        setTimeout(() => {
                            try {
                                ventana.focus();
                                ventana.print();
                                ventana.close();
                            } catch (error) {
                                console.error('Error en impresión:', error);
                            }
                        }, 300);
                    }
                };
                if (imgs.length === 0) {
                    try {
                        ventana.focus();
                        ventana.print();
                        ventana.close();
                    } catch (error) {
                        console.error('Error en impresión sin imágenes:', error);
                    }
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
            //document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/approveAjax') ?>/" + id + "/" + userId, {
                    method: "GET"
                });
                const result = await response.json();
                //document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    showSuccess();
                    await loadStamp(); // recarga inmediata
                } else {
                    showError('Hubo un error');
                    alert("Error al aprobar.");
                }
            } catch (error) {
                showError('Hubo un error');
                //document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en approveStamp:", error);
            }
        }
    }

    // Función para denegar el timbre (con loader)
    async function denyStamp(id, userId) {
        if (confirm('¿Denegar este timbre?')) {
            showLoading();
            //document.getElementById('loader-laboral').style.display = 'block';
            try {
                const response = await fetch("<?= site_url('stamp/denyAjax') ?>/" + id + "/" + userId, {
                    method: "GET"
                });
                const result = await response.json();
                //document.getElementById('loader-laboral').style.display = 'none';
                if (result.success) {
                    showSuccess();
                    await loadStamp(); // recarga inmediata
                } else {
                    showError('Hubo un error');
                    alert("Error al denegar.");
                }
            } catch (error) {
                showError('Hubo un error');
                //document.getElementById('loader-laboral').style.display = 'none';
                console.error("Error en denyStamp:", error);
            }
        }
    }


    function updateStampCounts() {
        const pendingCount = allStamps.filter(s => s.approved == 0 && !s.provider_user_id).length;
        const approvedCount = allStamps.filter(s => s.approved == 1).length;
        const deniedCount = allStamps.filter(s => s.approved == 0 && !!s.provider_user_id).length;

        document.querySelector('#stampStatusTabs [data-status="approved"]')
            .textContent = `Aprobadas (${approvedCount})`;

        document.querySelector('#stampStatusTabs [data-status="pending"]')
            .textContent = `Pendientes (${pendingCount})`;

        document.querySelector('#stampStatusTabs [data-status="denied"]')
            .textContent = `Negadas (${deniedCount})`;
    }

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

     const toastEl = document.getElementById('newStampToast');
    const newStampToast = new bootstrap.Toast(toastEl);

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
                // cambia filtro y recarga la página 0 para ese estado
                currentStatus = tab.dataset.status;
                loadStamp(0);
            });
        });

    // 2) Al pulsar “¡Tienes todo listo!” en el modal de instrucciones…
    document.getElementById('printerReadyBtn').addEventListener('click', () => {
        // Oculta el modal de instrucciones
        bootstrap.Modal.getInstance(document.getElementById('printerInstructionsModal')).hide();
        // Y abre el modal de confirmación final
        bootstrap.Modal.getOrCreateInstance(document.getElementById('printerConfirmModal')).show();
    });


    // Función para mostrar la descripción de un timbre en un modal
    async function mostrarDescripcionTimbre(token) {
        try {
            const response = await fetch(`<?= site_url('stamp/detail/') ?>${token}`);
            const data = await response.json();
            if (!data.success) {
                alert('No se encontró información del timbre.');
                return;
            }
             const baseUrl = '<?= rtrim(site_url(), '/') ?>';
            const stamp = data.stamp;
            const tokenPrefix = stamp.token.substring(0, 8).toUpperCase();
            const tokenRest = stamp.token.substring(8).toLowerCase();
            const tokenFormatted = tokenPrefix + tokenRest;
            var firmaImg = `<?= site_url('firmas/') ?>${stamp.provider_signature}`;
            const fechaHora = new Date(stamp.created_at).toLocaleString("en-US", {
                month: "2-digit",
                day: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true,
            });
            // Si el timbre incluye la imagen de la plantilla, la mostramos (suponiendo que stamp.template_image exista)
            let templateImageHTML = "<div class='row'>";
            if (stamp.template_image) {
                const templateUrl = `${baseUrl}/${stamp.template_image}`;
                templateImageHTML += `
                <div class='col-md-6' style="text-align:center; margin-bottom:10px;">
                                    <img src="${templateUrl}" alt="Plantilla" style="width:100%; height:100%; object-fit:contain;" class="zoomable">
                                  </div>`;
            }
            const html = `
            ${templateImageHTML}
            <div class='col-md-6'>
            <p><strong>Clínica:</strong> ${stamp.clinic_select}</p>
            <p><strong>Provider:</strong> ${stamp.provider ? stamp.provider : "Pendiente"}</p>
            <p><strong>NPI:</strong> ${stamp.provider_npi ? stamp.provider_npi : "Pendiente"}</p>
            <p><strong>Roles:</strong> ${stamp.provider_role ? stamp.provider_role : "Pendiente"}</p>
            <p><strong>Tamaño:</strong> ${stamp.size}</p>
            <p><strong>Fecha:</strong> ${fechaHora}</p>
            <hr>
            <p><strong>Descripción:</strong></p>
            <p>${stamp.description}</p>
            <hr>
            <div style="text-align:left;">
                <p><strong>Sincerely,</strong></p>
                <img src="${firmaImg}" style="height: 60px;"><br>
                <small>Electronic Signature Ref <b>${tokenFormatted}</b> ${fechaHora}</small><br>
                <small>
                    Este documento ha sido firmado digitalmente por el sistema RUBYMED INC.<br>
                    Puede verificarlo en www.clinicahispanarubymed.com/excusevalidator con el código <b>${tokenPrefix}</b>.
                </small>
            </div>
            <div>
        `;
            document.getElementById('contenidoModalDescripcion').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('modalDescripcionTimbre'));
            modal.show();
        } catch (error) {
            console.error("Error al obtener detalles del timbre:", error);
            alert("Ocurrió un error al cargar los detalles.");
        }
    }
</script>