<?php 
require_once 'php/session_check.php'; 
check_permission('admin'); // Solo los administradores pueden acceder a esta página
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibos de Sueldo - Sistema Financiero</title>
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
                <a href="gestion.php" class="list-group-item"><i class="fas fa-cog me-2"></i>Gestión</a>
                <a href="recibos_sueldo.php" class="list-group-item"><i class="fas fa-file-invoice-dollar me-2"></i>Recibos Sueldo</a>
                <a href="configuracion.php" class="list-group-item"><i class="fas fa-cogs me-2"></i>Configuración</a>
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
                <h1 class="h3 mb-4 text-gray-800">Gestión y Análisis de Recibos de Sueldo</h1>

                <div class="row">

                    <!-- Columna Izquierda: Carga y Listado -->
                    <div class="col-lg-7">
                        <!-- Sección de Carga de Recibos de Sueldo -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Cargar Nuevo Recibo de Sueldo</h6>
                            </div>
                            <div class="card-body">
                                <form id="formReciboSueldo" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="imagenRecibo" class="form-label">Archivo de Imagen del Recibo</label>
                                        <input class="form-control" type="file" id="imagenRecibo" name="imagen_recibo" accept="image/*" required>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Analizar y Subir</button>
                                    </div>
                                    <div id="loadingSpinner" class="text-center mt-3" style="display:none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2">Analizando recibo con IA, esto puede tardar unos segundos...</p>
                                    </div>
                                    <div id="reciboSueldoFeedback" class="mt-3"></div>
                                </form>
                            </div>
                        </div>

                        <!-- Sección de Listado de Recibos de Sueldo -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Recibos de Sueldo Cargados</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="recibosSueldoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Período</th>
                                                <th>Empleado</th>
                                                <th>Sueldo Neto</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recibos-sueldo-tbody">
                                            <!-- Los recibos de sueldo se cargarán aquí -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Informes Visuales -->
                    <div class="col-lg-5">
                        <div class="card shadow mb-4">
                             <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">Análisis de Evolución</h6>
                            </div>
                            <div class="card-body">
                                <div id="reports-loading-spinner" class="text-center">
                                    <div class="spinner-border text-secondary" role="status">
                                        <span class="visually-hidden">Cargando reportes...</span>
                                    </div>
                                    <p class="mt-2 text-secondary">Cargando datos para los gráficos...</p>
                                </div>
                                <div id="charts-container" style="display:none;">
                                    <div class="mb-4">
                                        <h6 class="text-center">Evolución de Sueldo (Bruto vs Neto)</h6>
                                        <canvas id="sueldoChart"></canvas>
                                    </div>
                                    <hr>
                                    <div class="mb-4">
                                        <h6 class="text-center">Evolución de Descuentos</h6>
                                        <canvas id="descuentosChart"></canvas>
                                    </div>
                                    <hr>
                                    <div class="mb-4">
                                        <h6 class="text-center">% Crecimiento Sueldo Neto</h6>
                                        <canvas id="crecimientoChart"></canvas>
                                    </div>
                                </div>
                                <div id="no-data-message" class="text-center" style="display:none;">
                                    <p class="text-muted">No hay suficientes datos para generar los informes. Se necesita al menos un recibo cargado.</p>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script src="js/recibos_sueldo.js"></script>
</body>
</html>

<!-- Modal para Ver Detalles del Recibo -->
<div class="modal fade" id="reciboDetalleModal" tabindex="-1" aria-labelledby="reciboDetalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reciboDetalleModalLabel">Detalles del Recibo de Sueldo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información General</h6>
                        <p><strong>Empleado:</strong> <span id="detalleEmpleado"></span> (<span id="detalleCuilEmpleado"></span>)</p>
                        <p><strong>Empleador:</strong> <span id="detalleEmpleador"></span> (<span id="detalleCuitEmpleador"></span>)</p>
                        <p><strong>Período:</strong> <span id="detallePeriodo"></span></p>
                        <p><strong>Fecha de Pago:</strong> <span id="detalleFechaPago"></span></p>
                        <p><strong>Lugar de Pago:</strong> <span id="detalleLugarPago"></span></p>
                        <p><strong>Forma de Pago:</strong> <span id="detalleFormaPago"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Montos Clave</h6>
                        <p><strong>Sueldo Bruto:</strong> <span id="detalleSueldoBruto"></span></p>
                        <p><strong>Sueldo Neto:</strong> <span id="detalleSueldoNeto"></span></p>
                        <p><strong>Descuentos Totales:</strong> <span id="detalleDescuentosTotal"></span></p>
                    </div>
                </div>

                <hr>

                <h6>Detalle de Conceptos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Unidad</th>
                                <th>Haberes c/Aporte</th>
                                <th>Haberes s/Aporte</th>
                                <th>Descuentos</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="detalleConceptosTbody">
                            <!-- Detalles de conceptos se cargarán aquí -->
                        </tbody>
                    </table>
                </div>

                <hr>

                <h6>Último Depósito Cargas Sociales</h6>
                <div id="detalleUltimoDeposito">
                    <p><strong>Banco:</strong> <span id="detalleDepositoBanco"></span></p>
                    <p><strong>Período:</strong> <span id="detalleDepositoPeriodo"></span></p>
                    <p><strong>Fecha de Depósito:</strong> <span id="detalleDepositoFecha"></span></p>
                </div>

                <hr>

                <p class="text-center">
                    <a id="detalleImagenOriginal" href="#" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-image me-2"></i>Ver Imagen Original</a>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>