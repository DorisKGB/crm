<?php
$permissions2 = $login_user->permissions;
$excuse_permission = get_array_value($permissions2, "excuse_permission");
?>
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        overflow: hidden;
        /* evita scroll general */
    }

    .container {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .form-container {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
    }

    .form-container h2,
    .form-container h4 {
        margin-top: 0;
        text-align: center;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea,
    #search-excuse {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        border: none !important;
        background-color: #f5f5f5;
        outline: none;
    }

    .form-group textarea {
        resize: vertical;
    }

    .loader {
        display: none;
        text-align: center;
    }

    .tab-loader {
        display: none;
        text-align: center;
        padding: 20px;
        color: #666;
        font-size: 14px;
    }

    .tab-loader i {
        animation: spin 1s linear infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Contenedor general para los cards */
    .cards-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
        background-color: #f5f5f5;
    }

    /* Estilos para cada card */
    .excusa-card {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 300px;
        margin: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    .excusa-card h2 {
        font-size: 18px;
        margin-top: 0;
        color: #333;
    }

    .excusa-card p {
        margin: 5px 0;
        color: #555;
        font-size: 14px;
    }

    .excusa-card .excusa-token {
        font-size: 12px;
        color: #999;
        margin-top: 10px;
        word-wrap: break-word;
    }

    .card_excuse {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 10px;
        border-radius: 15px;
        margin-top: 15px;
    }

    .form-excuse {
        height: 100%;
        max-height: 120vh;
        overflow-y: scroll;
        overflow-x: hidden;
        position: relative;
    }


    .btn-save {
        padding: 10px 15px;
        background-color: #1ba902;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    /* Estilo para notificaciones */
    #notification {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1000;
        min-width: 250px;
        padding: 15px;
        border-radius: 5px;
        display: none;
        color: #fff;
    }

    #notification.success {
        background-color: #28a745;
    }

    #notification.error {
        background-color: #dc3545;
    }

    /* Buscador */
    #search-excuse {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
</style>

