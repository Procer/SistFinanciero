<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_forma_pago'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requiere el ID de la forma de pago.']);
    exit;
}

$id = $data['id_forma_pago'];

// Por ahora, solo desactivamos. Se podría validar que no esté en uso.
try {
    $stmt = $pdo->prepare("UPDATE formas_pago SET activo = 0 WHERE id_forma_pago = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Forma de pago desactivada correctamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la forma de pago o ya estaba inactiva.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
