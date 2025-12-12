$(document).ready(function() {

    let gastosChart = null;

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

    // Llenar los selectores de mes y año
    function inicializarSelectoresFecha() {
        const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const fechaActual = new Date();
        const mesActual = fechaActual.getMonth();
        const anioActual = fechaActual.getFullYear();

        let mesesOptions = '';
        meses.forEach((mes, index) => {
            mesesOptions += `<option value="${index + 1}">${mes}</option>`;
        });
        $('#selectMes').html(mesesOptions).val(mesActual + 1);

        let aniosOptions = '';
        for (let i = anioActual; i >= 2020; i--) {
            aniosOptions += `<option value="${i}">${i}</option>`;
        }
        $('#selectAnio').html(aniosOptions).val(anioActual);
    }

    // Cargar y mostrar los datos del análisis
    function cargarAnalisisGastos() {
        const mes = $('#selectMes').val();
        const anio = $('#selectAnio').val();

        if (!mes || !anio) {
            return;
        }

        $.ajax({
            url: `php/api/analisis/reporte_gastos_mensuales.php?mes=${mes}&ano=${anio}`,
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#analisis-gastos-container').addClass('d-none');
                $('#no-gastos-message').removeClass('d-none').text('Cargando...');
            },
            success: function(response) {
                if (response.status === 'success' && response.data.total_gastos_mes > 0) {
                    $('#analisis-gastos-container').removeClass('d-none');
                    $('#no-gastos-message').addClass('d-none');
                    
                    const data = response.data;
                    renderResumen(data.total_gastos_mes);
                    renderRankingTabla(data.gastos_por_categoria);
                    renderGrafico(data.gastos_por_categoria);

                } else {
                    $('#analisis-gastos-container').addClass('d-none');
                    $('#no-gastos-message').removeClass('d-none').text('No se encontraron gastos para el período seleccionado.');
                     // Limpiar la tabla y el gráfico si no hay datos
                    renderResumen(0);
                    renderRankingTabla([]);
                    renderGrafico([]);
                }
            },
            error: function() {
                $('#analisis-gastos-container').addClass('d-none');
                $('#no-gastos-message').removeClass('d-none').text('Error de conexión al cargar el análisis.');
            }
        });
    }

    function renderResumen(total) {
        $('#total-gastos-mes').text('$' + formatNumber(total));
    }

    function renderRankingTabla(gastos) {
        let tablaHtml = '';
        if (gastos && gastos.length > 0) {
            gastos.forEach(g => {
                tablaHtml += `
                    <tr>
                        <td>${g.nombre}</td>
                        <td>$${formatNumber(g.total_gastado)}</td>
                        <td>${parseFloat(g.porcentaje).toFixed(2)}%</td>
                    </tr>
                `;
            });
        } else {
            tablaHtml = '<tr><td colspan="3" class="text-center">Sin datos de gastos.</td></tr>';
        }
        $('#rankingGastosBody').html(tablaHtml);
    }

    function renderGrafico(gastos) {
        const ctx = document.getElementById('gastosCategoriasChart').getContext('2d');
        
        if (gastosChart) {
            gastosChart.destroy();
        }

        if (!gastos || gastos.length === 0) {
            // Podríamos mostrar un gráfico vacío o simplemente dejarlo en blanco
             gastosChart = new Chart(ctx, {
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
        const data = gastos.map(g => g.total_gastado);
        
        // Generar colores aleatorios para el gráfico
        const backgroundColors = data.map(() => `rgba(${Math.floor(Math.random() * 225)}, ${Math.floor(Math.random() * 225)}, ${Math.floor(Math.random() * 225)}, 0.7)`);

        gastosChart = new Chart(ctx, {
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Eventos para cambiar mes o año
    $('#selectMes, #selectAnio').on('change', function() {
        cargarAnalisisGastos();
    });

    // Carga inicial
    inicializarSelectoresFecha();
    cargarAnalisisGastos();

});
