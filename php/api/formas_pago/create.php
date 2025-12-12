<?php
// php/api/formas_pago/create.php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nombre = $input['nombre'] ?? null;

    if (empty($nombre)) {
        $response['message'] = 'El nombre de la forma de pago es obligatorio.';
        echo json_encode($response);
        exit();
    }

    try {
        // Verificar si la forma de pago ya existe
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM formas_pago WHERE nombre = ?");
        $stmt_check->execute([$nombre]);
        if ($stmt_check->fetchColumn() > 0) {
            $response['message'] = "La forma de pago '{$nombre}' ya existe.";
            echo json_encode($response);
            exit();
        }

        // Insertar la nueva forma de pago
        $stmt_insert = $pdo->prepare("INSERT INTO formas_pago (nombre) VALUES (?)");
        $stmt_insert->execute([$nombre]);

        $response['status'] = 'success';
        $response['message'] = 'Forma de pago creada exitosamente.';

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
