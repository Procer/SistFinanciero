<?php 
require_once 'php/session_check.php'; 
check_permission('admin'); // Solo los administradores pueden acceder a esta página
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión - Sistema Financiero</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Manrope -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Finanzas Pro</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="#" class="list-group-item"><i class="fas fa-exchange-alt me-2"></i>Transacciones</a>
                <a href="#" class="list-group-item"><i class="fas fa-credit-card me-2"></i>Tarjetas</a>
                <a href="#" class="list-group-item"><i class="fas fa-chart-pie me-2"></i>Reportes</a>
                <a href="#analisisSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-toggle"><i class="fas fa-chart-line me-2"></i>Análisis</a>
                <div class="collapse" id="analisisSubmenu">
                    <a href="analisis_cuentas.php" class="list-group-item sub-item"><i class="fas fa-wallet me-2"></i>Por Cuenta</a>
                    <a href="analisis_proyectos.php" class="list-group-item sub-item"><i class="fas fa-project-diagram me-2"></i>Por Proyecto</a>
                    <a href="analisis_gastos.php" class="list-group-item sub-item"><i class="fas fa-money-bill-wave me-2"></i>Gastos Mensuales</a>
                </div>
                <a href="gestion.php" class="list-group-item active"><i class="fas fa-cog me-2"></i>Gestión</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="btn btn-link" id="menu-toggle"><i class="fas fa-bars"></i></button>
                    <div class="ms-auto">
                        <span class="navbar-text">
                            <i class="fas fa-user me-2"></i>
                            Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm ms-3">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                            </a>
                        </span>
                    </div>
                </div>
            </nav>

            <main class="container-fluid px-4 py-3">
                <h1 class="h3 mb-4 text-gray-800">Gestión de Datos</h1>

                <div class="row">
                    <!-- Sección de Categorías de Ingreso -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Categorías de Ingresos</h6>
                                <button class="btn btn-primary btn-sm btn-nueva-categoria" data-bs-toggle="modal" data-bs-target="#categoriaModal" data-tipo="ingreso"><i class="fas fa-plus"></i> Nueva</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="categoriasIngresoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categorias-ingreso-tbody">
                                            <!-- Las categorías de ingreso se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sección de Categorías de Gasto -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Categorías de Gastos</h6>
                                <button class="btn btn-primary btn-sm btn-nueva-categoria" data-bs-toggle="modal" data-bs-target="#categoriaModal" data-tipo="gasto"><i class="fas fa-plus"></i> Nueva</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="categoriasGastoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categorias-gasto-tbody">
                                            <!-- Las categorías de gasto se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Sección de Formas de Pago -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Formas de Pago</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#formaPagoModal"><i class="fas fa-plus"></i> Nueva</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="formasPagoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="formas-pago-tbody">
                                            <!-- Las formas de pago se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- Sección de Cuentas -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Cuentas</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#cuentaModal"><i class="fas fa-plus"></i> Nueva Cuenta</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="cuentasTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Saldo Inicial</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuentas-tbody">
                                            <!-- Las cuentas se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Sección de Tarjetas de Crédito -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Tarjetas de Crédito</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tarjetaModal"><i class="fas fa-plus"></i> Nueva Tarjeta</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tarjetasCreditoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Banco</th>
                                                <th>Límite</th>
                                                <th>Cierre Extracto</th>
                                                <th>Vencimiento Pago</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tarjetas-credito-tbody">
                                            <!-- Las tarjetas de crédito se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sección de Ingresos Fijos -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Ingresos Fijos</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ingresoFijoModal"><i class="fas fa-plus"></i> Nuevo Ingreso Fijo</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="ingresosFijosTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Monto</th>
                                                <th>Frecuencia</th>
                                                <th>Día de Pago</th>
                                                <th>Aumento Simulado (%)</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ingresos-fijos-tbody">
                                            <!-- Los ingresos fijos se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sección de Viajes y Proyectos -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Viajes y Proyectos</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viajeProyectoModal"><i class="fas fa-plus"></i> Nuevo Viaje/Proyecto</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="viajesProyectosTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Presupuesto Total</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viajes-proyectos-tbody">
                                            <!-- Los viajes y proyectos se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Sección de Gastos Fijos -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Gastos Fijos</h6>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#gastoFijoModal"><i class="fas fa-plus"></i> Nuevo Gasto Fijo</button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="gastosFijosTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Monto</th>
                                                <th>Frecuencia</th>
                                                <th>Día de Pago</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gastos-fijos-tbody">
                                            <!-- Los gastos fijos se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Modal para Nueva/Editar Categoría -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="categoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoriaModalLabel">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCategoria">
                        <input type="hidden" id="categoriaId" name="id_categoria">
                        <input type="hidden" id="categoriaTipo" name="tipo">
                        <div class="mb-3">
                            <label for="categoriaNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="categoriaNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoriaPadre" class="form-label">Categoría Padre (Opcional)</label>
                            <select class="form-select" id="categoriaPadre" name="id_categoria_padre">
                                <!-- Opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Forma de Pago -->
    <div class="modal fade" id="formaPagoModal" tabindex="-1" aria-labelledby="formaPagoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formaPagoModalLabel">Nueva Forma de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formFormaPago">
                        <input type="hidden" id="formaPagoId" name="id_forma_pago">
                        <div class="mb-3">
                            <label for="formaPagoNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="formaPagoNombre" name="nombre" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Cuenta -->
    <div class="modal fade" id="cuentaModal" tabindex="-1" aria-labelledby="cuentaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cuentaModalLabel">Nueva Cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCuenta">
                        <input type="hidden" id="cuentaId" name="id_cuenta">
                        <div class="mb-3">
                            <label for="cuentaNombre" class="form-label">Nombre de la Cuenta</label>
                            <input type="text" class="form-control" id="cuentaNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="cuentaTipo" class="form-label">Tipo de Cuenta</label>
                            <select class="form-select" id="cuentaTipo" name="tipo_cuenta" required>
                                <option value="banco">Banco</option>
                                <option value="billetera">Billetera</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cuentaSaldo" class="form-label">Saldo Inicial</label>
                            <input type="number" class="form-control" id="cuentaSaldo" name="saldo_inicial" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Tarjeta de Crédito -->
    <div class="modal fade" id="tarjetaModal" tabindex="-1" aria-labelledby="tarjetaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tarjetaModalLabel">Nueva Tarjeta de Crédito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTarjeta">
                        <input type="hidden" id="tarjetaId" name="id_tarjeta">
                        <div class="mb-3">
                            <label for="tarjetaNombre" class="form-label">Nombre de la Tarjeta</label>
                            <input type="text" class="form-control" id="tarjetaNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="tarjetaBanco" class="form-label">Banco (opcional)</label>
                            <input type="text" class="form-control" id="tarjetaBanco" name="banco">
                        </div>
                        <div class="mb-3">
                            <label for="tarjetaLimite" class="form-label">Límite de Crédito</label>
                            <input type="number" class="form-control" id="tarjetaLimite" name="limite_credito" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="tarjetaCierreExtracto" class="form-label">Día de Cierre de Extracto</label>
                            <input type="number" class="form-control" id="tarjetaCierreExtracto" name="fecha_cierre_extracto" min="1" max="31" required>
                        </div>
                        <div class="mb-3">
                            <label for="tarjetaVencimientoPago" class="form-label">Día de Vencimiento de Pago</label>
                            <input type="number" class="form-control" id="tarjetaVencimientoPago" name="fecha_vencimiento_pago" min="1" max="31" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo/Editar Ingreso Fijo -->
    <div class="modal fade" id="ingresoFijoModal" tabindex="-1" aria-labelledby="ingresoFijoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingresoFijoModalLabel">Nuevo Ingreso Fijo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formIngresoFijo">
                        <input type="hidden" id="ingresoFijoId" name="id_ingreso_fijo">
                        <div class="mb-3">
                            <label for="ingresoFijoNombre" class="form-label">Nombre del Ingreso</label>
                            <input type="text" class="form-control" id="ingresoFijoNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="ingresoFijoMonto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="ingresoFijoMonto" name="monto" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="ingresoFijoFrecuencia" class="form-label">Frecuencia</label>
                            <select class="form-select" id="ingresoFijoFrecuencia" name="frecuencia" required>
                                <option value="mensual">Mensual</option>
                                <option value="quincenal">Quincenal</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ingresoFijoDiaPago" class="form-label">Día de Pago (1-31)</label>
                            <input type="number" class="form-control" id="ingresoFijoDiaPago" name="dia_pago" min="1" max="31">
                        </div>
                        <div class="mb-3">
                            <label for="ingresoFijoAumentoSimulado" class="form-label">Aumento Simulado (%)</label>
                            <input type="number" class="form-control" id="ingresoFijoAumentoSimulado" name="proximo_aumento_simulado_porcentaje" step="0.01">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo/Editar Viaje/Proyecto -->
    <div class="modal fade" id="viajeProyectoModal" tabindex="-1" aria-labelledby="viajeProyectoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viajeProyectoModalLabel">Nuevo Viaje/Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formViajeProyecto">
                        <input type="hidden" id="viajeProyectoId" name="id_proyecto">
                        <div class="mb-3">
                            <label for="viajeProyectoNombre" class="form-label">Nombre del Viaje/Proyecto</label>
                            <input type="text" class="form-control" id="viajeProyectoNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="viajeProyectoFechaInicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="viajeProyectoFechaInicio" name="fecha_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="viajeProyectoFechaFin" class="form-label">Fecha de Fin (Opcional)</label>
                            <input type="date" class="form-control" id="viajeProyectoFechaFin" name="fecha_fin">
                        </div>
                        <div class="mb-3">
                            <label for="viajeProyectoPresupuesto" class="form-label">Presupuesto Total</label>
                            <input type="number" class="form-control" id="viajeProyectoPresupuesto" name="presupuesto_total" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo/Editar Gasto Fijo -->
    <div class="modal fade" id="gastoFijoModal" tabindex="-1" aria-labelledby="gastoFijoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gastoFijoModalLabel">Nuevo Gasto Fijo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formGastoFijo">
                        <input type="hidden" id="gastoFijoId" name="id_gasto_fijo">
                        <div class="mb-3">
                            <label for="gastoFijoNombre" class="form-label">Nombre del Gasto</label>
                            <input type="text" class="form-control" id="gastoFijoNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="gastoFijoMonto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="gastoFijoMonto" name="monto" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="gastoFijoFrecuencia" class="form-label">Frecuencia</label>
                            <select class="form-select" id="gastoFijoFrecuencia" name="frecuencia" required>
                                <option value="mensual">Mensual</option>
                                <option value="quincenal">Quincenal</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="gastoFijoDiaPago" class="form-label">Día de Pago (1-31)</label>
                            <input type="number" class="form-control" id="gastoFijoDiaPago" name="dia_pago" min="1" max="31">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS (main.js para el sidebar-toggle y un nuevo gestion.js) -->
    <script src="js/main.js"></script>
    <script src="js/gestion.js"></script>
</body>
</html>