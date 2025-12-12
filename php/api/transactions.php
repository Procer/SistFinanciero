<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $id_cuenta = $input['id_cuenta'] ?? null;
    $id_categoria = $input['id_categoria'] ?? null;
    $id_forma_pago = $input['id_forma_pago'] ?? null; // Puede ser NULL
    $tipo_movimiento = $input['tipo_movimiento'] ?? null;
    $monto = $input['monto'] ?? null;
    $descripcion = $input['descripcion'] ?? null;
    $fecha_transaccion = $input['fecha_transaccion'] ?? date('Y-m-d H:i:s'); // Por defecto, la fecha actual
    $id_proyecto = $input['id_proyecto'] ?? null; // Nuevo campo id_proyecto

    // Validación básica de datos
    if (!$id_cuenta || !$id_categoria || !$tipo_movimiento || !$monto || !in_array($tipo_movimiento, ['ingreso', 'gasto'])) {
        $response['message'] = 'Faltan datos obligatorios o el tipo de movimiento es inválido.';
        echo json_encode($response);
        exit();
    }

    if (!is_numeric($monto) || $monto <= 0) {
        $response['message'] = 'El monto debe ser un número positivo.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Insertar la transacción
        $stmt = $pdo->prepare("INSERT INTO transacciones (id_cuenta, id_categoria, id_forma_pago, tipo_movimiento, monto, descripcion, fecha_transaccion, id_proyecto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_cuenta, $id_categoria, $id_forma_pago, $tipo_movimiento, $monto, $descripcion, $fecha_transaccion, $id_proyecto]);

        // Actualizar el saldo de la cuenta
        $query_update_saldo = "";
        if ($tipo_movimiento === 'ingreso') {
            $query_update_saldo = "UPDATE cuentas SET saldo_inicial = saldo_inicial + ? WHERE id_cuenta = ?";
        } else { // 'gasto'
            $query_update_saldo = "UPDATE cuentas SET saldo_inicial = saldo_inicial - ? WHERE id_cuenta = ?";
        }
        $stmt_update = $pdo->prepare($query_update_saldo);
        $stmt_update->execute([$monto, $id_cuenta]);

        $pdo->commit();

        $response['status'] = 'success';
        $response['message'] = 'Transacción registrada y saldo de cuenta actualizado exitosamente.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['message'] = 'Error general: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