<div id="notification"></div>
<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-md-6">
            <div class=" card" id="excuse-form-container">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-book-reader"></i>
                        <?php echo app_lang('electronic_document') ?>
                    </h4>
                </div>

                <div class="card-body">
                    <form id="form-laboral">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="clinic_id"><b>Tipo de Documento que se desea emitir : </b></label>
                            <select name="" id="">
                                <option value="">Excusa Médica</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="clinic_id">Clinica que Emite el Documento : </label>
                            <select id="clinic_select" name="clinic" required>
                            </select>
                            <!-- Inputs ocultos para dirección y teléfono -->
                            <input type="hidden" name="clinic_id" id="clinic_id" value="">
                            <input type="hidden" name="clinic_address" id="clinic_address" value="">
                            <input type="hidden" name="clinic_phone" id="clinic_phone" value="">
                            <input type="hidden" name="generate_for" value="<?php echo $login_user->id;  ?>">
                            <input type="hidden" name="generate_name" value="<?php echo $login_user->first_name . ' ' . $login_user->last_name;  ?>">
                        </div>

                        <div class="form-group">
                            <label for="nombre_completo_laboral">Nombre Completo del Paciente</label>
                            <input type="text" id="nombre_completo_laboral" name="nombre_completo" required>
                            <input type="hidden" name="provider_npi" value="">
                            <input type="hidden" name="privider_role" value="">
                            <input type="hidden" name="privider_signature" value="">
                            <input type="hidden" name="provider_user_id" value="">
                        </div>

                        <div class="form-group">
                            <label for="fecha_nacimiento_laboral">Fecha Nacimiento</label>
                            <input type="date" id="fecha_nacimiento_laboral" name="fecha_nacimiento" required>
                        </div>

                        <div class="form-group">
                            <label for="fecha_atencion">Fecha y Hora de Atención (MM-DD-YYYY HH:MM)</label>
                            <input type="text" id="fecha_atencion" name="fecha_atencion" required>
                        </div>
                        <div class="form-group">
                            <label for="excuse_medical">Tipo de Excusa Médica</label>
                            <select id="excuse_medical" name="excuse" required>
                                <option value="">Seleccione una Excusa</option>
                                <option value="medica_laboral">Excusa Médica Laboral</option>
                                <option value="medica_escolar">Excusa Médica Escolar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="motivo_laboral">Razon</label>
                            <textarea id="motivo_laboral" name="motivo" rows="10" style="white-space: pre-wrap;background-color: #f7f7f7; border: 1px solid #ddd; padding: 10px;" required></textarea>
                        </div>
                        <div class="form-group d-none">
                            <label for="proveedor_laboral">Elegir Provider <?php if ($excuse_permission == "all" || $login_user->is_admin) {  ?> <button type="button" id="open-provider-modal" class="btn btn-default btn-sm d-none">Agregar Proveedor</button><?php } ?></label>
                            <select id="proveedor_laboral" name="proveedor" disabled required>
                                <!-- Se cargará dinámicamente desde la tabla providers -->
                                <option value="">Cargando proveedores...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha_inicio_laboral">Fecha Vigencia Inicio</label>
                            <input type="date" id="fecha_inicio_laboral" name="fecha_inicio" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin_laboral">Fecha Vigencia Final</label>
                            <input type="date" id="fecha_fin_laboral" name="fecha_fin" required>
                        </div>
                        <!-- Campo oculto para estado; en creación siempre "request" -->
                        <input type="hidden" id="state" name="state" value="request">
                        <button type="submit" class="btn-rubymed btn-rubymed-primary-in " id="submit-btn" style="color: #fff;">
                            <i class="fas fa-arrow-right"></i> Guardar excusa
                        </button>
                        <div class="loader" id="loader-laboral">
                            <p>Cargando...</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card form-excuse">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-book-reader"></i>
                        Lista de <?php echo app_lang('electronic_document') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <input type="text" id="search-excuse" placeholder="Buscar excusa...">
                     <!-- NAV DE ESTADOS -->
                    <ul class="nav nav-tabs mb-3" id="excuseStatusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-state="request">Pendientes (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-state="approved">Aprobadas (0)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-state="denied">Negadas (0)</a>
                        </li>
                    </ul>
                    <!-- Loader para tabs -->
                    <div class="tab-loader" id="tab-loader">
                        <i class="fas fa-spinner"></i>
                        Cargando excusas...
                    </div>
                    
                    <div class="" id="cards-container">
                        <!-- Aquí se cargarán las cards con JS -->
                    </div>
                    
                    <!-- Controles de paginación -->
                    <div id="pagination-controls" class="mt-3" style="display: none;">
                        <nav aria-label="Paginación de excusas">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" id="prev-page">
                                    <a class="page-link" href="#" onclick="loadExcuses(paginationData.prev_page, false); return false;">
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </a>
                                </li>
                                <li class="page-item active" id="current-page">
                                    <span class="page-link" id="current-page-text">1</span>
                                </li>
                                <li class="page-item" id="next-page">
                                    <a class="page-link" href="#" onclick="loadExcuses(paginationData.next_page, false); return false;">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted">
                            <small id="pagination-info">Mostrando 1-20 de 0 excusas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Botón para abrir el modal de agregar proveedor -->
    <!-- Modal: contenedor oculto para agregar proveedor -->
    <div id="provider-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div style="background:#fff; border-radius:8px; padding:20px; width:400px; margin:100px auto; position:relative;">
            <h4 style="text-align:center;">Agregar Proveedor</h4>
            <form id="provider-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="provider_name">Nombre</label>
                    <input type="text" id="provider_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="provider_npi">NPI</label>
                    <input type="number" id="provider_npi" name="npi" required>
                </div>
                <div class="form-group">
                    <label for="provider_role">Rol</label>
                    <input type="text" id="provider_role" name="role" required>
                </div>
                <button type="submit" class="btn btn-save btn-sm">Guardar Proveedor</button>
                <button type="button" id="close-provider-modal" class="btn btn-default">Cancelar</button>
            </form>
        </div>
    </div>

    <div class="container">
        <!-- Formulario para agregar/editar excusa -->


        <!-- Listado de excusas -->

    </div>
</div>

