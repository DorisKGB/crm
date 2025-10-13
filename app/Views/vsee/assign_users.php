<?php echo  view('calls_system/calls_bubble.php'); ?>
<div id="page-content" class="page-wrapper clearfix todo-page">
    <?php
    load_css([
        'assets/css/button.css',
    ]);
    ?>
    <style>
        .estado-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .switch {
            font-size: 12px;
            position: relative;
            display: inline-block;
            width: 3.5em;
            height: 2em;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #d4acfb;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 1.4em;
            width: 1.4em;
            left: 0.3em;
            bottom: 0.3em;
            background-color: white;
            border-radius: 50px;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .switch input:checked+.slider {
            background: #b84fce;
        }

        .switch input:focus+.slider {
            box-shadow: 0 0 1px #b84fce;
        }

        .switch input:checked+.slider:before {
            transform: translateX(1.6em);
            width: 2em;
            height: 2em;
            bottom: 0;
        }

        .estado-text {
            color: #6c757d;
        }

        .switch input:checked~.estado-text {
            color: #b84fce;
        }

        .switch-vsee input:checked+.slider {
            background: #28a745;
            /* verde */
        }

        .switch-vsee input:not(:checked)+.slider {
            background: #c3e6cb;
            /* verde claro pastel */
        }

        .switch-vsee input:focus+.slider {
            box-shadow: 0 0 1px #28a745;
        }
        .class-link{
            cursor: pointer;
        }
        .class-link:hover{
            color:#000 !important;
        }
    </style>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h4><b><i class="fas fa-user-tag"></i> Gestión de Usuarios VSee</b></h4>
                <div class="d-flex">
                    <button class="btn-button btn-button-outline-danger" onclick="openLinkForm()">
                        <i class="fa fa-plus"></i> Nuevo Enlace
                    </button>
                    <a href="<?= site_url('vsee/dashboard') ?>" class="btn-button btn-button-outline-success">Vsee</a>
                </div>
            </div>
            <p>En esta sección determinas el rol del usuario que determina la forma de interactuar dentro de vsee.</p>

            <!-- Tabs de filtros -->
            <ul class="nav nav-tabs mb-3" id="roleTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-role="clinic" href="#">Clínica</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-role="user" href="#">Usuario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-role="provider" href="#">Provider</a>
                </li>
            </ul>

            <table id="vsee-links-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-hospital"></i> Clínica</th>
                        <th><i class="fas fa-user-tag"></i> Rol</th>
                        <th><i class="fas fa-toggle-on"></i> Estado</th>
                        <th><i class="fas fa-toggle-on"></i> Vsee</th>
                        <th><i class="fas fa-cogs"></i> Opciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div id="modalContainer"></div>
</div>
<script>
    let table;

    $(document).ready(function() {
        table = $('#vsee-links-table').DataTable({
            ajax: {
                url: "<?= get_uri('vseeusers/list_data') ?>",
                dataSrc: "data"
            },
            language: {
                url: '<?= base_url('assets/js/datatable/es-ES.json') ?>'
            }
        });

        // Al iniciar, aplicar filtro inicial según el tab activo
        let defaultRole = $('#roleTabs a.active').data('role');
        table.on('init', function() {
            table.column(3).search(defaultRole, true, false).draw();
        });

        // Filtro por rol dinámico
        $('#roleTabs a').click(function(e) {
            e.preventDefault();
            $('#roleTabs a').removeClass('active');
            $(this).addClass('active');

            const selectedRole = $(this).data('role');
            table.column(3).search(selectedRole, true, false).draw();
        });
    });

    function openLinkForm() {
    $.get("<?= get_uri('vseeusers/form_modal') ?>", function (html) {
        $('#modalContainer').html(html);

        const modalEl = document.getElementById('modalVseeLink');
        if (!modalEl) {
            console.error('❌ No se encontró el modal con ID modalVseeLink en el HTML cargado.');
            console.log('Contenido recibido:', html);
            return;
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}


    function openDeleteModal(id) {
        showLoading();
        $.get("<?= get_uri('vseeusers/delete_modal') ?>", {
            id
        }, function(html) {
            hideLoading();
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalEliminarVseeLink'));
            modal.show();
        }).fail(() => {
            hideLoading();
            showError("No se pudo cargar el modal de eliminación.");
        });
    }

    function toggleState(id) {
        showLoading();
        $.post("<?= get_uri('vseeusers/toggle_state') ?>", {
            id
        }, function() {
            table.ajax.reload();
            showSuccess("Estado actualizado.");
        }, 'json');
    }

    function syncWithVsee(id) {
         showLoading();

        $.post("<?= get_uri('vseeusers/sync') ?>", {
            id
        }, function(response) {

            if (response.success) {
                table.ajax.reload();
                if (response.copied) {
                    showSuccess("Datos copiados de otra clínica.");
                } else {
                    showSuccess("Sincronización con VSee completada.");
                }
            } else {
                showError(response.message || "Error al sincronizar.");
            }
        }).fail(function() {
            showError("Fallo al conectar con el servidor.");
        });
    }


</script>