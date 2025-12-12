<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['nombre']) || !isset($data['fecha_inicio']) || !isset($data['presupuesto_total'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere nombre, fecha de inicio y presupuesto total.']);
    exit;
}

$nombre = trim($data['nombre']);
$fecha_inicio = $data['fecha_inicio'];
$fecha_fin = isset($data['fecha_fin']) && !empty($data['fecha_fin']) ? $data['fecha_fin'] : null;
$presupuesto_total = $data['presupuesto_total'];

if (empty($nombre) || empty($fecha_inicio) || !is_numeric($presupuesto_total) || $presupuesto_total < 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "INSERT INTO viajes_proyectos (nombre, fecha_inicio, fecha_fin, presupuesto_total) VALUES (:nombre, :fecha_inicio, :fecha_fin, :presupuesto_total)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
        ':presupuesto_total' => $presupuesto_total
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Viaje/Proyecto creado exitosamente.']);

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe un viaje/proyecto con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
