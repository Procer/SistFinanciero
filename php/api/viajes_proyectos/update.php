<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_proyecto']) || !isset($data['nombre']) || !isset($data['fecha_inicio']) || !isset($data['presupuesto_total'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere ID, nombre, fecha de inicio y presupuesto total.']);
    exit;
}

$id_proyecto = (int)$data['id_proyecto'];
$nombre = trim($data['nombre']);
$fecha_inicio = $data['fecha_inicio'];
$fecha_fin = isset($data['fecha_fin']) && !empty($data['fecha_fin']) ? $data['fecha_fin'] : null;
$presupuesto_total = (float)$data['presupuesto_total'];

if (!is_numeric($id_proyecto) || empty($nombre) || empty($fecha_inicio) || !is_numeric($presupuesto_total) || $presupuesto_total < 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "UPDATE viajes_proyectos SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, presupuesto_total = :presupuesto_total WHERE id_proyecto = :id_proyecto";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
        ':presupuesto_total' => $presupuesto_total,
        ':id_proyecto' => $id_proyecto
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Viaje/Proyecto actualizado exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios en el Viaje/Proyecto.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe otro viaje/proyecto con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
