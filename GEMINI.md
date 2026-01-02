# Bitácora de Desarrollo

## Sábado, 6 de diciembre de 2025
- Se han leído los archivos `GEMINI.md` y `BACKLOG.md` para comprender el contexto del proyecto.
- Se ha planificado la creación de un script `php/seeders.php` para la carga inicial de datos.
- Se ha creado `php/db_connection.php` y `php/seeders.php`.
- La carga inicial de datos (seeders) se ha ejecutado y verificado con éxito.
- Se han creado los endpoints de la API de backend: `php/api/dashboard_summary.php` (para resúmenes) y `php/api/transactions.php` (para registrar transacciones).
- Se ha iniciado el desarrollo del Frontend (Dashboard):
    - Se ha modificado `index.php` para crear la estructura visual del Dashboard (tarjetas de resumen, tablas de cuentas).
    - Se ha integrado JQuery/AJAX en `js/main.js` para consumir los datos de la API y mostrarlos dinámicamente.
- Se han creado los endpoints `php/api/get_cuentas.php`, `php/api/get_categorias.php`, `php/api/get_formas_pago.php` para la carga dinámica de datos en formularios.
- Se han diseñado y programado los formularios de transacción (en modales de Bootstrap) para agregar nuevas transacciones, con lógica de envío y recarga del dashboard en `js/main.js`.GEMINI.md
- Se ha implementado el ABM completo para **Cuentas** (Crear, Leer, Editar, Eliminar/Desactivar) en `gestion.php`, `js/gestion.js` y `php/api/cuentas/`.
- Se ha implementado el ABM completo para **Categorías** (Crear, Leer, Editar, Eliminar/Desactivar) en `gestion.php`, `js/gestion.js` y `php/api/categorias/`.
- Se ha implementado el ABM completo para **Formas de Pago** (Crear, Leer, Editar, Eliminar/Desactivar) en `gestion.php`, `js/gestion.js` y `php/api/formas_pago/`.
- Se ha implementado el ABM completo para **Tarjetas de Crédito** (Crear, Leer, Editar, Eliminar/Desactivar) en `gestion.php`, `js/gestion.js` y `php/api/tarjetas_credito/`.
- Se ha implementado el ABM completo para **Ingresos Fijos** (Crear, Leer, Editar, Eliminar/Desactivar) en `gestion.php`, `js/gestion.js` y `php/api/ingresos_fijos/`.
- Se ha implementado el registro de **Gastos de Tarjeta** a través del modal de transacciones en `index.php` y `js/main.js`, y se ha creado el endpoint `php/api/card_transactions.php`.
- Se ha añadido un listado de **Últimos Gastos de Tarjeta** en `index.php` con su correspondiente endpoint `php/api/get_card_expenses.php` y lógica en `js/main.js`.
- Se ha decidido implementar la gestión de **"Gastos Vacaciones"** mediante un sistema de **"Viajes/Proyectos"** a los que se asociarán transacciones. Esto implica modificaciones en la base de datos para crear la tabla `viajes_proyectos` y añadir una columna `id_proyecto` a `transacciones`.

# Premisas del Proyecto

- **Diseño:** Muy elegante, intuitivo y orientado a la simplicidad, claro en la usabilidad. Responsive.
- **Tecnologías de Backend:** PHP.
- **Tecnologías de Frontend:** HTML5, CSS3, JQuery, Ajax.
- **Calidad del Código:** Siempre funcional y seguro.
- **Comentarios:** Siempre en español.

# Estructura de Archivos y Directorios

- `index.php`: Archivo principal que contiene la interfaz de usuario (UI) y la estructura HTML base, incluyendo Bootstrap y jQuery.
- `/css`: Directorio para las hojas de estilo.
  - `style.css`: Hoja de estilos personalizada para la aplicación.
- `/js`: Directorio para los archivos de JavaScript.
  - `main.js`: Lógica de frontend, interacciones con JQuery y control del menú.
  - `gestion.js`: Lógica de frontend para la gestión de datos maestros (cuentas, categorías, etc.).
- `/php`: Directorio para los archivos de backend en PHP.
  - `db_connection.php`: Script para la configuración y conexión a la base de datos.
  - `seeders.php`: Script para la carga inicial de datos.
  - `db_update.php`: Script para aplicar actualizaciones de esquema de BD.
- `/uploads`: Directorio destinado a almacenar los archivos subidos (ej. imágenes de recibos).

# Base de Datos

**Nombre de la Base de Datos:** `sistfinanciero`
**Charset:** `utf8mb4`
**Collation:** `utf8mb4_unicode_ci`

