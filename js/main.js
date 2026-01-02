$(document).ready(function(){
    // Toggle para el menú lateral
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

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

    // Función para formatear números para el servidor (reemplaza coma por punto)
    function formatNumberForServer(numString) {
        if (numString === null || numString === undefined || numString === '') {
            return 0;
        }
        return parseFloat(numString.replace(',', '.'));
    }

    // Función para mostrar toasts (notificaciones no bloqueantes)
    function mostrarToast(titulo, mensaje, tipo = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${tipo} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${titulo}</strong> ${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('.toast-container').append(toastHtml);
        const toastEl = new bootstrap.Toast(document.getElementById(toastId));
        toastEl.show();

        // Eliminar el toast del DOM después de que se oculte
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }

    // Función para cargar los datos del dashboard
    function cargarDashboard() {
        console.log('Iniciando cargarDashboard()');
        $.ajax({
            url: 'php/api/dashboard_summary.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    console.log('Datos recibidos para el dashboard:', data);

                    // Actualizar tarjetas de resumen
                    $('#total-saldos').text('$' + formatNumber(data.total_saldos));
                    $('#ingresos-mes').text('$' + formatNumber(data.total_ingresos_mes));
                    $('#gastos-mes').text('$' + formatNumber(data.total_gastos_mes));
                    console.log('Tarjetas de resumen actualizadas.');

                    // Actualizar widgets de cuentas y generar botones de acción rápida
                    let widgetsHtml = '';
                    let quickButtonsHtml = '';
                    const quickActionAccounts = ['Banco Galicia', 'Billetera (Efectivo)', 'Mercado Pago', 'Cuenta Naranja X'];

                    if (data.cuentas.length > 0) {
                        data.cuentas.forEach(function(cuenta) {
                            // Lógica para widgets
                            let icon = 'fa-question-circle';
                            let bgColor = 'bg-secondary';
                            let customStyle = '';

                            if (cuenta.nombre === 'Cuenta Naranja X') {
                                icon = 'fa-mobile-alt';
                                customStyle = 'background-color: #50007f !important;';
                            } else if (cuenta.nombre === 'Banco Galicia') {
                                icon = 'fa-university';
                                customStyle = 'background-color: #c85000 !important;';
                            } else if (cuenta.nombre === 'Mercado Pago') {
                                icon = 'fa-money-bill-alt';
                                bgColor = 'bg-info';
                            } else if (cuenta.tipo_cuenta === 'billetera') {
                                icon = 'fa-wallet';
                                bgColor = 'bg-dark';
                            } else if (cuenta.tipo_cuenta === 'banco') {
                                icon = 'fa-university';
                                bgColor = 'bg-primary';
                            } else if (cuenta.tipo_cuenta === 'otro') {
                                icon = 'fa-star';
                                bgColor = 'bg-light text-dark';
                            }

                            widgetsHtml += `
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div class="card ${bgColor} text-white h-100" style="${customStyle}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="text-white-75 small">${cuenta.nombre}</div>
                                                    <div class="h4 fw-bold">$${formatNumber(cuenta.saldo_actual)}</div>
                                                </div>
                                                <i class="fas ${icon} fa-2x opacity-50"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Lógica para botones de acción rápida
                            if (quickActionAccounts.includes(cuenta.nombre)) {
                                let icon_quick = 'fa-wallet';
                                let card_style = 'border-primary';
                                if (cuenta.nombre.toLowerCase().includes('galicia')) {
                                    icon_quick = 'fa-university';
                                } else if (cuenta.nombre.toLowerCase().includes('billetera')) {
                                    icon_quick = 'fa-money-bill-wave';
                                } else if (cuenta.nombre.toLowerCase().includes('mercado pago')) {
                                    icon_quick = 'fa-hand-holding-usd';
                                    card_style = 'border-info';
                                } else if (cuenta.nombre.toLowerCase().includes('naranja x')) {
                                    icon_quick = 'fa-credit-card';
                                }
                                quickButtonsHtml += `
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="card card-body text-center quick-action-card btn-quick-expense ${card_style}" 
                                             data-account-id="${cuenta.id_cuenta}" 
                                             data-account-name="${cuenta.nombre}"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#transaccionModal"
                                             style="cursor: pointer;">
                                            <i class="fas ${icon_quick} fa-2x mb-2 text-muted"></i>
                                            <span class="fw-bold">Gasto en ${cuenta.nombre}</span>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                    } else {
                        widgetsHtml = '<div class="col-12"><p>No hay cuentas registradas.</p></div>';
                    }
                    console.log('Widgets HTML generados:', widgetsHtml);
                    $('#cuentas-widgets-container').html(widgetsHtml);
                    console.log('Botones de acción rápida HTML generados:', quickButtonsHtml);
                    $('#quick-expense-buttons-container').html(quickButtonsHtml);
                    console.log('Widgets y botones de acción rápida actualizados.');

                    // Actualizar tabla de transacciones
                    let transaccionesHtml = '';
                    if (data.transacciones_mes && data.transacciones_mes.length > 0) {
                        data.transacciones_mes.forEach(function(tx) {
                            if (tx.tipo_movimiento === 'transferencia') {
                                transaccionesHtml += `
                                    <tr class="table-light">
                                        <td data-label="Fecha">${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                                        <td data-label="Cuenta"></td>
                                        <td data-label="Monto" class="fw-bold text-muted">$${formatNumber(tx.monto)}</td>
                                        <td data-label="Categoría"><i class="fas fa-exchange-alt me-2 text-info"></i>Transferencia</td>
                                        <td data-label="Descripción">${tx.descripcion || `De ${tx.cuenta_origen_nombre} a ${tx.cuenta_destino_nombre}`}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Las transferencias no se pueden editar o eliminar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Las transferencias no se pueden editar o eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            } else {
                                const montoClass = tx.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger';
                                const montoSigno = tx.tipo_movimiento === 'ingreso' ? '+' : '-';
                                const descripcion = tx.descripcion ? tx.descripcion : '';
                                const txData = encodeURIComponent(JSON.stringify(tx));

                                transaccionesHtml += `
                                    <tr>
                                        <td data-label="Fecha">${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                                        <td data-label="Cuenta">${tx.cuenta_nombre || 'N/A'}</td>
                                        <td data-label="Monto" class="${montoClass} fw-bold">${montoSigno} $${formatNumber(tx.monto)}</td>
                                        <td data-label="Categoría">${tx.categoria_nombre || 'Sin categoría'}</td>
                                        <td data-label="Descripción">${descripcion}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary btn-edit-transaction" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#transaccionModal"
                                                    data-tx='${txData}'>
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-delete-transaction" data-tx-id="${tx.id_transaccion}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            }
                        });
                    } else {
                        transaccionesHtml = '<tr><td colspan="6" class="text-center">No hay transacciones este mes.</td></tr>';
                    }
                    $('#transacciones-tbody').html(transaccionesHtml);
                    console.log('Tabla de transacciones actualizada.');

                } else {
                    console.error('Error al cargar el dashboard:', response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la petición AJAX:', textStatus, errorThrown);
            }
        });
    }

    // Funciones para cargar selects del modal de transacción
    function cargarCuentasSelect(selectedAccountId = null) {
        return $.ajax({
            url: 'php/api/get_cuentas.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione...</option>';
                    response.data.forEach(function(cuenta) {
                        options += `<option value="${cuenta.id_cuenta}">${cuenta.nombre}</option>`;
                    });
                    $('#cuenta').html(options);
                    if (selectedAccountId) {
                        $('#cuenta').val(selectedAccountId);
                    }
                } else {
                    console.error('Error al cargar cuentas:', response.message);
                }
            }
        });
    }
    
    // ... (resto de funciones cargarCategoriasSelect, cargarFormasPagoSelect, etc. sin cambios)
    function cargarCategoriasSelect(tipo) {
        return $.ajax({
            url: 'php/api/categorias/read.php?tipo=' + tipo,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione...</option>';
                    
                    function renderOptions(categorias) {
                        categorias.forEach(function(categoria) {
                            // Si la categoría tiene subcategorías, la tratamos como un grupo
                            if (categoria.subcategorias && categoria.subcategorias.length > 0) {
                                options += `<optgroup label="${categoria.nombre}">`;
                                // Opcional: si quieres que la categoría padre también sea seleccionable
                                // options += `<option value="${categoria.id_categoria}">${categoria.nombre}</option>`;
                                
                                // Renderizar las subcategorías
                                categoria.subcategorias.forEach(function(subcategoria) {
                                    options += `<option value="${subcategoria.id_categoria}">&nbsp;&nbsp;&nbsp;${subcategoria.nombre}</option>`;
                                });

                                options += `</optgroup>`;
                            } else {
                                // Si no tiene subcategorías, es una opción normal
                                options += `<option value="${categoria.id_categoria}">${categoria.nombre}</option>`;
                            }
                        });
                    }

                    renderOptions(response.data);
                    $('#categoria').html(options);
                } else {
                    console.error('Error al cargar categorías:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX para cargar categorías:', error);
            }
        });
    }

    function cargarFormasPagoSelect() {
        return $.ajax({
            url: 'php/api/get_formas_pago.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">N/A</option>';
                    response.data.forEach(function(forma) {
                        options += `<option value="${forma.id_forma_pago}">${forma.nombre}</option>`;
                    });
                    $('#formaPago').html(options);
                } else {
                    console.error('Error al cargar formas de pago:', response.message);
                }
            }
        });
    }

    function cargarTarjetasCreditoSelect() {
        return $.ajax({
            url: 'php/api/tarjetas_credito/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(tarjetas) {
                let options = '<option value="">Seleccione...</option>';
                if (tarjetas.length > 0) {
                    tarjetas.forEach(function(tarjeta) {
                        options += `<option value="${tarjeta.id_tarjeta}">${tarjeta.nombre} (${tarjeta.banco || 'N/A'})</option>`;
                    });
                }
                $('#tarjetaCredito').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar tarjetas de crédito:', error);
            }
        });
    }

    function cargarViajesProyectosSelect() {
        return $.ajax({
            url: 'php/api/get_viajes_proyectos.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">N/A</option>';
                    response.data.forEach(function(proyecto) {
                        options += `<option value="${proyecto.id_proyecto}">${proyecto.nombre}</option>`;
                    });
                    $('#viajeProyecto').html(options);
                } else {
                    console.error('Error al cargar viajes/proyectos:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX para cargar viajes/proyectos:', error);
            }
        });
    }

    // Evento para el botón de "Editar Transacción"
    $(document).on('click', '.btn-edit-transaction', function() {
        $('#formTransaccion')[0].reset();
        const txDataString = decodeURIComponent($(this).data('tx'));
        const tx = JSON.parse(txDataString);

        $('#transaccionModalLabel').text('Editar Transacción');

        $.when(
            cargarCuentasSelect(tx.id_cuenta),
            cargarCategoriasSelect(tx.tipo_movimiento),
            cargarFormasPagoSelect(),
            cargarViajesProyectosSelect(),
            cargarTarjetasCreditoSelect()
        ).done(function() {
            $('#transaccionId').val(tx.id_transaccion);
            $('#tipoMovimiento').val(tx.tipo_movimiento);
            $('#tipoMovimientoWrapper').hide();
            $('#cuenta').prop('disabled', false);
            
            $('#montoTransaccion').val(tx.monto);
            $('#categoria').val(tx.id_categoria);
            $('#formaPago').val(tx.id_forma_pago).trigger('change');
            
            const fecha = new Date(tx.fecha_transaccion);
            fecha.setMinutes(fecha.getMinutes() - fecha.getTimezoneOffset());
            $('#fecha').val(fecha.toISOString().slice(0, 16));
            
            $('#descripcion').val(tx.descripcion);
            
            if (tx.id_proyecto) {
                $('#viajeProyecto').val(tx.id_proyecto);
            } else {
                $('#viajeProyecto').val('');
            }
            
            $('#camposTarjetaCredito').hide();
            $('#montoTransaccion').prop('required', true);
            $('#montoTotalTarjeta').prop('required', false);
        });
    });

    // Evento para el botón de GASTO RÁPIDO
    $(document).on('click', '.btn-quick-expense', function() {
        $('#formTransaccion')[0].reset();
        const accountId = $(this).data('account-id');
        const accountName = $(this).data('account-name').toLowerCase();
    
        $('#transaccionId').val('');
        $('#transaccionModalLabel').text(`Nuevo Gasto`);
    
        // Pre-seleccionar tipo gasto y ocultar
        $('#tipoMovimiento').val('gasto');
        $('#tipoMovimientoWrapper').hide();
    
        // Setear fecha actual
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#fecha').val(now.toISOString().slice(0,16));
    
        // Cargar todos los selects y luego pre-rellenar
        $.when(
            cargarCuentasSelect(accountId),
            cargarCategoriasSelect('gasto'),
            cargarFormasPagoSelect(),
            cargarTarjetasCreditoSelect(),
            cargarViajesProyectosSelect()
        ).done(function() {
            // La cuenta ya se selecciona en `cargarCuentasSelect`
            // Ahora, autocompletar la forma de pago
            const formaPagoSelect = $('#formaPago');
            let textoFormaPago = '';
    
            if (accountName.includes('banco galicia')) {
                textoFormaPago = 'transferencia bancaria';
            } else if (accountName.includes('billetera')) {
                textoFormaPago = 'efectivo';
            } else if (accountName.includes('mercado pago')) {
                textoFormaPago = 'mercado pago';
            } else if (accountName.includes('naranja x')) {
                textoFormaPago = 'transferencia bancaria';
            }
            
            if (textoFormaPago) {
                const option = formaPagoSelect.find('option').filter(function() {
                    return $(this).text().trim().toLowerCase() === textoFormaPago;
                }).val();
                if (option) {
                    formaPagoSelect.val(option).trigger('change');
                }
            }
        });
    });


    // Evento para los botones genéricos de "Nuevo Ingreso" y "Nuevo Gasto"
    $(document).on('click', '.btn-add-transaction', function() {
        $('#formTransaccion')[0].reset();
        const tipo = $(this).data('type');

        $('#transaccionId').val('');
        $('#tipoMovimientoWrapper').hide();
        
        if (tipo === 'ingreso') {
            $('#transaccionModalLabel').text('Nuevo Ingreso');
            $('#tipoMovimiento').val('ingreso');
        } else {
            $('#transaccionModalLabel').text('Nuevo Gasto');
            $('#tipoMovimiento').val('gasto');
        }

        // Cargar todos los selects
        cargarCuentasSelect();
        cargarCategoriasSelect(tipo);
        cargarFormasPagoSelect();
        cargarTarjetasCreditoSelect();
        cargarViajesProyectosSelect();

        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#fecha').val(now.toISOString().slice(0,16));
    });

    // Lógica para mostrar/ocultar campos de tarjeta de crédito
    $(document).on('change', '#formaPago', function() {
        const formaPagoSeleccionadaTexto = $(this).find('option:selected').text().toLowerCase();
        
        if (formaPagoSeleccionadaTexto.includes('crédito') || formaPagoSeleccionadaTexto.includes('tarjeta')) {
            $('#camposTarjetaCredito').show();
            $('#montoTransaccion').prop('required', false).val('');
            $('#montoTotalTarjeta').prop('required', true);
            $('#tarjetaCredito').prop('required', true);
            $('#cuotasTotales').prop('required', true);
        } else {
            $('#camposTarjetaCredito').hide();
            $('#montoTransaccion').prop('required', true);
            $('#montoTotalTarjeta').prop('required', false).val('');
            $('#tarjetaCredito').val('').prop('required', false);
            $('#cuotasTotales').val('1').prop('required', false);
        }
    });

    // Limpiar el formulario y re-habilitar campos cuando el modal se cierra
    $('#transaccionModal').on('hidden.bs.modal', function () {
        $('#formTransaccion')[0].reset();
        $('#transaccionId').val('');
        $('#transaccionModalLabel').text('Nueva Transacción');
        $('#tipoMovimientoWrapper').show();
        $('#cuenta').prop('disabled', false); // <-- Importante: re-habilitar la cuenta
        $('#formaPago').prop('disabled', false);
        $('#camposTarjetaCredito').hide();
        $('#tarjetaCredito').val('').prop('required', false);
        $('#cuotasTotales').val('1').prop('required', false);
        $('#montoTransaccion').prop('required', true);
        $('#montoTotalTarjeta').prop('required', false).val('');
        $('#viajeProyecto').val('');
    });

    // Enfocar el campo de monto cuando el modal se muestra completamente
    $('#transaccionModal').on('shown.bs.modal', function () {
        if ($('#camposTarjetaCredito').is(':visible')) {
            $('#montoTotalTarjeta').focus();
        } else {
            $('#montoTransaccion').focus();
        }
    });

    // Manejar el envío del formulario de transacción
    $('#formTransaccion').submit(function(e) {
        e.preventDefault();

        const idTransaccion = $('#transaccionId').val();
        const tipoMovimiento = $('#tipoMovimiento').val();
        const idCuenta = $('#cuenta').val();
        const idCategoria = $('#categoria').val();
        const idFormaPago = $('#formaPago').val() === '' ? null : $('#formaPago').val();
        const descripcion = $('#descripcion').val();
        const fechaTransaccion = $('#fecha').val();
        const idProyecto = $('#viajeProyecto').val() === '' || $('#viajeProyecto').val() === 'N/A' ? null : $('#viajeProyecto').val();

        let url = 'php/api/transactions.php';
        let formData = {};

        if ($('#camposTarjetaCredito').is(':visible') && !idTransaccion) {
            url = 'php/api/card_transactions.php';
            formData = {
                id_tarjeta: $('#tarjetaCredito').val(),
                descripcion: descripcion,
                monto_total: formatNumberForServer($('#montoTotalTarjeta').val()),
                cuotas_totales: $('#cuotasTotales').val(),
                fecha_compra: fechaTransaccion,
                id_cuenta: idCuenta,
                id_categoria: idCategoria,
                tipo_movimiento: tipoMovimiento,
                id_forma_pago: idFormaPago,
                id_proyecto: idProyecto
            };
        } else {
            formData = {
                id_transaccion: idTransaccion ? idTransaccion : null,
                id_cuenta: idCuenta,
                id_categoria: idCategoria,
                id_forma_pago: idFormaPago,
                tipo_movimiento: tipoMovimiento,
                monto: formatNumberForServer($('#montoTransaccion').val()),
                descripcion: descripcion,
                fecha_transaccion: fechaTransaccion,
                id_proyecto: idProyecto
            };
        }

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    mostrarToast('Transacción Exitosa', response.message, 'success');
                    $('#transaccionModal').modal('hide');
                    cargarDashboard();
                } else {
                    mostrarToast('Error', response.message, 'danger');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = 'Error al procesar la transacción.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                mostrarToast('Error de Transacción', errorMsg, 'danger');
                console.error('Error en la petición AJAX:', textStatus, errorThrown, jqXHR.responseJSON);
            }
        });
    });

    // Lógica para autocompletar forma de pago según la cuenta seleccionada
    $(document).on('change', '#cuenta', function() {
        const cuentaSeleccionada = $(this).find('option:selected').text().toLowerCase();
        const formaPagoSelect = $('#formaPago');

        const setFormaPago = (texto) => {
            const option = formaPagoSelect.find('option').filter(function() {
                return $(this).text().trim().toLowerCase() === texto;
            }).val();
            if (option) {
                formaPagoSelect.val(option).trigger('change');
            }
        };

        if (cuentaSeleccionada.includes('banco galicia')) {
            setFormaPago('transferencia bancaria');
        } else if (cuentaSeleccionada.includes('billetera')) {
            setFormaPago('efectivo');
        } else if (cuentaSeleccionada.includes('mercado pago')) {
            setFormaPago('mercado pago');
        } else if (cuentaSeleccionada.includes('naranja x') || cuentaSeleccionada.includes('natanjayx')) {
            setFormaPago('transferencia bancaria');
        }
    });

    // Cargar el dashboard al iniciar
    cargarDashboard();

    function cargarGastosTarjeta() {
        $.ajax({
            url: 'php/api/get_card_expenses.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#gastos-tarjeta-tbody');
                    tbody.empty();
                    if (response.data.length > 0) {
                        response.data.forEach(function(gasto) {
                            const fecha = new Date(gasto.fecha_compra).toLocaleDateString();
                            const montoTotal = formatNumber(gasto.monto_total);
                            const montoCuota = formatNumber(gasto.monto_por_cuota);
                            const descripcion = gasto.descripcion ? gasto.descripcion : '';

                            tbody.append(`
                                <tr>
                                    <td>${fecha}</td>
                                    <td>${gasto.tarjeta_nombre}</td>
                                    <td>${descripcion}</td>
                                    <td>$${montoTotal}</td>
                                    <td>${gasto.cuotas_totales}</td>
                                    <td>$${montoCuota}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="6" class="text-center">No hay gastos de tarjeta registrados.</td></tr>');
                    }
                } else {
                    console.error('Error al cargar gastos de tarjeta:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX para cargar gastos de tarjeta:', status, error);
                $('#gastos-tarjeta-tbody').empty().append('<tr><td colspan="6" class="text-center">Error al cargar los gastos de tarjeta.</td></tr>');
            }
        });
    }

    const originalCargarDashboard = cargarDashboard;
    cargarDashboard = function() {
        originalCargarDashboard();
        cargarGastosTarjeta();
    };

    function cargarCuentasGenerico(selector) {
        return $.ajax({
            url: 'php/api/get_cuentas.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione una cuenta...</option>';
                    response.data.forEach(function(cuenta) {
                        options += `<option value="${cuenta.id_cuenta}">${cuenta.nombre}</option>`;
                    });
                    $(selector).html(options);
                } else {
                    console.error('Error al cargar cuentas para el selector ' + selector + ':', response.message);
                }
            }
        });
    }

    $('#transferenciaModal').on('show.bs.modal', function () {
        cargarCuentasGenerico('#transferenciaCuentaOrigen');
        cargarCuentasGenerico('#transferenciaCuentaDestino');
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#transferenciaFecha').val(now.toISOString().slice(0, 16));
    });

    $('#transferenciaModal').on('hidden.bs.modal', function () {
        $('#formTransferencia')[0].reset();
    });

    $('#formTransferencia').submit(function(e) {
        e.preventDefault();

        const idCuentaOrigen = $('#transferenciaCuentaOrigen').val();
        const idCuentaDestino = $('#transferenciaCuentaDestino').val();
        const monto = formatNumberForServer($('#transferenciaMonto').val());

        if (idCuentaOrigen === idCuentaDestino) {
            mostrarToast('Error de Validación', 'La cuenta de origen y destino no pueden ser la misma.', 'warning');
            return;
        }
        if (monto <= 0) {
            mostrarToast('Error de Validación', 'El monto debe ser un número positivo.', 'warning');
            return;
        }

        const formData = {
            id_cuenta_origen: idCuentaOrigen,
            id_cuenta_destino: idCuentaDestino,
            monto: monto,
            fecha_transaccion: $('#transferenciaFecha').val(),
            descripcion: $('#transferenciaDescripcion').val() || 'Transferencia entre cuentas'
        };

        $.ajax({
            url: 'php/api/transferencia.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarToast('Transferencia Exitosa', 'Transferencia registrada con éxito.', 'success');
                    $('#transferenciaModal').modal('hide');
                    cargarDashboard();
                } else {
                    mostrarToast('Error de Transferencia', response.message, 'danger');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = 'Error al procesar la transferencia.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                mostrarToast('Error de Transferencia', errorMsg, 'danger');
                console.error('Error en la petición AJAX de transferencia:', textStatus, errorThrown, jqXHR.responseJSON);
            }
        });
    });

    // --- LÓGICA DE BÚSQUEDA Y ELIMINACIÓN ---

    // Buscador para la tabla de transacciones
    $('#transactionSearchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#transacciones-tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1)
        });
    });

    // Evento para el botón de eliminar transacción: Abre el modal de confirmación
    $(document).on('click', '.btn-delete-transaction', function() {
        const transactionId = $(this).data('tx-id');
        
        // Configurar el modal
        $('#confirmacionModalLabel').text('Confirmar Eliminación');
        $('#confirmacionModalBody').text('¿Estás seguro de que quieres eliminar esta transacción? Esta acción no se puede deshacer.');
        $('#btn-confirmar-accion').data('id', transactionId); // Guardar el ID en el botón de confirmar
        
        // Mostrar el modal
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmacionModal'));
        confirmModal.show();
    });

    // Evento para el botón de confirmación en el modal
    $('#btn-confirmar-accion').on('click', function() {
        const transactionId = $(this).data('id');
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmacionModal'));

        if (transactionId) {
            $.ajax({
                url: 'php/api/transactions/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_transaccion: transactionId }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        mostrarToast('Éxito', response.message, 'success');
                        cargarDashboard(); // Recargar todo para reflejar cambios
                    } else {
                        mostrarToast('Error', response.message, 'danger');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let errorMsg = 'Error al procesar la solicitud de eliminación.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                    mostrarToast('Error', errorMsg, 'danger');
                    console.error('Error en AJAX para eliminar:', textStatus, errorThrown);
                },
                complete: function() {
                    if(confirmModal) {
                        confirmModal.hide(); // Ocultar el modal después de la operación
                    }
                }
            });
        }
    });

});