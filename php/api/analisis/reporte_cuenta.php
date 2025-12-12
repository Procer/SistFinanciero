<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = ['status' => 'error', 'message' => 'Solicitud inválida.'];

if (isset($_GET['id_cuenta'])) {
    $id_cuenta = $_GET['id_cuenta'];
    $mes_actual = date('m');
    $ano_actual = date('Y');

    try {
        // 1. Total de Ingresos del Mes
        $stmt_ingresos = $pdo->prepare(
            "SELECT SUM(monto) as total FROM transacciones 
             WHERE id_cuenta = ? AND tipo_movimiento = 'ingreso' AND MONTH(fecha_transaccion) = ? AND YEAR(fecha_transaccion) = ?"
        );
        $stmt_ingresos->execute([$id_cuenta, $mes_actual, $ano_actual]);
        $total_ingresos = $stmt_ingresos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 2. Total de Gastos del Mes
        $stmt_gastos = $pdo->prepare(
            "SELECT SUM(monto) as total FROM transacciones 
             WHERE id_cuenta = ? AND tipo_movimiento = 'gasto' AND MONTH(fecha_transaccion) = ? AND YEAR(fecha_transaccion) = ?"
        );
        $stmt_gastos->execute([$id_cuenta, $mes_actual, $ano_actual]);
        $total_gastos = $stmt_gastos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 3. Gastos por Categoría
        $stmt_categorias = $pdo->prepare(
            "SELECT c.nombre, SUM(t.monto) as total 
             FROM transacciones t
             JOIN categorias c ON t.id_categoria = c.id_categoria
             WHERE t.id_cuenta = ? AND t.tipo_movimiento = 'gasto' AND MONTH(t.fecha_transaccion) = ? AND YEAR(t.fecha_transaccion) = ?
             GROUP BY c.nombre
             ORDER BY total DESC"
        );
        $stmt_categorias->execute([$id_cuenta, $mes_actual, $ano_actual]);
        $gastos_por_categoria = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

        // 4. Todas las Transacciones del Mes
        $stmt_transacciones = $pdo->prepare(
            "SELECT t.*, c.nombre as categoria_nombre 
             FROM transacciones t
             LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
             WHERE t.id_cuenta = ? AND MONTH(t.fecha_transaccion) = ? AND YEAR(t.fecha_transaccion) = ?
             ORDER BY t.fecha_transaccion DESC"
        );
        $stmt_transacciones->execute([$id_cuenta, $mes_actual, $ano_actual]);
        $transacciones = $stmt_transacciones->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'status' => 'success',
            'data' => [
                'total_ingresos' => $total_ingresos,
                'total_gastos' => $total_gastos,
                'gastos_por_categoria' => $gastos_por_categoria,
                'transacciones' => $transacciones
            ]
        ];

    } catch (PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>
