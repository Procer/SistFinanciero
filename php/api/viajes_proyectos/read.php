<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$sql = "SELECT id_proyecto, nombre, fecha_inicio, fecha_fin, presupuesto_total, activo, fecha_creacion
        FROM viajes_proyectos
        WHERE activo = 1
        ORDER BY fecha_inicio DESC, nombre ASC";

try {
    $stmt = $pdo->query($sql);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear fechas para mejor visualización si es necesario en el frontend
    foreach ($proyectos as &$proyecto) {
        $proyecto['fecha_inicio_formatted'] = (new DateTime($proyecto['fecha_inicio']))->format('d/m/Y');
        $proyecto['fecha_fin_formatted'] = $proyecto['fecha_fin'] ? (new DateTime($proyecto['fecha_fin']))->format('d/m/Y') : 'N/A';
        $proyecto['presupuesto_total_formatted'] = number_format($proyecto['presupuesto_total'], 2, ',', '.');
    }

    echo json_encode(['status' => 'success', 'data' => $proyectos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los viajes/proyectos: ' . $e->getMessage()]);
}
