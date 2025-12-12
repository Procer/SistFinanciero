<?php
include_once 'db_connection.php';

echo "Iniciando proceso de actualización de base de datos...\n";

// SQL para crear la tabla viajes_proyectos
$sql_create_table = "
CREATE TABLE IF NOT EXISTS `viajes_proyectos` (
    `id_proyecto` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL UNIQUE,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NULL,
    `presupuesto_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `activo` BOOLEAN NOT NULL DEFAULT TRUE,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// SQL para añadir la columna id_proyecto a la tabla transacciones
$sql_add_column = "
ALTER TABLE `transacciones`
ADD COLUMN `id_proyecto` INT NULL AFTER `descripcion`,
ADD CONSTRAINT `fk_transacciones_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `viajes_proyectos`(`id_proyecto`) ON DELETE SET NULL ON UPDATE CASCADE;
";

try {
    $pdo->exec($sql_create_table);
    echo "Tabla 'viajes_proyectos' verificada/creada exitosamente.\n";

    // Verificar si la columna ya existe antes de añadirla
    $stmt = $pdo->query("SHOW COLUMNS FROM `transacciones` LIKE 'id_proyecto'");
    $columnExists = $stmt->fetch();

    if (!$columnExists) {
        $pdo->exec($sql_add_column);
        echo "Columna 'id_proyecto' añadida a 'transacciones' y FK creada exitosamente.\n";
    } else {
        echo "Columna 'id_proyecto' ya existe en 'transacciones'. Omitiendo.\n";
    }

    echo "Proceso de actualización de base de datos completado.\n";

} catch (PDOException $e) {
    echo "Error en la actualización de la base de datos: " . $e->getMessage() . "\n";
}

