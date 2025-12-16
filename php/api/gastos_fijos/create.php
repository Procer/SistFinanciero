<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['nombre']) || !isset($data['monto']) || !isset($data['frecuencia'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere nombre, monto y frecuencia.']);
    exit;
}

$nombre = trim($data['nombre']);
$monto = (float)$data['monto'];
$frecuencia = $data['frecuencia'];
$dia_pago = isset($data['dia_pago']) && !empty($data['dia_pago']) ? $data['dia_pago'] : null;

if (empty($nombre) || !is_numeric($monto) || $monto <= 0 || !in_array($frecuencia, ['mensual', 'quincenal', 'anual'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "INSERT INTO gastos_fijos (nombre, monto, frecuencia, dia_pago) VALUES (:nombre, :monto, :frecuencia, :dia_pago)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':monto' => $monto,
        ':frecuencia' => $frecuencia,
        ':dia_pago' => $dia_pago
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Gasto fijo creado exitosamente.']);

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe un gasto fijo con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
