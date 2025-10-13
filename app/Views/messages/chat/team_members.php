<div class="search-container mb-3">
    <div class="input-group">
        <input type="text" id="team-search-input" class="form-control" placeholder="<?php echo app_lang('search_team_members'); ?>" />
        <span class="input-group-text">
            <i data-feather="search" class="icon-16"></i>
        </span>
    </div>
</div>

<?php if ($users) { ?>
    <div id="js-chat-team-members-list">
        <?php
        foreach ($users as $user) {
            $online = "";
            if ($user->last_online && is_online_user($user->last_online)) {
                $online = "<i class='online'></i>";
            }
            
            $unread_count = isset($user->unread_count) ? $user->unread_count : 0;
            
            $full_name = $user->first_name . " " . $user->last_name;
            $subline = $user->job_title;
            if ($user->user_type === "client" && $user->company_name) {
                $subline = $user->company_name;
            }
            ?>
            <div class="message-row js-message-row-of-<?php echo $page_type; ?> team-member-item" 
                 data-id="<?php echo $user->id; ?>" 
                 data-index="1" 
                 data-reply=""
                 data-name="<?php echo strtolower($full_name); ?>"
                 data-job="<?php echo strtolower($subline); ?>">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-xs" style="position: relative;">
                            <img alt="..." src="<?php echo get_avatar($user->image); ?>">
                            <?php echo $online; ?>
                            <?php if ($unread_count > 0) { ?>
                                <span class="chat-unread-badge"><?php echo $unread_count; ?></span>
                            <?php } ?>
                        </span>
                    </div>
                    <div class="w-100 ps-2">
                        <div class="mb5">
                            <strong><?php echo $full_name; ?></strong>
                        </div>
                        <small class="text-off w200 d-block"><?php echo $subline; ?></small>

                        <!-- Badges para Provider o Clínicas -->
                        <div class="mt-1 clinic-badges">
                            <?php if (isset($user->is_provider) && $user->is_provider): ?>
                                <span class="badge bg-success badge-sm">
                                    <i class="fa fa-user-md me-1"></i>Provider
                                </span>
                            <?php elseif (isset($user->user_clinics) && is_array($user->user_clinics) && !empty($user->user_clinics)): ?>
                                <?php 
                                try {
                                    $colors = ['bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                                    $clinics_to_show = array_slice($user->user_clinics, 0, 3);
                                    $has_more = count($user->user_clinics) > 3;
                                    ?>
                                    <?php foreach ($clinics_to_show as $index => $clinic): ?>
                                        <?php if (isset($clinic->name)): ?>
                                            <span class="badge <?php echo $colors[$index % count($colors)]; ?> badge-sm me-1">
                                                <?php echo esc($clinic->name); ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($has_more): ?>
                                        <span class="badge bg-dark badge-sm" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="<?php echo implode(', ', array_map(function($c) { return isset($c->name) ? $c->name : 'N/A'; }, $user->user_clinics)); ?>">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </span>
                                    <?php endif; ?>
                                <?php 
                                } catch (\Exception $e) {
                                    // En caso de error en la vista, no mostrar badges
                                }
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
<?php } else { ?>
    <div class="chat-no-messages text-off text-center">
        <i data-feather="frown" height="4rem" width="4rem"></i><br />
        <?php echo app_lang("no_users_found"); ?>
    </div>
<?php } ?>

<div id="no-results-message" class="chat-no-messages text-off text-center" style="display: none;">
    <i data-feather="search" height="3rem" width="3rem"></i><br />
    <?php echo app_lang("no_results_found"); ?>
</div>

<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    // Funcionalidad de búsqueda
    $('#team-search-input').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var visibleItems = 0;
        
        $('.team-member-item').each(function() {
            var name = $(this).data('name');
            var job = $(this).data('job');
            
            if (name.includes(searchTerm) || job.includes(searchTerm)) {
                $(this).show();
                visibleItems++;
            } else {
                $(this).hide();
            }
        });
        
        // Mostrar mensaje si no hay resultados
        if (visibleItems === 0 && searchTerm !== '') {
            $('#no-results-message').show();
            $('#js-chat-team-members-list').hide();
        } else {
            $('#no-results-message').hide();
            $('#js-chat-team-members-list').show();
        }
    });
    
    // Limpiar búsqueda al hacer clic en el ícono
    $('.input-group-text').click(function() {
        $('#team-search-input').val('');
        $('.team-member-item').show();
        $('#no-results-message').hide();
        $('#js-chat-team-members-list').show();
    });
});
</script>

<style>
.search-container {
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
}

.input-group-text {
    cursor: pointer;
    background-color: #f8f9fa;
}

.input-group-text:hover {
    background-color: #e9ecef;
}

.team-member-item {
    transition: opacity 0.2s ease;
}

#team-search-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.chat-unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    z-index: 10;
    min-width: 18px;
}
</style>