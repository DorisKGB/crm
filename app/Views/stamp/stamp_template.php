<?php
$permissions2 = $login_user->permissions;
$stamp_permission = get_array_value($permissions2, "stamp_permission_v1");
?>
<div id="page-content" class="page-wrapper clearfix grid-button">
    <style>
        .items-select:hover {
            transition: 8ms;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .items-select {
            border-radius: 15px;
        }
    </style>
    <div class="card" style="min-height:90vh;">
        <div class="card-title d-flex align-items-center">
            <a href="javascript:history.back()" style="margin-left:20px !important;" class="text-decoration-none fs-3 me-3 pl-5" aria-label="Volver">
                <i class="fas fa-arrow-left ml-5"></i>
            </a>
            <div class="text-center flex-grow-1">
                <h3>
                    <span class="badge badge-primary">Solicitud Timbre</span>
                    ¿Cómo deseas timbrar?
                </h3>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <div class="container">
                 <?php if (isset($_GET['request']) && $_GET['request'] === 'new.solicitud'): ?>
                 <div class="alert alert-success alert-dismissible fade show" role="alert">
                     <strong>¡Listo!</strong> Has agregado una NUEVA PLANTILLA satisfactoriamente.
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                 </div>
             <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">

                        <div class="d-flex justify-content-center align-items-center">
                            <div class="items-select pointer p-4 mode-card" style="cursor:pointer;" data-target="select_template">
                                <div class="text-center">
                                    <img width="50%" src="<?= base_url('assets/images/seleccionar_plantilla.png') ?>" alt="" style="border-radius:15px;">
                                </div>
                                <div class="text-center">
                                    <h3><b>Seleccionar Plantilla</b></h3>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="items-select pointer p-4 mode-card" style="cursor:pointer;" data-target="create_template">
                                <div class="text-center">
                                    <img width="50%" src="<?= base_url('assets/images/crear_plantilla.png') ?>" alt="" style="border-radius:15px;">
                                </div>
                                <div class="text-center">
                                    <h3><b>Crear Plantilla</b></h3>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.mode-card').forEach(card => {
            card.addEventListener('click', () => {
                const url = card.getAttribute('data-target');
                if (url) window.location.href = url;
            });
        });
    </script>
</div>