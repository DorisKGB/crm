<script src="https://cdn.jsdelivr.net/npm/flv.js@latest/dist/flv.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card">
        <div class="page-title clearfix notes-page-title">
            <h1><span class="blinking-btn"></span> Control de Monitoreo <b> <?php echo $clinic->name ?> </b></h1>
        </div>

        <style>
            @keyframes blink {
                0% { background-color: red; }
                50% { background-color: transparent; }
                100% { background-color: red; }
            }
            .blinking-btn {
                display: inline-block;
                animation: blink 1s infinite;
                border-radius: 50%;
                width: 10px;
                height: 10px;
                margin-right: 5px;
            }
        </style>

        <div class="container-fluid">
            <div class="row">
                <?php foreach ($cameras->getResult() as $index => $camera): ?>
                    <?php if($camera->status == 1){ ?>
                    <div class="col-md-6 mb-4">
                        <div class="mb-2">
                            <span class="badge bg-secondary" style="font-size: 20px;">
                                <span class="blinking-btn"></span> <?= esc($camera->labels) ?>
                            </span>
                        </div>

                        <?php
                            $camId = "cam{$index}";
                            $streamBase = "https://stream.clinicahispanarubymed.com";
                            if (strtolower($camera->type) === 'hikvision') {
                                $streamUrl = "$streamBase/hls/clinica{$camera->clinic_id}_{$camera->name}.m3u8";
                            } else {
                                $streamUrl = "$streamBase:8443/live/clinica{$camera->clinic_id}_{$camera->name}.flv";
                            }
                        ?>

                        <video id="<?= $camId ?>" width="100%" controls muted autoplay crossorigin="anonymous"></video>

                        <script>
                        (function () {
                            const camId = '<?= $camId ?>';
                            const type = '<?= strtolower($camera->type) ?>';
                            const url = '<?= $streamUrl ?>';
                            const video = document.getElementById(camId);

                            if (type === 'hikvision') {
                                if (Hls.isSupported()) {
                                    const hls = new Hls();
                                    hls.loadSource(url);
                                    hls.attachMedia(video);
                                    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
                                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                    video.src = url;
                                    video.addEventListener('loadedmetadata', () => video.play());
                                } else {
                                    video.insertAdjacentHTML('afterend', '<p>Tu navegador no soporta HLS.</p>');
                                }
                            } else {
                                if (flvjs.isSupported()) {
                                    const flvPlayer = flvjs.createPlayer({ type: 'flv', url: url });
                                    flvPlayer.attachMediaElement(video);
                                    flvPlayer.load();
                                    flvPlayer.play();
                                } else {
                                    video.insertAdjacentHTML('afterend', '<p>Tu navegador no soporta FLV.</p>');
                                }
                            }
                        })();
                        </script>
                    </div>
                    <? } ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
