<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../session_check.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_recibo = $_GET['id'] ?? null;

    if (empty($id_recibo)) {
        http_response_code(400);
        $response['message'] = 'ID de recibo es obligatorio.';
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "SELECT * FROM recibos_sueldo WHERE id_recibo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_recibo]);
        $recibo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recibo) {
            // Decodificar los campos JSON para enviarlos como objetos/arrays
            $recibo['detalle_json'] = json_decode($recibo['detalle_json'], true);
            $recibo['ultimo_deposito_cargas_sociales_json'] = json_decode($recibo['ultimo_deposito_cargas_sociales_json'], true);

            $response['status'] = 'success';
            $response['message'] = 'Detalles del recibo obtenidos exitosamente.';
            $response['data'] = $recibo;
            http_response_code(200);
        } else {
            http_response_code(404);
            $response['message'] = 'Recibo no encontrado.';
        }

    } catch (PDOException $e) {
        http_response_code(500);
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones GET.';
}

echo json_encode($response);
?>