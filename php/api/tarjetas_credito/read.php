<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT id_tarjeta, nombre, banco, limite_credito, fecha_cierre_extracto, fecha_vencimiento_pago, activo FROM tarjetas_credito WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $tarjetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tarjetas);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las tarjetas de crédito: ' . $e->getMessage()]);
}
