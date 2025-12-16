<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id_ingreso_fijo = $input['id_ingreso_fijo'] ?? null;
    $nombre = $input['nombre'] ?? null;
    $monto = (float)($input['monto'] ?? 0);
    $frecuencia = $input['frecuencia'] ?? null;
    $dia_pago = $input['dia_pago'] ?? null;
    $proximo_aumento_simulado_porcentaje = (float)($input['proximo_aumento_simulado_porcentaje'] ?? 0.00);

    // Validaciones
    if (empty($id_ingreso_fijo) || empty($nombre) || empty($frecuencia) || $monto <= 0) {
        $response['message'] = 'ID, nombre, monto y frecuencia son campos obligatorios y el monto debe ser positivo.';
        echo json_encode($response);
        exit();
    }

    if (!in_array($frecuencia, ['mensual', 'quincenal', 'anual'])) {
        $response['message'] = 'Frecuencia debe ser "mensual", "quincenal" o "anual".';
        echo json_encode($response);
        exit();
    }

    if ($dia_pago !== null && (!is_numeric($dia_pago) || $dia_pago < 1 || $dia_pago > 31)) {
        $response['message'] = 'Día de pago inválido.';
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "UPDATE ingresos_fijos SET nombre = ?, monto = ?, frecuencia = ?, dia_pago = ?, proximo_aumento_simulado_porcentaje = ? WHERE id_ingreso_fijo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $monto, $frecuencia, $dia_pago, $proximo_aumento_simulado_porcentaje, $id_ingreso_fijo]);

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Ingreso fijo actualizado exitosamente.';
        } else {
            $response['status'] = 'info';
            $response['message'] = 'No se realizaron cambios o el ingreso fijo no fue encontrado.';
        }
        http_response_code(200);

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
        error_log($response['message']);
        http_response_code(500);
    }

} else {
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
    http_response_code(405);
}

echo json_encode($response);
?>