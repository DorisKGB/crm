<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($clinic->name); ?> - Monitoreo en Vivo</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- hls.js -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <style>
        body, html {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #000;
        }
        video {
            border: 2px solid #222;
            background: #000;
            width: 100%;
        }
        #control-bar {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #1a1a1a;
            color: white;
            padding: 10px;
            text-align: center;
            z-index: 999;
        }
        #loader-overlay {
            position: fixed;
            z-index: 2000;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div id="loader-overlay">
        <div class="spinner-border text-light mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p>Iniciando servicios de cámaras...</p>
    </div>

    <div class="container-fluid" style="padding-bottom: 80px;">
        <div class="row">
            <?php foreach ($cameras->getResult() as $index => $camera): ?>
                <?php if ($camera->status == 1): ?>
                    <?php
                        $camId = "cam{$index}";
                        $streamBase = "https://stream.clinicahispanarubymed.com";
                        // Para Hikvision: {slug}.m3u8. Para Annke: {slug}_annke.m3u8
                        $streamUrl = (strtolower($camera->type) === 'hikvision')
                            ? "$streamBase/hls/clinica{$camera->clinic_id}_{$camera->name}.m3u8"
                            : "$streamBase/hls/clinica{$camera->clinic_id}_{$camera->name}_annke.m3u8";
                    ?>
                    <div class="col-md-6 mb-4">
                        <video
                            id="<?= $camId ?>"
                            controls
                            muted
                            autoplay
                            crossorigin="anonymous"
                            data-url="<?= $streamUrl ?>"
                        ></video>
                        <div class="text-center mt-1">
                            <button class="btn btn-warning btn-sm d-none"
                                onclick="reiniciarCamara('clinica<?= $camera->clinic_id ?>_<?= $camera->name ?>')">
                                🔁 Reiniciar
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="control-bar">
        <button class="btn btn-warning me-3" onclick="reiniciarTodo()">🔁 Reiniciar Todo</button>
        <button class="btn btn-danger" onclick="detenerDirecto()">🛑 Detener Directo en Vivo</button>
    </div>

    <!-- Modal de carga al detener -->
    <div class="modal fade" id="stopLoaderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white text-center p-4 d-flex justify-content-center">
                <div class="spinner-border text-light mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0" id="loaderMessage">Procesando...</p>
            </div>
        </div>
    </div>

    <script>
        let stopSent = false;
        const user_id = <?= json_encode($user_id) ?>;
        const clinic = <?= json_encode('clinica' . $clinic->id) ?>;

        // Llamada genérica a la API
        function callAPI(method, data) {
            return fetch("https://stream.clinicahispanarubymed.com/api/" + method, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            }).then(res => {
                if (!res.ok) throw new Error("API error: " + res.status);
                return res.json().catch(() => ({}));
            });
        }

        function initStreams() {
            document.querySelectorAll('video[data-url]').forEach(video => {
                const url = video.getAttribute('data-url');
                console.log(`▶️ Init HLS for ${video.id}`, url);

                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.on(Hls.Events.ERROR, (event, data) => console.error('HLS error', data));
                    hls.loadSource(url);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(e => console.error(e)));
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    // Navegadores con soporte HLS nativo (Safari)
                    video.src = url;
                    video.addEventListener('error', e => console.error('Video error', e));
                    video.play().catch(e => console.error(e));
                } else {
                    console.error('Este navegador no soporta HLS.');
                }
            });
        }

        window.addEventListener("load", () => {
            console.log("🟢 Window load fired", { clinic, user_id });
            callAPI("start", { clinic, user_id })
                .then(res => {
                    console.log("✅ start API response:", res);
                    document.getElementById("loader-overlay").style.display = "none";
                    initStreams();
                })
                .catch(err => {
                    console.error("❌ Error start API:", err);
                    document.getElementById("loader-overlay").style.display = "none";
                });
        });

        // Al cerrar ventana (garantizar detención)
        window.addEventListener("beforeunload", () => {
            if (!stopSent) {
                const data = JSON.stringify({ clinic, user_id });
                const blob = new Blob([data], { type: 'application/json' });
                navigator.sendBeacon("https://stream.clinicahispanarubymed.com/api/stop", blob);
                stopSent = true;
            }
        });

        // Botón para detener transmisión correctamente
        function detenerDirecto() {
            const modal = new bootstrap.Modal(document.getElementById('stopLoaderModal'));
            document.getElementById('loaderMessage').innerText = "Deteniendo transmisión en vivo...";
            modal.show();

            callAPI("stop", { clinic, user_id }).then(() => {
                stopSent = true;
                modal.hide();
                try {
                    window.close(); // Solo funciona si fue abierta con window.open()
                } catch (e) {
                    alert("Transmisión detenida. Puedes cerrar esta pestaña.");
                }
            }).catch(() => {
                modal.hide();
                alert("No se pudo detener la transmisión.");
            });
        }

        // Botón para reiniciar cámara específica
        function reiniciarCamara(servicio) {
            const modal = new bootstrap.Modal(document.getElementById('stopLoaderModal'));
            document.getElementById('loaderMessage').innerText = "Reiniciando cámara...";
            modal.show();
            callAPI("restart-one", { service: servicio }).then(() => {
                modal.hide();
            }).catch(() => {
                modal.hide();
                alert("Error reiniciando cámara.");
            });
        }

        // Botón para reiniciar todo
        function reiniciarTodo() {
            const modal = new bootstrap.Modal(document.getElementById('stopLoaderModal'));
            document.getElementById('loaderMessage').innerText = "Reiniciando servicios de cámaras...";
            modal.show();
            callAPI("restart", { clinic }).then(() => {
                modal.hide();
                location.reload();
            }).catch(() => {
                modal.hide();
                alert("Error reiniciando cámaras.");
            });
        }
    </script>
</body>
</html>
