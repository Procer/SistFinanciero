<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

try {
    // 1. OBTENER SALDOS ACTUALES DE LAS CUENTAS (LÓGICA CENTRAL)
    // Esta consulta calcula el saldo actual de cada cuenta considerando ingresos, gastos y transferencias.
    $cuentas_sql = "
        SELECT
            c.id_cuenta,
            c.nombre,
            c.tipo_cuenta,
            c.saldo_inicial,
            (c.saldo_inicial + COALESCE(mov.total, 0)) AS saldo_actual
        FROM
            cuentas c
        LEFT JOIN (
            SELECT
                id_cuenta,
                SUM(monto_ajustado) AS total
            FROM (
                -- Ingresos (suman)
                SELECT id_cuenta, monto AS monto_ajustado FROM transacciones WHERE tipo_movimiento = 'ingreso'
                UNION ALL
                -- Gastos (restan)
                SELECT id_cuenta, -monto AS monto_ajustado FROM transacciones WHERE tipo_movimiento = 'gasto'
                UNION ALL
                -- Transferencias Enviadas (restan del origen)
                SELECT id_cuenta, -monto AS monto_ajustado FROM transacciones WHERE tipo_movimiento = 'transferencia'
                UNION ALL
                -- Transferencias Recibidas (suman al destino)
                SELECT id_cuenta_destino AS id_cuenta, monto AS monto_ajustado FROM transacciones WHERE tipo_movimiento = 'transferencia' AND id_cuenta_destino IS NOT NULL
            ) AS movimientos
            WHERE id_cuenta IS NOT NULL
            GROUP BY id_cuenta
        ) AS mov ON c.id_cuenta = mov.id_cuenta
        WHERE c.activo = 1
        ORDER BY c.nombre ASC;
    ";
    $stmt_cuentas = $pdo->query($cuentas_sql);
    $cuentas = $stmt_cuentas->fetchAll(PDO::FETCH_ASSOC);

    // 2. CALCULAR SALDO TOTAL
    // Es la suma de los saldos actuales de todas las cuentas.
    $total_saldos = array_sum(array_column($cuentas, 'saldo_actual'));

    // 3. OBTENER INGRESOS Y GASTOS DEL MES
    $mes_actual = date('Y-m');
    $stmt_ingresos = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE tipo_movimiento = 'ingreso' AND DATE_FORMAT(fecha_transaccion, '%Y-%m') = ?");
    $stmt_ingresos->execute([$mes_actual]);
    $total_ingresos_mes = $stmt_ingresos->fetchColumn();

    $stmt_gastos = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE tipo_movimiento = 'gasto' AND DATE_FORMAT(fecha_transaccion, '%Y-%m') = ?");
    $stmt_gastos->execute([$mes_actual]);
    $total_gastos_mes = $stmt_gastos->fetchColumn();

    // 4. OBTENER ÚLTIMAS TRANSACCIONES DEL MES (CONSULTA MEJORADA)
    $transacciones_sql = "
        SELECT 
            t.id_transaccion, t.id_cuenta, t.id_cuenta_destino, t.id_categoria, t.id_forma_pago, t.id_proyecto,
            t.fecha_transaccion, t.descripcion, t.monto, t.tipo_movimiento,
            cat.nombre AS categoria_nombre, 
            fp.nombre AS forma_pago_nombre,
            cta_origen.nombre as cuenta_nombre,
            cta_destino.nombre as cuenta_destino_nombre
        FROM transacciones t
        LEFT JOIN categorias cat ON t.id_categoria = cat.id_categoria
        LEFT JOIN formas_pago fp ON t.id_forma_pago = fp.id_forma_pago
        LEFT JOIN cuentas cta_origen ON t.id_cuenta = cta_origen.id_cuenta
        LEFT JOIN cuentas cta_destino ON t.id_cuenta_destino = cta_destino.id_cuenta
        WHERE DATE_FORMAT(t.fecha_transaccion, '%Y-%m') = ?
        ORDER BY t.fecha_transaccion DESC
    ";
    $stmt_trans = $pdo->prepare($transacciones_sql);
    $stmt_trans->execute([$mes_actual]);
    $transacciones = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);

    // 5. CONSTRUIR RESPUESTA
    $response = [
        'status' => 'success',
        'message' => 'Resumen del dashboard obtenido correctamente.',
        'data' => [
            'total_saldos' => $total_saldos ? (float)$total_saldos : 0.00,
            'total_ingresos_mes' => $total_ingresos_mes ? (float)$total_ingresos_mes : 0.00,
            'total_gastos_mes' => $total_gastos_mes ? (float)$total_gastos_mes : 0.00,
            'cuentas' => $cuentas, // Aquí $cuentas ya tiene el saldo_actual
            'transacciones_mes' => $transacciones
        ],
        'debug_calculated_balances' => $cuentas // <-- LÍNEA DE DEPURACIÓN AÑADIDA
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log('Error en dashboard_summary.php (PDO): ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Error general: ' . $e->getMessage();
    error_log('Error en dashboard_summary.php (General): ' . $e->getMessage());
}

echo json_encode($response);
?>

