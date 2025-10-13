<?php
$user_id = $user_info->id;
$has_credentials = $has_provider_credentials;
$provider_info = $provider_info;
?>

<div class="card">
    <div class="card-header">
        <h4><?php echo app_lang('provider_credentials'); ?></h4>
    </div>
    <div class="card-body">
        <?php if ($has_credentials): ?>
            <!-- Provider credentials exist - show read-only form with update/delete options -->
            <div class="alert alert-success">
                <i data-feather="check-circle" class="icon-16"></i>
                <?php echo app_lang('provider_credentials_generated'); ?> - <?php echo $provider_info->name; ?> <?php echo app_lang('for_signing_documents'); ?>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_name" class="form-label"><?php echo app_lang('name'); ?></label>
                        <input type="text" class="form-control" id="provider_name" value="<?php echo $provider_info->name; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_npi" class="form-label"><?php echo app_lang('npi'); ?></label>
                        <input type="text" class="form-control" id="provider_npi" value="<?php echo $provider_info->npi; ?>" readonly>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_role" class="form-label"><?php echo app_lang('role'); ?></label>
                        <input type="text" class="form-control" id="provider_role" value="<?php echo $provider_info->role; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label"><?php echo app_lang('signature_preview'); ?></label>
                        <div class="signature-preview-container">
                            <?php if ($provider_info->url_signature && file_exists(FCPATH . 'writable/firmas/' . $provider_info->url_signature)): ?>
                                <img src="<?php echo site_url('firmas/' . $provider_info->url_signature); ?>" 
                                     alt="Signature Preview" 
                                     class="signature-preview" 
                                     style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                <div class="mt-2">
                                    <small class="text-muted"><?php echo $provider_info->url_signature; ?></small>
                                </div>
                            <?php else: ?>
                                <div class="no-signature">
                                    <i data-feather="image" class="icon-16"></i>
                                    <span><?php echo app_lang('no_signature_uploaded'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-primary" id="edit-provider-btn">
                    <i data-feather="edit" class="icon-16"></i> <?php echo app_lang('edit_credentials'); ?>
                </button>
                <button type="button" class="btn btn-danger" id="delete-provider-btn">
                    <i data-feather="trash-2" class="icon-16"></i> <?php echo app_lang('delete_credentials'); ?>
                </button>
            </div>
            
        <?php else: ?>
            <!-- No provider credentials - show creation form -->
            <div class="alert alert-info">
                <i data-feather="info" class="icon-16"></i>
                <?php echo app_lang('no_provider_credentials'); ?>
            </div>
            
            <?php echo form_open(get_uri("team_members/save_provider_credentials/" . $user_id), array("id" => "provider-form", "class" => "general-form", "role" => "form")); ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_name" class="form-label"><?php echo app_lang('name'); ?></label>
                        <input type="text" class="form-control" id="provider_name" name="provider_name" value="<?php echo $user_info->first_name . ' ' . $user_info->last_name; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_npi" class="form-label"><?php echo app_lang('npi'); ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="provider_npi" name="npi" placeholder="<?php echo app_lang('enter_npi_number'); ?>" required>
                        <small class="form-text text-muted"><?php echo app_lang('npi_help_text'); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provider_role" class="form-label"><?php echo app_lang('role'); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="provider_role" name="role" placeholder="<?php echo app_lang('enter_provider_role'); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="signature_file" class="form-label"><?php echo app_lang('signature_file'); ?></label>
                        <input type="file" class="form-control" id="signature_file" name="signature_file" accept="image/*" onchange="previewSignature(this)">
                        <small class="form-text text-muted"><?php echo app_lang('signature_file_help'); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="row" id="signature-preview-row" style="display: none;">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label"><?php echo app_lang('signature_preview'); ?></label>
                        <div class="signature-preview-container">
                            <img id="signature-preview-img" src="" alt="Signature Preview" class="signature-preview" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="plus" class="icon-16"></i> <?php echo app_lang('generate_credentials'); ?>
                </button>
            </div>
            
            <?php echo form_close(); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="edit-provider-modal" tabindex="-1" role="dialog" aria-labelledby="edit-provider-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit-provider-modal-label"><?php echo app_lang('edit_provider_credentials'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo form_open(get_uri("team_members/save_provider_credentials/" . $user_id), array("id" => "edit-provider-form", "class" => "general-form", "role" => "form")); ?>
                
                <div class="form-group">
                    <label for="edit_provider_npi" class="form-label"><?php echo app_lang('npi'); ?> <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="edit_provider_npi" name="npi" value="<?php echo $has_credentials ? $provider_info->npi : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_provider_role" class="form-label"><?php echo app_lang('role'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_provider_role" name="role" value="<?php echo $has_credentials ? $provider_info->role : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_signature_file" class="form-label"><?php echo app_lang('signature_file'); ?></label>
                    <input type="file" class="form-control" id="edit_signature_file" name="signature_file" accept="image/*" onchange="previewEditSignature(this)">
                    <small class="form-text text-muted"><?php echo app_lang('signature_file_help'); ?></small>
                    <?php if ($has_credentials && $provider_info->url_signature): ?>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="remove-signature-btn">
                            <i data-feather="trash-2" class="icon-16"></i> <?php echo app_lang('remove_signature'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                    <input type="hidden" id="remove_signature" name="remove_signature" value="0">
                </div>
                
                <?php if ($has_credentials && $provider_info->url_signature && file_exists(FCPATH . 'writable/firmas/' . $provider_info->url_signature)): ?>
                <div class="form-group">
                    <label class="form-label"><?php echo app_lang('current_signature'); ?></label>
                    <div class="signature-preview-container">
                        <img src="<?php echo site_url('firmas/' . $provider_info->url_signature); ?>" 
                             alt="Current Signature" 
                             class="signature-preview" 
                             style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                        <div class="mt-2">
                            <small class="text-muted"><?php echo $provider_info->url_signature; ?></small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group" id="edit-signature-preview-group" style="display: none;">
                    <label class="form-label"><?php echo app_lang('new_signature_preview'); ?></label>
                    <div class="signature-preview-container">
                        <img id="edit-signature-preview-img" src="" alt="New Signature Preview" class="signature-preview" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                </div>
                
                <?php echo form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo app_lang('cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="save-edit-provider"><?php echo app_lang('save_changes'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Function to preview signature image
function previewSignature(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#signature-preview-img').attr('src', e.target.result);
            $('#signature-preview-row').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Function to preview signature image in edit modal
function previewEditSignature(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#edit-signature-preview-img').attr('src', e.target.result);
            $('#edit-signature-preview-group').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function() {
    // Handle edit provider button
    $('#edit-provider-btn').click(function() {
        $('#edit-provider-modal').modal('show');
    });
    
    // Handle save edit provider
    $('#save-edit-provider').click(function() {
        $('#edit-provider-form').submit();
    });
    
    // Handle remove signature button
    $('#remove-signature-btn').click(function() {
        if (confirm('<?php echo app_lang('confirm_remove_signature'); ?>')) {
            $('#remove_signature').val('1');
            $('#edit-signature-preview-group').hide();
            $(this).hide();
        }
    });
    
    // Handle delete provider button
    $('#delete-provider-btn').click(function() {
        if (confirm('<?php echo app_lang('confirm_delete_provider_credentials'); ?>')) {
            var deleteBtn = $(this);
            var originalText = deleteBtn.html();
            
            // Show loading state
            deleteBtn.html('<i class="fas fa-spinner fa-spin"></i> <?php echo app_lang('processing'); ?>...').prop('disabled', true);
            
            $.ajax({
                url: '<?php echo get_uri("team_members/delete_provider_credentials/" . $user_id); ?>',
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    deleteBtn.html(originalText).prop('disabled', false);
                    if (result.success) {
                        appAlert.success(result.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        appAlert.error(result.message);
                    }
                },
                error: function() {
                    deleteBtn.html(originalText).prop('disabled', false);
                    appAlert.error('<?php echo app_lang('error_occurred'); ?>');
                }
            });
        }
    });
    
    // Handle provider form submission
    $('#provider-form, #edit-provider-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = this;
        var formData = new FormData(form);
        var submitBtn = $(form).find('button[type="submit"], #save-edit-provider');
        var originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> <?php echo app_lang('processing'); ?>...').prop('disabled', true);
        
        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(result) {
                submitBtn.html(originalText).prop('disabled', false);
                if (result.success) {
                    appAlert.success(result.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    appAlert.error(result.message);
                }
            },
            error: function() {
                submitBtn.html(originalText).prop('disabled', false);
                appAlert.error('<?php echo app_lang('error_occurred'); ?>');
            }
        });
    });
});
</script>
