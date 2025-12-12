<?php
header('Content-Type: application/json');
require '../../db_connection.php';

$response = ['status' => 'error', 'message' => ''];

try {
    // 1. Obtener el año de la solicitud GET
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // 2. Obtener id_cuenta para "Banco Galicia"
    $stmt_cuenta = $pdo->prepare("SELECT id_cuenta FROM cuentas WHERE nombre = 'Banco Galicia' LIMIT 1");
    $stmt_cuenta->execute();
    $cuenta_galicia = $stmt_cuenta->fetch();

    if (!$cuenta_galicia) {
        throw new Exception("Cuenta 'Banco Galicia' no encontrada.");
    }
    $id_cuenta_galicia = $cuenta_galicia['id_cuenta'];

    // 3. Obtener id_categoria para "Sueldo"
    $stmt_categoria = $pdo->prepare("SELECT id_categoria FROM categorias WHERE nombre = 'Sueldo' AND tipo = 'ingreso' LIMIT 1");
    $stmt_categoria->execute();
    $categoria_sueldo = $stmt_categoria->fetch();

    if (!$categoria_sueldo) {
        throw new Exception("Categoría 'Sueldo' no encontrada. Asegúrese de que existe y es de tipo 'ingreso'.");
    }
    $id_categoria_sueldo = $categoria_sueldo['id_categoria'];

    // 4. Definir feriados de Argentina (ejemplo, solo para 2025)
    // Esto debería ser más robusto en una aplicación real, quizás desde una DB.
    function getArgentineHolidays($year) {
        $holidays = [];
        // Feriados inamovibles (ejemplo para 2025)
        $holidays[] = "$year-01-01"; // Año Nuevo
        $holidays[] = "$year-03-24"; // Día Nacional de la Memoria por la Verdad y la Justicia
        $holidays[] = "$year-04-02"; // Día del Veterano y de los Caídos en la Guerra de Malvinas
        $holidays[] = "$year-05-01"; // Día del Trabajador
        $holidays[] = "$year-05-25"; // Día de la Revolución de Mayo
        $holidays[] = "$year-06-20"; // Paso a la Inmortalidad del Gral. Manuel Belgrano
        $holidays[] = "$year-07-09"; // Día de la Independencia
        $holidays[] = "$year-08-17"; // Paso a la Inmortalidad del Gral. José de San Martín
        $holidays[] = "$year-10-12"; // Día del Respeto a la Diversidad Cultural
        $holidays[] = "$year-12-08"; // Inmaculada Concepción de María
        $holidays[] = "$year-12-25"; // Navidad

        // Feriados puente (si aplican, para 2025 se deberían consultar los oficiales)
        // Ejemplo: $holidays[] = "$year-XX-YY";

        // Feriados trasladables y Carnaval (estos varían cada año)
        // Carnaval (fecha varía cada año, ejemplo para 2025)
        $holidays[] = "$year-03-03"; // Lunes de Carnaval
        $holidays[] = "$year-03-04"; // Martes de Carnaval

        // Semana Santa (fecha varía cada año, ejemplo para 2025)
        $holidays[] = "$year-04-17"; // Jueves Santo (no laborable)
        $holidays[] = "$year-04-18"; // Viernes Santo

        return $holidays;
    }

    $argentina_holidays = getArgentineHolidays($year);

    // 5. Función para verificar si un día es hábil
    function isBusinessDay($dateString, $holidays) {
        $timestamp = strtotime($dateString);
        $dayOfWeek = date('N', $timestamp); // 1 (lunes) a 7 (domingo)

        // Es fin de semana
        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
            return false;
        }

        // Es feriado
        if (in_array($dateString, $holidays)) {
            return false;
        }

        return true;
    }

    // 6. Función para calcular la fecha de depósito del sueldo
    function getSalaryDepositDate($month, $year, $holidays) {
        $date = new DateTime(sprintf("%d-%02d-01", $year, $month));

        // Si el primer día del mes no es hábil, retroceder al último día hábil del mes anterior.
        if (!isBusinessDay($date->format('Y-m-d'), $holidays)) {
            // Retroceder un día hasta encontrar un día hábil
            while (!isBusinessDay($date->format('Y-m-d'), $holidays)) {
                $date->modify('-1 day');
            }
        }
        return $date->format('Y-m-d');
    }

    $results = [];
    $salaryDates = []; // Para almacenar las fechas de sueldo calculadas por mes

    // Calcular todas las fechas de sueldo del año para un acceso eficiente
    for ($month = 1; $month <= 12; $month++) {
        $salaryDates[$month] = getSalaryDepositDate($month, $year, $argentina_holidays);
    }

    // Iterar por cada mes del año
    for ($month = 1; $month <= 12; $month++) {
        $monthName = DateTime::createFromFormat('!m', $month)->format('F'); // Nombre del mes

        // Fecha de sueldo del mes actual
        $salaryDateCurrentMonth = $salaryDates[$month];

        $startPeriod = null;
        $endPeriod = new DateTime($salaryDateCurrentMonth);
        $endPeriod->modify('-1 second'); // Hasta un segundo antes del depósito de sueldo actual

        // Determinar el inicio del período
        if ($month == 1) {
            // Para el primer mes del año, el período inicia el 1ro de Enero
            $startPeriod = new DateTime("$year-01-01 00:00:00");
        } else {
            // Para los meses siguientes, el período inicia justo después del sueldo del mes anterior
            $salaryDatePreviousMonth = $salaryDates[$month - 1];
            $startPeriod = new DateTime($salaryDatePreviousMonth);
            $startPeriod->modify('+1 second');
        }

        // Si es el primer mes y el salario se paga el mes anterior, el inicio del período
        // para el cálculo del saldo puede ser antes del 1ro de enero.
        // En este caso, si el startPeriod calculado para el primer mes es anterior al 1ro de Enero,
        // lo forzamos al 1ro de Enero para que el saldo inicial de la cuenta sea el punto de partida.
        if ($month == 1 && $startPeriod->format('Y-m-d') < "$year-01-01") {
            $startPeriod = new DateTime("$year-01-01 00:00:00");
        }


        // Sumar todos los movimientos de la cuenta Banco Galicia en el período
        $stmt_balance = $pdo->prepare("
            SELECT SUM(CASE WHEN tipo_movimiento = 'ingreso' THEN monto ELSE -monto END) as saldo_acumulado
            FROM transacciones
            WHERE id_cuenta = :id_cuenta
              AND fecha_transaccion BETWEEN :start_period AND :end_period
        ");
        $stmt_balance->execute([
            ':id_cuenta' => $id_cuenta_galicia,
            ':start_period' => $startPeriod->format('Y-m-d H:i:s'),
            ':end_period' => $endPeriod->format('Y-m-d H:i:s')
        ]);
        $saldo_periodo = $stmt_balance->fetchColumn();
        $saldo_periodo = $saldo_periodo === null ? 0.00 : (float)$saldo_periodo; // Asegurar que es un float

        $saldo_pre_sueldo = $saldo_periodo;

        // Si es el primer mes, necesitamos el saldo inicial de la cuenta como base
        if ($month == 1) {
            $stmt_saldo_inicial = $pdo->prepare("SELECT saldo_inicial FROM cuentas WHERE id_cuenta = :id_cuenta");
            $stmt_saldo_inicial->execute([':id_cuenta' => $id_cuenta_galicia]);
            $saldo_inicial_cuenta = $stmt_saldo_inicial->fetchColumn();
            $saldo_pre_sueldo += $saldo_inicial_cuenta;
        }

        $results[] = [
            'mes' => $monthName,
            'fecha_sueldo_calculada' => $salaryDateCurrentMonth,
            'saldo_pre_sueldo' => number_format((float)$saldo_pre_sueldo, 2, '.', '')
        ];
    }

    $response['status'] = 'success';
    $response['data'] = $results;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
