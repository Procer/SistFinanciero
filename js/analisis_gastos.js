$(document).ready(function() {
    // Variables para los gráficos para poder destruirlos antes de volver a dibujarlos
    let gastosCategoriasChart;
    let gastosFormaPagoChart;

    // --- INICIALIZACIÓN ---
    function inicializarFiltros() {
        const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const $selectMes = $('#selectMes');
        meses.forEach((mes, index) => {
            $selectMes.append(new Option(mes, index + 1));
        });

        const $selectAnio = $('#selectAnio');
        const anioActual = new Date().getFullYear();
        for (let i = anioActual + 1; i >= anioActual - 5; i--) {
            $selectAnio.append(new Option(i, i));
        }

        // Establecer mes y año actual por defecto
        $selectMes.val(new Date().getMonth() + 1);
        $selectAnio.val(anioActual);
    }

    // --- LÓGICA PRINCIPAL DE CARGA ---
    function cargarAnalisis() {
        const mes = $('#selectMes').val();
        const anio = $('#selectAnio').val();

        if (!mes || !anio) {
            alert('Por favor, seleccione un mes y un año.');
            return;
        }
        
        // Muestra un loader o deshabilita el botón mientras carga
        $('#btnAplicarFiltros').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Cargando...');

        $.ajax({
            url: 'php/api/analisis/reporte_gastos.php',
            type: 'GET',
            data: { mes: mes, anio: anio },
            dataType: 'json',
            success: function(data) {
                if (data.resumen.total_gastos > 0) {
                    $('#analisis-gastos-container').removeClass('d-none');
                    $('#no-gastos-message').addClass('d-none');
                    actualizarUI(data);
                } else {
                    $('#analisis-gastos-container').addClass('d-none');
                    $('#no-gastos-message').removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar el análisis:", error);
                alert('Hubo un error al cargar los datos. Revise la consola para más detalles.');
                $('#analisis-gastos-container').addClass('d-none');
                $('#no-gastos-message').removeClass('d-none').text('Error al cargar los datos.');
            },
            complete: function() {
                // Vuelve a habilitar el botón
                $('#btnAplicarFiltros').prop('disabled', false).html('<i class="fas fa-filter me-2"></i>Aplicar Filtros');
            }
        });
    }

    // --- FUNCIONES DE ACTUALIZACIÓN DE UI ---
    function actualizarUI(data) {
        // Formateador de moneda
        const currencyFormatter = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });

        // 1. Actualizar Resumen (KPIs)
        $('#total-gastos-mes').text(currencyFormatter.format(data.resumen.total_gastos));
        $('#burn-rate-diario').text(currencyFormatter.format(data.resumen.burn_rate_diario));

        // 2. Actualizar Ranking por Categoría
        const $rankingCategoriasBody = $('#rankingCategoriasBody');
        $rankingCategoriasBody.empty();
        data.gastos_por_categoria.forEach(item => {
            const row = `<tr>
                <td>${item.categoria}</td>
                <td>${currencyFormatter.format(item.total_gastado)}</td>
                <td>${parseFloat(item.porcentaje_del_total).toFixed(2)}%</td>
            </tr>`;
            $rankingCategoriasBody.append(row);
        });

        // 3. Actualizar Ranking por Forma de Pago
        const $rankingFormaPagoBody = $('#rankingFormaPagoBody');
        $rankingFormaPagoBody.empty();
        data.gastos_por_forma_pago.forEach(item => {
            const row = `<tr>
                <td>${item.forma_pago}</td>
                <td>${currencyFormatter.format(item.total_gastado)}</td>
                <td>${parseFloat(item.porcentaje_del_total).toFixed(2)}%</td>
            </tr>`;
            $rankingFormaPagoBody.append(row);
        });

        // 4. Actualizar Detalle de Gastos
        const $detalleGastosBody = $('#detalleGastosBody');
        $detalleGastosBody.empty();
        data.detalle_gastos.forEach(item => {
            const row = `<tr>
                <td>${item.fecha}</td>
                <td>${item.descripcion}</td>
                <td>${item.categoria}</td>
                <td>${item.forma_pago}</td>
                <td>${currencyFormatter.format(item.monto)}</td>
                <td>${item.cuenta_tarjeta}</td>
            </tr>`;
            $detalleGastosBody.append(row);
        });

        // 5. Renderizar Gráficos
        renderizarGraficoCategorias(data.gastos_por_categoria);
        renderizarGraficoFormaPago(data.gastos_por_forma_pago);
    }

    // --- FUNCIONES DE RENDERIZADO DE GRÁFICOS ---
    function renderizarGraficoCategorias(data) {
        if (gastosCategoriasChart) {
            gastosCategoriasChart.destroy();
        }
        const ctx = document.getElementById('gastosCategoriasBarChart').getContext('2d');
        gastosCategoriasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.categoria),
                datasets: [{
                    label: 'Total Gastado',
                    data: data.map(d => d.total_gastado),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function renderizarGraficoFormaPago(data) {
        if (gastosFormaPagoChart) {
            gastosFormaPagoChart.destroy();
        }
        const ctx = document.getElementById('gastosFormaPagoPieChart').getContext('2d');
        gastosFormaPagoChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(d => d.forma_pago),
                datasets: [{
                    data: data.map(d => d.total_gastado),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // --- EVENT LISTENERS ---
    $('#btnAplicarFiltros').on('click', cargarAnalisis);

    // --- Carga inicial ---
    inicializarFiltros();
    cargarAnalisis();
});