<?php
header('Content-Type: application/json');
include_once '../../db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!$data || !isset($data['id_cuenta']) || !isset($data['nombre']) || !isset($data['tipo_cuenta']) || !isset($data['saldo_inicial'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos. Se requiere id, nombre, tipo y saldo.']);
    exit;
}

$id_cuenta = $data['id_cuenta'];
$nombre = trim($data['nombre']);
$tipo_cuenta = $data['tipo_cuenta'];
$saldo_inicial = $data['saldo_inicial'];

if (empty($id_cuenta) || empty($nombre) || !in_array($tipo_cuenta, ['banco', 'billetera', 'otro']) || !is_numeric($saldo_inicial)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$sql = "UPDATE cuentas SET nombre = :nombre, tipo_cuenta = :tipo_cuenta, saldo_inicial = :saldo_inicial WHERE id_cuenta = :id_cuenta";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':tipo_cuenta' => $tipo_cuenta,
        ':saldo_inicial' => $saldo_inicial,
        ':id_cuenta' => $id_cuenta
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Cuenta actualizada exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se realizaron cambios en la cuenta.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Error: Ya existe otra cuenta con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
