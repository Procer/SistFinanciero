<?php
// php/api/categorias/create.php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nombre = $input['nombre'] ?? null;
    $tipo = $input['tipo'] ?? null;
    $id_categoria_padre = $input['id_categoria_padre'] ?? null;

    if (empty($nombre) || empty($tipo)) {
        $response['message'] = 'El nombre y el tipo de la categoría son obligatorios.';
        echo json_encode($response);
        exit();
    }

    if (!in_array($tipo, ['ingreso', 'gasto'])) {
        $response['message'] = 'El tipo de categoría debe ser "ingreso" o "gasto".';
        echo json_encode($response);
        exit();
    }

    // Si id_categoria_padre es vacío o no es un número válido, tratarlo como NULL
    if (empty($id_categoria_padre) || !is_numeric($id_categoria_padre)) {
        $id_categoria_padre = null;
    }

    try {
        // Verificar si la categoría ya existe para ese tipo (evitar duplicados exactos)
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = ? AND tipo = ? AND (id_categoria_padre = ? OR (id_categoria_padre IS NULL AND ? IS NULL))");
        $stmt_check->execute([$nombre, $tipo, $id_categoria_padre, $id_categoria_padre]);
        if ($stmt_check->fetchColumn() > 0) {
            $response['message'] = "La categoría '{$nombre}' con esta jerarquía ya existe.";
            http_response_code(409); // Conflict
            echo json_encode($response);
            exit();
        }

        // Insertar la nueva categoría
        $sql = "INSERT INTO categorias (nombre, tipo, id_categoria_padre) VALUES (?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([$nombre, $tipo, $id_categoria_padre]);

        $response['status'] = 'success';
        $response['message'] = 'Categoría creada exitosamente.';
        $response['data'] = ['id' => $pdo->lastInsertId()];
        http_response_code(201); // Created

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
        http_response_code(500);
    }

} else {
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
    http_response_code(405); // Method Not Allowed
}

echo json_encode($response);
?>
