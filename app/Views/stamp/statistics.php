<!-- File: app/Views/stamp/statistics.php -->
<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="col-md-12">
        <div class="card">
            <div class="card-title d-flex align-items-center">
                <a href="<?= site_url('stamp/main') ?>" style="margin-left:20px !important;" class="text-decoration-none fs-3 me-3 pl-5" aria-label="Volver">
                    <i class="fas fa-arrow-left ml-5"></i>
                </a>
                <div class="text-center flex-grow-1">
                    <h3>
                        <span class="badge badge-primary">Estadisticas</span>
                        Modulo de Timbres
                    </h3>
                </div>
            </div>
            <style>
                .border-danger{
                    border: 1px solid #eee !important;
                }
            </style>
            <div class="card-body">
                <div class="container py-4">
                    <!-- Clínicas Rubymed -->
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fas fa-building"></i> Clínicas Rubymed</h4>
                            <table id="tableClinicsRubymed" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Clínica</th>
                                        <th>Timbres Generados</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div style="max-width:400px; max-height:800px; margin:auto;">
                                <canvas id="chartClinicsRubymed"></canvas>
                            </div>
                            <div class="row mt-3" id="cardsNoStampsRubymed"></div>
                        </div>
                    </div>
                    <!-- Tarjetas Clínicas Rubymed sin timbres -->

                    <hr>
                    <!-- Clínicas Aliadas -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h4><i class="fas fa-handshake"></i> Clínicas Aliadas</h4>
                            <table id="tableClinicsAliadas" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Clínica</th>
                                        <th>Timbres Generados</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div style="max-width:400px; max-height:800px; margin:auto;">
                                <canvas id="chartClinicsAliadas"></canvas>
                            </div>
                                                <!-- Tarjetas Clínicas Aliadas sin timbres -->
                    <div class="row mt-3" id="cardsNoStampsAliadas"></div>
                        </div>
                    </div>

                    <hr>
                    <!-- Providers -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h4><i class="fas fa-signature"></i> Detalle por Provider</h4>
                            <table id="tableProviders" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Timbres Firmados</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div style="max-width:400px; max-height:800px; margin:auto;">
                                <canvas id="chartProviders"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Providers
        fetch('<?= site_url("stamp/providerStats") ?>')
            .then(r => r.json())
            .then(data => {
                data.sort((a, b) => b.stamps_signed - a.stamps_signed);
                const names = data.map(x => x.provider_name);
                const counts = data.map(x => x.stamps_signed);
                new Chart(document.getElementById('chartProviders'), {
                    type: 'bar',
                    data: {
                        labels: names,
                        datasets: [{
                            label: 'Firmados',
                            data: counts
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                const tbody = document.querySelector('#tableProviders tbody');
                data.forEach(r => tbody.insertAdjacentHTML('beforeend', `<tr><td>${r.provider_name}</td><td>${r.stamps_signed}</td></tr>`));
            });

        // Clínicas Rubymed
        fetch('<?= site_url("stamp/clinicStatsRubymed") ?>')
            .then(r => r.json())
            .then(data => {
                const positives = data.filter(r => r.stamps_generated > 0);
                const zeros = data.filter(r => r.stamps_generated == 0);
                // Tabla y gráfico
                const names = positives.map(x => x.clinic_name);
                const counts = positives.map(x => x.stamps_generated);
                new Chart(document.getElementById('chartClinicsRubymed'), {
                    type: 'pie',
                    data: {
                        labels: names,
                        datasets: [{
                            label: 'Generados',
                            data: counts
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                const tbody = document.querySelector('#tableClinicsRubymed tbody');
                positives.forEach(r => tbody.insertAdjacentHTML('beforeend', `<tr><td>${r.clinic_name}</td><td>${r.stamps_generated}</td></tr>`));
                // Tarjetas para zeros
                const cardContainer = document.getElementById('cardsNoStampsRubymed');
                zeros.forEach(r => {
                    const card = `
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h5 class="card-title">${r.clinic_name}</h5>
                                    <p class="card-text text-danger">0 Timbres</p>
                                </div>
                            </div>
                        </div>
                    `;
                    cardContainer.insertAdjacentHTML('beforeend', card);
                });
            });

        // Clínicas Aliadas
        fetch('<?= site_url("stamp/clinicStatsAliadas") ?>')
            .then(r => r.json())
            .then(data => {
                const positives = data.filter(r => r.stamps_generated > 0);
                const zeros = data.filter(r => r.stamps_generated == 0);
                // Tabla y gráfico
                const names = positives.map(x => x.clinic_name);
                const counts = positives.map(x => x.stamps_generated);
                new Chart(document.getElementById('chartClinicsAliadas'), {
                    type: 'pie',
                    data: {
                        labels: names,
                        datasets: [{
                            label: 'Generados',
                            data: counts
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                const tbody = document.querySelector('#tableClinicsAliadas tbody');
                positives.forEach(r => tbody.insertAdjacentHTML('beforeend', `<tr><td>${r.clinic_name}</td><td>${r.stamps_generated}</td></tr>`));
                // Tarjetas para zeros
                const cardContainer = document.getElementById('cardsNoStampsAliadas');
                zeros.forEach(r => {
                    const card = `
                        <div class="col-sm-6 col-md-4 mb-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h6 class="card-title">${r.clinic_name}</h6>
                                    <p class="card-text text-warning">0 Timbres</p>
                                </div>
                            </div>
                        </div>
                    `;
                    cardContainer.insertAdjacentHTML('beforeend', card);
                });
            });
    </script>
</div>