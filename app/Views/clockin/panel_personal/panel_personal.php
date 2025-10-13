<style>
.delete-record-btn {
    transition: all 0.3s ease;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.delete-record-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.delete-record-btn:active {
    transform: scale(0.95);
}

#deleteRecordModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#deleteRecordModal .modal-title {
    color: #dc3545;
    font-weight: 600;
}

#deleteRecordModal .alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #1565c0;
}

.locked-icon {
    color: #6c757d;
    font-size: 16px;
    opacity: 0.6;
}
</style>

<div id="panel-personal" class="panel-section <?= $activeOption === 'staff' ? 'active' : '' ?>">

    <h3 class="d-flex justify-content-between align-items-center">
        <span><b><i class="fas fa-user"></i> <?= app_lang('text_nav_people_attendance') ?></b></span>
        <?php if (isset($data_clinic)) : ?>
            <span class="clinic-badge" style="font-size: 16px;"><i
                    class="fas fa-check-circle"></i><?= esc($data_clinic->name) ?></span>
        <?php endif; ?>
    </h3>
    <p> <?= app_lang('text_nav_attendance_details') ?></p>
    
    <?php if (isset($data_clinic)) : ?>
        <div class="alert alert-info">
            <h6><i class="fas fa-clock"></i> <?= app_lang('clinic_schedules') ?></h6>
            <?php 
            $clinicHours = get_clinic_hours_summary($data_clinic->id);
            if (!empty($clinicHours)) : 
            ?>
                <div class="row">
                    <?php foreach ($clinicHours as $hour) : ?>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <small><strong><?= $hour['day'] ?>:</strong> <?= $hour['formatted'] ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <small class="text-muted"><?= app_lang('no_schedules_configured') ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($_GET['user_id'])): ?>
        <div class="row mt-4">
            <?php foreach ($users as $user): ?>
                <div class="col-md-3">
                    <div class="card targetCard">
                        <div class="card-body text-center">
                            <?php if ($user->image != ""): ?>
                                <img src="<?php echo get_avatar($user->image); ?>" class="rounded-circle"
                                    width="100" alt="<?= app_lang('photo_of') ?> <?= esc($user->first_name) ?>"
                                    style="object-fit: cover; border:10px solid #f3f3f3; cursor:pointer;">
                            <?php else: ?>
                                <img src="<?php echo base_url("public/uploads/clinics/default-clinic.png"); ?>"
                                    class="rounded-circle" width="100" alt="<?= app_lang('default_photo') ?>"
                                    style="object-fit: cover; border:10px solid #f3f3f3; cursor:pointer;">
                            <?php endif; ?>
                            <p class="text-center"><?= esc($user->first_name); ?>
                                <?= esc($user->last_name); ?></p>
                            <p><b><small><?= esc($user->job_title) ?></small></b></p>
                            <p>
                                <button class="btn-rubymed btn-rubymed-primary-in ver-registro"
                                    data-user="<?= $user->id ?>">
                                    <i class="fas fa-clock"></i> <?= app_lang('text_view_click') ?>
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>

        <?php if (isset($data_user) && isset($request)): ?>
            <div class="alert  mt-4">
                <div class="row">
                    <div class="col-md-5 d-flex ">
                        <div>
                            <img src="<?php echo get_avatar($data_user->image); ?>"
                                class="rounded-circle" width="100"
                                alt="Foto de <?= esc($data_user->first_name) ?>"
                                style="object-fit: cover; border:5px solid #f3f3f3; cursor:pointer;">
                        </div>

                        <div class="ms-4">

                            <h4 class="ml-3"><b><?= esc($data_user->first_name) ?>
                                    <?= esc($data_user->last_name) ?></b>
                            </h4>
                            <p><?= esc($data_user->job_title) ?></p>
                            <p><?= esc($data_user->email) ?></p>

                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between align-items-center">

                            <?php
                            // Calcular total de horas trabajadas
                            $total_segundos = 0;
                            $accion = 'Entrada';
                            $last_entrada = null;

                            foreach ($request as $registro) {
                                $datetime = DateTime::createFromFormat('H:i:s', $registro->time);
                                if ($accion === 'Entrada') {
                                    $last_entrada = $datetime;
                                    $accion = 'Salida';
                                } else {
                                    if ($last_entrada) {
                                        $diff = $datetime->getTimestamp() - $last_entrada->getTimestamp();
                                        $total_segundos += $diff;
                                    }
                                    $accion = 'Entrada';
                                }
                            }
                            $horas = floor($total_segundos / 3600);
                            $minutos = floor(($total_segundos % 3600) / 60);
                            
                            // Usar las horas esperadas calculadas en el controlador
                            $horas_esperadas_dia = $horas_esperadas_dia ?? 8; // Valor por defecto si no está definido
                            ?>

                            <div id="resumenUsuario" class="ms-auto me-3" style="
                                                    background-color: #d4e3f8;
                                                    color: #003366;
                                                    width: 110px;
                                                    height: 110px;
                                                    border-radius: 50%;
                                                    display: flex;
                                                    flex-direction: column;
                                                    justify-content: center;
                                                    align-items: center;
                                                    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                                                ">
                                <i class="fas fa-clock" style="font-size: 20px;"></i>
                                <div style="font-size: 24px;"><b><?= $horas ?>h <?= $minutos ?>m</b></div>
                                <div style="font-size: 12px;"><?= app_lang('text_worked') ?></div>
                                <div style="font-size: 10px; color: #666;">Meta: <?= $horas_esperadas_dia ?>h</div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="excuseStatusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-state="request"><b><i class="fas fa-check-double"></i> <?= app_lang('text_attendance'); ?></b></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-state="daily"><b><i class="fas fa-calendar-day"></i> <?= app_lang('text_summary_daily'); ?></b></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-state="chart"><b><i class="fas fa-chart-line"></i> <?= app_lang('text_graphic'); ?></b></a>
                        </li>
                    </ul>

                    <!-- Contenedor del gráfico -->
                    <div id="grafico-contenedor" class="p-3" style="background:#f9f9f9; display:none;">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label><b><?= app_lang('text_from'); ?>:</b></label>
                                <input type="text" id="fechaInicio" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label><b><?= app_lang('text_to'); ?>:</b></label>
                                <input type="text" id="fechaFin" class="form-control">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="generarGrafico()">
                                    <i class="fas fa-chart-bar"></i> <?= app_lang('text_graphic'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="graficoAsistencia" height="100"></canvas>

                            </div>
                            <div class="col-md-4">
                                <div id="graficoResumen" class="text-center my-3" style="display:none;">
                                    <div style="
                                                            background-color: #d4e3f8;
                                                            color: #003366;
                                                            width: 110px;
                                                            height: 110px;
                                                            border-radius: 50%;
                                                            display: inline-flex;
                                                            flex-direction: column;
                                                            justify-content: center;
                                                            align-items: center;
                                                            box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                        <i class="fas fa-clock" style="font-size: 20px;"></i>
                                        <div style="font-size: 24px;"><b id="totalHorasTexto">0h</b></div>
                                        <div style="font-size: 12px;">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="resumen-diario-contenedor" class="p-3" style="background:#f9f9f9; display:none;">
             
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table  mt-1 text-center" style="background-color: #fff;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?= app_lang('date') ?></th>
                                            <th><?= app_lang('in_text_singular') ?></th>
                                            <th><?= app_lang('out_text_singular') ?></th>
                                            <th><?= app_lang('punctuality') ?></th>
                                            <th><?= app_lang('desfase_in') ?></th>
                                            <th><?= app_lang('desfase_out') ?></th>
                                            <th><?= app_lang('expected_hours') ?></th>
                                        </tr>
                                    </thead>
                                    <?php if (isset($dataTable)): ?>
                                        <?php if (count($dataTable) > 0): ?>
                                            <?php $index = 1; ?>
                                            <?php foreach ($dataTable as $record): ?>
                                                <tr>
                                                    <th><?= $index++ ?></th>
                                                    <td>
                                                        <?= format_date_to_spanish_long($record['date']) ?>
                                                        <button class="rounded-circle" style="
                                                            background-color: #e5d4f3;
                                                            color: #6a1b9a;
                                                            width: 40px;
                                                            height: 40px;
                                                            border: none;
                                                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <?= convert_time_to_12hours_format($record['check_in']) ?>
                                                        <button class="rounded-circle" style="
                                                            background-color: <?= $record['in_color'] ?>;
                                                            color: #333;
                                                            width: 40px;
                                                            height: 40px;
                                                            border: none;
                                                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <?= convert_time_or_dash($record['check_out']) ?>
                                                        <button class="rounded-circle" style="
                                                            background-color: <?= $record['out_color'] ?>;
                                                            color: #333;
                                                            width: 40px;
                                                            height: 40px;
                                                            border: none;
                                                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                                            <i class="fas fa-sign-out-alt"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <?= $record['late'] ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: <?= $record['in_color'] ?>; color: #000;">
                                                            <?= $record['in_offset'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: <?= $record['out_color'] ?>; color: #000;">
                                                            <?= $record['out_offset'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?= $record['horas_esperadas'] ?? 8 ?>h
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8">
                                                    <div class="alert alert-warning text-center">
                                                        <i class="fas fa-info-circle"></i> <?= app_lang('text_register_available') ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="p-3" id="cards-container" style="background:#f9f9f9;">

                        <?php if (!$login_user->is_admin): ?>
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> 
                                <strong><?= app_lang('note') ?>:</strong> 
                                <?= app_lang('delete_records_admin_only') ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="my-3">
                                    <span class=" p-2 mb-3"><b><?= app_lang('text_select_date')  ?> :</b></span>
                                    <input type="text" id="fechaVisible" class="form-control"
                                        placeholder="MM/DD/YYYY">
                                    <input type="hidden" id="fechaReal">
                                </div>
                            </div>
                            <div class="col-md-2 d-none">
                                <div class="d-flex justify-content-center align-items-center"
                                    style="background-color: #f3f3f3; border-radius:50%; height:120px; width:120px;">
                                    <h6 class="text-center"><?= app_lang('text_clock') ?> <br />
                                        <h1> <b>IN</b></h1>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-md-3 d-none">
                                <h4><?= app_lang('text_time_work') ?></h4>
                                <p style="font-size: 40px;">00:00:00</p>
                            </div>
                            <div class="col-md-3 d-none">
                                <div class="d-flex justify-content-center align-items-center"
                                    style="background-color: #f68080;color:#fff; border-radius:50%; height:120px; width:120px;">
                                    <h6 class="text-center">Clock <br />
                                        <h1> <b>OUT</b></h1>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <table class="table  mt-1 text-center" style="background-color: #fff;">
                                    <thead class="">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col"><?= app_lang('date') ?></th>
                                            <th scope="col"><?= app_lang('hour') ?></th>
                                            <th scope="col"><?= app_lang('text_action') ?></th>
                                            <th scope="col"><?= app_lang('actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($request)): ?>
                                            <?php if (is_array($request) && count($request) > 0): ?>

                                                <?php
                                                $index = 1;
                                                $accion = 'Entrada';
                                                ?>

                                                <?php foreach ($request as $record) : ?>
                                                    <tr>
                                                        <th scope="row"><?= $index++; ?></th>
                                                        <td><?php echo format_date_to_spanish_long($record->date); ?>

                                                            <button class="rounded-circle" style="
                                                                                    background-color: #e5d4f3;  /* violeta pastel */
                                                                                    color: #6a1b9a;            /* violeta más fuerte para el ícono */
                                                                                    width: 40px;
                                                                                    height: 40px;
                                                                                    border: none;
                                                                                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                                                                                ">
                                                                <i class="fas fa-clipboard-check"></i>
                                                            </button>
                                                        </td>
                                                        <td><?php echo convert_time_to_12hours_format($record->time); ?>
                                                            <button class="rounded-circle" style="
                                                                                    background-color: #f8d5e6;  /* rosado pastel */
                                                                                    color: #b43775;             /* tono oscuro complementario */
                                                                                    width: 40px;
                                                                                    height: 40px;
                                                                                    border: none;
                                                                                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                                                                                ">
                                                                <i class="fas fa-clipboard-check"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <?php if ($accion === 'Entrada'): ?>
                                                                <button class="rounded-circle"
                                                                    style="
                                                                                        background-color: #d4edda;  /* verde pastel */
                                                                                        color: #155724;              /* verde fuerte para icono */
                                                                                        width: 40px;
                                                                                        height: 40px;
                                                                                        border: none;
                                                                                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);" title="Entrada">
                                                                    <i class="fas fa-circle-check"></i>
                                                                </button>
                                                                <span class="ms-2 text-success fw-bold"><?= app_lang('in_text_singular') ?></span>
                                                            <?php else: ?>
                                                                <button class="rounded-circle"
                                                                    style="
                                                                                        background-color: #f8d7da;  /* rojo pastel */
                                                                                        color: #721c24;              /* rojo fuerte para icono */
                                                                                        width: 40px;
                                                                                        height: 40px;
                                                                                        border: none;
                                                                                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);" title="Salida">
                                                                    <i class="fas fa-door-open"></i>
                                                                </button>
                                                                <span class="ms-2 text-danger fw-bold"><?= app_lang('out_text_singular') ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($login_user->is_admin): ?>
                                                                <button class="btn btn-danger btn-sm delete-record-btn" 
                                                                    data-record-id="<?= $record->id ?>" 
                                                                    data-record-date="<?= $record->date ?>" 
                                                                    data-record-time="<?= $record->time ?>"
                                                                    title="<?= app_lang('delete') ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted locked-icon" title="<?= app_lang('admin_only') ?>">
                                                                    <i class="fas fa-lock"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    // Alternar acción
                                                    $accion = ($accion === 'Entrada') ? 'Salida' : 'Entrada';
                                                    ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5"><button class="rounded-circle" style="
                                                                                background-color: #f8d7da;  /* rojo pastel claro */
                                                                                color: #c82333;             /* rojo fuerte para el ícono */
                                                                                width: 40px;
                                                                                height: 40px;
                                                                                border: none;
                                                                                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                                                                            ">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <?= app_lang('text_register_available') ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Modal de confirmación para eliminar registro -->
<div class="modal fade" id="deleteRecordModal" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRecordModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning"></i> <?= app_lang('confirm_delete') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?= app_lang('are_you_sure_delete_record') ?></p>
                <div class="alert alert-info">
                    <strong><?= app_lang('date') ?>:</strong> <span id="modal-record-date"></span><br>
                    <strong><?= app_lang('hour') ?>:</strong> <span id="modal-record-time"></span>
                </div>
                <p class="text-muted small"><?= app_lang('this_action_cannot_be_undone') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> <?= app_lang('cancel') ?>
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> <?= app_lang('delete') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let recordToDelete = null;
    
    // Manejar clic en botón de eliminar
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-record-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.delete-record-btn');
            recordToDelete = {
                id: btn.getAttribute('data-record-id'),
                date: btn.getAttribute('data-record-date'),
                time: btn.getAttribute('data-record-time')
            };
            
            // Llenar el modal con los datos del registro
            document.getElementById('modal-record-date').textContent = recordToDelete.date;
            document.getElementById('modal-record-time').textContent = recordToDelete.time;
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));
            modal.show();
        }
    });
    
    // Manejar confirmación de eliminación
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (recordToDelete) {
            deleteRecord(recordToDelete.id);
        }
    });
    
    // Función para eliminar el registro
    function deleteRecord(recordId) {
        // Mostrar loading
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= app_lang('deleting') ?>...';
        confirmBtn.disabled = true;
        
        // Realizar petición AJAX
        fetch('<?= site_url("clockin/delete_clockin_record") ?>/' + recordId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                showAlert('success', data.message);
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRecordModal'));
                modal.hide();
                
                // Recargar la página después de un breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                // Mostrar mensaje de error
                showAlert('error', data.message);
                
                // Restaurar botón
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '<?= app_lang('error_occurred') ?>');
            
            // Restaurar botón
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        });
    }
    
    // Función para mostrar alertas
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insertar alerta al inicio del panel
        const panel = document.getElementById('panel-personal');
        panel.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            const alert = panel.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});
</script>