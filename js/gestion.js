$(document).ready(function() {
    // --- Lógica para Categorías ---

    // Cargar ambas tablas de categorías al iniciar
    cargarCategoriasGasto();
    cargarCategoriasIngreso();

    /**
     * Carga y muestra la lista de categorías de GASTOS.
     */
    function cargarCategoriasGasto() {
        $.ajax({
            url: 'php/api/categorias/read.php?tipo=gasto',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#categorias-gasto-tbody');
                    tbody.empty();
                    response.data.forEach(function(categoria) {
                        tbody.append(`
                            <tr>
                                <td>${categoria.nombre}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editar-categoria" data-id="${categoria.id_categoria}" data-nombre="${categoria.nombre}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger eliminar-categoria" data-id="${categoria.id_categoria}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                }
            }
        });
    }

    /**
     * Carga y muestra la lista de categorías de INGRESOS.
     */
    function cargarCategoriasIngreso() {
        $.ajax({
            url: 'php/api/categorias/read.php?tipo=ingreso',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#categorias-ingreso-tbody');
                    tbody.empty();
                    response.data.forEach(function(categoria) {
                        tbody.append(`
                            <tr>
                                <td>${categoria.nombre}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editar-categoria" data-id="${categoria.id_categoria}" data-nombre="${categoria.nombre}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger eliminar-categoria" data-id="${categoria.id_categoria}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                }
            }
        });
    }

    // Preparar el modal para NUEVA categoría
    $('.btn-nueva-categoria').on('click', function() {
        $('#formCategoria')[0].reset();
        $('#categoriaId').val('');
        const tipo = $(this).data('tipo');
        $('#categoriaTipo').val(tipo);
        $('#categoriaModalLabel').text(`Nueva Categoría de ${tipo === 'ingreso' ? 'Ingreso' : 'Gasto'}`);
    });

    // Preparar modal para EDITAR categoría
    $(document).on('click', '.editar-categoria', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#categoriaId').val(id);
        $('#categoriaNombre').val(nombre);
        
        $('#categoriaModalLabel').text('Editar Categoría');
        $('#categoriaModal').modal('show');
    });

    /**
     * Maneja el envío del formulario para crear/editar una categoría.
     */
    $('#formCategoria').submit(function(e) {
        e.preventDefault();
        
        const id = $('#categoriaId').val();
        const url = id ? 'php/api/categorias/update.php' : 'php/api/categorias/create.php';
        
        const categoriaData = {
            id_categoria: id,
            nombre: $('#categoriaNombre').val(),
            tipo: $('#categoriaTipo').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(categoriaData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'info') {
                    alert(response.message);
                    $('#categoriaModal').modal('hide');
                    cargarCategoriasGasto();
                    cargarCategoriasIngreso();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                 let errorMsg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    // Manejar la eliminación de una categoría
    $(document).on('click', '.eliminar-categoria', function() {
        const id = $(this).data('id');
        const nombre = $(this).closest('tr').find('td:first').text();

        if (confirm(`¿Estás seguro de que quieres eliminar la categoría "${nombre}"?`)) {
            $.ajax({
                url: 'php/api/categorias/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_categoria: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarCategoriasGasto();
                        cargarCategoriasIngreso();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }
    });


    // --- Lógica para Formas de Pago ---

    // Cargar la tabla de formas de pago al iniciar
    cargarFormasPago();

    /**
     * Carga y muestra la lista de formas de pago.
     */
    function cargarFormasPago() {
        $.ajax({
            url: 'php/api/formas_pago/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#formas-pago-tbody');
                    tbody.empty(); // Limpiar tabla
                    if (response.data.length > 0) {
                        response.data.forEach(function(forma) {
                            tbody.append(`
                                <tr>
                                    <td>${forma.nombre}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info editar-fp" data-id="${forma.id_forma_pago}" data-nombre="${forma.nombre}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger eliminar-fp" data-id="${forma.id_forma_pago}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="2">No hay formas de pago registradas.</td></tr>');
                    }
                } else {
                    console.error('Error al cargar formas de pago:', response.message);
                }
            },
            error: function() {
                console.error('Error en la petición AJAX para cargar formas de pago.');
            }
        });
    }

    // Preparar modal para NUEVA forma de pago
    $('button[data-bs-target="#formaPagoModal"]').on('click', function() {
        $('#formFormaPago')[0].reset();
        $('#formaPagoId').val('');
        $('#formaPagoModalLabel').text('Nueva Forma de Pago');
    });

    // Preparar modal para EDITAR forma de pago
    $(document).on('click', '.editar-fp', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#formaPagoId').val(id);
        $('#formaPagoNombre').val(nombre);
        
        $('#formaPagoModalLabel').text('Editar Forma de Pago');
        $('#formaPagoModal').modal('show');
    });

    /**
     * Maneja el envío del formulario para crear/editar una forma de pago.
     */
    $('#formFormaPago').submit(function(e) {
        e.preventDefault();
        
        const id = $('#formaPagoId').val();
        const url = id ? 'php/api/formas_pago/update.php' : 'php/api/formas_pago/create.php';
        
        const formaPagoData = {
            id_forma_pago: id,
            nombre: $('#formaPagoNombre').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formaPagoData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'info') {
                    alert(response.message);
                    $('#formaPagoModal').modal('hide');
                    cargarFormasPago();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    // Manejar la eliminación de una forma de pago
    $(document).on('click', '.eliminar-fp', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        if (confirm(`¿Estás seguro de que quieres eliminar la forma de pago "${nombre}"?`)) {
            $.ajax({
                url: 'php/api/formas_pago/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_forma_pago: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarFormasPago();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }
    });


    // --- Lógica para Ingresos Fijos ---

    cargarIngresosFijos();

    // Preparar modal para NUEVO ingreso fijo
    $('button[data-bs-target="#ingresoFijoModal"]').on('click', function() {
        $('#formIngresoFijo')[0].reset();
        $('#ingresoFijoId').val('');
        $('#ingresoFijoModalLabel').text('Nuevo Ingreso Fijo');
    });

    // Preparar modal para EDITAR ingreso fijo
    $('#ingresos-fijos-tbody').on('click', '.editar-ingreso-fijo', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');
        const monto = row.data('monto');
        const frecuencia = row.data('frecuencia');
        const diaPago = row.data('dia-pago');
        const aumentoSimulado = row.data('aumento-simulado');

        $('#ingresoFijoId').val(id);
        $('#ingresoFijoNombre').val(nombre);
        $('#ingresoFijoMonto').val(monto);
        $('#ingresoFijoFrecuencia').val(frecuencia);
        $('#ingresoFijoDiaPago').val(diaPago);
        $('#ingresoFijoAumentoSimulado').val(aumentoSimulado);
        
        $('#ingresoFijoModalLabel').text('Editar Ingreso Fijo');
        $('#ingresoFijoModal').modal('show');
    });

    /**
     * Carga y muestra la lista de ingresos fijos.
     */
    function cargarIngresosFijos() {
        $.ajax({
            url: 'php/api/ingresos_fijos/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#ingresos-fijos-tbody');
                    tbody.empty();
                    if (response.data.length > 0) {
                        response.data.forEach(function(ingreso) {
                            let montoFormateado = parseFloat(ingreso.monto).toFixed(2);
                            let aumentoFormateado = parseFloat(ingreso.proximo_aumento_simulado_porcentaje).toFixed(2);
                            tbody.append(`
                                <tr data-id="${ingreso.id_ingreso_fijo}" data-nombre="${ingreso.nombre}" data-monto="${montoFormateado}" data-frecuencia="${ingreso.frecuencia}" data-dia-pago="${ingreso.dia_pago || ''}" data-aumento-simulado="${aumentoFormateado}">
                                    <td>${ingreso.nombre}</td>
                                    <td>$${montoFormateado}</td>
                                    <td>${ingreso.frecuencia}</td>
                                    <td>${ingreso.dia_pago || 'N/A'}</td>
                                    <td>${aumentoFormateado}%</td>
                                    <td>
                                        <button class="btn btn-sm btn-info editar-ingreso-fijo"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger eliminar-ingreso-fijo"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="6">No hay ingresos fijos registrados.</td></tr>');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar ingresos fijos:', error);
                $('#ingresos-fijos-tbody').empty().append('<tr><td colspan="6">Error al cargar los ingresos fijos.</td></tr>');
            }
        });
    }

    /**
     * Maneja el envío del formulario para crear/editar un ingreso fijo.
     */
    $('#formIngresoFijo').submit(function(e) {
        e.preventDefault();

        const id = $('#ingresoFijoId').val();
        const url = id ? 'php/api/ingresos_fijos/update.php' : 'php/api/ingresos_fijos/create.php';
        
        const ingresoFijoData = {
            id_ingreso_fijo: id,
            nombre: $('#ingresoFijoNombre').val(),
            monto: $('#ingresoFijoMonto').val(),
            frecuencia: $('#ingresoFijoFrecuencia').val(),
            dia_pago: $('#ingresoFijoDiaPago').val(),
            proximo_aumento_simulado_porcentaje: $('#ingresoFijoAumentoSimulado').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(ingresoFijoData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'info') {
                    alert(response.message);
                    $('#ingresoFijoModal').modal('hide');
                    cargarIngresosFijos();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    // Manejar la eliminación de un ingreso fijo
    $('#ingresos-fijos-tbody').on('click', '.eliminar-ingreso-fijo', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');

        if (confirm(`¿Estás seguro de que quieres eliminar el ingreso fijo "${nombre}"?`)) {
            $.ajax({
                url: 'php/api/ingresos_fijos/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_ingreso_fijo: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarIngresosFijos();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }
    });


    // --- Lógica para Viajes y Proyectos ---

    cargarViajesProyectos();

    // Preparar modal para NUEVO viaje/proyecto
    $('button[data-bs-target="#viajeProyectoModal"]').on('click', function() {
        $('#formViajeProyecto')[0].reset();
        $('#viajeProyectoId').val('');
        $('#viajeProyectoModalLabel').text('Nuevo Viaje/Proyecto');
    });

    // Preparar modal para EDITAR viaje/proyecto
    $('#viajes-proyectos-tbody').on('click', '.editar-viaje-proyecto', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');
        const fechaInicio = row.data('fecha-inicio');
        const fechaFin = row.data('fecha-fin');
        const presupuesto = row.data('presupuesto');

        $('#viajeProyectoId').val(id);
        $('#viajeProyectoNombre').val(nombre);
        $('#viajeProyectoFechaInicio').val(fechaInicio);
        $('#viajeProyectoFechaFin').val(fechaFin);
        $('#viajeProyectoPresupuesto').val(presupuesto);
        
        $('#viajeProyectoModalLabel').text('Editar Viaje/Proyecto');
        $('#viajeProyectoModal').modal('show');
    });

    /**
     * Carga y muestra la lista de viajes/proyectos.
     */
    function cargarViajesProyectos() {
        $.ajax({
            url: 'php/api/viajes_proyectos/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#viajes-proyectos-tbody');
                    tbody.empty();
                    if (response.data.length > 0) {
                        response.data.forEach(function(proyecto) {
                            let presupuestoFormatted = parseFloat(proyecto.presupuesto_total).toFixed(2);
                            tbody.append(`
                                <tr data-id="${proyecto.id_proyecto}" data-nombre="${proyecto.nombre}" data-fecha-inicio="${proyecto.fecha_inicio}" data-fecha-fin="${proyecto.fecha_fin || ''}" data-presupuesto="${presupuestoFormatted}">
                                    <td>${proyecto.nombre}</td>
                                    <td>${proyecto.fecha_inicio_formatted}</td>
                                    <td>${proyecto.fecha_fin_formatted}</td>
                                    <td>$${presupuestoFormatted}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info editar-viaje-proyecto"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger eliminar-viaje-proyecto"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5">No hay viajes o proyectos registrados.</td></tr>');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar viajes/proyectos:', error);
                $('#viajes-proyectos-tbody').empty().append('<tr><td colspan="5">Error al cargar los viajes/proyectos.</td></tr>');
            }
        });
    }

    /**
     * Maneja el envío del formulario para crear/editar un viaje/proyecto.
     */
    $('#formViajeProyecto').submit(function(e) {
        e.preventDefault();

        const id = $('#viajeProyectoId').val();
        const url = id ? 'php/api/viajes_proyectos/update.php' : 'php/api/viajes_proyectos/create.php';
        
        const viajeProyectoData = {
            id_proyecto: id,
            nombre: $('#viajeProyectoNombre').val(),
            fecha_inicio: $('#viajeProyectoFechaInicio').val(),
            fecha_fin: $('#viajeProyectoFechaFin').val(),
            presupuesto_total: $('#viajeProyectoPresupuesto').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(viajeProyectoData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'info') {
                    alert(response.message);
                    $('#viajeProyectoModal').modal('hide');
                    cargarViajesProyectos();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    // Manejar la eliminación de un viaje/proyecto
    $('#viajes-proyectos-tbody').on('click', '.eliminar-viaje-proyecto', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');

        if (confirm(`¿Estás seguro de que quieres eliminar el viaje/proyecto "${nombre}"?`)) {
            $.ajax({
                url: 'php/api/viajes_proyectos/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_proyecto: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarViajesProyectos();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }
    });


    // --- Lógica para Gastos Fijos ---

    cargarGastosFijos();

    // Preparar modal para NUEVO gasto fijo
    $('button[data-bs-target="#gastoFijoModal"]').on('click', function() {
        $('#formGastoFijo')[0].reset();
        $('#gastoFijoId').val('');
        $('#gastoFijoModalLabel').text('Nuevo Gasto Fijo');
    });

    // Preparar modal para EDITAR gasto fijo
    $('#gastos-fijos-tbody').on('click', '.editar-gasto-fijo', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');
        const monto = row.data('monto');
        const frecuencia = row.data('frecuencia');
        const diaPago = row.data('dia-pago');

        $('#gastoFijoId').val(id);
        $('#gastoFijoNombre').val(nombre);
        $('#gastoFijoMonto').val(monto);
        $('#gastoFijoFrecuencia').val(frecuencia);
        $('#gastoFijoDiaPago').val(diaPago);
        
        $('#gastoFijoModalLabel').text('Editar Gasto Fijo');
        $('#gastoFijoModal').modal('show');
    });

    /**
     * Carga y muestra la lista de gastos fijos.
     */
    function cargarGastosFijos() {
        $.ajax({
            url: 'php/api/gastos_fijos/read.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let tbody = $('#gastos-fijos-tbody');
                    tbody.empty();
                    if (response.data.length > 0) {
                        response.data.forEach(function(gasto) {
                            let montoFormateado = parseFloat(gasto.monto).toFixed(2);
                            tbody.append(`
                                <tr data-id="${gasto.id_gasto_fijo}" data-nombre="${gasto.nombre}" data-monto="${montoFormateado}" data-frecuencia="${gasto.frecuencia}" data-dia-pago="${gasto.dia_pago || ''}">
                                    <td>${gasto.nombre}</td>
                                    <td>$${montoFormateado}</td>
                                    <td>${gasto.frecuencia}</td>
                                    <td>${gasto.dia_pago || 'N/A'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info editar-gasto-fijo"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger eliminar-gasto-fijo"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5">No hay gastos fijos registrados.</td></tr>');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar gastos fijos:', error);
                $('#gastos-fijos-tbody').empty().append('<tr><td colspan="5">Error al cargar los gastos fijos.</td></tr>');
            }
        });
    }

    /**
     * Maneja el envío del formulario para crear/editar un gasto fijo.
     */
    $('#formGastoFijo').submit(function(e) {
        e.preventDefault();

        const id = $('#gastoFijoId').val();
        const url = id ? 'php/api/gastos_fijos/update.php' : 'php/api/gastos_fijos/create.php';
        
        const gastoFijoData = {
            id_gasto_fijo: id,
            nombre: $('#gastoFijoNombre').val(),
            monto: $('#gastoFijoMonto').val(),
            frecuencia: $('#gastoFijoFrecuencia').val(),
            dia_pago: $('#gastoFijoDiaPago').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(gastoFijoData),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'info') {
                    alert(response.message);
                    $('#gastoFijoModal').modal('hide');
                    cargarGastosFijos();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    // Manejar la eliminación de un gasto fijo
    $('#gastos-fijos-tbody').on('click', '.eliminar-gasto-fijo', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const nombre = row.data('nombre');

        if (confirm(`¿Estás seguro de que quieres eliminar el gasto fijo "${nombre}"?`)) {
            $.ajax({
                url: 'php/api/gastos_fijos/delete.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id_gasto_fijo: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarGastosFijos();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        }
    });

});