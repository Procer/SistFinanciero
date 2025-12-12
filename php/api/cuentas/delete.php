<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_cuenta'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Se requiere el ID de la cuenta.']);
    exit;
}

$id_cuenta = $data['id_cuenta'];

// Lógica para verificar si la cuenta puede ser eliminada (ej. no tiene transacciones asociadas)
// Por simplicidad, por ahora solo la desactivaremos.

$sql = "UPDATE cuentas SET activo = 0 WHERE id_cuenta = :id_cuenta";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_cuenta' => $id_cuenta]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Cuenta desactivada exitosamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'La cuenta no fue encontrada o ya estaba inactiva.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
