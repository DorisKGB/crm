<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card" style="min-height:90vh;">
        <div class="card-title text-center">
            <h3 class="d-flex align-items-center justify-content-center">
                <i class="fas fa-check-circle text-success fa-2x"></i>
                <span class="badge badge-success ms-2">Timbre Solicitado</span>
            </h3>
            <h5>Ahora notifícale a tu Family Nurse Practitioner que revise y apruebe el timbre.</h5>
            <hr>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Preview de la plantilla -->
                <div class="col-md-4 text-center">
                    <h5>Vista previa</h5>
                    <img
                        src="<?= site_url($stamp->template_image) ?>"
                        alt="Plantilla timbre #<?= $stamp->id ?>"
                        class="img-fluid img-thumbnail" width="60%" />
                </div>

                <!-- Detalles -->
                <div class="col-md-8">
                    <h5>Detalles del Timbre</h5>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td><?= esc($stamp->id) ?></td>
                            </tr>
                            <tr>
                                <th>Clínica</th>
                                <td><?= esc($stamp->clinic_select) ?></td>
                            </tr>
                            <tr>
                                <th>Tamaño de página</th>
                                <td><?= esc($stamp->page_size) ?></td>
                            </tr>
                            <tr>
                                <th>Nombre plantilla</th>
                                <td><?= esc($stamp->template_name) ?></td>
                            </tr>
                            <tr>
                                <th>Descripción</th>
                                <td><?= nl2br(esc($stamp->description)) ?></td>
                            </tr>
                            <tr>
                                <th>Coordenadas firma</th>
                                <td>X: <?= esc($stamp->signature_x) ?>, Y: <?= esc($stamp->signature_y) ?></td>
                            </tr>
                            <tr>
                                <th>Generado por</th>
                                <td><?= esc($stamp->generate_name) ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de creación</th>
                                <td><?= date('d/m/Y H:i', strtotime($stamp->created_at)) ?></td>
                            </tr>
                            <tr>
                                <th>Token</th>
                                <td><code><?= esc($stamp->token) ?></code></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Acciones -->
                    <div class="mt-4">
                        <a
                            href="<?= site_url('stamp/type_request') ?>"
                            class="btn-rubymed btn-rubymed-success me-2">
                            <i class="fas fa-arrow-left"></i>
                            Volver a Timbrar
                        </a>
                        <a
                            href="<?= site_url('stamp/stamp_v1') ?>"
                            class="btn-rubymed btn-rubymed-primary">
                            <i class="fas fa-signature"></i> Lista de Timbres
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>