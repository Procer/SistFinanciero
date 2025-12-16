<?php
// php/api/categorias/read.php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error desconocido.',
    'data' => []
];

// Obtener el tipo de categoría de la URL, por defecto 'gasto' si no se especifica.
$tipo = $_GET['tipo'] ?? 'gasto';
$flat = isset($_GET['flat']) ? filter_var($_GET['flat'], FILTER_VALIDATE_BOOLEAN) : false;


if (!in_array($tipo, ['ingreso', 'gasto', 'todos'])) {
    $response['message'] = 'El tipo de categoría debe ser "ingreso", "gasto" o "todos".';
    echo json_encode($response);
    exit();
}

try {
    if ($tipo === 'todos') {
        $stmt = $pdo->prepare("SELECT id_categoria, nombre, tipo, id_categoria_padre, activo FROM categorias ORDER BY nombre ASC");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT id_categoria, nombre, tipo, id_categoria_padre, activo FROM categorias WHERE tipo = ? AND activo = TRUE ORDER BY nombre ASC");
        $stmt->execute([$tipo]);
    }
    
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($flat) {
        $response = [
            'status' => 'success',
            'message' => "Categorías de {$tipo} obtenidas correctamente (formato plano).",
            'data' => $categorias
        ];
    } else {
        // Construir la jerarquía
        $hierarquia = [];
        $categoriasPorId = [];

        foreach ($categorias as $categoria) {
            $categoriasPorId[$categoria['id_categoria']] = $categoria;
            $categoriasPorId[$categoria['id_categoria']]['subcategorias'] = [];
        }

        foreach ($categoriasPorId as $id => &$categoria) {
            if (isset($categoria['id_categoria_padre']) && isset($categoriasPorId[$categoria['id_categoria_padre']])) {
                // Es una subcategoría, la agregamos a su padre
                $categoriasPorId[$categoria['id_categoria_padre']]['subcategorias'][] = &$categoria;
            } else {
                // Es una categoría de nivel superior
                $hierarquia[] = &$categoria;
            }
        }
        // Des-referenciar el último elemento para evitar bugs
        unset($categoria); 

        $response = [
            'status' => 'success',
            'message' => "Categorías de {$tipo} obtenidas correctamente.",
            'data' => $hierarquia
        ];
    }

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    error_log($response['message']);
} catch (Exception $e) {
    $response['message'] = 'Error general: ' . $e->getMessage();
    error_log($response['message']);
}

echo json_encode($response);
?>
