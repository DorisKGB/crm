<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="">


        <div id="page-content" class="page-wrapper clearfix">
            <div class="">
                <div class="container-fluid">
                    <div class="row">
                        <?php foreach ($cameras as $camera) { ?>
                            <div class="col-md-3">
                                <div class="bg-white p-4" style="width: 18rem; border-radius:20px;">
                                    <div class="text-center">
                                        <h5 class="card-title mb-4"><?php echo $camera->name; ?></h5>
                                        <div>
                                            <span class="card-text p-3 text-info" style="font-size: 25px;border-radius:50%;"><i class="fas fa-video"></i></span>
                                        </div>
                                        <a href="javascript:void(0);" class="btn-rubymed btn-rubymed-info text-center mt-4" onclick="abrirMonitoreo('<?php echo $camera->id; ?>')">
                                            <span class="blinking-btn"></span><?php echo app_lang('direct_view') ?>
                                        </a>
                                          <?php if ($camera->connect_dvr): ?>
                                            <a href="https://stream.clinicahispanarubymed.com/dvr/clinica<?php echo $camera->id; ?>/" target="_blank" class="btn-rubymed btn-rubymed-success text-center mt-4" >
                                                <i class="fas fa-cog"></i> dvr
                                            </a>
                                        <?php endif; ?>
                                        <!--<a href="camera/view/<?php echo $camera->id; ?>" class="btn btn-primary text-center"><span class="blinking-btn"></span><?php echo app_lang('direct_view') ?></a>-->
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <style>
        @keyframes blink {
            0% {
                background-color: red;
            }

            50% {
                background-color: transparent;
            }

            100% {
                background-color: red;
            }
        }

        .blinking-btn {
            display: inline-block;
            animation: blink 1s infinite;
            border-radius: 50%;
            width: 10px;
            height: 10px;
            text-align: center !important;
            margin-right: 5px;
        }
    </style>
    <script>
        function abrirMonitoreo(clinicId) {
            const width = screen.availWidth;
            const height = screen.availHeight;
            const base = "<?= base_url('index.php/camera/live/') ?>";
            const win = window.open(base + clinicId, '_blank', `width=${width},height=${height},fullscreen=yes`);
            win.focus();
        }
    </script>


</div>