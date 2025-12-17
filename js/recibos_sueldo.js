$(document).ready(function() {
    // Instancias de los gráficos para poder destruirlos antes de volver a crearlos
    let sueldoChartInstance;
    let descuentosChartInstance;
    let crecimientoChartInstance;

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

    // Manejar el envío del formulario de carga de recibo de sueldo
    $('#formReciboSueldo').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        $('#loadingSpinner').show();
        $('#reciboSueldoFeedback').empty();

        $.ajax({
            url: 'php/api/recibos_sueldo/upload.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('#loadingSpinner').hide();
                if (response.status === 'success') {
                    $('#reciboSueldoFeedback').html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#formReciboSueldo')[0].reset(); // Limpiar formulario
                    cargarRecibosSueldo(); // Recargar la tabla
                    cargarReportes();     // Recargar los gráficos
                } else {
                    $('#reciboSueldoFeedback').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#loadingSpinner').hide();
                let errorMsg = 'Error al procesar la solicitud.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                $('#reciboSueldoFeedback').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    });

    // Función para cargar los recibos de sueldo
    function cargarRecibosSueldo() {
        $.ajax({
            url: 'php/api/recibos_sueldo/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                let tbody = $('#recibos-sueldo-tbody');
                tbody.empty();
                if (response.status === 'success' && response.data.length > 0) {
                    response.data.forEach(function(recibo) {
                        tbody.append(`
                            <tr data-id="${recibo.id_recibo}">
                                <td>${recibo.periodo_sueldo}</td>
                                <td>${recibo.nombre_empleado}</td>
                                <td>$${formatNumber(recibo.sueldo_neto)}</td>
                                <td>
                                    <button class="btn btn-sm btn-info ver-detalle-recibo" data-id="${recibo.id_recibo}" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                                    <a href="${recibo.ruta_imagen}" target="_blank" class="btn btn-sm btn-secondary" title="Ver Imagen"><i class="fas fa-image"></i></a>
                                    <button class="btn btn-sm btn-danger eliminar-recibo" data-id="${recibo.id_recibo}" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">No hay recibos de sueldo cargados.</td></tr>');
                }
            },
            error: function() {
                $('#reciboSueldoFeedback').html('<div class="alert alert-danger">Error al cargar la lista de recibos.</div>');
            }
        });
    }

    // Función para cargar y renderizar los reportes gráficos
    function cargarReportes() {
        $('#reports-loading-spinner').show();
        $('#charts-container').hide();
        $('#no-data-message').hide();
        
        $.ajax({
            url: 'php/api/recibos_sueldo/get_growth_report.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#reports-loading-spinner').hide();

                if (!response || !response.sueldoChart || response.sueldoChart.labels.length === 0) {
                    $('#no-data-message').show();
                    return;
                }

                $('#charts-container').show();

                // Destruir gráficos existentes si ya han sido creados
                if(sueldoChartInstance) sueldoChartInstance.destroy();
                if(descuentosChartInstance) descuentosChartInstance.destroy();
                if(crecimientoChartInstance) crecimientoChartInstance.destroy();

                // Gráfico de Evolución de Sueldo
                const sueldoCtx = document.getElementById('sueldoChart').getContext('2d');
                sueldoChartInstance = new Chart(sueldoCtx, {
                    type: 'line',
                    data: response.sueldoChart,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value) { return '$' + formatNumber(value); }
                                }
                            }
                        }
                    }
                });

                // Gráfico de Evolución de Descuentos
                const descuentosCtx = document.getElementById('descuentosChart').getContext('2d');
                descuentosChartInstance = new Chart(descuentosCtx, {
                    type: 'line',
                    data: response.descuentosChart,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return '$' + formatNumber(value); }
                                }
                            }
                        }
                    }
                });

                // Gráfico de Crecimiento Porcentual
                const crecimientoCtx = document.getElementById('crecimientoChart').getContext('2d');
                crecimientoChartInstance = new Chart(crecimientoCtx, {
                    type: 'bar',
                    data: response.crecimientoChart,
                    options: {
                        responsive: true,
                        scales: {
                            yPercentage: {
                                position: 'left',
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return value + '%'; }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y + '%';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            },
            error: function() {
                $('#reports-loading-spinner').hide();
                $('#no-data-message').html('<p class="text-danger">Error al cargar los datos de los reportes.</p>').show();
            }
        });
    }

    // Cargar datos iniciales al entrar a la página
    cargarRecibosSueldo();
    cargarReportes();

    // Evento para ver detalles del recibo
    $(document).on('click', '.ver-detalle-recibo', function() {
        // (El código existente para el modal de detalles no necesita cambios)
        // ... (se ha omitido por brevedad, ya que es el mismo que el original)
        const idRecibo = $(this).data('id');
        
        $.ajax({
            url: 'php/api/recibos_sueldo/get_details.php',
            method: 'GET',
            data: { id: idRecibo },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const recibo = response.data;
                    
                    $('#detalleEmpleado').text(recibo.nombre_empleado || 'N/A');
                    $('#detalleCuilEmpleado').text(recibo.cuil_empleado || 'N/A');
                    $('#detalleEmpleador').text(recibo.nombre_empleador || 'N/A');
                    $('#detalleCuitEmpleador').text(recibo.cuit_empleador || 'N/A');
                    $('#detallePeriodo').text(recibo.periodo_sueldo || 'N/A');
                    $('#detalleFechaPago').text(recibo.fecha_pago || 'N/A');
                    $('#detalleLugarPago').text(recibo.lugar_pago || 'N/A');
                    $('#detalleFormaPago').text(recibo.forma_pago_detalle || 'N/A');
                    $('#detalleSueldoBruto').text('$' + formatNumber(recibo.sueldo_bruto));
                    $('#detalleSueldoNeto').text('$' + formatNumber(recibo.sueldo_neto));
                    $('#detalleDescuentosTotal').text('$' + formatNumber(recibo.descuentos_total));

                    const conceptosTbody = $('#detalleConceptosTbody');
                    conceptosTbody.empty();
                    if (recibo.detalle_json && recibo.detalle_json.length > 0) {
                        recibo.detalle_json.forEach(function(concepto) {
                            conceptosTbody.append(`
                                <tr>
                                    <td>${concepto.codigo || ''}</td>
                                    <td>${concepto.descripcion || ''}</td>
                                    <td>${concepto.unidad || ''}</td>
                                    <td>$${formatNumber(concepto.haberes_con_aporte)}</td>
                                    <td>$${formatNumber(concepto.haberes_sin_aporte)}</td>
                                    <td>$${formatNumber(concepto.descuentos_item)}</td>
                                    <td>${concepto.tipo || ''}</td>
                                </tr>
                            `);
                        });
                    } else {
                        conceptosTbody.append('<tr><td colspan="7" class="text-center">No hay detalles de conceptos.</td></tr>');
                    }

                    const ultimoDeposito = recibo.ultimo_deposito_cargas_sociales_json;
                    if (ultimoDeposito && Object.keys(ultimoDeposito).length > 0) {
                        $('#detalleDepositoBanco').text(ultimoDeposito.banco || 'N/A');
                        $('#detalleDepositoPeriodo').text(ultimoDeposito.periodo || 'N/A');
                        $('#detalleDepositoFecha').text(ultimoDeposito.fecha_deposito || 'N/A');
                    } else {
                        $('#detalleUltimoDeposito').html('<p class="text-muted">No hay información de último depósito.</p>');
                    }

                    $('#detalleImagenOriginal').attr('href', recibo.ruta_imagen);
                    
                    $('#reciboDetalleModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al obtener los detalles del recibo.');
            }
        });
    });

    // Evento para eliminar un recibo
    $(document).on('click', '.eliminar-recibo', function() {
        const idRecibo = $(this).data('id');
        if (confirm('¿Estás seguro de que quieres eliminar este recibo?')) {
            $.ajax({
                url: 'php/api/recibos_sueldo/delete.php',
                method: 'POST',
                data: JSON.stringify({ id_recibo: idRecibo }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarRecibosSueldo();
                        cargarReportes(); // Actualizar gráficos tras eliminar
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al eliminar el recibo.');
                }
            });
        }
    });
});
