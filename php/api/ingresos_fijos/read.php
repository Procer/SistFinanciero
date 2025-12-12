<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT id_ingreso_fijo, nombre, monto, frecuencia, dia_pago, ultima_ejecucion, proximo_aumento_simulado_porcentaje, activo FROM ingresos_fijos WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $ingresosFijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $ingresosFijos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los ingresos fijos: ' . $e->getMessage()]);
}
