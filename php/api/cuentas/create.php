<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['nombre']) || !isset($data['tipo_cuenta']) || !isset($data['saldo_inicial'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere nombre, tipo de cuenta y saldo inicial.']);
    exit;
}

$nombre = trim($data['nombre']);
$tipo_cuenta = $data['tipo_cuenta'];
$saldo_inicial = $data['saldo_inicial'];

if (empty($nombre) || !in_array($tipo_cuenta, ['banco', 'billetera', 'otro']) || !is_numeric($saldo_inicial)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Verifique la información proporcionada.']);
    exit;
}

$sql = "INSERT INTO cuentas (nombre, tipo_cuenta, saldo_inicial) VALUES (:nombre, :tipo_cuenta, :saldo_inicial)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':tipo_cuenta' => $tipo_cuenta,
        ':saldo_inicial' => $saldo_inicial
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Cuenta creada exitosamente.']);

} catch (PDOException $e) {
    http_response_code(500);
    // Verificar si es un error de duplicado (código 23000)
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe una cuenta con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
