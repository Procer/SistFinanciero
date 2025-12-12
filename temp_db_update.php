<?php
require_once __DIR__ . '/php/db_connection.php';

try {
    // Actualizar el nombre de la cuenta
    $stmt = $pdo->prepare("UPDATE cuentas SET nombre = :new_name WHERE nombre = :old_name");
    $stmt->execute([':new_name' => 'Naranja X', ':old_name' => 'Cuenta NatanjaX']);
    echo "Nombre de cuenta 'Cuenta NatanjaX' actualizado a 'Naranja X'." . PHP_EOL;

    // Resetear todos los saldos iniciales a 0.00
    $stmt = $pdo->prepare("UPDATE cuentas SET saldo_inicial = 0.00");
    $stmt->execute();
    echo "Todos los saldos iniciales de las cuentas han sido reseteados a 0.00." . PHP_EOL;

} catch (\PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . PHP_EOL;
}
?>