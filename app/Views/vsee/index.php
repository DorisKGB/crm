
<div id="page-content" class="page-wrapper clearfix todo-page">
    <?php
    load_css([
        'assets/css/button.css',
    ]);
    ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3>
                        <b> Vsee </b>
                    </h3>
                </div>
                <div class="d-flex">
                    <a href="<?= site_url('vsee/dashboard') ?>" class="btn-button btn-button-outline-success">
                        <i class="fas fa-video"></i> Vsee
                    </a>

                    <a href="<?= site_url('vseeusers/') ?>" class="btn-button btn-button-outline-danger">
                        <i class="fas fa-user"></i> Usuarios
                    </a>
                </div>
            </div>
        </div>
        <style>
            /* sombra muy discreta */
            .box-shadow-sm {
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
                border-radius: 6px;
                /* opcional, luce mejor */
                padding: 1rem;
                /* separa el texto del borde */
                background: #fff;
                /* para que la sombra se note sobre fondos claros */
                border-radius: 15px;
            }

            .card-timer {
                position: absolute;
                top: 8px;
                right: 12px;
                background: #fff;
                padding: 4px 10px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 2rem;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
                z-index: 2;
            }
        </style>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">

                    <div class="cards-container">
                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">

                            <div class="col-md-12">
                                <div class="box-shadow-sm">

                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Louisville</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Osmani</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light" data-bs-toggle="modal"
                                            data-bs-target="#commentModal"
                                            data-comment="El paciente refirió dolor leve.">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>

                            </div>
                        </div>


                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">

                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <!-- Cronómetro -->
                                    <span class="card-timer" data-countdown="2025-07-05T22:00:00-05:00"></span>

                                    <p><b><i class="fas fa-user"></i> Fecha</b>: sábado, 5 de julio de 2025 a las 10:00 p. m</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Aurora</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-warning"><b><i class="fas fa-check-double"></i> Estado</b>:Pendiente <img src="<?= base_url("assets/images/pendiente.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                        <button class="btn-button btn-button-purple">
                                            <i class="fas fa-video"></i> Unirme a la consulta
                                        </button>

                                    </p>
                                </div>
                            </div>
                        </div>


                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">

                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Hobbs</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>


                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">
                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Louisville</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="col-md-6">

                    <div class="cards-container">
                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">
                            <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">

                                <div class="col-md-12">
                                    <div class="box-shadow-sm">
                                        <span class="card-timer" data-countdown="2025-07-04T22:00:00-05:00"></span>
                                        <p><b><i class="fas fa-user"></i> Fecha</b>: hoy, 4 de julio de 2025 a las 10:00 p. m</p>
                                        <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Aurora</span> </p>
                                        <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                        <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                        <p class="text-warning"><b><i class="fas fa-check-double"></i> Estado</b>:Pendiente <img src="<?= base_url("assets/images/pendiente.png"); ?>" width="30" alt=""></p>
                                        <p>
                                            <button class="btn-button btn-button-light" data-bs-toggle="modal"
                                                data-bs-target="#commentModal"
                                                data-comment="El paciente refirió dolor leve.">
                                                <i class="fas fa-comment"></i> Ver Comentarios
                                            </button>
                                            <button class="btn-button btn-button-purple">
                                                <i class="fas fa-video"></i> Unirme a la consulta
                                            </button>
                                            <button class="btn-button btn-button-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-id="70"
                                                data-title="Visita del 5‑Jul‑2025 10 pm">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                            <button class="btn-button btn-button-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rescheduleModal"
                                                data-id="70"
                                                data-title="Visita del 5‑Jul‑2025 10 pm"
                                                data-datetime="2025-07-05T22:00">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Louisville</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>


                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">

                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Hobbs</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="row list-stamp mb-4" id="cardStamp-70" style="position: relative;">
                            <div class="col-md-12">
                                <div class="box-shadow-sm">
                                    <p><b><i class="fas fa-user"></i> Fecha</b>: 10:44pm a las 20 mayo 2002</p>
                                    <p><b><i class="fas fa-home"></i> Clinica</b>: <span>Louisville</span> </p>
                                    <p><b><i class="fas fa-user"></i> Paciente</b>: <span>Julio Rafael Melgar</span> </p>
                                    <p><b><i class="fas fa-user"></i> Provider</b>: <span>Telemedicine Provider</span> </p>
                                    <p class="text-success"><b><i class="fas fa-check-double"></i> Estado</b>:Finalizada <img src="<?= base_url("assets/images/check.png"); ?>" width="30" alt=""></p>
                                    <p>
                                        <button class="btn-button btn-button-light">
                                            <i class="fas fa-comment"></i> Ver Comentarios
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="Paginación de timbres" class="d-flex justify-content-end my-3">
                        <ul id="stampPagination" class="pagination ">
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-left"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                        </ul>
                    </nav>

                </div>
            </div>
        </div>

        <!-- ========== MODAL COMENTARIO ========== -->
        <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commentModalLabel"><i class="fas fa-comment me-2"></i>Comentario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p id="commentContent" class="mb-0"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== MODAL CONFIRMAR ELIMINACIÓN ========== -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-trash me-2"></i>Eliminar visita</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-semibold mb-2" id="deleteWhat"></p>
                        <p class="text-muted mb-0">Esta acción no se puede deshacer. ¿Seguro que deseas continuar?</p>
                        <!-- campo oculto para enviar al backend -->
                        <input type="hidden" id="deleteId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-button btn-button-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn-button btn-button-danger" id="confirmDeleteBtn">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== MODAL REPROGRAMAR CITA ========== -->
        <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="rescheduleModalLabel">
                            <i class="fas fa-calendar-alt me-2"></i>Reprogramar cita
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="fw-semibold mb-2" id="rescheduleWhat"></p>

                        <div class="mb-3">
                            <label for="newDateTime" class="form-label">Nueva fecha y hora</label>
                            <input type="datetime-local" class="form-control" id="newDateTime" required>
                        </div>

                        <!-- Campos ocultos -->
                        <input type="hidden" id="rescheduleId">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-button btn-button-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn-button btn-button-primary" id="confirmRescheduleBtn">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* ----- Modal Comentario ----- */
            const commentModal = document.getElementById('commentModal');
            commentModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget; // botón que abrió el modal
                const comment = button.getAttribute('data-comment') || 'Sin comentarios';
                document.getElementById('commentContent').textContent = comment;
            });

            /* ----- Modal Eliminar ----- */
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                // Rellenamos los campos ocultos/textos
                document.getElementById('deleteId').value = button.getAttribute('data-id');
                document.getElementById('deleteWhat').textContent = button.getAttribute('data-title');
            });

            // Cuando el usuario confirma la eliminación
            document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                const id = document.getElementById('deleteId').value;

                /* ---- Ejemplo de petición AJAX ---- */
                fetch(`<?= base_url('visitas/eliminar'); ?>/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(resp => resp.ok ? resp.json() : Promise.reject(resp))
                    .then(json => {
                        // Elimina la tarjeta de la interfaz
                        const row = document.querySelector(`[data-id-row="${id}"]`);
                        if (row) row.remove();

                        // Oculta el modal
                        bootstrap.Modal.getInstance(deleteModal).hide();
                    })
                    .catch(err => {
                        alert('No se pudo eliminar. Intenta de nuevo.');
                        console.error(err);
                    });
            });

            /* ----- Mostrar modal y precargar datos ----- */
            const rescheduleModal = document.getElementById('rescheduleModal');
            rescheduleModal.addEventListener('show.bs.modal', event => {
                const btn = event.relatedTarget;
                const id = btn.getAttribute('data-id');
                const title = btn.getAttribute('data-title');
                const dt = btn.getAttribute('data-datetime');

                // Rellenamos campos
                document.getElementById('rescheduleId').value = id;
                document.getElementById('rescheduleWhat').textContent = title;
                document.getElementById('newDateTime').value = dt;
            });

            /* ----- Guardar la nueva fecha/hora ----- */
            document.getElementById('confirmRescheduleBtn').addEventListener('click', () => {
                const id = document.getElementById('rescheduleId').value;
                const newDT = document.getElementById('newDateTime').value;

                if (!newDT) {
                    alert('Selecciona una fecha y hora válidas');
                    return;
                }

                fetch(`<?= base_url('visitas/reprogramar'); ?>/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            datetime: newDT
                        })
                    })
                    .then(resp => resp.ok ? resp.json() : Promise.reject(resp))
                    .then(json => {
                        // ⬇️  Actualiza la UI sin recargar
                        const btnOrigin = document.querySelector(`[data-id="${id}"][data-bs-target="#rescheduleModal"]`);
                        if (btnOrigin) {
                            btnOrigin.setAttribute('data-datetime', newDT); // guarda la nueva fecha
                        }

                        // Si tienes un cronómetro o texto con fecha, actualízalo aquí…

                        bootstrap.Modal.getInstance(rescheduleModal).hide();
                    })
                    .catch(err => {
                        alert('No se pudo reprogramar. Intenta de nuevo.');
                        console.error(err);
                    });
            });

        });

        (function() {
            // formatea a HH : MM : SS
            function fmt(n) {
                return n.toString().padStart(2, '0');
            }

            // actualiza todos los cronómetros cada segundo
            function updateTimers() {
                document.querySelectorAll('.card-timer').forEach(function(el) {
                    const target = new Date(el.dataset.countdown);
                    const now = new Date();
                    let diff = Math.max(0, target - now); // ms restantes, nunca negativo
                    const h = Math.floor(diff / 36e5);
                    diff -= h * 36e5;
                    const m = Math.floor(diff / 6e4);
                    diff -= m * 6e4;
                    const s = Math.floor(diff / 1e3);
                    el.textContent = fmt(h) + ' : ' + fmt(m) + ' : ' + fmt(s);
                });
            }

            updateTimers(); // primer cálculo inmediato
            setInterval(updateTimers, 1000); // luego cada segundo
        })();
    </script>
</div>



