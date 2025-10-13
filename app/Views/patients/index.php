<?php

load_css([
    'assets/css/button.css',
]);

?>
<div id="page-content" class="page-wrapper clearfix">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h4><b><i class="fas fa-user-check"></i> Pacientes</b></h4>
                <div class="d-flex">
                    <a href="<?= site_url('appointments') ?>" class="btn-ghost btn-ghost-primary"><i class="fas fa-arrow-left"></i> Volver</a>
                    <button class="btn-ghost btn-ghost-danger" onclick="openPatientForm()">
                        <i class="fa fa-plus"></i> Nuevo paciente
                    </button>

                </div>
            </div>
            <style>
                #patients-table tbody tr {
                    transition: 0.3s ease;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }

                #patients-table tbody tr:hover {

                    background-color: #f8f9fa;
                }

                #patients-table td {
                    vertical-align: middle;
                }
            </style>
            <div class="table-responsive">
                <table id="patients-table" class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Registro</th>
                            <th>Ver</th>
                            <th>Motivos</th>
                            <th>Extra</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    th:nth-child(7),
    td:nth-child(7),
    th:nth-child(8),
    td:nth-child(8) {
        width: 80px;
        text-align: center;
        vertical-align: middle;
    }
</style>
<!-- Contenedor para los modales -->
<div id="modalContainer"></div>

<script>
    window.loadPatients = function() {
        $.get("<?= get_uri('patients/list_data') ?>", function(res) {
            const table = $('#patients-table').DataTable();
            table.clear().draw();
            res.data.forEach(row => {
                table.row.add(row).draw();
            });
        }, 'json');
    }

    window.openPatientForm = function() {

        $.get("<?= get_uri('patients/new_modal') ?>", function(html) {
            $('#modalContainer').html(html);
            $('#modalPacienteNuevo').modal('show');
        });
    }

    window.openReasonForm = function(id) {
        $('#modalContainer').empty();
        $.get("<?= get_uri('patients/add_reason_modal') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalAddReason'));
            modal.show();
        });
    }


    window.openExtraForm = function(id) {
        $('#modalContainer').empty();
        $.get("<?= get_uri('patients/edit_extra_data_modal') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalExtraData'));
            modal.show();
        });
    }


    window.openDetail = function(id) {
        $('#modalContainer').empty(); // Limpiamos contenedor
        $.get("<?= get_uri('patients/view_modal') ?>", {
            id
        }, function(html) {
            $('#modalContainer').html(html);
            const modal = new bootstrap.Modal(document.getElementById('modalVerPaciente'));
            modal.show();
        });
    }


    window.openEditModal = function(id) {
        $.get("<?= get_uri('patients/edit_modal') ?>", {
            id
        }, function(html) {
            $("#modalContainer").html(html);
            const modal = new bootstrap.Modal(document.getElementById("modalEditPatient"));
            modal.show();
        });
    }

    window.openDeleteModal = function(id) {
        $.get("<?= get_uri('patients/delete_modal') ?>", {
            id
        }, function(html) {
            $("#modalContainer").html(html);
            const modal = new bootstrap.Modal(document.getElementById("modalDeletePatient"));
            modal.show();
        });
    }

    $(document).ready(function() {
        $('#patients-table').DataTable({
            language: {
                url: '<?= base_url('assets/js/datatable/es-ES.json') ?>'
            },
            order: [
                [0, 'desc']
            ],
        });
        loadPatients();
    });
</script>