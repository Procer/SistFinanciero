<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Financiero Personal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts: Manrope -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                <a href="index.php" class="list-group-item active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
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
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                </div>

                <div class="row">
                    <!-- Tarjeta de Saldo Total -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold mb-1">Saldo Total</div>
                                        <div class="h5 mb-0" id="total-saldos">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-wallet fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Ingresos del Mes -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold mb-1">Ingresos del Mes</div>
                                        <div class="h5 mb-0" id="ingresos-mes">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-outline-light btn-sm rounded-circle btn-add-transaction" data-type="ingreso" data-bs-toggle="modal" data-bs-target="#transaccionModal" style="width: 2.5rem; height: 2.5rem;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Gastos del Mes -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold mb-1">Gastos del Mes</div>
                                        <div class="h5 mb-0" id="gastos-mes">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-outline-light btn-sm rounded-circle btn-add-transaction" data-type="gasto" data-bs-toggle="modal" data-bs-target="#transaccionModal" style="width: 2.5rem; height: 2.5rem;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-arrow-down fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h4 class="mb-3 text-gray-800">Mis Cuentas</h4>
                        <div class="row" id="cuentas-widgets-container">
                            <!-- Los widgets de las cuentas se cargarán aquí vía AJAX -->
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">Últimas Transacciones del Mes</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="transaccionesTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Categoría</th>
                                                <th>Forma de Pago</th>
                                                <th>Descripción</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="transacciones-tbody">
                                            <!-- Las transacciones se cargarán aquí vía AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">Últimos Gastos de Tarjeta</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="gastosTarjetaTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tarjeta</th>
                                                <th>Descripción</th>
                                                <th>Monto Total</th>
                                                <th>Cuotas</th>
                                                <th>Monto por Cuota</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gastos-tarjeta-tbody">
                                            <!-- Los gastos de tarjeta se cargarán aquí vía AJAX -->
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>

    <!-- Modal para Nueva Transacción -->
    <div class="modal fade" id="transaccionModal" tabindex="-1" aria-labelledby="transaccionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="padding: 10px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="transaccionModalLabel">Nueva Transacción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form id="formTransaccion">
                        <input type="hidden" id="transaccionId" name="id_transaccion">
                        <div class="mb-3" id="tipoMovimientoWrapper">
                            <label for="tipoMovimiento" class="form-label">Tipo de Movimiento</label>
                            <select class="form-select" id="tipoMovimiento" name="tipo_movimiento" required>
                                <option value="">Seleccione...</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="gasto">Gasto</option>
                            </select>
                        </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="montoTransaccion" class="form-label">Monto</label>
                                                    <input type="number" class="form-control" id="montoTransaccion" name="monto_transaccion" step="0.01" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="cuenta" class="form-label">Cuenta</label>
                                                    <select class="form-select" id="cuenta" name="id_cuenta" required>
                                                        <!-- Opciones cargadas por AJAX -->
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="categoria" class="form-label">Categoría</label>
                                                    <select class="form-select" id="categoria" name="id_categoria" required>
                                                        <!-- Opciones cargadas por AJAX -->
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="formaPago" class="form-label">Forma de Pago</label>
                                                    <select class="form-select" id="formaPago" name="id_forma_pago">
                                                        <option value="">N/A</option>
                                                        <!-- Opciones cargadas por AJAX -->
                                                    </select>
                                                </div>
                                                <div class="mb-3" id="camposTarjetaCredito" style="display: none;">
                                                    <hr>
                                                    <h6 class="mb-3">Detalles de Tarjeta de Crédito</h6>
                                                    <div class="mb-3">
                                                        <label for="tarjetaCredito" class="form-label">Tarjeta de Crédito</label>
                                                        <select class="form-select" id="tarjetaCredito" name="id_tarjeta_credito">
                                                            <!-- Opciones cargadas por AJAX -->
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="montoTotalTarjeta" class="form-label">Monto Total de Compra</label>
                                                        <input type="number" class="form-control" id="montoTotalTarjeta" name="monto_total_tarjeta" step="0.01">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="cuotasTotales" class="form-label">Número de Cuotas</label>
                                                    <input type="number" class="form-control" id="cuotasTotales" name="cuotas_totales" min="1" value="1">
                                                </div>
                                                    <hr>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="viajeProyecto" class="form-label">Viaje/Proyecto (Opcional)</label>
                                                    <select class="form-select" id="viajeProyecto" name="id_proyecto">
                                                        <option value="">Seleccione...</option>
                                                        <option value="N/A">N/A</option>
                                                        <!-- Opciones cargadas por AJAX -->
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="fecha" class="form-label">Fecha y Hora</label>
                                                    <input type="datetime-local" class="form-control" id="fecha" name="fecha_transaccion">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Guardar Transacción</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>