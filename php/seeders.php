<?php
// Incluir el archivo de conexión a la base de datos
require_once 'db_connection.php';

echo "Iniciando proceso de seeder...\n";

// Datos de ejemplo para la tabla 'categorias'
$categorias = [
    ['nombre' => 'Alimentos', 'tipo' => 'gasto'],
    ['nombre' => 'Transporte', 'tipo' => 'gasto'],
    ['nombre' => 'Servicios', 'tipo' => 'gasto'],
    ['nombre' => 'Entretenimiento', 'tipo' => 'gasto'],
    ['nombre' => 'Salario', 'tipo' => 'ingreso'],
    ['nombre' => 'Ventas', 'tipo' => 'ingreso'],
    ['nombre' => 'Regalo', 'tipo' => 'ingreso'],
];

// Insertar categorías si no existen
foreach ($categorias as $categoria) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = ? AND tipo = ?");
    $stmt->execute([$categoria['nombre'], $categoria['tipo']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, tipo) VALUES (?, ?)");
        $stmt->execute([$categoria['nombre'], $categoria['tipo']]);
        echo "Categoría '{$categoria['nombre']}' ({$categoria['tipo']}) insertada.\n";
    } else {
        echo "Categoría '{$categoria['nombre']}' ({$categoria['tipo']}) ya existe.\n";
    }
}

// Datos de ejemplo para la tabla 'formas_pago'
$formas_pago = [
    ['nombre' => 'Efectivo'],
    ['nombre' => 'Tarjeta de Débito'],
    ['nombre' => 'Tarjeta de Crédito'],
    ['nombre' => 'Transferencia Bancaria'],
    ['nombre' => 'Mercado Pago'],
];

// Insertar formas de pago si no existen
foreach ($formas_pago as $forma) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM formas_pago WHERE nombre = ?");
    $stmt->execute([$forma['nombre']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO formas_pago (nombre) VALUES (?)");
        $stmt->execute([$forma['nombre']]);
        echo "Forma de pago '{$forma['nombre']}' insertada.\n";
    } else {
        echo "Forma de pago '{$forma['nombre']}' ya existe.\n";
    }
}

// Datos de ejemplo para la tabla 'cuentas'
$cuentas = [
    ['nombre' => 'Mercado Pago', 'saldo_inicial' => 0.00, 'tipo_cuenta' => 'billetera'],
    ['nombre' => 'Banco Galicia', 'saldo_inicial' => 1500.00, 'tipo_cuenta' => 'banco'],
    ['nombre' => 'Billetera (Efectivo)', 'saldo_inicial' => 200.00, 'tipo_cuenta' => 'billetera'],
    ['nombre' => 'Cuenta NatanjayX', 'saldo_inicial' => 0.00, 'tipo_cuenta' => 'otro'],
];

// Insertar cuentas si no existen
foreach ($cuentas as $cuenta) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cuentas WHERE nombre = ?");
    $stmt->execute([$cuenta['nombre']]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO cuentas (nombre, saldo_inicial, tipo_cuenta) VALUES (?, ?, ?)");
        $stmt->execute([$cuenta['nombre'], $cuenta['saldo_inicial'], $cuenta['tipo_cuenta']]);
        echo "Cuenta '{$cuenta['nombre']}' insertada.\n";
    } else {
        echo "Cuenta '{$cuenta['nombre']}' ya existe.\n";
    }
}

echo "Proceso de seeder finalizado.\n";

// Opcional: Cerrar la conexión a la base de datos si no se hace automáticamente
$pdo = null;
?>
