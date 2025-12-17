<?php
header('Content-Type: application/json');
require_once '../../session_check.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'configured' => false
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $config_file = '../../config.ini';

    if (file_exists($config_file)) {
        $config_content = parse_ini_file($config_file, true);
        $gemini_api_key = $config_content['gemini']['api_key'] ?? null;

        if (!empty($gemini_api_key) && $gemini_api_key !== 'YOUR_GEMINI_API_KEY') {
            $response['status'] = 'success';
            $response['message'] = 'Gemini API Key configurada.';
            $response['configured'] = true;
            http_response_code(200);
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Gemini API Key no configurada o usa valor por defecto.';
            $response['configured'] = false;
            http_response_code(200);
        }
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Archivo de configuración (config.ini) no encontrado.';
        $response['configured'] = false;
        http_response_code(200);
    }
} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones GET.';
}

echo json_encode($response);
?>