**Esquema de Tablas:**

```sql
CREATE TABLE `cuentas` ( `id_cuenta` INT AUTO_INCREMENT PRIMARY KEY, `nombre` VARCHAR(100) NOT NULL, `saldo_inicial` DECIMAL(10, 2) NOT NULL DEFAULT 0.00, `tipo_cuenta` ENUM('banco', 'billetera', 'otro') NOT NULL, `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP, `activo` BOOLEAN NOT NULL DEFAULT TRUE );
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('ingreso','gasto') NOT NULL,
  `id_categoria_padre` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_categoria`),
  KEY `fk_categoria_padre` (`id_categoria_padre`),
  CONSTRAINT `fk_categoria_padre` FOREIGN KEY (`id_categoria_padre`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `formas_pago` ( `id_forma_pago` INT AUTO_INCREMENT PRIMARY KEY, `nombre` VARCHAR(100) NOT NULL, `activo` BOOLEAN NOT NULL DEFAULT TRUE );
CREATE TABLE `transacciones` ( `id_transaccion` INT AUTO_INCREMENT PRIMARY KEY, `id_cuenta` INT NOT NULL, `id_categoria` INT NOT NULL, `id_forma_pago` INT NULL, `tipo_movimiento` ENUM('ingreso', 'gasto') NOT NULL, `monto` DECIMAL(10, 2) NOT NULL, `descripcion` TEXT, `fecha_transaccion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `ruta_imagen_recibo` VARCHAR(255) NULL, `analisis_recibo_texto` TEXT NULL, FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas`(`id_cuenta`), FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`), FOREIGN KEY (`id_forma_pago`) REFERENCES `formas_pago`(`id_forma_pago`) );
CREATE TABLE `tarjetas_credito` ( `id_tarjeta` INT AUTO_INCREMENT PRIMARY KEY, `nombre` VARCHAR(100) NOT NULL, `banco` VARCHAR(100) NULL, `limite_credito` DECIMAL(10, 2) NOT NULL DEFAULT 0.00, `fecha_cierre_extracto` INT NOT NULL, `fecha_vencimiento_pago` INT NOT NULL, `activo` BOOLEAN NOT NULL DEFAULT TRUE );
CREATE TABLE `gastos_tarjeta` ( `id_gasto_tarjeta` INT AUTO_INCREMENT PRIMARY KEY, `id_tarjeta` INT NOT NULL, `descripcion` TEXT, `monto_total` DECIMAL(10, 2) NOT NULL, `cuotas_totales` INT NOT NULL DEFAULT 1, `cuotas_pagadas` INT NOT NULL DEFAULT 0, `monto_por_cuota` DECIMAL(10, 2) NOT NULL, `fecha_compra` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `ruta_imagen_recibo` VARCHAR(255) NULL, FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjetas_credito`(`id_tarjeta`) );
CREATE TABLE `ingresos_fijos` ( `id_ingreso_fijo` INT AUTO_INCREMENT PRIMARY KEY, `nombre` VARCHAR(100) NOT NULL, `monto` DECIMAL(10, 2) NOT NULL, `frecuencia` ENUM('mensual', 'quincenal', 'anual') NOT NULL, `dia_pago` INT NULL, `ultima_ejecucion` DATE NULL, `proximo_aumento_simulado_porcentaje` DECIMAL(5, 2) NULL DEFAULT 0.00, `activo` BOOLEAN NOT NULL DEFAULT TRUE );
```

# Plan de Desarrollo

1.  **Carga de Datos Iniciales (Seeders):** Crear un script PHP para poblar las tablas `categorias`, `formas_pago` y `cuentas` con datos de ejemplo para poder empezar a operar.
2.  **Desarrollo del Backend (API):**
    *   Crear los primeros archivos en `/php` para manejar peticiones AJAX (ej. `api/get_dashboard_summary.php`).
    *   Implementar la lógica para obtener los resúmenes del Dashboard: saldos de cuentas, total de ingresos y gastos del mes.
    *   Implementar el endpoint para registrar nuevas transacciones (ingresos y gastos).
    **Estado:** Completado.
3.  **Desarrollo del Frontend (Dashboard):
    *   Modificar `index.php` para crear la estructura visual del Dashboard (tarjetas de resumen, tablas, etc.). **Estado:** Completado.
    *   Utilizar JQuery/AJAX en `js/main.js` para consumir los datos de la API y mostrarlos dinámicamente en el Dashboard. **Estado:** Completado.
    *   Diseñar y programar los formularios (en modales de Bootstrap) para agregar/editar transacciones. **Estado:** Completado.
    **Estado:** Completado.