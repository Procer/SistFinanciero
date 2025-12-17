<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../session_check.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_recibo = $input['id_recibo'] ?? null;

    if (empty($id_recibo)) {
        http_response_code(400);
        $response['message'] = 'ID del recibo es obligatorio.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Obtener la ruta de la IMAGEN antes de eliminar el registro
        $stmt_get_path = $pdo->prepare("SELECT ruta_imagen FROM recibos_sueldo WHERE id_recibo = ?");
        $stmt_get_path->execute([$id_recibo]);
        $ruta_imagen = $stmt_get_path->fetchColumn();

        if (!$ruta_imagen) {
            throw new Exception("Recibo no encontrado o ruta de imagen no especificada.");
        }

        // 2. Eliminar cualquier transacción asociada al recibo (si se implementó)
        // Opcional: Dependerá de cómo se maneje la relación. Aquí, si existe id_recibo en transacciones, se elimina.
        // Asegúrate que la tabla `transacciones` tenga la FK a `recibos_sueldo` configurada para ON DELETE CASCADE
        // O bien, eliminar explícitamente las transacciones aquí:
        // $stmt_delete_tx = $pdo->prepare("DELETE FROM transacciones WHERE id_recibo = ?");
        // $stmt_delete_tx->execute([$id_recibo]);

        // 3. Eliminar el registro de la base de datos
        $stmt_delete_recibo = $pdo->prepare("DELETE FROM recibos_sueldo WHERE id_recibo = ?");
        $stmt_delete_recibo->execute([$id_recibo]);

        if ($stmt_delete_recibo->rowCount() === 0) {
            throw new Exception("No se encontró el recibo o no se pudo eliminar.");
        }

        // 4. Eliminar el archivo de IMAGEN del servidor
        $full_path_imagen = '../../../' . $ruta_imagen; // Ajustar la ruta si es necesario
        if (file_exists($full_path_imagen)) {
            unlink($full_path_imagen);
        } else {
            // No es un error crítico si el archivo ya no existe, solo loguear
            error_log("Archivo de imagen no encontrado para eliminar: " . $full_path_imagen);
        }

        $pdo->commit();
        $response['status'] = 'success';
        $response['message'] = 'Recibo de sueldo y imagen asociada eliminados exitosamente.';

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        $response['message'] = 'Error al eliminar el recibo: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>