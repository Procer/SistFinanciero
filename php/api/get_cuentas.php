<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado. Por favor, inicie sesión.'
    ]);
    exit;
}

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

try {
    $stmt = $pdo->query("SELECT id_cuenta, nombre, tipo_cuenta FROM cuentas WHERE activo = TRUE ORDER BY nombre ASC");
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'message' => 'Cuentas obtenidas correctamente.',
        'data' => $cuentas
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
