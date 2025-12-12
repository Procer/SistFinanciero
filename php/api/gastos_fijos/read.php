<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT id_gasto_fijo, nombre, monto, frecuencia, dia_pago, ultima_ejecucion, activo FROM gastos_fijos WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $gastosFijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $gastosFijos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los gastos fijos: ' . $e->getMessage()]);
}