<script>

    let allExcuses = [];
    let currentState = 'request'; 
    let currentPage = 1;
    let perPage = 20;
    let paginationData = null; 

    flatpickr("#fecha_nacimiento_laboral", {
        dateFormat: "m-d-Y", // Establece el formato MM-DD-YYYY
    });

    flatpickr("#fecha_inicio_laboral", {
        dateFormat: "m-d-Y", // Establece el formato MM-DD-YYYY
        minDate: "today" // Por ejemplo, puedes configurar restricciones si lo necesitas
    });

    flatpickr("#fecha_fin_laboral", {
        dateFormat: "m-d-Y", // Establece el formato MM-DD-YYYY
        minDate: "today" // Por ejemplo, puedes configurar restricciones si lo necesitas
    });

    flatpickr("#fecha_atencion", {
        enableTime: true,
        dateFormat: "m-d-Y H:i", // MM-DD-YYYY HH:MM
        time_24hr: false,
        maxDate: "today" // Usa formato 12 horas si lo deseas; cámbialo a true para 24 horas.
    });

    const scrollContainer = document.querySelector('.form-excuse');

    scrollContainer.addEventListener('wheel', function(e) {
        const delta = e.deltaY;

        const atTop = scrollContainer.scrollTop === 0;
        const atBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop === scrollContainer.clientHeight;

        if ((delta < 0 && atTop) || (delta > 0 && atBottom)) {
            // En los extremos, deja que el scroll siga su camino
            return;
        }

        // Si no estás en los extremos, evita que la página se mueva
        e.preventDefault();
        scrollContainer.scrollTop += delta;
    }, {
        passive: false
    });

    function notify(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.className = '';
        notification.classList.add(type);
        notification.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    function showTabLoader() {
        document.getElementById('tab-loader').style.display = 'block';
        document.getElementById('cards-container').style.display = 'none';
        document.getElementById('pagination-controls').style.display = 'none';
    }

    function hideTabLoader() {
        document.getElementById('tab-loader').style.display = 'none';
        document.getElementById('cards-container').style.display = 'block';
        if (paginationData && paginationData.total_pages > 1) {
            document.getElementById('pagination-controls').style.display = 'block';
        }
    }

    function formatDateUS(isoDate) {
        let parts = isoDate.split('-');
        return `${parts[1]}-${parts[2]}-${parts[0]}`;
    }



    // Variable global para saber si se está editando (null = creación, ID = edición)
    let editingId = null;

    // Función para construir la card de una excusa (se mantiene el diseño solicitado)
    function buildCard(excuse) {

        let fechaNacimiento = new Date(`${excuse.birth}T00:00:00`).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
        let fechaInicio = new Date(`${excuse.date_start}T00:00:00`).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
        let fechaFin = new Date(`${excuse.date_end}T00:00:00`).toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
        const user_id = "<?= $login_user->id ?>";
        let tipoExcusa = excuse.type === 'medica_escolar' ? 'Excusa Médica Escolar' : 'Excusa Médica Laboral';

        let actionButtons = '';
        if (excuse.state === 'request') {
            <?php if ($excuse_permission == "all" || $excuse_permission == "provider" || $login_user->is_admin) { ?>
                actionButtons = `
                <button class="btn-rubymed btn-rubymed-primary btn-sm btn-view ml-1" data-id="${excuse.id}">
                <i class="fas fa-eye"></i> Ver Documento
            </button>
            <button class="btn-rubymed btn-rubymed-success btn-sm btn-approve" data-user="${user_id}" data-id="${excuse.id}">
                <i class="fas fa-check-double"></i> Aprobar y Generar PDF
            </button>
            <button class="btn-rubymed btn-rubymed-danger btn-sm btn-deny" data-user="${user_id}" data-id="${excuse.id}">
                <i class="fas fa-times"></i> Denegar
            </button>`;
            <?php  } else { ?>
                actionButtons = `<small style="font-size:12px;color:#336eff;">
                            <i class="fas fa-clock"></i> En Espera de Aprobacion
                         </small>`;

            <?php  } ?>
        } else if (excuse.state === 'approved') {
            actionButtons = `
            <button class="btn-rubymed btn-rubymed-primary btn-sm btn-view" data-id="${excuse.id}">
                <i class="fas fa-eye"></i> Ver Documento
            </button>`;
        } else if (excuse.state === 'denied') {
            actionButtons = `<small style="font-size:12px;color:#dc3545;">
                            <i class="fas fa-times"></i> Negado
                         </small>`;
        }

        const Providers = excuse.provider ? excuse.provider : "Pendiente" ;

        return `
        <div class="card_excuse" data-id="${excuse.id}">
            <div class="row">
                <div class="col-md-6">
                    <span><b>Token:</b> ${excuse.token.substring(0, 8).toUpperCase()}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Clinica:</b> ${excuse.clinic}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Paciente:</b> ${excuse.name}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Fecha Nacimiento:</b> ${fechaNacimiento}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Excusa:</b> ${tipoExcusa}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Provider:</b> ${Providers}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Incapacidad:</b> ${fechaInicio} - ${fechaFin}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Generada Por:</b> ${excuse.generate_name} </span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 my-2" >
                    <span class='my-3' ><small><b>Razon:</b> ${excuse.reason}</small></span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    ${actionButtons}
                </div>
            </div>
        </div>
    `;
    }


    // Función para cargar la lista de excusas mediante AJAX
    /*async function loadExcuses() {
        try {
            let response = await fetch('<?= site_url("excuse/listAjax") ?>');
            let data = await response.json();
            if (data.success) {
                let html = '';
                data.excuses.forEach(excuse => {
                    html += buildCard(excuse);
                });
                document.getElementById('cards-container').innerHTML = html;
                attachCardEventListeners();
            }
        } catch (error) {
            console.error('Error al cargar excusas:', error);
        }
    }*/

    async function loadExcuses(page = 1, showLoader = true) {
        try {
            if (showLoader) {
                showTabLoader();
                // Pequeño delay para que el loader sea visible
                await new Promise(resolve => setTimeout(resolve, 300));
            }
            
            currentPage = page;
            let url = `<?= site_url("excuse/listAjax") ?>?page=${page}&per_page=${perPage}&state=${currentState}`;
            console.log('Cargando excusas desde:', url);
            
            let response = await fetch(url);
            
            // Verificar si la respuesta es HTML (error)
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                notify('Error: El servidor devolvió una respuesta no válida', 'error');
                return;
            }
            
            let data = await response.json();
            if (data.success) {
                allExcuses = data.excuses;
                paginationData = data.pagination;
                await renderFilteredExcuses();
                updatePaginationControls();
            } else {
                console.error('Error del servidor:', data);
                notify('Error: ' + (data.message || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('Error al cargar excusas:', error);
            notify('Error de conexión al cargar excusas', 'error');
        } finally {
            if (showLoader) {
                hideTabLoader();
            }
        }
    }

    async function renderFilteredExcuses() {
        await updateExcuseCounts();
        let container = document.getElementById('cards-container');
        container.innerHTML = '';
        
        // Ya no necesitamos filtrar por estado aquí, se hace en el backend
        // Solo mostramos las excusas que vienen del servidor
        allExcuses.forEach(exc => {
            container.insertAdjacentHTML('beforeend', buildCard(exc));
        });
        attachCardEventListeners();
        applySearchFilter(); // reaplica el filtro de búsqueda
    }

    async function updateExcuseCounts() {
        try {
            let response = await fetch('<?= site_url("excuse/countsAjax") ?>');
            
            // Verificar si la respuesta es HTML (error)
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta de conteos no es JSON:', text);
                // Usar conteos por defecto
        document.querySelector('#excuseStatusTabs [data-state="request"]')
                    .textContent = `Pendientes (0)`;
        document.querySelector('#excuseStatusTabs [data-state="approved"]')
                    .textContent = `Aprobadas (0)`;
        document.querySelector('#excuseStatusTabs [data-state="denied"]')
                    .textContent = `Negadas (0)`;
                return;
            }
            
            let data = await response.json();
            if (data.success) {
                const counts = data.counts;
                document.querySelector('#excuseStatusTabs [data-state="request"]')
                    .textContent = `Pendientes (${counts.request})`;
                document.querySelector('#excuseStatusTabs [data-state="approved"]')
                    .textContent = `Aprobadas (${counts.approved})`;
                document.querySelector('#excuseStatusTabs [data-state="denied"]')
                    .textContent = `Negadas (${counts.denied})`;
            }
        } catch (error) {
            console.error('Error al cargar conteos:', error);
            // Usar conteos por defecto en caso de error
            document.querySelector('#excuseStatusTabs [data-state="request"]')
                .textContent = `Pendientes (0)`;
            document.querySelector('#excuseStatusTabs [data-state="approved"]')
                .textContent = `Aprobadas (0)`;
            document.querySelector('#excuseStatusTabs [data-state="denied"]')
                .textContent = `Negadas (0)`;
        }
    }

    function updatePaginationControls() {
        if (!paginationData) return;
        
        const controls = document.getElementById('pagination-controls');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const currentPageText = document.getElementById('current-page-text');
        const paginationInfo = document.getElementById('pagination-info');
        
        // Mostrar/ocultar controles según si hay múltiples páginas
        if (paginationData.total_pages > 1) {
            controls.style.display = 'block';
        } else {
            controls.style.display = 'none';
        }
        
        // Actualizar estado de botones
        if (paginationData.has_prev) {
            prevBtn.classList.remove('disabled');
            prevBtn.querySelector('a').style.pointerEvents = 'auto';
        } else {
            prevBtn.classList.add('disabled');
            prevBtn.querySelector('a').style.pointerEvents = 'none';
        }
        
        if (paginationData.has_next) {
            nextBtn.classList.remove('disabled');
            nextBtn.querySelector('a').style.pointerEvents = 'auto';
        } else {
            nextBtn.classList.add('disabled');
            nextBtn.querySelector('a').style.pointerEvents = 'none';
        }
        
        // Actualizar texto de página actual
        currentPageText.textContent = `${paginationData.current_page} de ${paginationData.total_pages}`;
        
        // Actualizar información de registros
        const start = ((paginationData.current_page - 1) * paginationData.per_page) + 1;
        const end = Math.min(paginationData.current_page * paginationData.per_page, paginationData.total);
        paginationInfo.textContent = `Mostrando ${start}-${end} de ${paginationData.total} excusas`;
    }


    document.querySelectorAll('#excuseStatusTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            
            // Solo proceder si no es el tab activo actual
            if (tab.classList.contains('active')) {
                return;
            }
            
            document.querySelectorAll('#excuseStatusTabs .nav-link')
                .forEach(a => a.classList.remove('active'));
            tab.classList.add('active');
            currentState = tab.dataset.state;
            
            // Resetear a la primera página cuando se cambie de estado
            currentPage = 1;
            
            // Mostrar loader y cargar excusas
            loadExcuses(1, true);
        });
    });

    let globalProviders = [];
    // Función para cargar proveedores desde la tabla providers y actualizar el select
    async function loadProviders() {
        try {
            let response = await fetch('<?= site_url("provider/listAjax") ?>');
            let data = await response.json();
            if (data.success) {
                globalProviders = data.providers; // Guarda la lista globalmente
                let select = document.getElementById('proveedor_laboral');
                select.innerHTML = '<option value="">Seleccione un proveedor</option>';
                data.providers.forEach(provider => {
                    // Se asume que cada provider tiene "name" (puedes ajustar según tu modelo)
                    select.innerHTML += `<option value="${provider.name}" data-provider="${provider.id}" >${provider.name}</option>`;
                });
            }
        } catch (error) {
            console.error('Error al cargar proveedores:', error);
        }
    }

    // Escucha el evento change en el select para asignar npi y role a los campos ocultos
    document.getElementById('proveedor_laboral').addEventListener('change', function() {
        let selectedName = this.value;
        // Buscar en globalProviders el proveedor con el nombre seleccionado
        let provider = globalProviders.find(prov => prov.name === selectedName);
        if (provider) {
            // Asignar el valor del NPI y rol a los campos ocultos
            document.querySelector('input[name="provider_npi"]').value = provider.npi;
            document.querySelector('input[name="privider_role"]').value = provider.role;
            document.querySelector('input[name="privider_signature"]').value = provider.url_signature;
            document.querySelector('input[name="provider_user_id"]').value = provider.user_id;
        } else {
            // Si no se encontró, limpiar los campos
            document.querySelector('input[name="provider_npi"]').value = '';
            document.querySelector('input[name="privider_role"]').value = '';
            document.querySelector('input[name="privider_signature"]').value = '';
            document.querySelector('input[name="provider_user_id"]').value = '';
        }
    });

    function convertToISO(dateStr) {
        // Se asume que dateStr está en formato MM-DD-YYYY
        let parts = dateStr.split('-'); // parts[0]=MM, parts[1]=DD, parts[2]=YYYY
        // Asegurarse de que mes y día tengan dos dígitos:
        let month = parts[0].padStart(2, '0');
        let day = parts[1].padStart(2, '0');
        let year = parts[2];
        return `${year}-${month}-${day}`;
    }

    // Función para validar fechas antes de enviar el formulario
    /*function validateDates() {
        const inicio = document.getElementById('fecha_inicio_laboral').value;
        const fin = document.getElementById('fecha_fin_laboral').value;
        const hoy = new Date().toISOString().split('T')[0];
        if (inicio < hoy) {
            notify('La fecha de inicio debe ser hoy o mayor', 'error');
            return false;
        }
        if (fin <= inicio) {
            notify('La fecha final debe ser mayor a la fecha de inicio', 'error');
            return false;
        }
        return true;
    }*/

    function validateDates() {
        const inicio = document.getElementById('fecha_inicio_laboral').value;
        const fin = document.getElementById('fecha_fin_laboral').value;

        // Convertir las fechas recibidas al formato ISO
        const inicioISO = convertToISO(inicio);
        const finISO = convertToISO(fin);

        // Obtener la fecha actual en formato ISO
        const hoy = new Date().toISOString().split('T')[0];

        if (inicioISO < hoy) {
            notify('La fecha de inicio debe ser hoy o mayor', 'error');
            return false;
        }
        if (finISO <= inicioISO) {
            notify('La fecha final debe ser mayor a la fecha de inicio', 'error');
            return false;
        }
        return true;
    }

    // Función para enviar el formulario de excusa (creación o edición) mediante AJAX
    function convertToISOn(dateStr) {
        // Se asume que dateStr está en formato MM-DD-YYYY (por ejemplo, "03-25-2025")
        let parts = dateStr.split('-'); // parts[0] = MM, parts[1] = DD, parts[2] = YYYY
        return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
    }

    function convertToISOn2(dateTimeStr) {
        // Separa la parte de fecha y la parte de hora (si existe)
        let parts = dateTimeStr.split(' ');
        let datePart = parts[0];
        let timePart = parts[1] || ''; // Si no hay hora, queda vacío
        let dateParts = datePart.split('-'); // [MM, DD, YYYY]
        let isoDate = `${dateParts[2]}-${dateParts[0].padStart(2, '0')}-${dateParts[1].padStart(2, '0')}`;
        return timePart ? `${isoDate} ${timePart}` : isoDate;
    }

    async function submitForm(event) {
        event.preventDefault();
        document.getElementById('loader-laboral').style.display = 'block';

        const form = document.getElementById('form-laboral');
        const formData = new FormData(form);

        // Convertir las fechas antes de enviar
        const fechaAtencion = formData.get('fecha_atencion');
        const fechaNacimiento = formData.get('fecha_nacimiento');
        const fechaInicio = formData.get('fecha_inicio');
        const fechaFin = formData.get('fecha_fin');

        // Actualiza los valores en el FormData
        formData.set('fecha_nacimiento', convertToISOn(fechaNacimiento));
        formData.set('fecha_inicio', convertToISOn(fechaInicio));
        formData.set('fecha_fin', convertToISOn(fechaFin));
        formData.set('fecha_atencion', convertToISOn2(fechaAtencion));

        // Determinar URL: si editingId es null se crea, de lo contrario se actualiza
        let url = '<?= site_url("excuse/storeAjax") ?>';
        if (editingId !== null) {
            url = '<?= site_url("excuse/updateAjax") ?>/' + editingId;
        }

        try {
            let response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            let result = await response.json();
            if (result.success) {
                notify(result.message, 'success');
                form.reset();
                editingId = null;
                document.getElementById('state').value = 'request';
                await loadExcuses(1, false);
            } else {
                notify('Error: ' + JSON.stringify(result.errors), 'error');
            }
        } catch (error) {
            console.error('Error al enviar el formulario:', error);
            notify('Error al enviar el formulario', 'error');
        } finally {
            document.getElementById('loader-laboral').style.display = 'none';
        }
    }


    // Función para adjuntar eventos a los botones en las cards
    function attachCardEventListeners() {
        // Botones de editar
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                try {
                    let response = await fetch('<?= site_url("excuse/showAjax") ?>/' + id);
                    let result = await response.json();
                    if (result.success) {
                        const excuse = result.excuse;
                        document.getElementById('nombre_completo_laboral').value = excuse.name;
                        document.getElementById('fecha_nacimiento_laboral').value = excuse.birth;
                        document.getElementById('excuse_medical').value = excuse.type;
                        document.getElementById('motivo_laboral').value = excuse.reason;
                        document.getElementById('proveedor_laboral').value = excuse.provider;
                        document.getElementById('fecha_inicio_laboral').value = excuse.date_start;
                        document.getElementById('fecha_fin_laboral').value = excuse.date_end;
                        document.getElementById('state').value = excuse.state;
                        editingId = id;
                        document.getElementById('excuse-form-container').scrollIntoView();
                    }
                } catch (error) {
                    console.error('Error al cargar datos para edición:', error);
                }
            });
        });

        // Botones de aprobar
        document.querySelectorAll('.btn-approve').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                const dataUser = this.getAttribute('data-user');
                if (confirm('¿Aprobar esta excusa?')) {
                    try {
                        let response = await fetch('<?= site_url("excuse/approveAjax") ?>/' + id + '/'+dataUser, {
                            method: 'GET'
                        });
                        let result = await response.json();
                        if (result.success) {
                            await loadExcuses(1, false);
                        } else {
                            alert('Error al aprobar');
                        }
                    } catch (error) {
                        console.error('Error al aprobar:', error);
                    }
                }
            });
        });

        // Botones de denegar
        document.querySelectorAll('.btn-deny').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                if (confirm('¿Denegar esta excusa?')) {
                    try {
                        let response = await fetch('<?= site_url("excuse/denyAjax") ?>/' + id, {
                            method: 'GET'
                        });
                        let result = await response.json();
                        if (result.success) {
                            await loadExcuses(1, false);
                        } else {
                            alert('Error al denegar');
                        }
                    } catch (error) {
                        console.error('Error al denegar:', error);
                    }
                }
            });
        });
    }

    // Buscador: filtra las cards de excusas en la vista
    function applySearchFilter() {
        let query = document.getElementById('search-excuse').value.toLowerCase();
        document.querySelectorAll('.card_excuse').forEach(card => {
            let text = card.textContent.toLowerCase();
            if (text.indexOf(query) !== -1) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    document.getElementById('search-excuse').addEventListener('input', applySearchFilter);

    // Asignar el evento de envío del formulario de excusa
    document.getElementById('form-laboral').addEventListener('submit', submitForm);

    // Cargar la lista de excusas y la lista de proveedores al iniciar
    loadExcuses(1, false).then(() => {
        loadProviders();
        loadClinics();
    });

    // Eventos para el modal de proveedor
    /*document.getElementById('open-provider-modal').addEventListener('click', function() {
        document.getElementById('provider-modal').style.display = 'block';
    });
    document.getElementById('close-provider-modal').addEventListener('click', function() {
        document.getElementById('provider-modal').style.display = 'none';
    });*/
    document.getElementById('provider-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);
        try {
            let response = await fetch('<?= site_url("provider/storeAjax") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            let result = await response.json();
            if (result.success) {
                alert(result.message);

                form.reset();
                document.getElementById('provider-modal').style.display = 'none';
                // Recargar la lista de proveedores para actualizar el select
                loadProviders();
            } else {
                alert('Error: ' + JSON.stringify(result.errors));
            }
        } catch (error) {
            console.error('Error al guardar proveedor:', error);
        }
    });

    // Función para aprobar y generar PDF
    async function approveAndGeneratePdf(id,userID) {
        if (confirm('¿Aprobar esta excusa y generar PDF?')) {
            try {
                let response = await fetch('<?= site_url("excuse/approvePdfAjax") ?>/' + id + '/' + userID, {
                    method: 'GET'
                });
                let result = await response.json();
                if (result.success) {
                    notify(result.message, 'success');
                    // Abre el PDF usando la URL que retorna el endpoint (generatePdf)
                    window.open(result.pdfUrl, '_blank');
                    await loadExcuses(1, false);
                } else {
                    notify('Error al aprobar', 'error');
                }
            } catch (error) {
                console.error('Error al aprobar:', error);
                notify('Error al aprobar', 'error');
            }
        }
    }



    // Función para ver el PDF de una excusa aprobada
    function viewPdf(id) {
        window.open('<?= site_url("excuse/generatePdf") ?>/' + id + '?mode=view', '_blank');
    }

    // Asignar eventos en attachCardEventListeners:
    function attachCardEventListeners() {
        // Botones de aprobar
        document.querySelectorAll('.btn-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const userID = this.getAttribute('data-user');
                approveAndGeneratePdf(id,userID);
            });
        });

        // Botones de ver documento
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                viewPdf(id);
            });
        });

        // Botones de denegar (ya existentes)
        document.querySelectorAll('.btn-deny').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                const userID = this.getAttribute('data-user');
                if (confirm('¿Denegar esta excusa?')) {
                    try {
                        let response = await fetch('<?= site_url("excuse/denyAjax") ?>/' + id + "/" + userID, {
                            method: 'GET'
                        });
                        let result = await response.json();
                        if (result.success) {
                            notify(result.message, 'success');
                            await loadExcuses(1, false);
                        } else {
                            notify('Error al denegar', 'error');
                        }
                    } catch (error) {
                        console.error('Error al denegar:', error);
                        notify('Error al denegar', 'error');
                    }
                }
            });
        });
    }

    // Función para cargar las clínicas a las que el usuario tiene acceso
    async function loadClinics() {
        console.log("Cargando clinicas...");
        try {
            let response = await fetch('<?= site_url("excuse/clinicsAjax") ?>');
            let data = await response.json();
            if (data.success) {
                let select = document.getElementById('clinic_select');
                select.innerHTML = '<option value="">Seleccione una clínica</option>';
                data.clinics.forEach(clinic => {
                    select.innerHTML += `<option value="${clinic.name}" data-id="${clinic.id}" data-provider="${clinic.provider_id}" data-address="${clinic.address}" data-phone="${clinic.phone}">${clinic.name}</option>`;
                });
            }
        } catch (error) {
            console.error('Error al cargar clínicas:', error);
        }
    }

    // Al cambiar la selección en el select de clínicas, llenar los inputs ocultos
    document.getElementById('clinic_select').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        let clinic_id = selectedOption.getAttribute('data-id') || '';
        let address = selectedOption.getAttribute('data-address') || '';
        let phone = selectedOption.getAttribute('data-phone') || '';
        let providerId = selectedOption.getAttribute('data-provider') || '';
        console.log(providerId);

        document.getElementById('clinic_id').value = clinic_id;
        document.getElementById('clinic_address').value = address;
        document.getElementById('clinic_phone').value = phone;

        // Seleccionar automáticamente el proveedor en el select de proveedores
        let providerSelect = document.getElementById('proveedor_laboral');
        if (providerSelect) {
            // Recorre las opciones y selecciona la que tenga data-provider igual a providerId
            for (let option of providerSelect.options) {
                if (option.getAttribute('data-provider') === providerId) {
                    option.selected = true;
                    // Disparar el evento change si es necesario
                    providerSelect.dispatchEvent(new Event('change'));
                    break;
                }
            }
        }
    });
</script>
</body>

</html>