    flatpickr("#fechaInicio", {
        dateFormat: "Y-m-d",
        defaultDate: new Date(new Date().setDate(new Date().getDate() - 15))
    });

    flatpickr("#fechaFin", {
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
        maxDate: new Date()
    });

    
    <?php if (isset($_GET['user_id'])): ?>

        const clinicId = <?= $_GET['clinic'] ?>;
        const userId = <?= $_GET['user_id'] ?>;

        async function generarGrafico() {
            const inicio = document.getElementById('fechaInicio').value;
            const fin = document.getElementById('fechaFin').value;
            if (!inicio || !fin) return alert('Selecciona ambas fechas');

            try {
                const response = await fetch(`<?= site_url('clockin/getHorasPorDia') ?>?clinic_id=${clinicId}&user_id=${userId}&from=${inicio}&to=${fin}`);
                const json = await response.json();

                if (!json.success || !Array.isArray(json.data)) {
                    console.error('Respuesta inesperada:', json);
                    return alert('No se pudo generar el gráfico');
                }

                const fechas = json.data.map(r => r.fecha);
                const horasTrabajadas = json.data.map(r => r.horas);
                const lineabase = json.data.map(r => r.horas_esperadas || 8);

                const totalHoras = horasTrabajadas.reduce((acc, h) => acc + h, 0).toFixed(2);


                const ctx = document.getElementById('graficoAsistencia').getContext('2d');
                if (window.chartAsistencia) {
                    window.chartAsistencia.destroy(); // destruye gráfico anterior si existe
                }

                window.chartAsistencia = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [{
                                label: 'Horas trabajadas',
                                data: horasTrabajadas,
                                borderColor: 'blue',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                tension: 0.3
                            },
                            {
                                label: 'Meta (Horas Esperadas)',
                                data: lineabase,
                                borderColor: 'green',
                                borderDash: [10, 5],
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 20,
                                title: {
                                    display: true,
                                    text: 'Horas'
                                },
                                ticks: {
                                    stepSize: 2, // Puedes cambiar a 1 o 5 si lo prefieres
                                    callback: function(value) {
                                        return value + 'h'; // Opcional: para mostrar "8h", "10h", etc.
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Fecha'
                                }
                            }
                        }

                    }
                });

                document.getElementById('totalHorasTexto').innerText = `${totalHoras}h`;
                document.getElementById('graficoResumen').style.display = 'block';

            } catch (error) {
                console.error(error);
                alert('Ocurrió un error al cargar los datos del gráfico.');
            }
        }
    <?php endif; ?>

    // Función para procesar datos de asistencia
    function procesarDatosAsistencia(json) {
        if (!json || !json.data || !Array.isArray(json.data)) {
            console.error('Datos de asistencia no válidos:', json);
            return;
        }

        let totalHoras = 0;
        let resumenPorUsuario = {};
        let entradasSalidas = {};
        let fechasConsultadas = new Set();

        json.data.forEach(row => {
            const key = row.nombre;
            if (!resumenPorUsuario[key]) resumenPorUsuario[key] = 0;
            resumenPorUsuario[key] += parseFloat(row.horas);
            totalHoras += parseFloat(row.horas);

            // Agrupar entradas/salidas
            const fecha = row.fecha;
            fechasConsultadas.add(fecha);
            if (!entradasSalidas[fecha]) entradasSalidas[fecha] = {
                entradas: 0,
                salidas: 0
            };

            const registros = row.registros ?? [];
            registros.forEach((r, i) => {
                if (i % 2 === 0) entradasSalidas[fecha].entradas++;
                else entradasSalidas[fecha].salidas++;
            });
        });

        // Render resumen total
        const resumenHoras = document.getElementById('resumenHoras');
        if (resumenHoras) {
            const resumenOrdenado = Object.entries(resumenPorUsuario).sort((a, b) => b[1] - a[1]);
            let resumenHTML = `
            <li><b>Total horas trabajadas por todos:</b> ${totalHoras.toFixed(2)}h</li>
            <li><b>Usuario con más asistencia:</b> ${resumenOrdenado[0][0]} (${resumenOrdenado[0][1].toFixed(2)}h)</li>
            <li><b>Usuario con menos asistencia:</b> ${resumenOrdenado.at(-1)[0]} (${resumenOrdenado.at(-1)[1].toFixed(2)}h)</li>
        `;
            resumenHoras.innerHTML = resumenHTML;
        }

        // Render entradas/salidas por fecha
        const tablaES = document.getElementById("tablaEntradasSalidas");
        if (tablaES) {
            tablaES.innerHTML = '';
            for (const [fecha, valores] of Object.entries(entradasSalidas)) {
                tablaES.innerHTML += `
                <tr>
                    <td>${fecha}</td>
                    <td>${valores.entradas}</td>
                    <td>${valores.salidas}</td>
                </tr>
            `;
            }
        }

        // Render faltas
        const tablaFaltas = document.getElementById("tablaFaltas");
        if (tablaFaltas) {
            tablaFaltas.innerHTML = '';
            const fromDate = new Date(document.getElementById("asistenciaDesde").value);
            const toDate = new Date(document.getElementById("asistenciaHasta").value);
            for (let d = new Date(fromDate); d <= toDate; d.setDate(d.getDate() + 1)) {
                const dStr = d.toISOString().split('T')[0];
                const falto = !fechasConsultadas.has(dStr);
                tablaFaltas.innerHTML += `
                <tr>
                    <td>${dStr}</td>
                    <td>${falto ? '❌' : '✅'}</td>
                </tr>
            `;
            }
        }
    }