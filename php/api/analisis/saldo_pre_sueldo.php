<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../session_check.php';

$response = ['status' => 'success', 'data' => []];

try {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $id_usuario = $_SESSION['id_usuario'] ?? 0;

    $sql = "
        SELECT 
            sps.id_saldo_pre_sueldo,
            sps.saldo,
            sps.fecha_registro,
            sps.id_recibo_sueldo,
            c.nombre AS nombre_cuenta,
            rs.periodo_sueldo,
            rs.fecha_pago
        FROM 
            saldo_pre_sueldo sps
        JOIN 
            cuentas c ON sps.id_cuenta = c.id_cuenta
        LEFT JOIN
            recibos_sueldo rs ON sps.id_recibo_sueldo = rs.id_recibo
        WHERE
            c.id_usuario = :id_usuario AND YEAR(sps.fecha_registro) = :year
        ORDER BY 
            sps.fecha_registro DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':year' => $year
    ]);
    
    $saldos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['data'] = $saldos;

} catch (PDOException $e) {
    http_response_code(500);
    $response = ['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()];
} catch (Exception $e) {
    http_response_code(500);
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
?>
