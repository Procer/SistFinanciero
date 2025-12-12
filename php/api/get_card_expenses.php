<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT gt.fecha_compra, tc.nombre AS tarjeta_nombre, gt.descripcion, gt.monto_total, gt.cuotas_totales, gt.monto_por_cuota
        FROM gastos_tarjeta gt
        JOIN tarjetas_credito tc ON gt.id_tarjeta = tc.id_tarjeta
        WHERE tc.activo = 1
        ORDER BY gt.fecha_compra DESC
        LIMIT 10"; // Limitar a las últimas 10 transacciones, por ejemplo

try {
    $stmt = $pdo->query($sql);
    $gastos_tarjeta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $gastos_tarjeta]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los gastos de tarjeta: ' . $e->getMessage()]);
}
