<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Gastos Mensuales</title>
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
                <a href="#analisisSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="list-group-item list-group-item-toggle active"><i class="fas fa-chart-line me-2"></i>Análisis</a>
                <div class="collapse show" id="analisisSubmenu">
                    <a href="analisis_cuentas.php" class="list-group-item sub-item"><i class="fas fa-wallet me-2"></i>Por Cuenta</a>
                    <a href="analisis_proyectos.php" class="list-group-item sub-item"><i class="fas fa-project-diagram me-2"></i>Por Proyecto</a>
                    <a href="analisis_gastos.php" class="list-group-item sub-item active"><i class="fas fa-money-bill-wave me-2"></i>Gastos Mensuales</a>
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
                    <h1 class="h3 mb-0 text-gray-800">Análisis de Gastos Mensuales</h1>
                    <div class="col-md-4 d-flex align-items-center">
                        <label for="selectMes" class="form-label me-2">Mes:</label>
                        <select class="form-select me-3" id="selectMes"></select>
                        <label for="selectAnio" class="form-label me-2">Año:</label>
                        <select class="form-select" id="selectAnio"></select>
                    </div>
                </div>

                <div id="analisis-gastos-container">
                    <!-- Tarjeta de Gasto Total -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <div class="text-uppercase fw-bold mb-1">Total Gastado en el Mes</div>
                                    <div class="h5 mb-0" id="total-gastos-mes">$0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico y Tabla -->
                    <div class="row mt-4">
                        <div class="col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">Gastos por Categoría</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4">
                                        <canvas id="gastosCategoriasChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 fw-bold text-primary">Ranking de Gastos por Categoría</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="rankingGastosTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th>Total Gastado</th>
                                                    <th>% del Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rankingGastosBody">
                                                <!-- Datos vía AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="no-gastos-message" class="text-center d-none">
                    <p>No se encontraron gastos para el período seleccionado.</p>
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
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script src="js/analisis_gastos.js"></script>
</body>
</html>
