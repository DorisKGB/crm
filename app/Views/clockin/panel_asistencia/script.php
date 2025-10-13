<script>
    flatpickr("#asistenciaDesde", {
        dateFormat: "Y-m-d",
        defaultDate: new Date()
    });
    flatpickr("#asistenciaHasta", {
        dateFormat: "Y-m-d",
        defaultDate: new Date()
    });

    async function cargarResumenAsistencia() {
        showLoading();
        const desde = document.getElementById('asistenciaDesde').value;
        const hasta = document.getElementById('asistenciaHasta').value;
        const clinicId = <?= $_GET['clinic'] ?? 'null' ?>;

        if (!desde || !hasta) return alert("Selecciona ambas fechas");
        if (!clinicId) return alert("No se ha seleccionado una clínica");

        try {
            const response = await fetch(
                `<?= site_url("clockin/getResumenAsistenciaPorClinica") ?>?clinic_id=${clinicId}&from=${desde}&to=${hasta}`
            );
            const json = await response.json();

            const tabla = document.getElementById("tablaResumenAsistencia");
            const tbody = document.getElementById("bodyResumenAsistencia");
            tbody.innerHTML = '';

            if (json.success && json.data.length > 0) {
                tabla.style.display = 'table';

                let index = 1;
                let resumenPorUsuario = {};
                let entradasSalidas = {};
                let fechasConsultadas = new Set();

                json.data.forEach(row => {
                    const fechaFormateada = new Date(row.fecha).toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    let avatarUrl = "<?= base_url("assets/images/avatar.jpg") ?>"; // valor por defecto

                    if (row.foto === "system_bot" || row.foto === "bitbucket" || row.foto === "github") {
                        avatarUrl = getAvatarJS(row.foto);
                    } else if (typeof row.foto === 'string' && row.foto.startsWith('a:')) {
                        const imageObj = parseSerializedPHP(row.foto);
                        if (imageObj && imageObj.file_name) {
                            avatarUrl = "<?= base_url(get_setting('profile_image_path')) ?>/" + imageObj.file_name;
                        }
                    } else if (typeof row.foto === 'string' && row.foto.length > 0) {
                        avatarUrl = "<?= base_url(get_setting('profile_image_path')) ?>/" + row.foto;
                    }


                    // Render tabla principal
                    const eficiencia = row.eficiencia || 0;
                    const eficienciaColor = eficiencia >= 100 ? 'text-success' : eficiencia >= 80 ? 'text-warning' : 'text-danger';
                    
                    tbody.innerHTML += `
                    <tr class="text-center">
                        <td>${index++}</td>
                        <td><img src="${avatarUrl}" class="rounded-circle" width="40"></td>
                        <td>${row.nombre}</td>
                        <td>${row.rol}</td>
                        <td><b>${row.horas}h</b></td>
                        <td><b>${row.horas_esperadas || 0}h</b></td>
                        <td><span class="${eficienciaColor}"><b>${eficiencia}%</b></span></td>
                    </tr>
                `;

                    // Resumen por usuario
                    if (!resumenPorUsuario[row.nombre]) resumenPorUsuario[row.nombre] = 0;
                    resumenPorUsuario[row.nombre] += parseFloat(row.horas);

                    // Entradas y salidas por fecha
                    if (!entradasSalidas[row.fecha]) entradasSalidas[row.fecha] = {
                        entradas: 0,
                        salidas: 0
                    };
                    entradasSalidas[row.fecha].entradas += 1;
                    entradasSalidas[row.fecha].salidas += 1;

                    // Fechas únicas
                    fechasConsultadas.add(row.fecha);
                });

                // Render resumen
                const resumen = Object.entries(resumenPorUsuario).sort((a, b) => b[1] - a[1]);
                const resumenHoras = document.getElementById('resumenHoras');
                
                // Calcular total de horas esperadas
                const totalHorasEsperadas = json.data.reduce((sum, user) => sum + (user.horas_esperadas || 0), 0);
                const totalHorasTrabajadas = Object.values(resumenPorUsuario).reduce((a, b) => a + b, 0);
                const eficienciaGeneral = totalHorasEsperadas > 0 ? ((totalHorasTrabajadas / totalHorasEsperadas) * 100).toFixed(2) : 0;
                
                resumenHoras.innerHTML = `
                <li><b>Total horas trabajadas por todos:</b> ${totalHorasTrabajadas.toFixed(2)}h</li>
                <li><b>Total horas esperadas:</b> ${totalHorasEsperadas.toFixed(2)}h</li>
                <li><b>Eficiencia general:</b> ${eficienciaGeneral}%</li>
                <li><b>Usuario con más asistencia:</b> ${resumen[0][0]} (${resumen[0][1].toFixed(2)}h)</li>
                <li><b>Usuario con menos asistencia:</b> ${resumen.at(-1)[0]} (${resumen.at(-1)[1].toFixed(2)}h)</li>
            `;

                // Render entradas/salidas
                const tablaES = document.getElementById("tablaEntradasSalidas");
                tablaES.innerHTML = '';
                for (const [fecha, datos] of Object.entries(entradasSalidas)) {
                    tablaES.innerHTML += `
                    <tr>

                        <td>${datos.entradas}</td>
                        <td>${datos.salidas}</td>
                    </tr>
                `;
                }

                // Render faltas
                const tablaFaltas = document.getElementById("tablaFaltas");
                tablaFaltas.innerHTML = '';
                const fromDate = new Date(desde);
                const toDate = new Date(hasta);
                for (let d = new Date(fromDate); d <= toDate; d.setDate(d.getDate() + 1)) {
                    const dia = d.toISOString().split('T')[0];
                    const falto = !fechasConsultadas.has(dia);
                    tablaFaltas.innerHTML += `
                    <tr>
                        <td>${dia}</td>
                        <td>${falto ? '❌' : '✅'}</td>
                    </tr>
                `;
                }

            } else {
                tabla.style.display = 'none';
                alert("No hay registros en el rango seleccionado.");
            }
            hideLoading();
        } catch (error) {
            console.error("Error al cargar resumen de asistencia:", error);
            alert("Error en la solicitud.");
            hideLoading();
        }
    }

    // Manejar pestañas
    document.addEventListener("DOMContentLoaded", function() {
        const tabs = document.querySelectorAll('#attendanceTabs .nav-link');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover clase active de todas las pestañas
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(tc => tc.style.display = 'none');
                
                // Agregar clase active a la pestaña clickeada
                this.classList.add('active');
                
                // Mostrar contenido correspondiente
                const tabName = this.getAttribute('data-tab');
                const targetContent = document.getElementById(tabName + 'Tab');
                if (targetContent) {
                    targetContent.style.display = 'block';
                    
                    // Inicializar el mapa de calor si es necesario
                    if (tabName === 'heatmap') {
                        // Inicializar el mapa de calor si la función existe
                        if (typeof initializeHeatmap === 'function') {
                            initializeHeatmap();
                        }
                    }
                }
            });
        });

        // Espera un poco para asegurar que flatpickr haya aplicado los valores
        setTimeout(cargarResumenAsistencia, 100);
    });


    function getAvatarJS(image) {
        if (image === "system_bot") {
            return "<?= base_url("assets/images/avatar-bot.jpg") ?>";
        } else if (image === "bitbucket") {
            return "<?= base_url("assets/images/bitbucket_logo.png") ?>";
        } else if (image === "github") {
            return "<?= base_url("assets/images/github_logo.png") ?>";
        } else if (typeof image === 'object' && image.file_name) {
            return "<?= base_url(get_setting('profile_image_path')) ?>/" + image.file_name;
        } else if (typeof image === 'string' && image.length > 0) {
            return "<?= base_url(get_setting('profile_image_path')) ?>/" + image;
        } else {
            return "<?= base_url("assets/images/avatar.jpg") ?>";
        }
    }

    function parseSerializedPHP(str) {
        try {
            // Maneja strings como: a:1:{s:9:"file_name";s:29:"_file683794a468135-avatar.png";}
            const match = str.match(/s:\d+:"([^"]+\.(jpg|jpeg|png|gif))"/i);
            return match ? {
                file_name: match[1]
            } : null;
        } catch (e) {
            return null;
        }
    }

</script>
