<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_categoria'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requiere el ID de la categoría.']);
    exit;
}

$id_categoria = $data['id_categoria'];

// Aquí se podría agregar una validación para no permitir eliminar categorías que ya están en uso en transacciones.
// Por ahora, solo la desactivamos.

$sql = "UPDATE categorias SET activo = 0 WHERE id_categoria = :id_categoria";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_categoria' => $id_categoria]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Categoría desactivada exitosamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'La categoría no fue encontrada o ya estaba inactiva.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
