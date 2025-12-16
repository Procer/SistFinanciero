<?php
header('Content-Type: application/json');
require_once '../db_connection.php';
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id_cuenta_origen = $data['id_cuenta_origen'] ?? null;
$id_cuenta_destino = $data['id_cuenta_destino'] ?? null;
$monto = (float)($data['monto'] ?? null);
$fecha_transaccion = $data['fecha_transaccion'] ?? date('Y-m-d H:i:s');
$descripcion = $data['descripcion'] ?? 'Transferencia entre cuentas';

// --- Validaciones ---
if (!$id_cuenta_origen || !$id_cuenta_destino || !$monto) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios: cuenta de origen, destino y monto.']);
    exit;
}

if ($id_cuenta_origen == $id_cuenta_destino) {
    echo json_encode(['success' => false, 'message' => 'La cuenta de origen y destino no pueden ser la misma.']);
    exit;
}

if (!is_numeric($monto) || $monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'El monto debe ser un número positivo.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // --- Manejo de la categoría para transferencias ---
    $nombre_categoria_transferencia = 'Transferencia Interna';
    $stmt_cat = $pdo->prepare("SELECT id_categoria FROM categorias WHERE nombre = ?");
    $stmt_cat->execute([$nombre_categoria_transferencia]);
    $id_categoria_transferencia = $stmt_cat->fetchColumn();

    if (!$id_categoria_transferencia) {
        // La categoría no existe, la creamos. Usamos 'gasto' como tipo por defecto, pero no se mostrará en reportes.
        $stmt_insert_cat = $pdo->prepare("INSERT INTO categorias (nombre, tipo, activo) VALUES (?, 'gasto', 1)");
        $stmt_insert_cat->execute([$nombre_categoria_transferencia]);
        $id_categoria_transferencia = $pdo->lastInsertId();
    }
    // --- Fin del manejo de categoría ---

    $sql = "INSERT INTO transacciones (id_cuenta, id_cuenta_destino, tipo_movimiento, monto, fecha_transaccion, descripcion, id_categoria, id_forma_pago)
            VALUES (?, ?, 'transferencia', ?, ?, ?, ?, NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cuenta_origen, $id_cuenta_destino, $monto, $fecha_transaccion, $descripcion, $id_categoria_transferencia]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Transferencia registrada con éxito.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    // En un entorno de producción, sería mejor loguear el error que mostrarlo directamente.
    echo json_encode(['success' => false, 'message' => 'Error al registrar la transferencia: ' . $e->getMessage()]);
}
