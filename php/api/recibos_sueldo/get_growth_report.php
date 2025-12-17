<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db_connection.php';
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$id_usuario = $_SESSION['id_usuario'];


try {
    // 1. Obtener todos los recibos ordenados por fecha de pago
    $stmt = $pdo->prepare(
        "SELECT 
            periodo_sueldo,
            fecha_pago,
            sueldo_bruto,
            sueldo_neto,
            descuentos_total
        FROM recibos_sueldo
        WHERE id_usuario = :id_usuario AND sueldo_bruto > 0 AND sueldo_neto > 0
        ORDER BY fecha_pago ASC"
    );
    $stmt->execute(['id_usuario' => $id_usuario]);
    $recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($recibos) < 1) {
        echo json_encode([
            'labels' => [],
            'datasets' => []
        ]);
        exit;
    }

    // 2. Procesar los datos para los gráficos
    $labels = [];
    $sueldoBrutoData = [];
    $sueldoNetoData = [];
    $descuentosData = [];
    $crecimientoSueldoNeto = [];

    $previousNeto = null;

    foreach ($recibos as $recibo) {
        // Usar el formato 'Mes Año' para las etiquetas
        $date = new DateTime($recibo['fecha_pago']);
        $labels[] = $date->format('m/Y');

        $sueldoBrutoData[] = $recibo['sueldo_bruto'];
        $sueldoNetoData[] = $recibo['sueldo_neto'];
        $descuentosData[] = $recibo['descuentos_total'];

        // Calcular el crecimiento porcentual del sueldo neto
        if ($previousNeto !== null && $previousNeto > 0) {
            $growth = (($recibo['sueldo_neto'] - $previousNeto) / $previousNeto) * 100;
            $crecimientoSueldoNeto[] = round($growth, 2);
        } else {
            // No hay crecimiento para el primer dato
            $crecimientoSueldoNeto[] = 0;
        }
        $previousNeto = $recibo['sueldo_neto'];
    }


    // 3. Preparar la estructura de datos para Chart.js
    $response = [
        'sueldoChart' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sueldo Bruto',
                    'data' => $sueldoBrutoData,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Sueldo Neto',
                    'data' => $sueldoNetoData,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'yAxisID' => 'y',
                ]
            ]
        ],
        'descuentosChart' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Descuentos',
                    'data' => $descuentosData,
                    'borderColor' => 'rgba(255, 206, 86, 1)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'yAxisID' => 'y',
                ]
            ]
        ],
        'crecimientoChart' => [
            // Empezamos desde el segundo mes para el label, ya que el primer mes no tiene crecimiento
            'labels' => array_slice($labels, 1),
            'datasets' => [
                [
                    'label' => '% Crecimiento Sueldo Neto vs Mes Anterior',
                    'data' => array_slice($crecimientoSueldoNeto, 1),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'type' => 'bar', // Este puede ser un gráfico de barras
                    'yAxisID' => 'yPercentage',
                ]
            ]
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

?>