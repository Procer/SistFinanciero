<?php
$host = 'localhost';
$db   = 'sistfinanciero';
$user = 'root'; // Asume el usuario por defecto de XAMPP/Laragon
$pass = '';     // Asume la contraseña vacía por defecto de XAMPP/Laragon
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Conexión a la base de datos exitosa."; // Se puede descomentar para depuración
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>