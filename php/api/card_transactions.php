<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos principales de gasto de tarjeta
if (!$data || !isset($data['id_tarjeta']) || !isset($data['monto_total']) || !isset($data['cuotas_totales']) || !isset($data['fecha_compra']) || !isset($data['id_cuenta']) || !isset($data['id_categoria']) || !isset($data['tipo_movimiento']) || !isset($data['id_forma_pago'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para el gasto de tarjeta.']);
    exit;
}

$id_tarjeta = $data['id_tarjeta'];
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
$monto_total = $data['monto_total'];
$cuotas_totales = $data['cuotas_totales'];
$fecha_compra = $data['fecha_compra'];

// Datos para la tabla de transacciones
$id_cuenta = $data['id_cuenta'];
$id_categoria = $data['id_categoria'];
$tipo_movimiento = $data['tipo_movimiento']; // Debería ser 'gasto'
$id_forma_pago = $data['id_forma_pago'];
$id_proyecto = $data['id_proyecto'] ?? null; // Nuevo campo id_proyecto

if (!is_numeric($id_tarjeta) || !is_numeric($monto_total) || $monto_total <= 0 || !is_numeric($cuotas_totales) || $cuotas_totales < 1) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos de gasto de tarjeta inválidos.']);
    exit;
}

$monto_por_cuota = round($monto_total / $cuotas_totales, 2);

try {
    $pdo->beginTransaction();

    // 1. Insertar en gastos_tarjeta
    $sql_gasto_tarjeta = "INSERT INTO gastos_tarjeta (id_tarjeta, descripcion, monto_total, cuotas_totales, cuotas_pagadas, monto_por_cuota, fecha_compra) VALUES (:id_tarjeta, :descripcion, :monto_total, :cuotas_totales, 0, :monto_por_cuota, :fecha_compra)";
    $stmt_gasto_tarjeta = $pdo->prepare($sql_gasto_tarjeta);
    $stmt_gasto_tarjeta->execute([
        ':id_tarjeta' => $id_tarjeta,
        ':descripcion' => $descripcion,
        ':monto_total' => $monto_total,
        ':cuotas_totales' => $cuotas_totales,
        ':monto_por_cuota' => $monto_por_cuota,
        ':fecha_compra' => $fecha_compra
    ]);
    $id_gasto_tarjeta_insertado = $pdo->lastInsertId();

    // 2. Insertar una transacción en la tabla 'transacciones'
    // Esta transacción inicial representa el gasto total en el dashboard.
    // La idea es que las cuotas se gestionen aparte o se refleje el gasto total al principio.
    // Para simplificar, insertamos el monto total como un gasto.
    // Futuramente, se podría ajustar la lógica para solo registrar la primera cuota o tener un módulo de resumen de tarjeta.
    $sql_transaccion = "INSERT INTO transacciones (id_cuenta, id_categoria, id_forma_pago, tipo_movimiento, monto, descripcion, fecha_transaccion, id_proyecto) VALUES (:id_cuenta, :id_categoria, :id_forma_pago, :tipo_movimiento, :monto, :descripcion, :fecha_transaccion, :id_proyecto)";
    $stmt_transaccion = $pdo->prepare($sql_transaccion);
    $stmt_transaccion->execute([
        ':id_cuenta' => $id_cuenta,
        ':id_categoria' => $id_categoria,
        ':id_forma_pago' => $id_forma_pago,
        ':tipo_movimiento' => $tipo_movimiento, // 'gasto'
        ':monto' => $monto_total, // Registramos el monto total como gasto inicial
        ':descripcion' => "Gasto en tarjeta: " . ($descripcion ?: "Sin descripción"),
        ':fecha_transaccion' => $fecha_compra,
        ':id_proyecto' => $id_proyecto // Incluir id_proyecto
    ]);


    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Gasto en tarjeta registrado exitosamente.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
