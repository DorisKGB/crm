<style>
.clockin-checkbox {
    margin-top: 5px;
}
.branch-row {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}
.branch-row:last-child {
    border-bottom: none;
}
</style>

<div class="tab-content">

<?php
    $reload_url = get_uri("team_members/branch/" . $user_id);
    $save_url = get_uri("team_members/save_branch/" . $user_id);
    $show_submit = true;

    /*if (isset($user_type)) {
        if ($user_type === "client") {
            $reload_url = "";
            $save_url = get_uri("clients/save_contact_social_links/" . $user_id);
            if (isset($can_edit_clients) && !$can_edit_clients) {
                $show_submit = false;
            }
        } else if ($user_type === "lead") {
            $reload_url = "";
            $save_url = get_uri("leads/save_contact_social_links/" . $user_id);
        }
    }*/

    //echo form_open(get_uri("team_members/save_branch/" . $user_id), array("id" => "general-info-form", "class" => "general-form dashed-row white", "role" => "form")); 
    echo form_open($save_url, array("id" => "social-links-form", "class" => "general-form dashed-row white", "role" => "form"));
    
    ?>
    
<div class="card rounded-bottom">
    <div class="card-header">
        <h4><?php echo app_lang('branches'); ?> </h4>
        <p><?php echo  app_lang('details_admon_daily_reports') ?></p>
    </div>
    
        <div class="card-body">
            <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
            <div class="form-group">
                <button type="button" id="toggleAllBtn" class="btn btn-primary btn-sm"><?php echo  app_lang('check_uncheck_all') ?></button>
            </div>
            <div class="row mb-3">
                <div class="col-md-2"><strong><?php echo app_lang('branches'); ?></strong></div>
                <div class="col-md-5"><strong><?php echo app_lang('assign_to'); ?></strong></div>
                <div class="col-md-5"><strong><?php echo app_lang('clockin'); ?></strong></div>
            </div>
            <?php foreach($clinics as $clinic){ ?>
            <div class="form-group branch-row">
                <div class="row">
                    <label for="disable_login<?php echo $clinic['id']; ?>" class="col-md-2"><?php echo $clinic['name'] ?></label>
                    <div class="col-md-5">
                        <div class="form-check">
                            <?php
                            echo form_checkbox('clinic_list[]', $clinic['id'], $clinic['used']==0?false : true, "class='form-check-input' id='clinic_".$clinic['id']."'");
                            ?>
                            <label class="form-check-label" for="clinic_<?php echo $clinic['id']; ?>">
                                <?php echo app_lang('assign_to'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-check clockin-checkbox">
                            <?php
                            $clockin_checked = isset($clinic['clockin']) && $clinic['clockin'] == 1 ? true : false;
                            echo form_checkbox('clockin_list[]', $clinic['id'], $clockin_checked, "class='form-check-input' id='clockin_".$clinic['id']."'");
                            ?>
                            <label class="form-check-label" for="clockin_<?php echo $clinic['id']; ?>">
                                <?php echo app_lang('clockin'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        
            <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>

        </div>
        <?php echo form_close(); ?>


        <script>
            document.getElementById('toggleAllBtn').addEventListener('click', function() {
                const branchCheckboxes = document.querySelectorAll('input[name="clinic_list[]"]');
                let allChecked = Array.from(branchCheckboxes).every(checkbox => checkbox.checked);
                branchCheckboxes.forEach(checkbox => {
                    checkbox.checked = !allChecked;
                });
            });

            // Enable clockin checkbox only when branch is assigned
            document.addEventListener('change', function(e) {
                if (e.target.name === 'clinic_list[]') {
                    const clinicId = e.target.value;
                    const clockinCheckbox = document.getElementById('clockin_' + clinicId);
                    const clockinLabel = clockinCheckbox.nextElementSibling;
                    
                    if (e.target.checked) {
                        clockinCheckbox.disabled = false;
                        clockinLabel.style.opacity = '1';
                    } else {
                        clockinCheckbox.checked = false;
                        clockinCheckbox.disabled = true;
                        clockinLabel.style.opacity = '0.5';
                    }
                }
            });

            // Initialize disabled state for unassigned branches
            document.addEventListener('DOMContentLoaded', function() {
                const branchCheckboxes = document.querySelectorAll('input[name="clinic_list[]"]');
                branchCheckboxes.forEach(checkbox => {
                    if (!checkbox.checked) {
                        const clinicId = checkbox.value;
                        const clockinCheckbox = document.getElementById('clockin_' + clinicId);
                        const clockinLabel = clockinCheckbox.nextElementSibling;
                        
                        clockinCheckbox.disabled = true;
                        clockinLabel.style.opacity = '0.5';
                    }
                });
            });
        </script>
</div>
</div>
