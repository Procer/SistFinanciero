<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Por favor, inicie sesión.']);
    exit;
}

$sql = "SELECT id_cuenta, nombre, saldo_inicial, tipo_cuenta, fecha_creacion, activo FROM cuentas WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $cuentas]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las cuentas: ' . $e->getMessage()]);
}
