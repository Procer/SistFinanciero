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

    // --- Lógica para Reporte por Viaje/Proyecto ---

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

});
