<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = ['status' => 'error', 'message' => 'Solicitud inválida.'];

// Obtener mes y año de los parámetros GET, o usar los actuales por defecto
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

try {
    // 1. Total de Gastos del Mes
    $stmt_total = $pdo->prepare(
        "SELECT SUM(monto) as total FROM transacciones 
         WHERE tipo_movimiento = 'gasto' AND MONTH(fecha_transaccion) = ? AND YEAR(fecha_transaccion) = ?"
    );
    $stmt_total->execute([$mes, $ano]);
    $total_gastos_mes = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Gastos por Categoría
    $stmt_categorias = $pdo->prepare(
        "SELECT 
            c.nombre, 
            SUM(t.monto) as total_gastado,
            (SUM(t.monto) / ?) * 100 as porcentaje
         FROM transacciones t
         JOIN categorias c ON t.id_categoria = c.id_categoria
         WHERE t.tipo_movimiento = 'gasto' AND MONTH(t.fecha_transaccion) = ? AND YEAR(t.fecha_transaccion) = ?
         GROUP BY c.id_categoria, c.nombre
         ORDER BY total_gastado DESC"
    );
    // Para el cálculo del porcentaje, si el total es 0, evitamos división por cero.
    $stmt_categorias->execute([$total_gastos_mes > 0 ? $total_gastos_mes : 1, $mes, $ano]);
    $gastos_por_categoria = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);


    $response = [
        'status' => 'success',
        'data' => [
            'total_gastos_mes' => (float)$total_gastos_mes,
            'gastos_por_categoria' => $gastos_por_categoria
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
?>
