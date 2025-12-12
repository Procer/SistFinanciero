<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_gasto_fijo'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requiere el ID del gasto fijo.']);
    exit;
}

$id_gasto_fijo = $data['id_gasto_fijo'];

try {
    $stmt = $pdo->prepare("UPDATE gastos_fijos SET activo = 0 WHERE id_gasto_fijo = :id_gasto_fijo");
    $stmt->execute([':id_gasto_fijo' => $id_gasto_fijo]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Gasto fijo desactivado exitosamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'El gasto fijo no fue encontrado o ya estaba inactivo.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
