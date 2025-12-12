<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_categoria']) || !isset($data['nombre'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere id y nombre.']);
    exit;
}

$id_categoria = $data['id_categoria'];
$nombre = trim($data['nombre']);

if (empty($id_categoria) || empty($nombre)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$sql = "UPDATE categorias SET nombre = :nombre WHERE id_categoria = :id_categoria";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':id_categoria' => $id_categoria
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Categoría actualizada exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios en la categoría.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe otra categoría con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
