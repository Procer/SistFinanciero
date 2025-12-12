<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_proyecto'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requiere el ID del viaje/proyecto.']);
    exit;
}

$id_proyecto = $data['id_proyecto'];

try {
    $stmt = $pdo->prepare("UPDATE viajes_proyectos SET activo = 0 WHERE id_proyecto = :id_proyecto");
    $stmt->execute([':id_proyecto' => $id_proyecto]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Viaje/Proyecto desactivado exitosamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'El Viaje/Proyecto no fue encontrado o ya estaba inactivo.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
