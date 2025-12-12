<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis por Cuenta</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Manrope -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <!-- Nueva estructura de menú para Análisis -->
                <a href="#analisisSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="list-group-item list-group-item-toggle active"><i class="fas fa-chart-line me-2"></i>Análisis</a>
                <div class="collapse show" id="analisisSubmenu">
                    <a href="analisis_cuentas.php" class="list-group-item sub-item active"><i class="fas fa-wallet me-2"></i>Por Cuenta</a>
                    <a href="analisis_proyectos.php" class="list-group-item sub-item"><i class="fas fa-project-diagram me-2"></i>Por Proyecto</a>
                    <a href="analisis_gastos.php" class="list-group-item sub-item"><i class="fas fa-money-bill-wave me-2"></i>Gastos Mensuales</a>
                </div>
                <a href="gestion.php" class="list-group-item"><i class="fas fa-cog me-2"></i>Gestión</a>
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
                            Bienvenido, Usuario
                        </span>
                    </div>
                </div>
            </nav>

            <main class="container-fluid px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Análisis de Cuentas</h1>
                    <div class="col-md-4">
                        <label for="selectCuentaAnalisis" class="form-label">Seleccionar Cuenta</label>
                        <select class="form-select" id="selectCuentaAnalisis">
                            <!-- Opciones de cuenta cargadas por AJAX -->
                        </select>
                    </div>
                </div>

                <!-- Contenedor para la data del análisis -->
                <div id="analisis-container" class="d-none">
                    <!-- Tarjetas de Resumen -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="text-uppercase fw-bold mb-1">Ingresos del Mes</div>
                                    <div class="h5 mb-0" id="total-ingresos-cuenta">$0.00</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="text-uppercase fw-bold mb-1">Gastos del Mes</div>
                                    <div class="h5 mb-0" id="total-gastos-cuenta">$0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico y Tabla de Transacciones -->
                    <div class="row mt-4">
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">Gastos por Categoría</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4">
                                        <canvas id="gastosPorCategoriaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">Últimos Movimientos de la Cuenta</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="transaccionesCuentaTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Descripción</th>
                                                    <th>Categoría</th>
                                                    <th>Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transaccionesCuentaBody">
                                                <!-- Datos se cargarán aquí vía AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="no-data-message" class="text-center">
                    <p>Seleccione una cuenta para comenzar el análisis.</p>
                </div>

                <hr class="my-5">

                <!-- Sección de Saldo Pre-Sueldo (Existente) -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary">Análisis de Saldo Pre-Sueldo (Banco Galicia)</h6>
                                <div class="form-group mb-0">
                                    <label for="selectYear" class="sr-only">Seleccionar Año</label>
                                    <select class="form-control form-control-sm" id="selectYear">
                                        <!-- Años se cargarán dinámicamente -->
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="saldoPreSueldoTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Mes</th>
                                                <th>Fecha de Sueldo Estimada</th>
                                                <th>Saldo Pre-Sueldo</th>
                                            </tr>
                                        </thead>
                                        <tbody id="saldoPreSueldoBody">
                                            <!-- Datos se cargarán aquí vía AJAX -->
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/analisis_cuentas.js"></script>
</body>
</html>
