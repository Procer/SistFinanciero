<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_forma_pago']) || !isset($data['nombre'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requieren el ID y el nuevo nombre.']);
    exit;
}

$id = $data['id_forma_pago'];
$nombre = trim($data['nombre']);

if (empty($id) || empty($nombre)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE formas_pago SET nombre = :nombre WHERE id_forma_pago = :id");
    $stmt->execute([':nombre' => $nombre, ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Forma de pago actualizada correctamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    // Considerar un error específico si el nuevo nombre ya existe
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe una forma de pago con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
