<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../session_check.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // En un sistema real, se debería filtrar por id_usuario
        $sql = "SELECT id_recibo, nombre_empleado, nombre_empleador, periodo_sueldo, fecha_pago, sueldo_neto, ruta_imagen
                FROM recibos_sueldo ORDER BY fecha_carga DESC";
        $stmt = $pdo->query($sql);
        $recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['status'] = 'success';
        $response['message'] = 'Recibos de sueldo cargados exitosamente.';
        $response['data'] = $recibos;

    } catch (PDOException $e) {
        http_response_code(500);
        $response['message'] = 'Error en la base de datos al cargar los recibos: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones GET.';
}

echo json_encode($response);
?>