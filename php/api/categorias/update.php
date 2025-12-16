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
$id_categoria_padre = $data['id_categoria_padre'] ?? null;

if (empty($id_categoria) || empty($nombre)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

// Validación: una categoría no puede ser su propia padre
if ($id_categoria == $id_categoria_padre) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Una categoría no puede ser su propia subcategoría.']);
    exit;
}

// Si id_categoria_padre es vacío, 0, o no numérico, lo tratamos como NULL
if (empty($id_categoria_padre) || !is_numeric($id_categoria_padre)) {
    $id_categoria_padre = null;
}

$sql = "UPDATE categorias SET nombre = :nombre, id_categoria_padre = :id_categoria_padre WHERE id_categoria = :id_categoria";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':id_categoria_padre' => $id_categoria_padre,
        ':id_categoria' => $id_categoria
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Categoría actualizada exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios en la categoría.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // 23000 es el código de error para violación de integridad, como unique keys.
    if ($e->getCode() == 23000) { 
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe otra categoría con ese nombre en la misma jerarquía.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
