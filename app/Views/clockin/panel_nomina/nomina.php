<?php
// Calcular valores predeterminados
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-1 month'));
$to = $_GET['to'] ?? date('Y-m-d');
$user_id = $_GET['user_id'] ?? 0; // mantener user_id en la URL
?>

<div id="panel-personal" class="panel-section <?= $activeOption === 'nomina' ? 'active' : '' ?>">

    <h3 class="d-flex justify-content-between align-items-center">
        <span><b><i class="fas fa-search-dollar"></i> <?= app_lang('text_nav_momina'); ?></b></span>
        <?php if (isset($data_clinic)) : ?>
            <span class="clinic-badge" style="font-size: 16px;"><i
                    class="fas fa-check-circle"></i><?= esc($data_clinic->name) ?></span>
        <?php endif; ?>
    </h3>
    <p> <?= app_lang('text_nav_momina_details'); ?></p>
    
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
                                <img src="<?php echo get_avatar($user->image); ?>" class="rounded-circle" width="100"
                                    alt="<?= app_lang('photo_of') ?> <?= esc($user->first_name) ?>"
                                    style="object-fit: cover; border:10px solid #f3f3f3; cursor:pointer;">
                            <?php else: ?>
                                <img src="<?php echo base_url("public/uploads/clinics/default-clinic.png"); ?>"
                                    class="rounded-circle" width="100" alt="<?= app_lang('default_photo') ?>"
                                    style="object-fit: cover; border:10px solid #f3f3f3; cursor:pointer;">
                            <?php endif; ?>
                            <p class="text-center"><?= esc($user->first_name); ?>
                                <?= esc($user->last_name); ?></p>
                            <p><span class="badge badge-success"><?= esc($user->job_title) ?></span></p>
                            <p>
                                <button class="btn-rubymed btn-rubymed-success-in ver-nomina" data-user="<?= $user->id ?>">
                                    <i class="fas fa-dollar-sign"></i> <?= app_lang('text_view_nomina'); ?>
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php if (isset($nominas)): ?>
            <div>
                <div class="row">
                    <div class="col-md-5 d-flex ">
                        <div>
                            <img src="<?php echo get_avatar($data_user->image); ?>" class="rounded-circle" width="100"
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
                    <div class="col-md-12">
                        <div class="">
                            <div class="row mb-4">
                                <div class="col-md-12 mt-5">
                                    <form id="formNomina" class="d-flex align-items-center gap-3">
                                        <span><?= app_lang('from') ?>: </span>
                                        <input type="text" id="fromDate" name="from" class="form-control" placeholder="<?= app_lang('from') ?>" value="<?= $from ?>" style="max-width: 200px;">
                                        <span><?= app_lang('to') ?>: </span>
                                        <input type="text" id="toDate" name="to" class="form-control" placeholder="<?= app_lang('to') ?>" value="<?= $to ?>" style="max-width: 200px;">
                                        <button type="submit" class="btn-rubymed btn-rubymed-success-in"><i class="fas fa-search"></i> <?= app_lang('consult') ?></button>
                                    </form>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-4 mt-4 p-4" >

                                <!-- Total de Horas Trabajadas -->
                                <div class="text-center">
                                    <div class=" d-flex p-4"
                                        style="background-color: #d1f7e2; color: #155724; font-size: 20px; font-weight: bold;border-radius:15px;">
                                        <?= esc($nominas['total_horas']) ?>
                                    </div>
                                    <small class="mt-2 d-block "><b><?= app_lang('text_total_hour') ?></b></small>
                                </div>

                                <!-- Horas Esperadas -->
                                <div class="text-center">
                                    <div class=" d-flex p-4"
                                        style="background-color: #e2e3e5; color: #383d41; font-size: 20px; font-weight: bold;border-radius:15px;">
                                        <?= esc($nominas['horas_esperadas'] ?? 0) ?>
                                    </div>
                                    <small class="mt-2 d-block "><b><?= app_lang('expected_hours') ?></b></small>
                                </div>

                                <!-- Eficiencia -->
                                <div class="text-center">
                                    <div class=" d-flex p-4"
                                        style="background-color: <?= ($nominas['eficiencia'] ?? 0) >= 100 ? '#d1f7e2' : (($nominas['eficiencia'] ?? 0) >= 80 ? '#fff3cd' : '#f8d7da') ?>; color: <?= ($nominas['eficiencia'] ?? 0) >= 100 ? '#155724' : (($nominas['eficiencia'] ?? 0) >= 80 ? '#856404' : '#721c24') ?>; font-size: 20px; font-weight: bold;border-radius:15px;">
                                        <?= esc($nominas['eficiencia'] ?? 0) ?>%
                                    </div>
                                    <small class="mt-2 d-block "><b><?= app_lang('efficiency') ?></b></small>
                                </div>

                                <span style="font-size:25px;">X</span>
                                <!-- Salario por Hora -->
                                <div class="text-center">
                                    <div class=" d-flex p-4"
                                        style="background-color: #d1ecf1; color: #0c5460; font-size: 20px; font-weight: bold;border-radius:15px;">
                                        $ <?= esc($nominas['salario_hora']) ?>
                                    </div>
                                    <small class="mt-2 d-block "><b><?= app_lang('text_salary_hour') ?></b></small>
                                </div>
                                <span style="font-size:25px;">=</span>
                                <!-- Total Nómina -->
                                <div class="text-center">
                                    <div class=" d-flex p-4"
                                        style=" background-color: #fbe4d5; color: #8a4b08; font-size: 20px; font-weight: bold;border-radius:15px;">
                                        $ <?= esc(round($nominas['total'], 2)) ?>
                                    </div>
                                    <small class="mt-2 d-block "><b><?= app_lang('text_total_nomina') ?></b></small>
                                </div>

                            </div>
                        </div>

                    </div>



                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>