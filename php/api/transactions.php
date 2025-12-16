<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Datos de la transacción
    $id_transaccion = $input['id_transaccion'] ?? null;
    $id_cuenta = $input['id_cuenta'] ?? null;
    $id_categoria = $input['id_categoria'] ?? null;
    $id_forma_pago = $input['id_forma_pago'] ?? null;
    $tipo_movimiento = $input['tipo_movimiento'] ?? null;
    $monto = (float)($input['monto'] ?? 0);
    $descripcion = $input['descripcion'] ?? null;
    $fecha_transaccion = $input['fecha_transaccion'] ?? date('Y-m-d H:i:s');
    $id_proyecto = $input['id_proyecto'] ?? null;

    // Validación
    if (!$id_cuenta || !$id_categoria || !$tipo_movimiento || !isset($monto) || !in_array($tipo_movimiento, ['ingreso', 'gasto'])) {
        http_response_code(400);
        $response['message'] = 'Faltan datos obligatorios o el tipo de movimiento es inválido.';
        echo json_encode($response);
        exit();
    }

    if (!is_numeric($monto) || $monto < 0) {
        http_response_code(400);
        $response['message'] = 'El monto debe ser un número no negativo.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction();

        if ($id_transaccion) {
            // --- LÓGICA DE ACTUALIZACIÓN (EDIT) ---

            // 1. Obtener la transacción original para saber montos y cuentas anteriores
            $stmt_old = $pdo->prepare("SELECT * FROM transacciones WHERE id_transaccion = ?");
            $stmt_old->execute([$id_transaccion]);
            $old_tx = $stmt_old->fetch();

            if (!$old_tx) {
                throw new Exception("La transacción que intenta editar no existe.");
            }

            // 2. Revertir el saldo en la cuenta original - COMENTADO PARA EVITAR INCONSISTENCIAS
            /*
            if ($old_tx['tipo_movimiento'] === 'ingreso') {
                $stmt_revert = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial - ? WHERE id_cuenta = ?");
            } else { // gasto
                $stmt_revert = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial + ? WHERE id_cuenta = ?");
            }
            $stmt_revert->execute([$old_tx['monto'], $old_tx['id_cuenta']]);
            */

            // 3. Actualizar la transacción con los nuevos datos
            $stmt_update = $pdo->prepare(
                "UPDATE transacciones SET 
                    id_cuenta = ?, id_categoria = ?, id_forma_pago = ?, 
                    monto = ?, descripcion = ?, fecha_transaccion = ?, id_proyecto = ?
                 WHERE id_transaccion = ?"
            );
            $stmt_update->execute([$id_cuenta, $id_categoria, $id_forma_pago, $monto, $descripcion, $fecha_transaccion, $id_proyecto, $id_transaccion]);

            // 4. Aplicar el nuevo saldo en la nueva cuenta (puede ser la misma) - COMENTADO PARA EVITAR INCONSISTENCIAS
            /*
            if ($tipo_movimiento === 'ingreso') {
                $stmt_apply = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial + ? WHERE id_cuenta = ?");
            } else { // gasto
                $stmt_apply = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial - ? WHERE id_cuenta = ?");
            }
            $stmt_apply->execute([$monto, $id_cuenta]);
            */

            $response['message'] = 'Transacción actualizada exitosamente.';

        } else {
            // --- LÓGICA DE CREACIÓN (INSERT) ---

            // 1. Insertar la nueva transacción
            $stmt_insert = $pdo->prepare(
                "INSERT INTO transacciones (id_cuenta, id_categoria, id_forma_pago, tipo_movimiento, monto, descripcion, fecha_transaccion, id_proyecto) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt_insert->execute([$id_cuenta, $id_categoria, $id_forma_pago, $tipo_movimiento, $monto, $descripcion, $fecha_transaccion, $id_proyecto]);

            // 2. Actualizar el saldo de la cuenta - COMENTADO PARA EVITAR DOBLE CONTEO
            /*
            if ($tipo_movimiento === 'ingreso') {
                $stmt_update_saldo = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial + ? WHERE id_cuenta = ?");
            } else { // 'gasto'
                $stmt_update_saldo = $pdo->prepare("UPDATE cuentas SET saldo_inicial = saldo_inicial - ? WHERE id_cuenta = ?");
            }
            $stmt_update_saldo->execute([$monto, $id_cuenta]);
            */
            
            $response['message'] = 'Transacción registrada exitosamente.';
        }

        $pdo->commit();
        $response['status'] = 'success';

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        $response['message'] = 'Error general: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
