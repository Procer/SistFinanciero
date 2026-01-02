<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

require_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_transaccion']) || !is_numeric($data['id_transaccion'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de transacción inválido o no proporcionado.']);
    exit;
}

$id_transaccion = (int)$data['id_transaccion'];

try {
    $pdo->beginTransaction();

    // Simplemente eliminar la transacción. El saldo se calcula dinámicamente.
    $stmt = $pdo->prepare("DELETE FROM transacciones WHERE id_transaccion = ?");
    $stmt->execute([$id_transaccion]);

    // Verificar si la eliminación fue exitosa
    if ($stmt->rowCount() === 0) {
        throw new Exception("La transacción no existe o ya fue eliminada.", 404);
    }

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Transacción eliminada con éxito.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $http_code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    http_response_code($http_code);
    
    // Devolver un mensaje más específico si es un error conocido
    $message = ($e->getCode() == 404) ? $e->getMessage() : 'Error al procesar la solicitud de eliminación.';
    
    echo json_encode(['status' => 'error', 'message' => $message]);
    error_log("Error en delete.php: " . $e->getMessage());
}
?>
