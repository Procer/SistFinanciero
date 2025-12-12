<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

try {
    $tipo = $_GET['tipo'] ?? null;
    $sql = "SELECT id_categoria, nombre, tipo FROM categorias WHERE activo = TRUE";
    $params = [];

    if ($tipo) {
        $sql .= " AND tipo = ?";
        $params[] = $tipo;
    }

    $sql .= " ORDER BY nombre ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'message' => 'Categorías obtenidas correctamente.',
        'data' => $categorias
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log($response['message']);
} catch (Exception $e) {
    $response['message'] = 'Error general: ' . $e->getMessage();
    error_log($response['message']);
}

echo json_encode($response);
?>
