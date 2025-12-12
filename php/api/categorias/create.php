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

    try {
        // Verificar si la categoría ya existe para ese tipo
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = ? AND tipo = ?");
        $stmt_check->execute([$nombre, $tipo]);
        if ($stmt_check->fetchColumn() > 0) {
            $response['message'] = "La categoría '{$nombre}' del tipo '{$tipo}' ya existe.";
            echo json_encode($response);
            exit();
        }

        // Insertar la nueva categoría
        $stmt_insert = $pdo->prepare("INSERT INTO categorias (nombre, tipo) VALUES (?, ?)");
        $stmt_insert->execute([$nombre, $tipo]);

        $response['status'] = 'success';
        $response['message'] = 'Categoría creada exitosamente.';

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
