<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['nombre']) || !isset($data['limite_credito']) || !isset($data['fecha_cierre_extracto']) || !isset($data['fecha_vencimiento_pago'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere nombre, límite de crédito, día de cierre y día de vencimiento.']);
    exit;
}

$nombre = trim($data['nombre']);
$banco = isset($data['banco']) ? trim($data['banco']) : null;
$limite_credito = $data['limite_credito'];
$fecha_cierre_extracto = $data['fecha_cierre_extracto'];
$fecha_vencimiento_pago = $data['fecha_vencimiento_pago'];

if (empty($nombre) || !is_numeric($limite_credito) || !is_numeric($fecha_cierre_extracto) || !is_numeric($fecha_vencimiento_pago) || $fecha_cierre_extracto < 1 || $fecha_cierre_extracto > 31 || $fecha_vencimiento_pago < 1 || $fecha_vencimiento_pago > 31) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "INSERT INTO tarjetas_credito (nombre, banco, limite_credito, fecha_cierre_extracto, fecha_vencimiento_pago) VALUES (:nombre, :banco, :limite_credito, :fecha_cierre_extracto, :fecha_vencimiento_pago)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':banco' => $banco,
        ':limite_credito' => $limite_credito,
        ':fecha_cierre_extracto' => $fecha_cierre_extracto,
        ':fecha_vencimiento_pago' => $fecha_vencimiento_pago
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Tarjeta de crédito creada exitosamente.']);

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe una tarjeta de crédito con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
