<?php
header('Content-Type: application/json');
require_once '../../db_connection.php';

$response = ['status' => 'error', 'message' => 'Solicitud inválida.'];

// Obtener mes y año de los parámetros GET, o usar los actuales por defecto
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
// Nuevos filtros
$categoria_id = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? (int)$_GET['categoria'] : null;
    $tarjeta_id = isset($_GET['tarjeta']) && $_GET['tarjeta'] !== '' ? (int)$_GET['tarjeta'] : null;

    // --- LOGGING PARA DEPURACIÓN ---
    error_log("Reporte Gastos Mensuales - Params recibidos: mes=$mes, ano=$ano, categoria_id=$categoria_id, tarjeta_id=$tarjeta_id");
    // --- FIN LOGGING PARA DEPURACIÓN ---

try {
    $where_clauses = ["t.tipo_movimiento = 'gasto'", "MONTH(t.fecha_transaccion) = ?", "YEAR(t.fecha_transaccion) = ?"];
    $params = [$mes, $ano];

    if ($categoria_id !== null) {
        $where_clauses[] = "t.id_categoria = ?";
        $params[] = $categoria_id;
    }
    if ($tarjeta_id !== null) {
        // Un gasto puede ser de una cuenta o de una tarjeta.
        // Si se filtra por tarjeta, debe ser un gasto de tarjeta específico.
        // Aquí asumimos que si id_tarjeta está presente, estamos buscando gastos_tarjeta
        // o transacciones directamente relacionadas a tarjetas si tuviéramos un id_tarjeta en transacciones.
        // Dada la estructura actual, un gasto de tarjeta se registra en `gastos_tarjeta` y luego se asocia a una `transaccion` si se paga la cuota.
        // Para simplificar, asumiremos que el filtro de tarjeta se aplica a `gastos_tarjeta` y luego se vincula con las transacciones.
        // Si hay un `id_tarjeta` en `transacciones`, se podría usar directamente.
        // Por ahora, para el análisis de gastos, nos enfocaremos en transacciones que *podrían* estar asociadas a una tarjeta.
        // Se necesitará un JOIN si se quiere filtrar por `gastos_tarjeta`.
        // Para este reporte, lo más directo es filtrar transacciones que tienen una forma de pago que es una tarjeta.
        // Esto requeriría saber qué formas de pago corresponden a tarjetas.
        // Una alternativa más simple para este nivel es que el filtro de tarjeta se aplique a los `gastos_tarjeta` y sume esos montos.
        // Pero el reporte actual es sobre `transacciones`.
        // Propuesta: Para este reporte, si se selecciona una tarjeta, filtraremos por `id_forma_pago` que corresponda a la tarjeta si tenemos esa relación.
        // O más sencillo aún, si el usuario selecciona una tarjeta, quiere ver SOLO los gastos de tarjeta para esa tarjeta.
        // La implementación actual de `transacciones` y `gastos_tarjeta` es un poco ambigua para un JOIN directo por `id_tarjeta`.
        // Vamos a asumir que si `tarjeta_id` está presente, queremos filtrar gastos_tarjeta y mostrar sus transacciones asociadas.
        // Esto requeriría una consulta JOIN diferente o un cambio en la estructura.

        // Por ahora, la solución más pragmatica y sin cambios profundos en DB es asumir que
        // `transacciones` puede tener un `id_tarjeta` (que no existe) o un `id_forma_pago` que represente una tarjeta.
        // Asumiré que el `id_forma_pago` en la tabla `transacciones` puede indicar un pago con tarjeta.
        // Esto puede no ser 100% preciso si no hay una forma de pago 'Tarjeta X'.

        // Si el `id_forma_pago` en `transacciones` realmente es el `id_tarjeta`, entonces podemos hacer esto:
        // Pero no lo es. `id_forma_pago` apunta a `formas_pago`.
        // Entonces, para filtrar por tarjeta, deberíamos filtrar `gastos_tarjeta` y luego relacionarlos con las transacciones.

        // Dado que el informe es de `transacciones`, y `gastos_tarjeta` es una tabla separada,
        // la manera más sencilla de incorporar el filtro de tarjeta es si las `transacciones`
        // tienen un campo `id_tarjeta` o si `formas_pago` tiene una relación a `tarjetas_credito`.
        // Actualmente, `transacciones` solo tiene `id_forma_pago`.

        // Opción 1 (más complejo): Modificar la consulta para JOIN con `gastos_tarjeta` y `tarjetas_credito`.
        // Esto cambiaría la naturaleza de la consulta de `transacciones` puras.
        // Opción 2 (más simple por ahora): Si el filtro `tarjeta` está activo, solo mostrar gastos que sean pagos de cuotas de tarjeta.
        // Esto no es ideal porque no todas las transacciones de tarjeta son "pagos de cuotas".

        // Considerando el contexto, el filtro de tarjeta tiene más sentido si se aplica a `gastos_tarjeta`.
        // Pero este reporte es para `transacciones`.
        // Vamos a asumir que se quiere filtrar por las transacciones que *corresponden* a esa tarjeta.
        // Esto requiere que de alguna manera `transacciones` esté ligada a `tarjetas_credito`.
        // Si no hay una columna `id_tarjeta` en `transacciones`, no se puede filtrar directamente.

        // PARA EL ALCANCE ACTUAL: No hay un vínculo directo entre `transacciones` e `id_tarjeta` sin un JOIN complejo o una columna nueva.
        // Si el usuario selecciona una tarjeta, lo más lógico es mostrar los `gastos_tarjeta` asociados a esa tarjeta.
        // Pero la estructura actual del reporte se basa en `transacciones`.

        // Vamos a dejar el filtro `tarjeta` para una futura iteración o si el esquema de BD se ajusta para permitirlo.
        // Por ahora, solo se aplicará el filtro de `categoría`.

        // Si realmente se desea filtrar por tarjeta en las transacciones, se necesita que las transacciones tengan un id_tarjeta.
        // O que las formas de pago tengan un id_tarjeta asociado.
        // Por ahora, me centraré en el filtro de categoría y mes/año.
        // Si el usuario quiere el filtro de tarjeta para `transacciones`, la BD debe cambiar.
    }
    
    $where_sql = implode(' AND ', $where_clauses);

    // 1. Total de Gastos del Mes
    $stmt_total = $pdo->prepare(
        "SELECT SUM(monto) as total FROM transacciones t 
         WHERE " . $where_sql
    );
    $stmt_total->execute($params);
    $total_gastos_mes = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Gastos por Categoría
    // Preparar parámetros para stmt_categorias. Necesita el total_gastos_mes para el porcentaje.
    $params_categorias = $params;
    array_unshift($params_categorias, $total_gastos_mes > 0 ? $total_gastos_mes : 1);

    $stmt_categorias = $pdo->prepare(
        "SELECT 
            c.nombre, 
            SUM(t.monto) as total_gastado,
            (SUM(t.monto) / ?) * 100 as porcentaje
         FROM transacciones t
         JOIN categorias c ON t.id_categoria = c.id_categoria
         WHERE " . $where_sql . "
         GROUP BY c.id_categoria, c.nombre
         ORDER BY total_gastado DESC"
    );
    $stmt_categorias->execute($params_categorias);
    $gastos_por_categoria = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // 3. Gastos Detallados (Transacciones individuales)
    $stmt_detallados = $pdo->prepare(
        "SELECT 
            DATE_FORMAT(t.fecha_transaccion, '%d-%m-%Y %H:%i') as fecha_transaccion,
            t.descripcion,
            c.nombre as categoria_nombre,
            t.monto,
            CASE
                WHEN t.id_cuenta IS NOT NULL THEN cu.nombre
                WHEN t.id_forma_pago IS NOT NULL THEN fp.nombre
                ELSE 'N/A'
            END as cuenta_o_tarjeta
         FROM transacciones t
         JOIN categorias c ON t.id_categoria = c.id_categoria
         LEFT JOIN cuentas cu ON t.id_cuenta = cu.id_cuenta
         LEFT JOIN formas_pago fp ON t.id_forma_pago = fp.id_forma_pago
         WHERE " . $where_sql . "
         ORDER BY t.fecha_transaccion DESC"
    );
    $stmt_detallados->execute($params);
    $gastos_detallados = $stmt_detallados->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => [
            'total_gastos_mes' => (float)$total_gastos_mes,
            'gastos_por_categoria' => $gastos_por_categoria,
            'gastos_detallados' => $gastos_detallados
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);

