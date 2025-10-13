<?php
$permissions2 = $login_user->permissions;
$directory_permission = get_array_value($permissions2, "directory_permission");
$solo_vista = $directory_permission === "view";
?>

<style>
    .dot-rubymed {
        background-color: #007bff;
    }
    
    .modal-body {
        max-height: 60vh;
        overflow-y: auto;
    }

    .targetCard {
        box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
        -webkit-box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
        -moz-box-shadow: 9px 5px 7px 3px rgba(222, 222, 222, 0.81);
        border-radius: 15px;
    }
</style>

<div id="notification"></div>
<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-md-12">
            <div class="card" id="excuse-form-container">
                <div class="card-header">
                    <h4>
                        <i class="fas fa-book-reader"></i>
                        <?php echo app_lang('directory_clinic') ?>
                        <button id="btnAddClinic" class="btn-rubymed btn-rubymed-primary-in d-none"><?php echo app_lang('add_clinic'); ?></button>
                    </h4>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="search-clinic" placeholder="<?php echo app_lang('search_clinic'); ?>...">
                        </div>
                    </div>
                    <div class="row">

                        <?php if (empty($listClinic)): ?>
                            <p><?php echo app_lang('no_clinics_registered'); ?></p>
                        <?php else: ?>

                            <?php foreach ($listClinic as $clinic): ?>
                                <div class="col-md-3">
                                    <div class="card targetCard">
                                        <div class="card-body">
                                            <div class="text-center">
                                                <?php
                                                $photoPath = "public/" . $clinic->photo;
                                                $defaultPhoto = "public/uploads/clinics/default-clinic.png";
                                                $photoToShow = file_exists(FCPATH . $photoPath) && !empty($clinic->photo)
                                                    ? base_url($photoPath)
                                                    : base_url($defaultPhoto);
                                                ?>

                                                <img
                                                    src="<?= esc($photoToShow) ?>"
                                                    class="rounded-circle"
                                                    width="150"
                                                    alt="<?php echo app_lang('logo_of'); ?> <?= esc($clinic->name) ?>"
                                                    style="object-fit: cover; border:10px solid #f3f3f3; cursor:pointer;">
                                            </div>
                                            <h4 class="text-center">
                                                <b><?= esc($clinic->name) ?></b>
                                            </h4>
                                            <p class="d-none"><i class="fas fa-users"></i> <?php echo app_lang('members'); ?> : <span
                                                    style="border-radius: 50%; background-color:#6b6a6a; color:#fff;" class="p-2"> <?= sprintf('%02d', $clinic->member_count) ?>
                                                </span></p>
                                            <p class=""><i class="fas fa-mobile-alt"></i> <?php echo app_lang('phone'); ?> : <span
                                                    class=""><?= esc($clinic->phone) ?></span></p>
                                            <p class=""><i class="fas fa-map-marker-alt"></i> <?php echo app_lang('address'); ?> : <span
                                                    class=""><?= esc($clinic->address) ?></span></p>
                                            <p class="">

                                               <?php if ($clinic->is_aliada == 1): ?>
                                                    <button class="btn-rubymed btn-rubymed-success btn-sm">
                                                        <span class="dot-live"></span><b><?php echo app_lang('allied'); ?></b>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn-rubymed btn-rubymed-primary btn-sm">
                                                        <span class="dot-live dot-rubymed"></span><b>RUBYMED</b>
                                                    </button>
                                                <?php endif ?>

                                            </p>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn-rubymed btn-rubymed-primary btn-edit-clinic"
                                                data-id="<?= $clinic->id ?>"><i class="fas fa-pencil-alt"></i>
                                                <?php echo app_lang('edit'); ?></button>
                                            <?php if (count($clinic->hours) > 0): ?>
                                            <button class="btn-rubymed btn-rubymed-warning btn-view-schedule"
                                                data-hours='<?= json_encode($clinic->hours) ?>'
                                                data-clinic="<?= esc($clinic->name) ?>">
                                                <i class="fas fa-clock"></i> <?php echo app_lang('view_schedule'); ?>
                                            </button>
                                            <?php endif; ?>

                                            <button class="btn-rubymed btn-rubymed-info d-none"><i class="fas fa-envelope"></i>
                                                <?php echo app_lang('email'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        <?php endif ?>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <?php $days = [0 => app_lang('sunday'), 1 => app_lang('monday'), 2 => app_lang('tuesday'), 3 => app_lang('wednesday'), 4 => app_lang('thursday'), 5 => app_lang('friday'), 6 => app_lang('saturday')]; ?>

    <!-- Modal para Agregar/Editar Clínica -->
    <div class="modal fade" id="clinicModal" tabindex="-1" aria-labelledby="clinicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title" id="clinicModalLabel"><?php echo app_lang('title'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo app_lang('close'); ?>"></button>
                </div>
                <form id="clinicForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" name="id">

                                <div class="mb-3">
                                    <label for="clinicName" class="form-label"><?php echo app_lang('name'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="clinicName" class="form-control" required>
                                    <div class="invalid-feedback"><?php echo app_lang('field_required'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicPhoto" class="form-label"><?php echo app_lang('photo'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="photo" id="clinicPhoto" accept="image/*"
                                        class="form-control">
                                    <div class="invalid-feedback"><?php echo app_lang('photo_required'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicPhone" class="form-label"><?php echo app_lang('phone'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="clinicPhone" class="form-control" required>
                                    <div class="invalid-feedback"><?php echo app_lang('field_required'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicAddress" class="form-label"><?php echo app_lang('address'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="address" id="clinicAddress" class="form-control" required>
                                    <div class="invalid-feedback"><?php echo app_lang('field_required'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicEmail" class="form-label"><?php echo app_lang('email'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="clinicEmail" class="form-control" required>
                                    <div class="invalid-feedback"><?php echo app_lang('enter_valid_email'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicExtension" class="form-label"><?php echo app_lang('extension'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="extension" id="clinicExtension" class="form-control"
                                        required>
                                    <div class="invalid-feedback"><?php echo app_lang('field_required'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <label for="clinicFax" class="form-label"><?php echo app_lang('fax'); ?> <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="fax" id="clinicFax" class="form-control" required>
                                    <div class="invalid-feedback"><?php echo app_lang('field_required'); ?></div>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <!-- Horarios por día -->
                                <div class="mb-3">
                                    <label class="form-label"><?php echo app_lang('business_hours'); ?></label>
                                    <?php foreach ($days as $key => $label): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input day-checkbox" type="checkbox" data-day="<?= $key ?>"
                                                    id="open_<?= $key ?>" name="days[<?= $key ?>][enabled]" value="1">
                                                <label class="form-check-label ms-2" for="open_<?= $key ?>"><?= $label ?></label>
                                            </div>
                                            <input type="time" name="days[<?= $key ?>][open]"
                                                class="form-control form-control-sm ms-3 time-input">
                                            <input type="time" name="days[<?= $key ?>][close]"
                                                class="form-control form-control-sm ms-2 time-input">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-rubymed btn-rubymed-secondary"
                            data-bs-dismiss="modal"><?php echo app_lang('cancel'); ?></button>
                        <button type="submit" id="btnSaveClinic"
                            class="btn-rubymed btn-rubymed-primary"> <i class="fas fa-save"></i> <?php echo app_lang('save'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Horarios -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel"><?php echo app_lang('clinic_schedule'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo app_lang('close'); ?>"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th><?php echo app_lang('day'); ?></th>
                                    <th><?php echo app_lang('opening_time'); ?></th>
                                    <th><?php echo app_lang('closing_time'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="scheduleTableBody">
                                <!-- Aquí se cargan los horarios con JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script AJAX, validaciones y loader -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('clinicForm');
            const title = document.getElementById('clinicModalLabel');

            // Abrir modal para agregar
            document.getElementById('btnAddClinic').addEventListener('click', () => {
                title.textContent = '<?php echo app_lang("add_clinic"); ?>';
                form.reset();
                form.classList.remove('was-validated');

                const modalEl = document.getElementById('clinicModal');
                const modal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static'
                });
                modal.show();
            });

            // Abrir modal para editar
            document.querySelectorAll('.btn-edit-clinic').forEach(btn => {
                btn.addEventListener('click', () => {
                    showLoading();
                    const id = btn.dataset.id;
                    title.textContent = '<?php echo app_lang("edit_clinic"); ?>';
                    form.reset();
                    form.classList.remove('was-validated');

                    fetch(`<?= site_url('directory/getClinic/') ?>${id}`)
                        .then(r => r.json()).then(data => {
                            form.querySelector('[name=id]').value = data.id;
                            form.querySelector('[name=name]').value = data.name;
                            form.querySelector('[name=phone]').value = data.phone;
                            form.querySelector('[name=address]').value = data.address;
                            form.querySelector('[name=email]').value = data.email;
                            form.querySelector('[name=extension]').value = data.extension;
                            form.querySelector('[name=fax]').value = data.fax;

                            document.querySelectorAll('.day-checkbox').forEach(cb => {
                                const d = cb.dataset.day;
                                cb.checked = data.days?.[d]?.enabled == 1;
                            });
                            document.querySelectorAll('.time-input').forEach(input => {
                                const match = input.name.match(/days\[(\d+)\]\[(open|close)\]/);
                                if (match) {
                                    input.value = data.days?.[match[1]]?.[match[2]] || '';
                                }
                            });

                            hideLoading();
                            $('#clinicModal').modal('show');
                        });
                });
            });

            // Guardar clínica
            form.addEventListener('submit', e => {
                e.preventDefault();
                e.stopPropagation();

                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                let ok = true;
                document.querySelectorAll('.day-checkbox').forEach(cb => {
                    if (cb.checked) {
                        const d = cb.dataset.day;
                        const o = form.querySelector(`[name="days[${d}][open]"]`);
                        const c = form.querySelector(`[name="days[${d}][close]"]`);
                        if (!o.value || !c.value) {
                            ok = false;
                            o.classList.add('is-invalid');
                            c.classList.add('is-invalid');
                        }
                    }
                });

                if (!ok) {
                    alert('<?php echo app_lang("complete_schedule"); ?>');
                    return;
                }

                showLoading();
                fetch(`<?= site_url('directory/save') ?>`, {
                    method: 'POST',
                    body: new FormData(form)
                }).then(r => r.json()).then(res => {
                    hideLoading();
                    if (res.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('clinicModal'));
                        modal?.hide();
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                }).catch(err => {
                    hideLoading();
                    console.error(err);
                });
            });

            // Ver horarios
            document.querySelectorAll('.btn-view-schedule').forEach(btn => {
                btn.addEventListener('click', () => {
                    const hours = JSON.parse(btn.dataset.hours);
                    const clinicName = btn.dataset.clinic;

                    const label = document.getElementById('scheduleModalLabel');
                    if (label) {
                        label.textContent = `<?php echo app_lang("schedule_of"); ?> ${clinicName}`;
                    }

                    const tableBody = document.getElementById('scheduleTableBody');
                    tableBody.innerHTML = '';

                    if (hours.length === 0) {
                        tableBody.innerHTML = `
                        <tr><td colspan="3"><?php echo app_lang("no_schedules_registered"); ?></td></tr>`;
                    } else {
                        hours.forEach(h => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td><strong>${h.day_name}</strong></td>
                            <td>${h.opening_time}</td>
                            <td>${h.closing_time}</td>`;
                            tableBody.appendChild(row);
                        });
                    }

                    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
                    modal.show();
                });
            });

            // Búsqueda de clínicas
            document.getElementById('search-clinic').addEventListener('input', function() {
                const value = this.value.toLowerCase();
                document.querySelectorAll('.targetCard').forEach(card => {
                    const name = card.querySelector('h4')?.innerText.toLowerCase() || '';
                    if (name.includes(value)) {
                        card.parentElement.style.display = '';
                    } else {
                        card.parentElement.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <?php if ($solo_vista): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('btnAddClinic')?.remove();
                document.querySelectorAll('.btn-edit-clinic').forEach(b => b.remove());
                const form = document.getElementById('clinicForm');
                if (form) {
                    form.querySelectorAll('input, select, textarea, button').forEach(el => {
                        el.disabled = true;
                    });
                }
            });
        </script>
    <?php endif; ?>

</div>