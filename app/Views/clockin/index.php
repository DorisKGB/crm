<?php
// Redirección automática si no hay clínica seleccionada
if (!isset($_GET['clinic']) || empty($_GET['clinic'])) {
    if (!empty($clinics)) {
        $firstClinicId = $clinics[0]->id;
        $baseUrl = current_url(); // o site_url(uri_string()) si usas CodeIgniter
        $queryParams = $_GET;
        $queryParams['clinic'] = $firstClinicId;
        $newUrl = $baseUrl . '?' . http_build_query($queryParams);
        header("Location: $newUrl");
        exit;
    }
}
$activeOption = $_GET['option'] ?? 'attendance';

?>

<div id="page-content" class="page-wrapper clearfix grid-button">
    <style>
        .list-group {
            border-radius: none;
        }

        .list-group-item {
            border: 1px solid #e9e9e9;
            cursor: pointer;
        }

        .panel-section {
            display: none;
        }

        .panel-section.active {
            display: block;
        }

        .targetCard {
            box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
            -webkit-box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
            -moz-box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
            border-radius: 15px;
        }

        .clinic-badge {
            display: inline-block;
            background-color: #e5d4f3;
            /* morado pastel */
            color: #5a287d;
            padding: 0.4em 0.8em;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .clinic-badge i {
            margin-right: 6px;
            color: #5a287d;
            font-weight: 600;
        }
    </style>
    <div class="card">
        <div class="row">
            <div class="col-md-12">
                <div class="text-left">
                    <div class="page-title clearfix notes-page-title">
                        <h1><i class="fas fa-fingerprint"></i> <span id="panel-title"><?= app_lang('clockin'); ?></span>
                         <button id="btn-sync" class="btn-rubymed btn-rubymed-primary">
                                <i class="fas fa-sync-alt"></i> <?= app_lang('sync_now') ?>
                            </button>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <div class="p-4" style="background-color: rgb(255, 255, 255) !important;">
                    <ul class="list-group">
                        <li class="list-group-item" style="background-color:rgb(244, 244, 244) !important;"><b><?= app_lang('text_navigation'); ?></b></li>
                        <li class="list-group-item">
                            <p><?= app_lang('text_select_clinic'); ?> :</p>
                            <select class="form-control" id="clinicSelector">
                                <?php foreach ($clinics as $clinic) { ?>
                                    <option value="<?= esc($clinic->id); ?>"
                                        <?= ($_GET['clinic'] ?? '') == $clinic->id ? 'selected' : '' ?>>
                                        <?= esc($clinic->name); ?></option>
                                <?php } ?>
                            </select>
                        </li>
                    </ul>

                    <ul class="list-group mt-5 ">
                        <li class="list-group-item" style="background-color:rgb(244, 244, 244) !important;"><b><?= app_lang('main_menu') ?></b></li>

                        <a href="?clinic=<?= $_GET['clinic'] ?>&option=activity"
                            class="d-none list-group-item <?= $activeOption === 'activity' ? 'active' : '' ?>">
                            <i class="fas fa-cash-register"></i> <?= app_lang('text_nav_activity'); ?>
                        </a>
                        <?php if (can_view_clinic_clockin()): ?>
                        <a href="?clinic=<?= $_GET['clinic'] ?>&option=attendance"
                            class="list-group-item <?= $activeOption === 'attendance' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> <?= app_lang('text_nav_attendance'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (can_view_clinic_clockin()): ?>
                        <a href="?clinic=<?= $_GET['clinic'] ?>&option=staff"
                            class="list-group-item <?= $activeOption === 'staff' ? 'active' : '' ?>">
                            <i class="fas fa-user"></i> <?= app_lang('text_nav_people'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (can_admin_clockin()): ?>
                        <a href="?clinic=<?= $_GET['clinic'] ?>&option=heatmap"
                            class="list-group-item <?= $activeOption === 'heatmap' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt"></i> <?= app_lang('heatmap') ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (can_admin_clockin()): ?>
                        <a href="?clinic=<?= $_GET['clinic'] ?>&option=nomina"
                            class="list-group-item <?= $activeOption === 'nomina' ? 'active' : '' ?>">
                            <i class="fas fa-search-dollar"></i> <?= app_lang('text_nav_momina'); ?>
                        </a>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>
            <div class="col-md-9">
                <div class="p-3" style="background-color: rgb(255, 255, 255) !important;">
                    <div id="panel-actividad d-none" class="panel-section <?= $activeOption === 'activity' ? 'active' : '' ?>">
                        <!--<h3>Panel: Registro de Actividad</h3>
                        <p>Contenido para el registro de actividad...</p>-->
                    </div>
                    <?php if (can_view_clinic_clockin()): ?>
                    <?php include('panel_asistencia/panel_asistencia.php'); ?>
                    <?php endif; ?>
                    
                    <?php if (can_view_clinic_clockin()): ?>
                    <?php include('panel_personal/panel_personal.php'); ?>
                    <?php endif; ?>
                    
                    <?php if (can_admin_clockin()): ?>
                    <?php include('panel_heatmap/heatmap.php'); ?>
                    <?php endif; ?>
                    
                    <?php if (can_admin_clockin()): ?>
                    <?php include('panel_nomina/nomina.php'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include('panel_asistencia/script.php') ?>
    <?php include('panel_heatmap/styles.php') ?>
    <?php include('panel_heatmap/script.php') ?>
    <?php include('panel_nomina/script.php') ?>

    <script>
        // Redirección por clínica
        const clinicSelector = document.getElementById('clinicSelector');
        if (clinicSelector) {
            clinicSelector.addEventListener('change', function() {
            const clinicId = this.value;
            const currentUrl = new URL(window.location.href);
            const baseUrl = currentUrl.origin + currentUrl.pathname;

            const newParams = new URLSearchParams();
            newParams.set('clinic', clinicId);

            const option = currentUrl.searchParams.get('option');
            if (option) {
                newParams.set('option', option);
            }

            const newUrl = `${baseUrl}?${newParams.toString()}`;
            window.location.href = newUrl;
            });
        }

        // Panel de navegación dinámica
        document.querySelectorAll('.list-group-item[data-panel]').forEach(item => {
            item.addEventListener('click', () => {
                const panel = item.getAttribute('data-panel');
                document.querySelectorAll('.panel-section').forEach(p => p.classList.remove('active'));
                document.getElementById(`panel-${panel}`).classList.add('active');
                document.getElementById('panel-title').textContent = item.innerText.trim();
            });
        });

        // Función para configurar event listeners de ver-registro
        function setupVerRegistroListeners() {
            console.log('🔧 Configurando event listeners de ver-registro...');
            const verRegistroBtns = document.querySelectorAll(".ver-registro");
            console.log('🔍 Botones encontrados:', verRegistroBtns.length);
            
            verRegistroBtns.forEach((btn, index) => {
                console.log(`🔍 Botón ${index + 1}:`, btn, 'data-user:', btn.dataset.user);
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    console.log('🖱️ Clic en ver-registro, userId:', this.dataset.user);
                    const userId = this.dataset.user;
                    const url = new URL(window.location.href);
                    url.searchParams.set('user_id', userId);
                    url.searchParams.set('option', 'staff'); // fuerza mantener en staff
                    console.log('🔗 URL de redirección:', url.toString());
                    window.location.href = url.toString();
                });
            });
        }

        // Configurar event listeners cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupVerRegistroListeners);
        } else {
            setupVerRegistroListeners();
        }

        document.querySelectorAll(".ver-nomina").forEach(btn => {
            btn.addEventListener("click", function() {
                const userId = this.dataset.user;
                const url = new URL(window.location.href);
                url.searchParams.set('user_id', userId);
                url.searchParams.set('option', 'nomina'); // fuerza mantener en staff
                window.location.href = url.toString();
            });
        });

        // Función para obtener parámetros GET
        function getUrlParam(param) {
            const url = new URL(window.location.href);
            return url.searchParams.get(param);
        }

        const dateParam = getUrlParam('date');
        const defaultDate = dateParam ?
            new Date(dateParam + "T00:00:00") // fuerza hora local
            :
            new Date();

        flatpickr("#fechaVisible", {
            dateFormat: "m/d/Y", // MM/DD/YYYY
            defaultDate: defaultDate,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    const isoDate = `${yyyy}-${mm}-${dd}`;

                    document.getElementById("fechaReal").value = isoDate;
                    showLoading();
                    // Redirige con nueva fecha en la URL
                    const url = new URL(window.location.href);
                    url.searchParams.set('date', isoDate);
                    window.location.href = url.toString();
                }
            }
        });

        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const state = link.getAttribute('data-state');
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');

                // Mostrar u ocultar secciones según la pestaña seleccionada
                const mostrarAsistencia = (state === 'request');
                const mostrarGrafico = (state === 'chart');
                const mostrarResumenDiario = (state === 'daily');

                document.getElementById('cards-container').style.display = mostrarAsistencia ? 'block' : 'none';
                document.getElementById('grafico-contenedor').style.display = mostrarGrafico ? 'block' : 'none';
                document.getElementById('resumen-diario-contenedor').style.display = mostrarResumenDiario ? 'block' : 'none';

                // Mostrar u ocultar la bolita de resumen del usuario
                const resumenUsuario = document.getElementById('resumenUsuario');
                if (resumenUsuario) {
                    resumenUsuario.style.display = mostrarAsistencia ? 'flex' : 'none';
                }
            });
        });

        const btnSync = document.getElementById('btn-sync');
        if (btnSync) {
            btnSync.addEventListener('click', function() {
                const params = new URLSearchParams(window.location.search);
                const clinic = params.get('clinic');

                if (!clinic) {
                    alert('<?= app_lang('must_select_clinic') ?>');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= app_lang('synchronizing') ?>';
                showLoading('<?= app_lang('sync_last_15_days') ?>......');
                fetch(`<?= site_url('clockin/ajaxSyncLastDays') ?>?clinic=${clinic}&days=15`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message);
                            window.location.reload();
                        } else {
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-calendar-sync"></i> <?= app_lang('sync_last_15_days') ?>';
                        }
                    })
                    .catch(() => {
                        showError('<?= app_lang('sync_error') ?>');
                        alert('<?= app_lang('sync_error') ?>');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-calendar-sync"></i> <?= app_lang('sync_last_15_days') ?>';
                    });
            });
        }

        // Script del panel de personal
        <?php include('panel_personal/script.php') ?>

        // Re-configurar event listeners después de cambios dinámicos en el DOM
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Si se agregaron nodos, reconfigurar event listeners
                    const newVerRegistroBtns = document.querySelectorAll(".ver-registro:not([data-listener-added])");
                    if (newVerRegistroBtns.length > 0) {
                        console.log('🔄 Reconfigurando event listeners para nuevos botones...');
                        newVerRegistroBtns.forEach(btn => {
                            btn.setAttribute('data-listener-added', 'true');
                            btn.addEventListener("click", function(e) {
                                e.preventDefault();
                                console.log('🖱️ Clic en ver-registro (nuevo), userId:', this.dataset.user);
                                const userId = this.dataset.user;
                                const url = new URL(window.location.href);
                                url.searchParams.set('user_id', userId);
                                url.searchParams.set('option', 'staff');
                                console.log('🔗 URL de redirección:', url.toString());
                                window.location.href = url.toString();
                            });
                        });
                    }
                }
            });
        });

        // Observar cambios en el DOM
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
</div>