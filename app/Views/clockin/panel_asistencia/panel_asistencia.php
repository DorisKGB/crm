<div id="panel-asistencia"
    class="panel-section <?= $activeOption === 'attendance' ? 'active' : '' ?>">

    <h3 class="d-flex justify-content-between align-items-center">
        <span><b><i class="fas fa-clipboard-check"></i> <?= app_lang('text_nav_attendance') ?></b></span>
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

    <!-- Pestañas para diferentes vistas -->
    <ul class="nav nav-tabs mb-4" id="attendanceTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#" data-tab="summary">
                <i class="fas fa-list"></i> <?= app_lang('summary') ?>
            </a>
        </li>

    </ul>

    <!-- Contenido de Resumen -->
    <div id="summaryTab" class="tab-content">
        <div class="row mb-4">
            <div class="col-md-4">
                <label><b><?= app_lang('text_from') ?>:</b></label>
                <input type="text" id="asistenciaDesde" class="form-control">
            </div>
            <div class="col-md-4">
                <label><b><?= app_lang('text_to') ?>:</b></label>
                <input type="text" id="asistenciaHasta" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="cargarResumenAsistencia()">
                    <i class="fas fa-search"></i> <?= app_lang('text_search_attendance'); ?>
                </button>
            </div>
        </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-hover" id="tablaResumenAsistencia" style="display:none;">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th><?= app_lang('avatar'); ?></th>
                        <th><?= app_lang('name'); ?></th>
                        <th><?= app_lang('role'); ?></th>
                        <th><?= app_lang('hour_worked'); ?></th>
                        <th><?= app_lang('expected_hours') ?></th>
                        <th><?= app_lang('efficiency') ?></th>
                    </tr>
                </thead>
                <tbody id="bodyResumenAsistencia">

                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="p-3 targetCard" style="background-color:#f9f9f9;">
                        <h5><i class="fas fa-list-alt"></i> <?= app_lang('summary_period'); ?></h5>
                        <ul id="resumenHoras" class="mb-0">

                        </ul>
                    </div>
                </div>
            </div>

            <div class="row mt-4 d-none">
                <div class="col-md-12">
                    <div class="p-3 targetCard" style="background-color:#f9f9f9;">
                        <h5><i class="fas fa-door-open"></i> <?= app_lang('in_out_daily'); ?></h5>
                        <table class="table table-sm table-bordered text-center">
                            <thead>
                                <tr>
                                    <th><?= app_lang('in_text'); ?></th>
                                    <th><?= app_lang('out_text'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="tablaEntradasSalidas">
           
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4 d-none">
                <div class="col-md-12">
                    <div class="p-3 targetCard" style="background-color:#f9f9f9;">
                        <h5><i class="fas fa-calendar-times"></i><?= app_lang('in_text_no_daily_attendance') ?></h5>
                        <table class="table table-sm table-bordered text-center">
                            <thead>
                                <tr>
                                    <th><?= app_lang('date') ?></th>
                                    <th><?= app_lang('in_short') ?></th>
                                </tr>
                            </thead>
                            <tbody id="tablaFaltas">
                    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
    </div>

</div>