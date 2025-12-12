<?php
header('Content-Type: application/json');
include_once '../db_connection.php';

$response = ['status' => 'error', 'message' => ''];

$id_proyecto = $_GET['id_proyecto'] ?? null;

if (!$id_proyecto || !is_numeric($id_proyecto)) {
    http_response_code(400);
    $response['message'] = 'ID de proyecto inválido o no proporcionado.';
    echo json_encode($response);
    exit;
}

try {
    // 1. Obtener detalles del proyecto
    $stmt_proyecto = $pdo->prepare("SELECT id_proyecto, nombre, fecha_inicio, fecha_fin, presupuesto_total FROM viajes_proyectos WHERE id_proyecto = ? AND activo = 1");
    $stmt_proyecto->execute([$id_proyecto]);
    $proyecto = $stmt_proyecto->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        http_response_code(404);
        $response['message'] = 'Proyecto no encontrado o inactivo.';
        echo json_encode($response);
        exit;
    }

    // 2. Obtener transacciones asociadas al proyecto
    $stmt_transacciones = $pdo->prepare("
        SELECT
            t.fecha_transaccion,
            t.descripcion,
            c.nombre AS categoria_nombre,
            t.tipo_movimiento,
            t.monto
        FROM transacciones t
        JOIN categorias c ON t.id_categoria = c.id_categoria
        WHERE t.id_proyecto = ?
        ORDER BY t.fecha_transaccion DESC
    ");
    $stmt_transacciones->execute([$id_proyecto]);
    $transacciones = $stmt_transacciones->fetchAll(PDO::FETCH_ASSOC);

    // 3. Calcular ingresos y gastos totales del proyecto
    $total_ingresos = 0;
    $total_gastos = 0;
    foreach ($transacciones as $tx) {
        if ($tx['tipo_movimiento'] == 'ingreso') {
            $total_ingresos += $tx['monto'];
        } else {
            $total_gastos += $tx['monto'];
        }
    }

    $response['status'] = 'success';
    $response['data'] = [
        'proyecto' => $proyecto,
        'transacciones' => $transacciones,
        'totales' => [
            'ingresos' => $total_ingresos,
            'gastos' => $total_gastos,
            'balance' => $total_ingresos - $total_gastos
        ]
    ];
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
