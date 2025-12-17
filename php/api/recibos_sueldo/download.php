<?php
require_once '../../db_connection.php';
require_once '../../session_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die('ID de recibo no especificado.');
}

$id_recibo = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT ruta_pdf, periodo_sueldo, nombre_empleado FROM recibos_sueldo WHERE id_recibo = ?");
    $stmt->execute([$id_recibo]);
    $recibo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recibo) {
        http_response_code(404);
        die('Recibo no encontrado.');
    }

    $filePath = '../../../' . $recibo['ruta_pdf']; // Ajustar la ruta si es necesario

    if (!file_exists($filePath)) {
        http_response_code(404);
        die('Archivo PDF no encontrado en el servidor.');
    }

    // Preparar el nombre del archivo para la descarga
    $fileName = 'Recibo_Sueldo_' . ($recibo['periodo_sueldo'] ?? 'Desconocido') . '_' . ($recibo['nombre_empleado'] ?? 'Empleado') . '.pdf';
    $fileName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $fileName); // Limpiar el nombre del archivo

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $fileName . '"'); // 'inline' para mostrar en el navegador, 'attachment' para descargar
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    flush(); // Vaciar el búfer de salida del sistema
    readfile($filePath); // Leer el archivo y enviarlo al navegador
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    error_log('Error en la base de datos al descargar recibo: ' . $e->getMessage());
    die('Error interno del servidor.');
}
?>