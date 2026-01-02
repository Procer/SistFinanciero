<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

// Validar y sanear las entradas
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

if ($mes < 1 || $mes > 12 || $anio < 2000 || $anio > 2100) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros de fecha inválidos.']);
    exit;
}

try {
    // La variable $pdo ya está disponible desde db_connection.php
    
    // --- 1. Resumen (Total y Burn Rate) ---
    $stmtResumen = $pdo->prepare("
        SELECT
            SUM(monto_gasto) AS total_gastos
        FROM v_transacciones_enriquecidas
        WHERE mes = ? AND anio = ? AND tipo_movimiento = 'gasto'
    ");
    $stmtResumen->execute([$mes, $anio]);
    $resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);
    $totalGastos = $resumen['total_gastos'] ?? 0;

    $diasDelMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
    $burnRateDiario = ($diasDelMes > 0 && $totalGastos > 0) ? $totalGastos / $diasDelMes : 0;

    // --- 2. Gastos por Categoría ---
    $stmtCategorias = $pdo->prepare("
        SELECT
            categoria,
            SUM(monto_gasto) AS total_gastado,
            (SUM(monto_gasto) / ?) * 100 AS porcentaje_del_total
        FROM v_transacciones_enriquecidas
        WHERE mes = ? AND anio = ? AND tipo_movimiento = 'gasto' AND categoria IS NOT NULL
        GROUP BY categoria
        ORDER BY total_gastado DESC
    ");
    $stmtCategorias->execute([$totalGastos ?: 1, $mes, $anio]);
    $gastosPorCategoria = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. Gastos por Forma de Pago ---
    $stmtFormaPago = $pdo->prepare("
        SELECT
            forma_pago,
            SUM(monto_gasto) AS total_gastado,
            (SUM(monto_gasto) / ?) * 100 AS porcentaje_del_total
        FROM v_transacciones_enriquecidas
        WHERE mes = ? AND anio = ? AND tipo_movimiento = 'gasto' AND forma_pago IS NOT NULL
        GROUP BY forma_pago
        ORDER BY total_gastado DESC
    ");
    $stmtFormaPago->execute([$totalGastos ?: 1, $mes, $anio]);
    $gastosPorFormaPago = $stmtFormaPago->fetchAll(PDO::FETCH_ASSOC);

    // --- 4. Detalle de Gastos ---
    $stmtDetalle = $pdo->prepare("
        SELECT
            DATE_FORMAT(fecha_transaccion, '%d/%m/%Y') as fecha,
            descripcion,
            categoria,
            forma_pago,
            monto_gasto AS monto,
            cuenta_tarjeta
        FROM v_transacciones_enriquecidas
        WHERE mes = ? AND anio = ? AND tipo_movimiento = 'gasto'
        ORDER BY fecha_transaccion DESC
    ");
    $stmtDetalle->execute([$mes, $anio]);
    $detalleGastos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);


    // --- Ensamblar la respuesta final ---
    $respuesta = [
        'resumen' => [
            'total_gastos' => (float)$totalGastos,
            'burn_rate_diario' => (float)$burnRateDiario,
        ],
        'gastos_por_categoria' => $gastosPorCategoria,
        'gastos_por_forma_pago' => $gastosPorFormaPago,
        'detalle_gastos' => $detalleGastos,
        'periodo' => [
            'mes' => $mes,
            'anio' => $anio
        ]
    ];

    echo json_encode($respuesta);

} catch (Exception $e) {
    http_response_code(500);
    // No mostrar errores detallados en producción
    echo json_encode(['error' => 'Error al procesar la solicitud.', 'details' => $e->getMessage()]);
}

?>