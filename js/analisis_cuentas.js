$(document).ready(function() {
    // Función para dar formato a los números (Duplicado para mantener la autonomía por ahora)
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

    let myPieChart = null; // Variable para mantener la instancia del gráfico

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
                    $('#no-data-message').removeClass('d-none').text('No se pudieron cargar los datos para esta cuenta.');
                }
            },
            error: function() {
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

    // --- Lógica para Saldo Pre-Sueldo ---
    
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
                            tableBodyHtml += `
                                <tr>
                                    <td>${item.mes}</td>
                                    <td>${item.fecha_sueldo_calculada || item.fecha_sueldo_estimada}</td>
                                    <td>$${formatNumber(item.saldo_pre_sueldo)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableBodyHtml = '<tr><td colspan="3" class="text-center">No hay datos disponibles para el año seleccionado.</td></tr>';
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
