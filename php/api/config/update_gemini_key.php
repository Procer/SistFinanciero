<?php
header('Content-Type: application/json');
require_once '../../session_check.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $new_api_key = $input['api_key'] ?? null;

    if (empty($new_api_key)) {
        http_response_code(400);
        $response['message'] = 'La clave de API no puede estar vacía.';
        echo json_encode($response);
        exit();
    }

    $config_file = '../../config.ini';

    // Leer el archivo config.ini
    $config_content = [];
    if (file_exists($config_file)) {
        $config_content = parse_ini_file($config_file, true);
    }

    // Actualizar la sección [gemini]
    if (!isset($config_content['gemini'])) {
        $config_content['gemini'] = [];
    }
    $config_content['gemini']['api_key'] = $new_api_key;

    // Escribir de vuelta al archivo config.ini
    $new_config_string = '';
    foreach ($config_content as $section => $properties) {
        $new_config_string .= '[' . $section . ']' . PHP_EOL;
        foreach ($properties as $key => $value) {
            $new_config_string .= $key . ' = "' . str_replace('"', '\"', $value) . '"' . PHP_EOL;
        }
        $new_config_string .= PHP_EOL;
    }

    if (file_put_contents($config_file, $new_config_string) !== false) {
        $response['status'] = 'success';
        $response['message'] = 'Gemini API Key guardada exitosamente.';
        http_response_code(200);
    } else {
        http_response_code(500);
        $response['message'] = 'Error al escribir en el archivo de configuración. Verifique los permisos.';
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
