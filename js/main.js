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

                    // Actualizar widgets de cuentas
                    let widgetsHtml = '';
                    if (data.cuentas.length > 0) {
                        data.cuentas.forEach(function(cuenta) {
                            let icon = 'fa-question-circle';
                            let bgColor = 'bg-secondary'; // Color por defecto
                            let customStyle = ''; // Inicializar customStyle

                            if (cuenta.nombre === 'Cuenta Naranja X') {
                                icon = 'fa-mobile-alt';
                                customStyle = 'background-color: #50007f !important;';
                            } else if (cuenta.nombre === 'Banco Galicia') {
                                icon = 'fa-university';
                                customStyle = 'background-color: #c85000 !important;';
                            } else if (cuenta.nombre === 'Mercado Pago') {
                                icon = 'fa-money-bill-alt';
                                bgColor = 'bg-info'; // Celeste (bg-info)
                            } else if (cuenta.tipo_cuenta === 'billetera') {
                                icon = 'fa-wallet';
                                bgColor = 'bg-dark'; // Otro color para billetera (negro/gris oscuro)
                            } else if (cuenta.tipo_cuenta === 'banco') {
                                icon = 'fa-university';
                                bgColor = 'bg-primary'; // Azul Bootstrap por defecto para otros bancos
                            } else if (cuenta.tipo_cuenta === 'otro') {
                                icon = 'fa-star';
                                bgColor = 'bg-light text-dark'; // Otros, con fondo claro y texto oscuro
                            }

                            widgetsHtml += `
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div class="card ${bgColor} text-white h-100" style="${customStyle}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="text-white-75 small">${cuenta.nombre}</div>
                                                    <div class="h4 fw-bold">$${formatNumber(cuenta.saldo_inicial)}</div>
                                                </div>
                                                <i class="fas ${icon} fa-2x opacity-50"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        widgetsHtml = '<div class="col-12"><p>No hay cuentas registradas.</p></div>';
                    }
                    console.log('HTML de widgets de cuentas generado:', widgetsHtml);
                    $('#cuentas-widgets-container').html(widgetsHtml);
                    console.log('Widgets de cuentas actualizados.');

                    // Actualizar tabla de transacciones
                    let transaccionesHtml = '';
                    if (data.transacciones_mes && data.transacciones_mes.length > 0) {
                        data.transacciones_mes.forEach(function(tx) {
                            const montoClass = tx.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger';
                            const montoSigno = tx.tipo_movimiento === 'ingreso' ? '+' : '-';
                            const descripcion = tx.descripcion ? tx.descripcion : '';

                            // Convertir el objeto tx a una cadena JSON y luego escaparla para el atributo de datos
                            const txData = encodeURIComponent(JSON.stringify(tx));

                            transaccionesHtml += `
                                <tr>
                                    <td>${new Date(tx.fecha_transaccion).toLocaleDateString()}</td>
                                    <td class="${montoClass} fw-bold">${montoSigno} $${formatNumber(tx.monto)}</td>
                                    <td>${tx.categoria_nombre}</td>
                                    <td>${tx.forma_pago_nombre || 'Efectivo'}</td>
                                    <td>${descripcion}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary btn-edit-transaction" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#transaccionModal"
                                                data-tx='${txData}'>
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        transaccionesHtml = '<tr><td colspan="6" class="text-center">No hay transacciones este mes.</td></tr>';
                    }
                    $('#transacciones-tbody').html(transaccionesHtml);
                    console.log('Tabla de transacciones actualizada.');

                } else {
                    console.error('Error al cargar el dashboard:', response.message);
                    // Aquí se podría mostrar un mensaje de error en la UI
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la petición AJAX:', textStatus, errorThrown);
                // Aquí se podría mostrar un mensaje de error en la UI
            }
        });
    }

    // Funciones para cargar selects del modal de transacción
    function cargarCuentasSelect() {
        return $.ajax({
            url: 'php/api/get_cuentas.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione...</option>';
                    response.data.forEach(function(cuenta) {
                        options += `<option value="${cuenta.id_cuenta}">${cuenta.nombre} (${cuenta.tipo_cuenta})</option>`;
                    });
                    $('#cuenta').html(options);
                } else {
                    console.error('Error al cargar cuentas:', response.message);
                }
            }
        });
    }

    function cargarCategoriasSelect(tipo) {
        return $.ajax({
            url: 'php/api/get_categorias.php?tipo=' + tipo, // Pasar el tipo como parámetro
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Seleccione...</option>';
                    response.data.forEach(function(categoria) {
                        options += `<option value="${categoria.id_categoria}">${categoria.nombre}</option>`; // Eliminar (tipo)
                    });
                    $('#categoria').html(options);
                } else {
                    console.error('Error al cargar categorías:', response.message);
                }
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
            url: 'php/api/tarjetas_credito/read.php', // Usamos el endpoint de read que ya existe
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
                    let options = '<option value="">N/A</option>'; // Opción para no asignar proyecto
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
        const txDataString = decodeURIComponent($(this).data('tx'));
        const tx = JSON.parse(txDataString);

        $('#transaccionModalLabel').text('Editar Transacción');

        // Usamos $.when para asegurarnos de que todos los selects se carguen antes de setear los valores
        $.when(
            cargarCuentasSelect(),
            cargarCategoriasSelect(tx.tipo_movimiento),
            cargarFormasPagoSelect(),
            cargarViajesProyectosSelect(),
            cargarTarjetasCreditoSelect() // Asegurarse que se cargue si es necesario
        ).done(function() {
            // Todos los selects se han cargado, ahora podemos setear los valores
            $('#transaccionId').val(tx.id_transaccion);
            $('#tipoMovimiento').val(tx.tipo_movimiento);
            $('#tipoMovimientoWrapper').hide();
            
            $('#montoTransaccion').val(tx.monto);
            $('#cuenta').val(tx.id_cuenta);
            $('#categoria').val(tx.id_categoria);
            $('#formaPago').val(tx.id_forma_pago).trigger('change');
            
            // Formatear la fecha para el input datetime-local
            const fecha = new Date(tx.fecha_transaccion);
            fecha.setMinutes(fecha.getMinutes() - fecha.getTimezoneOffset());
            $('#fecha').val(fecha.toISOString().slice(0, 16));
            
            $('#descripcion').val(tx.descripcion);
            
            if (tx.id_proyecto) {
                $('#viajeProyecto').val(tx.id_proyecto);
            } else {
                $('#viajeProyecto').val('');
            }
            
            // La edición de transacciones simples no maneja la lógica de tarjeta de crédito
            $('#camposTarjetaCredito').hide();
            $('#montoTransaccion').prop('required', true);
            $('#montoTotalTarjeta').prop('required', false);
        });
    });


    // Evento para los botones de "Nuevo Ingreso" y "Nuevo Gasto"
    $(document).on('click', '.btn-add-transaction', function() {
        const tipo = $(this).data('type');

        // Limpiar el ID de transacción para asegurar que es una creación
        $('#transaccionId').val('');

        // Cargar los selects
        cargarCuentasSelect();
        cargarCategoriasSelect(tipo);
        cargarFormasPagoSelect();
        cargarTarjetasCreditoSelect(); // Cargar las tarjetas de crédito
        cargarViajesProyectosSelect(); // Cargar los viajes/proyectos

        // Establecer la fecha y hora actual en el campo de fecha
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#fecha').val(now.toISOString().slice(0,16));


        // Preseleccionar el tipo de movimiento, ocultar el campo y cambiar el título
        $('#tipoMovimientoWrapper').hide();
        if (tipo === 'ingreso') {
            $('#tipoMovimiento').val('ingreso');
            $('#transaccionModalLabel').text('Nuevo Ingreso');
            $('#montoTransaccion').prop('required', true); // Requerir monto para ingresos
            $('#montoTotalTarjeta').prop('required', false).val(''); // Desrequerir monto tarjeta
            $('#camposTarjetaCredito').hide(); // Ocultar campos de tarjeta
            $('#tarjetaCredito').val('').prop('required', false);
            $('#cuotasTotales').val('1').prop('required', false);
        } else if (tipo === 'gasto') {
            $('#tipoMovimiento').val('gasto');
            $('#transaccionModalLabel').text('Nuevo Gasto');
            // La visibilidad de campos de tarjeta se maneja por el change de formaPago
            $('#montoTransaccion').prop('required', true);
            $('#montoTotalTarjeta').prop('required', false).val('');
        }
    });

    // Lógica para mostrar/ocultar campos de tarjeta de crédito
    $(document).on('change', '#formaPago', function() {
        const formaPagoSeleccionadaTexto = $(this).find('option:selected').text().toLowerCase();
        
        if (formaPagoSeleccionadaTexto.includes('crédito') || formaPagoSeleccionadaTexto.includes('tarjeta')) { // Asumimos que cualquier forma de pago que incluya "crédito" o "tarjeta" es una tarjeta de crédito
            $('#camposTarjetaCredito').show();
            $('#montoTransaccion').prop('required', false).val(''); // Desrequerir monto normal
            $('#montoTotalTarjeta').prop('required', true); // Requerir monto de tarjeta
            $('#tarjetaCredito').prop('required', true);
            $('#cuotasTotales').prop('required', true);
        } else {
            $('#camposTarjetaCredito').hide();
            $('#montoTransaccion').prop('required', true); // Requerir monto normal
            $('#montoTotalTarjeta').prop('required', false).val(''); // Desrequerir monto de tarjeta
            $('#tarjetaCredito').val('').prop('required', false);
            $('#cuotasTotales').val('1').prop('required', false);
        }
    });


    // Limpiar el formulario y mostrar campos ocultos cuando el modal se cierra
    $('#transaccionModal').on('hidden.bs.modal', function () {
        $('#formTransaccion')[0].reset();
        $('#transaccionId').val(''); // Asegurarse de limpiar el ID
        $('#transaccionModalLabel').text('Nueva Transacción');
        $('#tipoMovimientoWrapper').show(); // Asegurarse de que el campo sea visible la próxima vez
        $('#formaPago').prop('disabled', false); // Habilitar siempre el campo al cerrar
        $('#camposTarjetaCredito').hide(); // Ocultar campos de tarjeta
        $('#tarjetaCredito').val('').prop('required', false); // Limpiar y desrequerir
        $('#cuotasTotales').val('1').prop('required', false); // Limpiar y desrequerir
        $('#montoTransaccion').prop('required', true); // Asegurar que el monto normal sea requerido por defecto
        $('#montoTotalTarjeta').prop('required', false).val(''); // Limpiar y desrequerir monto tarjeta
        $('#viajeProyecto').val(''); // Limpiar el select de viaje/proyecto
    });

    // Enfocar el campo de monto cuando el modal se muestra completamente
    $('#transaccionModal').on('shown.bs.modal', function () {
        // Enfocar el monto correcto basado en si los campos de tarjeta están visibles
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
        const idProyecto = $('#viajeProyecto').val() === '' || $('#viajeProyecto').val() === 'N/A' ? null : $('#viajeProyecto').val(); // Obtener id_proyecto

        let url = 'php/api/transactions.php';
        let formData = {};

        // La edición no puede crear gastos de tarjeta, solo actualiza transacciones normales
        if ($('#camposTarjetaCredito').is(':visible') && !idTransaccion) {
            // Es un gasto de tarjeta NUEVO
            url = 'php/api/card_transactions.php';
            formData = {
                id_tarjeta: $('#tarjetaCredito').val(),
                descripcion: descripcion,
                monto_total: $('#montoTotalTarjeta').val(),
                cuotas_totales: $('#cuotasTotales').val(),
                fecha_compra: fechaTransaccion,
                // Otros campos necesarios para la transacción en la tabla `transacciones`
                id_cuenta: idCuenta,
                id_categoria: idCategoria,
                tipo_movimiento: tipoMovimiento, // Debería ser 'gasto'
                id_forma_pago: idFormaPago, // La forma de pago de la tarjeta
                id_proyecto: idProyecto // Incluir id_proyecto
            };
        } else {
            // Es una transacción normal (ingreso/gasto) o una EDICIÓN
            formData = {
                id_transaccion: idTransaccion ? idTransaccion : null,
                id_cuenta: idCuenta,
                id_categoria: idCategoria,
                id_forma_pago: idFormaPago,
                tipo_movimiento: tipoMovimiento,
                monto: $('#montoTransaccion').val(),
                descripcion: descripcion,
                fecha_transaccion: fechaTransaccion,
                id_proyecto: idProyecto // Incluir id_proyecto
            };
        }
        console.log("Datos a enviar:", formData);
        console.log("URL de la API:", url);

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#transaccionModal').modal('hide'); // Cerrar modal
                    // $('#formTransaccion')[0].reset(); // Limpiar formulario - ya lo hace hidden.bs.modal
                    cargarDashboard(); // Recargar el dashboard
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = 'Error al procesar la transacción.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                alert(errorMsg);
                console.error('Error en la petición AJAX:', textStatus, errorThrown, jqXHR.responseJSON);
            }
        });
    });

    // Lógica para autocompletar forma de pago según la cuenta seleccionada
    $(document).on('change', '#cuenta', function() {
        const cuentaSeleccionada = $(this).find('option:selected').text().toLowerCase();
        const formaPagoSelect = $('#formaPago');

        // Función para encontrar y seleccionar una opción
        const setFormaPago = (texto) => {
            const option = formaPagoSelect.find('option').filter(function() {
                return $(this).text().trim().toLowerCase() === texto;
            }).val();
            if (option) {
                formaPagoSelect.val(option).trigger('change'); // Trigger change para actualizar campos de tarjeta
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

    // Llamar a cargarGastosTarjeta al iniciar el dashboard
    const originalCargarDashboard = cargarDashboard;
    cargarDashboard = function() {
        originalCargarDashboard();
        cargarGastosTarjeta();
    };

});