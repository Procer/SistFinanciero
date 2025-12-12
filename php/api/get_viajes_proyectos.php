<?php
header('Content-Type: application/json');
include_once '../db_connection.php';

$sql = "SELECT id_proyecto, nombre FROM viajes_proyectos WHERE activo = 1 ORDER BY nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $proyectos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los viajes/proyectos: ' . $e->getMessage()]);
}
