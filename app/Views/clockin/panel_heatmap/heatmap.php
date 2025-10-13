<div id="panel-heatmap" class="panel-section <?= $activeOption === 'heatmap' ? 'active' : '' ?>">
    <div class="targetCard p-4">
        <!-- Header del Mapa de Calor -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                
                    <h3 class="mb-1 font-weight-bold">
                        <b>
                            <i class="fas fa-calendar-alt  me-2"></i>
                            <?= app_lang('monthly_heatmap') ?>
                        </b>
                    </h3>
         
                <p class="text-muted mb-0">Visualización mensual de asistencia y productividad por día</p>
            </div>

            <div class="clinic-badge">
                <i class="fas fa-check-circle me-2"></i>
                <?= esc($data_clinic->name) ?>
            </div>
        </div>

        <!-- Controles -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar me-2"></i><?= app_lang('month') ?>:
                </label>
                <input type="month" id="heatmapMonth" class="form-control" value="<?= date('Y-m') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-user me-2"></i><?= app_lang('user_optional') ?>:
                </label>
                <select id="heatmapUser" class="form-control">
                    <option value=""><?= app_lang('all_users') ?></option>
                    <?php if (isset($users)) : ?>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?= $user->id ?>"><?= esc($user->first_name . ' ' . $user->last_name) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="loadHeatmap()">
                    <i class="fas fa-search me-2"></i><?= app_lang('load_map') ?>
                </button>
            </div>
        </div>


        <!-- Loading Spinner -->
        <div class="text-center" id="loadingSpinner" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?= app_lang('loading') ?></span>
            </div>
            <p class="mt-3"><?= app_lang('loading_heatmap') ?></p>
        </div>

        <!-- Botón para abrir el mapa de calor -->
        <div class="text-center">
            <button class="btn btn-primary btn-lg" onclick="openHeatmapModal()">
                <i class="fas fa-calendar-alt me-2"></i><?= app_lang('open_heatmap') ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal de pantalla completa para el mapa de calor -->
<div class="modal fade" id="heatmapModal" tabindex="-1" aria-labelledby="heatmapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="heatmapModalLabel">
                    <i class="fas fa-calendar-alt me-2"></i><?= app_lang('monthly_heatmap') ?> - <?= esc($data_clinic->name) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Contenedor del mapa de calor -->
                <div id="heatmapContainer" class="p-4">
                    <div class="text-center p-5">
                        <i class="fas fa-calendar-alt fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted"><?= app_lang('select_month_load_map') ?></h4>
                        <p class="text-muted"><?= app_lang('visualize_attendance_productivity') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del día -->
<div class="modal fade" id="dayDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-day me-2"></i><?= app_lang('day_details') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayDetailContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>
