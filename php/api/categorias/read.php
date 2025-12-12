<?php
// php/api/categorias/read.php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

// Obtener el tipo de categoría de la URL, por defecto 'gasto' si no se especifica.
$tipo = $_GET['tipo'] ?? 'gasto';

if (!in_array($tipo, ['ingreso', 'gasto'])) {
    $response['message'] = 'El tipo de categoría debe ser "ingreso" o "gasto".';
    echo json_encode($response);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id_categoria, nombre FROM categorias WHERE tipo = ? AND activo = TRUE ORDER BY nombre ASC");
    $stmt->execute([$tipo]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'message' => "Categorías de {$tipo} obtenidas correctamente.",
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
