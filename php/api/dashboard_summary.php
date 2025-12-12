<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

try {
    // 1. Obtener saldo total de todas las cuentas
    $stmt = $pdo->query("SELECT SUM(saldo_inicial) AS total_saldos FROM cuentas WHERE activo = TRUE");
    $total_saldos = $stmt->fetchColumn();

    // 2. Obtener total de ingresos del mes actual
    $mes_actual = date('Y-m');
    $stmt = $pdo->prepare("SELECT SUM(monto) AS total_ingresos FROM transacciones WHERE tipo_movimiento = 'ingreso' AND DATE_FORMAT(fecha_transaccion, '%Y-%m') = ?");
    $stmt->execute([$mes_actual]);
    $total_ingresos_mes = $stmt->fetchColumn();

    // 3. Obtener total de gastos del mes actual
    $stmt = $pdo->prepare("SELECT SUM(monto) AS total_gastos FROM transacciones WHERE tipo_movimiento = 'gasto' AND DATE_FORMAT(fecha_transaccion, '%Y-%m') = ?");
    $stmt->execute([$mes_actual]);
    $total_gastos_mes = $stmt->fetchColumn();

    // 4. Obtener saldos individuales de cada cuenta
    $stmt = $pdo->query("SELECT id_cuenta, nombre, saldo_inicial, tipo_cuenta FROM cuentas WHERE activo = TRUE ORDER BY nombre ASC");
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Obtener últimas transacciones del mes
    $stmt_trans = $pdo->prepare("
        SELECT 
            t.id_transaccion,
            t.id_cuenta,
            t.id_categoria,
            t.id_forma_pago,
            t.id_proyecto,
            t.fecha_transaccion, 
            t.descripcion, 
            c.nombre AS categoria_nombre, 
            fp.nombre AS forma_pago_nombre,
            t.monto, 
            t.tipo_movimiento
        FROM transacciones t
        JOIN categorias c ON t.id_categoria = c.id_categoria
        LEFT JOIN formas_pago fp ON t.id_forma_pago = fp.id_forma_pago
        WHERE DATE_FORMAT(t.fecha_transaccion, '%Y-%m') = ?
        ORDER BY t.fecha_transaccion DESC
    ");
    $stmt_trans->execute([$mes_actual]);
    $transacciones = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'message' => 'Resumen del dashboard obtenido correctamente.',
        'data' => [
            'total_saldos' => $total_saldos ? (float)$total_saldos : 0.00,
            'total_ingresos_mes' => $total_ingresos_mes ? (float)$total_ingresos_mes : 0.00,
            'total_gastos_mes' => $total_gastos_mes ? (float)$total_gastos_mes : 0.00,
            'cuentas' => $cuentas,
            'transacciones_mes' => $transacciones
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log($response['message']);
} catch (Exception $e) {
    $response['message'] = 'Error general: ' . $e->getMessage();
    error_log($response['message']);
}

echo json_encode($response);
?>
