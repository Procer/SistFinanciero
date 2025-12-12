<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_gasto_fijo']) || !isset($data['nombre']) || !isset($data['monto']) || !isset($data['frecuencia'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere ID, nombre, monto y frecuencia.']);
    exit;
}

$id_gasto_fijo = $data['id_gasto_fijo'];
$nombre = trim($data['nombre']);
$monto = $data['monto'];
$frecuencia = $data['frecuencia'];
$dia_pago = isset($data['dia_pago']) && !empty($data['dia_pago']) ? $data['dia_pago'] : null;

if (!is_numeric($id_gasto_fijo) || empty($nombre) || !is_numeric($monto) || $monto <= 0 || !in_array($frecuencia, ['mensual', 'quincenal', 'anual'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "UPDATE gastos_fijos SET nombre = :nombre, monto = :monto, frecuencia = :frecuencia, dia_pago = :dia_pago WHERE id_gasto_fijo = :id_gasto_fijo";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':monto' => $monto,
        ':frecuencia' => $frecuencia,
        ':dia_pago' => $dia_pago,
        ':id_gasto_fijo' => $id_gasto_fijo
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Gasto fijo actualizado exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios en el gasto fijo.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe otro gasto fijo con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
