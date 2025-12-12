<?php

$config_file = __DIR__ . '/config.ini';

if (!file_exists($config_file)) {
    die("Error de configuración: El archivo 'php/config.ini' no se encuentra. Por favor, cópielo desde 'php/config.example.ini' y configure sus credenciales de base de datos.");
}

$config = parse_ini_file($config_file, true);

if ($config === false || !isset($config['database'])) {
    die("Error de configuración: El archivo 'php/config.ini' es inválido o no contiene la sección [database].");
}

$db_config = $config['database'];

$host = $db_config['host'];
$db   = $db_config['dbname'];
$user = $db_config['user'];
$pass = $db_config['password'];
$charset = $db_config['charset'];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En un entorno de producción, sería mejor loguear este error que mostrarlo directamente.
    throw new \PDOException('Error de conexión a la base de datos. Verifique la configuración en php/config.ini. ' . $e->getMessage(), (int)$e->getCode());
}
?>