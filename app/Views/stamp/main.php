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
        <div class="card-title text-center">
            <h3><span class="badge badge-primary">Elije una Opción</span> ¿Que deseas realizar?</h3>
            <hr>
        </div>
        <div class="card-body">
            <div class="container">
                <div class="row">
                    <?php if ($stamp_permission === 'all' || $login_user->is_admin): ?>
                        <!-- Tres opciones: col-md-4 -->
                        <div class="col-md-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="items-select pointer p-4 mode-card" data-target="type_request">
                                    <div class="text-center">
                                        <img width="50%" src="<?= base_url('assets/images/timbre.png') ?>" alt="Solicitud de Timbre" style="border-radius:15px;">
                                    </div>
                                    <div class="text-center">
                                        <h3><b>Solicitud<br>de Timbre</b></h3>
                                    </div>
                                    <div class="well well-sm text-center">Escanea el documento y haz el timbre.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="items-select pointer p-4 mode-card" data-target="stamp_v1">
                                    <div class="text-center">
                                        <img width="50%" src="<?= base_url('assets/images/historial.png') ?>" alt="Ver Historial" style="border-radius:15px;">
                                    </div>
                                    <div class="text-center">
                                        <h3><b>Ver<br>Historial</b></h3>
                                    </div>
                                    <div class="well well-sm text-center">Aquí puedes ver el historial de timbres.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="items-select pointer p-4 mode-card" data-target="statistics">
                                    <div class="text-center">
                                        <img width="50%" src="<?= base_url('assets/images/statistic.png') ?>" alt="Estadísticas" style="border-radius:15px;">
                                    </div>
                                    <div class="text-center">
                                        <h3><b>Estadísticas<br>de Timbres</b></h3>
                                    </div>
                                    <div class="well well-sm text-center">Consulta gráficos y datos de uso.</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Solo dos opciones: col-md-6 -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="items-select pointer p-4 mode-card" data-target="type_request">
                                    <div class="text-center">
                                        <img width="50%" src="<?= base_url('assets/images/timbre.png') ?>" alt="Solicitud de Timbre" style="border-radius:15px;">
                                    </div>
                                    <div class="text-center">
                                        <h3><b>Solicitud<br>de Timbre</b></h3>
                                    </div>
                                    <div class="well well-sm text-center">Escanea el documento y haz el timbre.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="items-select pointer p-4 mode-card" data-target="stamp_v1">
                                    <div class="text-center">
                                        <img width="50%" src="<?= base_url('assets/images/historial.png') ?>" alt="Ver Historial" style="border-radius:15px;">
                                    </div>
                                    <div class="text-center">
                                        <h3><b>Ver<br>Historial</b></h3>
                                    </div>
                                    <div class="well well-sm text-center">Aquí puedes ver el historial de timbres.</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div> <!-- /.row -->
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