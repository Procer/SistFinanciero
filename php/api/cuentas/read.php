<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT id_cuenta, nombre, saldo_inicial, tipo_cuenta, fecha_creacion, activo FROM cuentas WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cuentas);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las cuentas: ' . $e->getMessage()]);
}
