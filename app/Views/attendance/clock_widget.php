<div id="js-clock-in-out" class="card dashboard-icon-widget clock-in-out-card">
    <div class="card-body">
        <div class="widget-icon  <?php echo (isset($clock_status->id)) ? 'bg-info' : 'bg-coral'; ?> ">
            <i data-feather="clock" class="icon"></i>
        </div>
        <div class="widget-details">
            <?php
            if (isset($clock_status->id)) {
                 //Si ha marcado por el clockin ya solo le sale boton para salir al trabajo
                echo modal_anchor(get_uri("attendance/note_modal_form"), "<i data-feather='log-out' class='icon-16'></i> " . app_lang('clock_out'), array("class" => "btn btn-default text-primary", "title" => app_lang('clock_out'), "id" => "timecard-clock-out", "data-post-id" => $clock_status->id, "data-post-clock_out" => 1));

                $in_time = get_hour_minute_from_datetime($clock_status->in_time);
                $in_datetime = format_to_datetime($clock_status->in_time);
                echo "<div class='mt5 bg-transparent-white' title='$in_datetime'> <b>" . app_lang('marcaje_text') ."</b> ". app_lang('clock_started_at_text') . " : $in_time</div>";
            } else {
                //Si no ha marcado muestra esto que es entrar al trabajo
                
                //button que ejecuta accion de entrar a trabajar
                echo ajax_anchor(get_uri("attendance/log_time"), "<i data-feather='log-out' class='icon-16'></i> " . app_lang('clock_in'), array("class" => "btn btn-default text-danger spinning-btn", "title" => app_lang('clock_in'), "data-inline-loader" => "1", "data-closest-target" => "#js-clock-in-out"));
                echo "<div class='mt5 bg-transparent-white'>" . app_lang('you_are_currently_clocked_out') . "</div>";
            }
            ?>
        </div>
    </div>
</div>