<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';
require_once '../../session_check.php';

// Cargar la clave de API de Gemini desde un archivo de configuración seguro
$config = parse_ini_file('../../config.ini', true);
$gemini_api_key = $config['gemini']['api_key'] ?? null;

if (!$gemini_api_key) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Clave de API de Gemini no configurada.']);
    exit();
}

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['imagen_recibo']) || $_FILES['imagen_recibo']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $response['message'] = 'Error al subir el archivo de imagen.';
        echo json_encode($response);
        exit();
    }

    $file = $_FILES['imagen_recibo'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($file_extension, $allowed_image_types)) {
        http_response_code(400);
        $response['message'] = 'Solo se permiten archivos de imagen (JPG, JPEG, PNG, GIF, WEBP).';
        echo json_encode($response);
        exit();
    }

    $uploadDir = '../../../uploads/recibos_sueldo/'; // Ajustar la ruta si es necesario
    $uniqueFileName = uniqid('recibo_') . '.' . $file_extension;
    $targetFilePath = $uploadDir . $uniqueFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        http_response_code(500);
        $response['message'] = 'Error al mover el archivo subido.';
        echo json_encode($response);
        exit();
    }

    // --- Parte 1: El archivo ya es una imagen, no se necesita conversión ---
    $imagePath = $targetFilePath; // La ruta al archivo de imagen subido
    $mimeType = mime_content_type($imagePath); // Obtener el tipo MIME real del archivo

    // --- Parte 2: Llamada a la API de Gemini ---
    $imageData = base64_encode(file_get_contents($imagePath));

    // Prompt optimizado para recibos de sueldo argentinos
    $prompt = "Eres un asistente experto en analizar recibos de sueldo de Argentina. Extrae toda la información posible del documento y devuélvela en formato JSON. Si un campo no se encuentra, deja el valor como cadena vacía, 0.00, 0 o array vacío según corresponda. Asegúrate de que todos los montos sean números flotantes (usando '.' como separador decimal). Presta especial atención al período del sueldo y al sueldo neto final.
    
    Campos a extraer:
    1.  `nombre_empleado`: Nombre completo del empleado.
    2.  `cuil_empleado`: CUIL del empleado.
    3.  `nombre_empleador`: Nombre o razón social del empleador.
    4.  `cuit_empleador`: CUIT del empleador.
    5.  `periodo_sueldo`: Período del sueldo (ej. 'Agosto 2020', '08/2020'). Normaliza a 'YYYY-MM' si es posible, sino usa el formato original.
    6.  `fecha_pago`: Fecha efectiva de pago (formato 'YYYY-MM-DD').
    7.  `lugar_pago`: Lugar donde se realiza el pago (ej. 'Buenos Aires').
    8.  `forma_pago`: Forma de pago (ej. 'Banco Galicia', 'Caja de Ahorro').
    9.  `sueldo_bruto`: Total de remuneraciones antes de descuentos.
    10. `sueldo_neto`: Total a cobrar después de todos los descuentos.
    11. `descuentos_total`: Sumatoria de todos los descuentos.
    12. `detalle_conceptos`: Un array de objetos, donde cada objeto representa un concepto o deducción. Cada objeto debe tener:
        *   `codigo`: Código del concepto (número).
        *   `descripcion`: Descripción detallada del ítem (ej. 'Sueldo Basico', 'Jubilacion').
        *   `unidad`: La unidad, si está presente (ej. '30,00', '1,00'). Convertir a float.
        *   `haberes_con_aporte`: Monto en haberes con aporte.
        *   `haberes_sin_aporte`: Monto en haberes sin aporte.
        *   `descuentos_item`: Monto del descuento específico de esta línea.
        *   `tipo`: Puede ser 'remunerativo', 'no_remunerativo', 'deduccion' (inferir si es posible, si no, dejar vacío).
    13. `ultimo_deposito_cargas_sociales`: Objeto con detalles del último depósito.
        *   `banco`: Banco del último depósito.
        *   `periodo`: Período del depósito (ej. '08-2020').
        *   `fecha_deposito`: Fecha del depósito (formato 'YYYY-MM-DD').

    Ejemplo de JSON esperado:
    ```json
    {
      \"nombre_empleado\": \"JUAN MANUEL DE ROSAS\",
      \"cuil_empleado\": \"20-30654047-6\",
      \"nombre_empleador\": \"ZARCAMS.A.\",
      \"cuit_empleador\": \"30-54709922-9\",
      \"periodo_sueldo\": \"2020-08\",
      \"fecha_pago\": \"2020-08-28\",
      \"lugar_pago\": \"Buenos Aires\",
      \"forma_pago\": \"Banco Galicia\",
      \"sueldo_bruto\": 62518.76,
      \"sueldo_neto\": 51891.00,
      \"descuentos_total\": 10627.76,
      \"detalle_conceptos\": [
        {
          \"codigo\": \"01100\",
          \"descripcion\": \"Sueldo Basico\",
          \"unidad\": 30.00,
          \"haberes_con_aporte\": 56982.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 0.00,
          \"tipo\": \"remunerativo\"
        },
        {
          \"codigo\": \"01105\",
          \"descripcion\": \"A Cta. Fut. Aumentos\",
          \"unidad\": 30.00,
          \"haberes_con_aporte\": 5536.76,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 0.00,
          \"tipo\": \"remunerativo\"
        },
        {
          \"codigo\": \"01210\",
          \"descripcion\": \"Feriados del Mes\",
          \"unidad\": 1.00,
          \"haberes_con_aporte\": 1899.40,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 0.00,
          \"tipo\": \"remunerativo\"
        },
        {
          \"codigo\": \"01211\",
          \"descripcion\": \"Desc. Feriados\",
          \"unidad\": 1.00,
          \"haberes_con_aporte\": 0.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 1899.40,
          \"tipo\": \"deduccion\"
        },
        {
          \"codigo\": \"06005\",
          \"descripcion\": \"Jubilacion\",
          \"unidad\": 11.00,
          \"haberes_con_aporte\": 0.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 6877.06,
          \"tipo\": \"deduccion\"
        },
        {
          \"codigo\": \"06010\",
          \"descripcion\": \"Ley 19032\",
          \"unidad\": 3.00,
          \"haberes_con_aporte\": 0.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 1875.56,
          \"tipo\": \"deduccion\"
        },
        {
          \"codigo\": \"06030\",
          \"descripcion\": \"Obra Social\",
          \"unidad\": 3.00,
          \"haberes_con_aporte\": 0.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 1875.56,
          \"tipo\": \"deduccion\"
        },
        {
          \"codigo\": \"16000\",
          \"descripcion\": \"Redondeo\",
          \"unidad\": 0.00,
          \"haberes_con_aporte\": 0.00,
          \"haberes_sin_aporte\": 0.00,
          \"descuentos_item\": 0.42,
          \"tipo\": \"deduccion\"
        }
      ],
      \"ultimo_deposito_cargas_sociales\": {
        \"banco\": \"Santander Rio\",
        \"periodo\": \"2020-08\",
        \"fecha_deposito\": \"2020-08-12\"
      }
    }
    ```
    Aquí está la imagen del recibo de sueldo:
    ";

    $requestBody = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt],
                    ['inlineData' => [
                        'mimeType' => $mimeType,
                        'data' => $imageData
                    ]]
                ]
            ]
        ],
        'generationConfig' => [
            'responseMimeType' => 'application/json', // Pedimos JSON directamente si el modelo lo soporta bien
            'temperature' => 0.2, // Baja temperatura para respuestas más concisas y menos creativas
            'topP' => 0.9,
            'topK' => 40
        ]
    ];

    $geminiEndpoint = 'https://generativelanguage.googleapis.com/v1alpha/models/gemini-2.5-flash:generateContent?key=' . $gemini_api_key;

    $ch = curl_init($geminiEndpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Considerar usar certificados CA en producción

    $geminiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // No se elimina la imagen subida si la API de Gemini falla, solo si hay errores de cURL o HTTP
    // if (file_exists($imagePath)) {
    //     unlink($imagePath);
    // }

    if ($curlError) {
        unlink($targetFilePath); // Eliminar IMAGEN si la llamada a Gemini falla por cURL
        http_response_code(500);
        $response['message'] = 'Error al comunicarse con la API de Gemini: ' . $curlError;
        echo json_encode($response);
        exit();
    }

    if ($httpCode !== 200) {
        unlink($targetFilePath); // Eliminar IMAGEN si Gemini devuelve un error HTTP
        $decodedGeminiResponse = json_decode($geminiResponse, true);
        $geminiErrorMessage = $decodedGeminiResponse['error']['message'] ?? 'Error desconocido de Gemini.';
        http_response_code(500);
        $response['message'] = 'Error de la API de Gemini (' . $httpCode . '): ' . $geminiErrorMessage;
        echo json_encode($response);
        exit();
    }

    $decodedGeminiResponse = json_decode($geminiResponse, true);
    $extractedData = null;

    // Intentar extraer el JSON del contenido de la respuesta de Gemini
    if (isset($decodedGeminiResponse['candidates'][0]['content']['parts'][0]['text'])) {
        $geminiText = $decodedGeminiResponse['candidates'][0]['content']['parts'][0]['text'];
        // Los modelos a veces envían el JSON dentro de bloques de Markdown, así que intentamos extraerlo.
        preg_match('/```json\s*(.*?)\s*```/s', $geminiText, $matches);
        if (isset($matches[1])) {
            $extractedData = json_decode($matches[1], true);
        } else {
            // Si no está en un bloque ```json, intentamos parsear el texto directamente
            $extractedData = json_decode($geminiText, true);
        }
    }

    if (!$extractedData || json_last_error() !== JSON_ERROR_NONE) {
        unlink($targetFilePath); // Eliminar IMAGEN si no se pudo parsear la respuesta de Gemini
        http_response_code(500);
        $response['message'] = 'No se pudo extraer o parsear la información del recibo de sueldo desde Gemini. Error: ' . json_last_error_msg() . ' Respuesta: ' . substr($geminiText, 0, 500) . '...';
        echo json_encode($response);
        exit();
    }
    
    // --- Parte 3: Guardar en Base de Datos ---
    try {
        $pdo->beginTransaction();

        // Preparar datos para inserción en recibos_sueldo
        $id_usuario = $_SESSION['id_usuario'] ?? null; // Asumiendo que el id_usuario está en sesión
        $nombre_empleado = $extractedData['nombre_empleado'] ?? '';
        $cuil_empleado = $extractedData['cuil_empleado'] ?? null;
        $nombre_empleador = $extractedData['nombre_empleador'] ?? null;
        $cuit_empleador = $extractedData['cuit_empleador'] ?? null;
        $periodo_sueldo = $extractedData['periodo_sueldo'] ?? 'Desconocido';
        $fecha_pago = $extractedData['fecha_pago'] ?? null;
        $lugar_pago = $extractedData['lugar_pago'] ?? null;
        $forma_pago_detalle = $extractedData['forma_pago'] ?? null; // Mapear a la nueva columna
        $sueldo_bruto = $extractedData['sueldo_bruto'] ?? 0.00;
        $sueldo_neto = $extractedData['sueldo_neto'] ?? 0.00;
        $descuentos_total = $extractedData['descuentos_total'] ?? 0.00;
        $detalle_json = json_encode($extractedData['detalle_conceptos'] ?? []);
        $ultimo_deposito_cargas_sociales_json = json_encode($extractedData['ultimo_deposito_cargas_sociales'] ?? []);
        
        // La ruta_imagen es relativa desde el proyecto web
        $db_ruta_imagen = 'uploads/recibos_sueldo/' . $uniqueFileName;

        // --- INICIO: Lógica de Saldo Pre-Sueldo y Transacción ---

        // IDs predefinidos para la cuenta de sueldo y categoría.
        // TODO: Hacer que estos valores sean configurables en el futuro.
        $id_cuenta_sueldo = 1; // ID de la cuenta donde se deposita el sueldo (ej. "Banco Galicia")
        $id_categoria_sueldo = 1; // ID de la categoría para ingresos por sueldo (ej. "Sueldo")
        $id_forma_pago_transferencia = 1; // ID de la forma de pago (ej. "Transferencia bancaria")

        // 1. OBTENER SALDO ACTUAL (PRE-SUELDO) DE LA CUENTA PRINCIPAL
        $sql_saldo = "
            SELECT (c.saldo_inicial + COALESCE(SUM(
                CASE
                    WHEN t.tipo_movimiento = 'ingreso' THEN t.monto
                    WHEN t.tipo_movimiento = 'gasto' THEN -t.monto
                    WHEN t.tipo_movimiento = 'transferencia' AND t.id_cuenta = :id_cuenta THEN -t.monto
                    WHEN t.tipo_movimiento = 'transferencia' AND t.id_cuenta_destino = :id_cuenta THEN t.monto
                    ELSE 0
                END
            ), 0)) AS saldo_actual
            FROM cuentas c
            LEFT JOIN transacciones t ON c.id_cuenta = t.id_cuenta OR c.id_cuenta = t.id_cuenta_destino
            WHERE c.id_cuenta = :id_cuenta
            GROUP BY c.id_cuenta, c.saldo_inicial;
        ";
        $stmt_saldo = $pdo->prepare($sql_saldo);
        $stmt_saldo->execute([':id_cuenta' => $id_cuenta_sueldo]);
        $saldo_pre_sueldo = $stmt_saldo->fetchColumn();
        if ($saldo_pre_sueldo === false) {
             // Si no hay transacciones, el saldo es el inicial
            $stmt_saldo_inicial = $pdo->prepare("SELECT saldo_inicial FROM cuentas WHERE id_cuenta = ?");
            $stmt_saldo_inicial->execute([$id_cuenta_sueldo]);
            $saldo_pre_sueldo = $stmt_saldo_inicial->fetchColumn();
        }


        // 2. GUARDAR RECIBO DE SUELDO
        $sql = "INSERT INTO recibos_sueldo (id_usuario, nombre_empleado, cuil_empleado, nombre_empleador, cuit_empleador, periodo_sueldo, fecha_pago, lugar_pago, forma_pago_detalle, sueldo_bruto, sueldo_neto, descuentos_total, ruta_imagen, detalle_json, ultimo_deposito_cargas_sociales_json) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_usuario, $nombre_empleado, $cuil_empleado, $nombre_empleador, $cuit_empleador,
            $periodo_sueldo, $fecha_pago, $lugar_pago, $forma_pago_detalle,
            $sueldo_bruto, $sueldo_neto, $descuentos_total,
            $db_ruta_imagen, $detalle_json, $ultimo_deposito_cargas_sociales_json
        ]);
        $id_recibo_insertado = $pdo->lastInsertId();

        // 3. GUARDAR EL SALDO PRE-SUELDO OBTENIDO
        if ($saldo_pre_sueldo !== false) {
            $sql_pre_sueldo = "INSERT INTO saldo_pre_sueldo (id_cuenta, saldo, id_recibo_sueldo) VALUES (?, ?, ?)";
            $stmt_pre_sueldo = $pdo->prepare($sql_pre_sueldo);
            $stmt_pre_sueldo->execute([$id_cuenta_sueldo, $saldo_pre_sueldo, $id_recibo_insertado]);
        }
        
        // 4. CREAR LA TRANSACCIÓN DE INGRESO ASOCIADA AL SUELDO
        if ($id_recibo_insertado && $sueldo_neto > 0) {
            $sql_transaccion = "INSERT INTO transacciones (id_cuenta, id_categoria, id_forma_pago, tipo_movimiento, monto, descripcion, fecha_transaccion, id_recibo)
                                VALUES (?, ?, ?, 'ingreso', ?, ?, ?, ?)";
            $stmt_transaccion = $pdo->prepare($sql_transaccion);
            $stmt_transaccion->execute([
                $id_cuenta_sueldo, 
                $id_categoria_sueldo, 
                $id_forma_pago_transferencia, 
                $sueldo_neto, 
                "Sueldo $periodo_sueldo (Recibo #$id_recibo_insertado)", 
                $fecha_pago ?? date('Y-m-d H:i:s'),
                $id_recibo_insertado
            ]);
        }

        // --- FIN: Lógica de Saldo Pre-Sueldo y Transacción ---

        $pdo->commit();
        $response['status'] = 'success';
        $response['message'] = 'Recibo de sueldo procesado y guardado exitosamente.';
        http_response_code(201); // Created

    } catch (PDOException $e) {
        $pdo->rollBack();
        unlink($targetFilePath); // Eliminar IMAGEN si falla la BD
        http_response_code(500);
        $response['message'] = 'Error en la base de datos al guardar el recibo: ' . $e->getMessage();
        error_log($response['message']);
    }

} else {
    http_response_code(405);
    $response['message'] = 'Método no permitido. Solo se aceptan peticiones POST.';
}

echo json_encode($response);
?>
