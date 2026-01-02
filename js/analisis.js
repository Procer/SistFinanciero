$(document).ready(function() {
    // Toggle para el menú lateral (si se necesita en esta página)
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    // --- INICIO: Nueva Lógica de Análisis de Cuenta ---

    let myPieChart = null; // Variable para mantener la instancia del gráfico

    // Función para dar formato a los números
    function formatNumber(num) {
        if (num === null || num === undefined) {
            num = 0;
        }
        let parts = parseFloat(num).toFixed(2).toString().split('.');
        let integerPart = parts[0];
        let decimalPart = parts[1];
        
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        return integerPart + "," + decimalPart;
    }

    // Cargar el selector de cuentas
    function cargarCuentasAnalisis() {
        $.ajax({
            url: 'php/api/get_cuentas.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Cuentas cargadas:", response); // Log para depuración
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione una cuenta...</option>';
                    let idGalicia = null;
                    response.data.forEach(function(cuenta) {
                        options += `<option value="${cuenta.id_cuenta}">${cuenta.nombre}</option>`;
                        if (cuenta.nombre.toLowerCase().includes('banco galicia')) {
                            idGalicia = cuenta.id_cuenta;
                        }
                    });
                    $('#selectCuentaAnalisis').html(options);

                    // Seleccionar Banco Galicia por defecto si se encuentra
                    if (idGalicia) {
                        $('#selectCuentaAnalisis').val(idGalicia).trigger('change');
                    }
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error al cargar cuentas:', textStatus, errorThrown);
            $('#no-data-message').removeClass('d-none').text('Error crítico: no se pudieron cargar las cuentas.');
        });
    }

    // Cargar y mostrar los datos del análisis para una cuenta específica
    function cargarAnalisisCuenta(cuentaId) {
        if (!cuentaId) {
            $('#analisis-container').addClass('d-none');
            $('#no-data-message').removeClass('d-none').text('Seleccione una cuenta para comenzar el análisis.');
            return;
        }

        $('#analisis-container').addClass('d-none');
        $('#no-data-message').removeClass('d-none').text('Cargando datos...');

        $.ajax({
            url: `php/api/analisis/reporte_cuenta.php?id_cuenta=${cuentaId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#no-data-message').addClass('d-none');
                    const data = response.data;
                    renderResumen(data);
                    renderGraficoCategorias(data.gastos_por_categoria);
                    renderTablaTransacciones(data.transacciones);
                    $('#analisis-container').removeClass('d-none');
                } else {
                    //alert('Error al cargar el análisis: ' + response.message);
                    $('#no-data-message').removeClass('d-none').text('No se pudieron cargar los datos para esta cuenta.');
                }
            },
            error: function() {
                //alert('Error de conexión al cargar el análisis.');
                $('#no-data-message').removeClass('d-none').text('Error de conexión al cargar el análisis.');
            }
        });
    }

    function renderResumen(data) {
        $('#total-ingresos-cuenta').text('$' + formatNumber(data.total_ingresos));
        $('#total-gastos-cuenta').text('$' + formatNumber(data.total_gastos));
    }

    function renderTablaTransacciones(transacciones) {
        let tablaHtml = '<tr><td colspan="4" class="text-center">No hay movimientos este mes.</td></tr>';
        if (transacciones && transacciones.length > 0) {
            tablaHtml = '';
            transacciones.forEach(function(tx) {
                const montoClass = tx.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger';
                const montoSigno = tx.tipo_movimiento === 'ingreso' ? '+' : '-';
                tablaHtml += `
                    <tr>
                        <td>${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                        <td>${tx.descripcion || ''}</td>
                        <td>${tx.categoria_nombre || 'N/A'}</td>
                        <td class="${montoClass} fw-bold">${montoSigno} $${formatNumber(tx.monto)}</td>
                    </tr>
                `;
            });
        }
        $('#transaccionesCuentaBody').html(tablaHtml);
    }

    function renderGraficoCategorias(gastos) {
        const ctx = document.getElementById('gastosPorCategoriaChart').getContext('2d');
        
        if (myPieChart) {
            myPieChart.destroy(); // Destruir el gráfico anterior para evitar conflictos
        }

        if (!gastos || gastos.length === 0) {
            myPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sin gastos este mes'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#f0f0f0'],
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            return;
        }

        const labels = gastos.map(g => g.nombre);
        const data = gastos.map(g => g.total);

        const backgroundColors = data.map(() => `rgba(${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, 0.8)`);

        myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }

    // Evento para cambiar de cuenta
    $('#selectCuentaAnalisis').on('change', function() {
        const cuentaId = $(this).val();
        cargarAnalisisCuenta(cuentaId);
    });

    // Carga inicial de cuentas para el nuevo análisis
    cargarCuentasAnalisis();

    // --- FIN: Nueva Lógica de Análisis de Cuenta ---


    // --- INICIO: Nueva Lógica de Análisis de Cuenta ---

    let myPieChart = null; // Variable para mantener la instancia del gráfico

    // Función para dar formato a los números
    function formatNumber(num) {
        if (num === null || num === undefined) {
            num = 0;
        }
        let parts = parseFloat(num).toFixed(2).toString().split('.');
        let integerPart = parts[0];
        let decimalPart = parts[1];
        
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        return integerPart + "," + decimalPart;
    }

    // Cargar el selector de cuentas
    function cargarCuentasAnalisis() {
        $.ajax({
            url: 'php/api/get_cuentas.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Cuentas cargadas:", response); // Log para depuración
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione una cuenta...</option>';
                    let idGalicia = null;
                    response.data.forEach(function(cuenta) {
                        options += `<option value="${cuenta.id_cuenta}">${cuenta.nombre}</option>`;
                        if (cuenta.nombre.toLowerCase().includes('banco galicia')) {
                            idGalicia = cuenta.id_cuenta;
                        }
                    });
                    $('#selectCuentaAnalisis').html(options);

                    // Seleccionar Banco Galicia por defecto si se encuentra
                    if (idGalicia) {
                        $('#selectCuentaAnalisis').val(idGalicia).trigger('change');
                    }
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error al cargar cuentas:', textStatus, errorThrown);
            $('#no-data-message').removeClass('d-none').text('Error crítico: no se pudieron cargar las cuentas.');
        });
    }

    // Cargar y mostrar los datos del análisis para una cuenta específica
    function cargarAnalisisCuenta(cuentaId) {
        if (!cuentaId) {
            $('#analisis-container').addClass('d-none');
            $('#no-data-message').removeClass('d-none').text('Seleccione una cuenta para comenzar el análisis.');
            return;
        }

        $('#analisis-container').addClass('d-none');
        $('#no-data-message').removeClass('d-none').text('Cargando datos...');

        $.ajax({
            url: `php/api/analisis/reporte_cuenta.php?id_cuenta=${cuentaId}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#no-data-message').addClass('d-none');
                    const data = response.data;
                    renderResumen(data);
                    renderGraficoCategorias(data.gastos_por_categoria);
                    renderTablaTransacciones(data.transacciones);
                    $('#analisis-container').removeClass('d-none');
                } else {
                    //alert('Error al cargar el análisis: ' + response.message);
                    $('#no-data-message').removeClass('d-none').text('No se pudieron cargar los datos para esta cuenta.');
                }
            },
            error: function() {
                //alert('Error de conexión al cargar el análisis.');
                $('#no-data-message').removeClass('d-none').text('Error de conexión al cargar el análisis.');
            }
        });
    }

    function renderResumen(data) {
        $('#total-ingresos-cuenta').text('$' + formatNumber(data.total_ingresos));
        $('#total-gastos-cuenta').text('$' + formatNumber(data.total_gastos));
    }

    function renderTablaTransacciones(transacciones) {
        let tablaHtml = '<tr><td colspan="4" class="text-center">No hay movimientos este mes.</td></tr>';
        if (transacciones && transacciones.length > 0) {
            tablaHtml = '';
            transacciones.forEach(function(tx) {
                const montoClass = tx.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger';
                const montoSigno = tx.tipo_movimiento === 'ingreso' ? '+' : '-';
                tablaHtml += `
                    <tr>
                        <td>${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                        <td>${tx.descripcion || ''}</td>
                        <td>${tx.categoria_nombre || 'N/A'}</td>
                        <td class="${montoClass} fw-bold">${montoSigno} $${formatNumber(tx.monto)}</td>
                    </tr>
                `;
            });
        }
        $('#transaccionesCuentaBody').html(tablaHtml);
    }

    function renderGraficoCategorias(gastos) {
        const ctx = document.getElementById('gastosPorCategoriaChart').getContext('2d');
        
        if (myPieChart) {
            myPieChart.destroy(); // Destruir el gráfico anterior para evitar conflictos
        }

        if (!gastos || gastos.length === 0) {
            myPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sin gastos este mes'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#f0f0f0'],
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            return;
        }

        const labels = gastos.map(g => g.nombre);
        const data = gastos.map(g => g.total);

        const backgroundColors = data.map(() => `rgba(${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, 0.8)`);

        myPieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }

    // Evento para cambiar de cuenta
    $('#selectCuentaAnalisis').on('change', function() {
        const cuentaId = $(this).val();
        cargarAnalisisCuenta(cuentaId);
    });

    // Carga inicial de cuentas para el nuevo análisis
    cargarCuentasAnalisis();

    // --- FIN: Nueva Lógica de Análisis de Cuenta ---

    // --- INICIO: Lógica para Reporte por Viaje/Proyecto ---

    function cargarProyectosAnalisis() {
        $.ajax({
            url: 'php/api/get_viajes_proyectos.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione un proyecto...</option>';
                    response.data.forEach(function(proyecto) {
                        options += `<option value="${proyecto.id_proyecto}">${proyecto.nombre}</option>`;
                    });
                    $('#selectProyectoAnalisis').html(options);
                } else {
                    console.error('Error al cargar proyectos:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX para cargar proyectos:', status, error);
            }
        });
    }

    function cargarReporteProyecto(idProyecto) {
        if (!idProyecto) {
            $('#reporte-proyecto-container').addClass('d-none');
            return;
        }

        $('#reporte-proyecto-container').removeClass('d-none');
        // Mostrar algún indicador de carga si es necesario

        $.ajax({
            url: `php/api/get_project_report.php?id_proyecto=${idProyecto}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const proyecto = response.data.proyecto;
                    const transacciones = response.data.transacciones;
                    const totales = response.data.totales;

                    // Actualizar detalles del proyecto
                    $('#proyecto-presupuesto').text('$' + formatNumber(proyecto.presupuesto_total));
                    $('#proyecto-gastos').text('$' + formatNumber(totales.gastos));
                    $('#proyecto-balance').text('$' + formatNumber(totales.balance));

                    // Actualizar color del card de balance
                    const balanceCard = $('#proyecto-balance-card');
                    balanceCard.removeClass('bg-success bg-danger bg-warning');
                    if (totales.balance >= 0) {
                        balanceCard.addClass('bg-success');
                    } else {
                        balanceCard.addClass('bg-danger');
                    }
                    balanceCard.find('.text-uppercase.fw-bold.mb-1').text(`Balance (${proyecto.nombre})`);


                    // Renderizar transacciones
                    let tablaHtml = '<tr><td colspan="4" class="text-center">No hay transacciones para este proyecto.</td></tr>';
                    if (transacciones && transacciones.length > 0) {
                        tablaHtml = '';
                        transacciones.forEach(function(tx) {
                            const montoClass = tx.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger';
                            const montoSigno = tx.tipo_movimiento === 'ingreso' ? '+' : '-';
                            tablaHtml += `
                                <tr>
                                    <td>${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                                    <td>${tx.descripcion || ''}</td>
                                    <td>${tx.categoria_nombre || 'N/A'}</td>
                                    <td class="${montoClass} fw-bold">${montoSigno} $${formatNumber(tx.monto)}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#transaccionesProyectoBody').html(tablaHtml);

                } else {
                    alert('Error al cargar el reporte del proyecto: ' + response.message);
                    $('#reporte-proyecto-container').addClass('d-none');
                }
            },
            error: function() {
                alert('Error de conexión al cargar el reporte del proyecto.');
                $('#reporte-proyecto-container').addClass('d-none');
            }
        });
    }

    // Evento para cambiar de proyecto
    $('#selectProyectoAnalisis').on('change', function() {
        const idProyecto = $(this).val();
        cargarReporteProyecto(idProyecto);
    });

    // Carga inicial de proyectos para el análisis
    cargarProyectosAnalisis();

    // --- FIN: Lógica para Reporte por Viaje/Proyecto ---


    // --- INICIO: Lógica Existente de Saldo Pre-Sueldo ---
    
    function cargarSaldoPreSueldoExistente(year) {
        $.ajax({
            url: `php/api/analisis/saldo_pre_sueldo.php?year=${year}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tableBodyHtml = '';
                    if (response.data.length > 0) {
                        response.data.forEach(function(item) {
                            // Formatear la fecha para legibilidad
                            const fechaRegistro = new Date(item.fecha_registro).toLocaleString('es-AR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            tableBodyHtml += `
                                <tr>
                                    <td>${item.periodo_sueldo || 'N/A'}</td>
                                    <td>${fechaRegistro}</td>
                                    <td>$${formatNumber(item.saldo)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBodyHtml = '<tr><td colspan="3" class="text-center">No hay datos de saldos pre-sueldo registrados para el año seleccionado.</td></tr>';
                    }
                    $('#saldoPreSueldoBody').html(tableBodyHtml);
                } else {
                    console.error('Error al cargar el análisis de saldo pre-sueldo:', response.message);
                    $('#saldoPreSueldoBody').html(`<tr><td colspan="3" class="text-center text-danger">${response.message}</td></tr>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la petición AJAX:', textStatus, errorThrown);
                $('#saldoPreSueldoBody').html(`<tr><td colspan="3" class="text-center text-danger">Error al cargar los datos.</td></tr>`);
            }
        });
    }

    function populateYearSelector() {
        const currentYear = new Date().getFullYear();
        let options = '';
        for (let i = currentYear; i >= 2020; i--) {
            options += `<option value="${i}">${i}</option>`;
        }
        $('#selectYear').html(options);
    }
    
    $('#selectYear').on('change', function() {
        const selectedYear = $(this).val();
        cargarSaldoPreSueldoExistente(selectedYear);
    });

    // Carga inicial de años y datos para el saldo pre-sueldo
    populateYearSelector();
    cargarSaldoPreSueldoExistente(new Date().getFullYear());

});