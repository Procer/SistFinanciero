# Backlog del Proyecto: Sistema Financiero Personal

Este archivo contiene todas las funcionalidades planificadas para el proyecto. A medida que se completen, se marcarán como realizadas.

## Módulo 1: Core y Gestión de Cuentas
- [x] Carga de Datos Iniciales (Seeders): Crear un script PHP para poblar las tablas `categorias`, `formas_pago` y `cuentas` con datos de ejemplo para poder empezar a operar.
- [x] Crear estructura inicial del proyecto (directorios, archivos base).
- [x] Diseñar e implementar el esquema de la base de datos.
- [x] **Gestión de Cuentas:**
    - [x] Permitir registrar saldos para cuentas (Mercado Pago, Banco Galicia, Cuenta NatanjayX).
    - [x] Permitir registrar la billetera (efectivo).
    - [x] Desarrollar la interfaz para listar/ver las cuentas y sus saldos.
    - [x] Implementado ABM completo (Alta, Baja, Modificación) para Cuentas.

## Módulo 2: Gestión de Transacciones
- [x] **Carga de Transacciones Manuales (Mejora UX):**
    - [x] Formulario único para cargar transacciones (modal).
    - [x] Botones de acceso rápido para "Nuevo Ingreso" y "Nuevo Gasto" en las tarjetas del dashboard.
    - [x] Ocultar y preseleccionar el tipo de movimiento según el botón pulsado.
    - [x] Reordenar el campo "Descripción" para mejorar el flujo.
    - [x] Implementado registro de Gastos de Tarjeta (con cuotas) a través del modal de transacciones.
- [x] **Categorización:**
    - [x] Permitir categorizar Gastos.
    - [x] Permitir categorizar Ingresos.
- [x] **Forma de Pago:**
    - [x] Permitir tipificar gastos por forma de pago.
- [ ] **Manejo de Recibos/Facturas (Simulación IA):**
    - [ ] Campo para cargar una imagen del recibo en el formulario de transacción.
    - [ ] Lógica de backend para gestionar la carga de la imagen.
    - [ ] Sección en la UI para mostrar el resultado simulado del análisis de la IA.

## Módulo 3: Gestión de Ingresos y Sueldo (Recurrentes)
- [x] **Gestión de Ingresos Fijos:**
    - [x] ABM completo para Ingresos Fijos.
    - [ ] Detallar la fuente del ingreso (parte de la descripción).
- [ ] **Gestión de Gastos Fijos:**
    - [x] ABM completo para Gastos Fijos.
- [ ] **Automatización:**
    - [ ] Función para registrar automáticamente ingresos y gastos fijos en transacciones.
    - [ ] Configuración para la ejecución automática (ej. vía cron job).
- [ ] **Registro de Sueldo:**
    - [ ] Función para registrar el sueldo.
    - [ ] Cálculo simulado del aumento mensual para validación.

## Módulo 4: Tarjetas de Crédito
- [x] **Gestión de Tarjetas de Crédito:**
    - [x] ABM completo para Tarjetas de Crédito.
- [x] **Registro de Gastos con Tarjeta:**
    - [x] Registrar gastos fijos en tarjetas.
    - [x] Registrar gastos en cuotas.
- [ ] **Descuento Automático (de Cuotas):**
    - [ ] Las cuotas se descuentan automáticamente mes a mes del resumen.
- [x] **Visualización de Gastos de Tarjeta:**
    - [x] Listado de últimos gastos de tarjeta en el Dashboard.
- [ ] **Resumen de Tarjeta (Avanzado):**
    - [ ] Resumen del gasto total de la tarjeta.
    - [ ] Permitir cargar gastos administrativos (ej. a través de la carga de imágenes/recibos).

## Módulo 5: Reportes y Resumen
- [x] **Análisis de Saldo Pre-Sueldo:** Mostrar el saldo de la cuenta "Banco Galicia" antes del depósito de sueldo cada mes, considerando feriados y días hábiles.
- [x] **Análisis Detallado por Cuenta:** Potenciar la página de 'Análisis' para incluir un selector de cuentas, resumen de ingresos/gastos, y un gráfico de gastos por categoría.
- [x] **Reporte por Viaje/Proyecto (Gastos Vacaciones):**
    - [x] Creación de la entidad `viajes_proyectos` en BD.
    - [x] Adición de `id_proyecto` a `transacciones`.
    - [x] ABM completo para Viajes/Proyectos en `gestion.php`.
    - [x] Integración en el modal de transacciones para asociar a proyectos.
    - [x] Visualización de reporte detallado por proyecto en `analisis.php`.
- [ ] **Dashboard Principal:**
    - [ ] Comparación instantánea de ingresos vs. gastos.
    - [ ] Resumen de proyectos activos.
- [ ] **Resumen Mensual:**
    - [ ] Resumen de gastos del mes actual.
    - [ ] Resumen de gastos del mes anterior para comparación.
- [ ] **Filtros Avanzados:**
    - [ ] Pantalla de filtros avanzados por mes y año.

## Módulo 6: Funciones Futuras (Control Presupuestario y Reportes Avanzados)
- [ ] **Presupuestos por Categoría:**
    - [ ] Permitir establecer un límite de gasto mensual por categoría.
    - [ ] Alertar visualmente al acercarse o exceder el límite.
- [ ] **Proyección de Flujo de Caja:**
    - [ ] Vista que proyecte el saldo de las cuentas en los próximos meses basado en gastos/ingresos fijos y cuotas.
- [ ] **Gráficos de Tendencias:**
    - [ ] Reportes visuales (pastel o barras) de distribución de gastos por categoría (anual/semestral).
- [ ] **Conciliación Bancaria:**
    - [ ] Proceso para cotejar transacciones del sistema con el extracto bancario real.

## Módulo 7: SUGERENCIAS DE IA (Funcionalidades Adicionales)

### Gestión Financiera Avanzada
- [ ] **Seguimiento de Inversiones:**
    - [ ] Módulo para registrar y seguir el valor de acciones, fondos, criptomonedas, etc.
    - [ ] Calcular y visualizar el rendimiento del portafolio.
- [ ] **Gestión de Deudas:**
    - [ ] Módulo para registrar préstamos (personales, de auto, etc.) y ver cronogramas de amortización.
    - [ ] Herramienta para comparar estrategias de pago de deudas (ej. "bola de nieve" vs "avalancha").
- [ ] **Establecimiento de Metas Financieras:**
    - [ ] Crear metas (ej. "Ahorro para vacaciones", "Fondo de emergencia").
    - [ ] Asignar cuentas o transacciones a una meta y visualizar el progreso.

### Automatización y Análisis
- [ ] **Gestión de Transacciones Recurrentes:**
    - [ ] Módulo para gestionar todos los gastos e ingresos recurrentes (suscripciones, alquiler, etc.).
    - [ ] Calendario de facturas para visualizar próximos pagos.
- [ ] **Análisis y Alertas Inteligentes:**
    - [ ] Alertas sobre gastos inusuales o duplicados.
    - [ ] Resumen de "A dónde va tu dinero" con análisis de hábitos de consumo.
- [ ] **Etiquetado Avanzado (Tags):**
    - [ ] Permitir añadir "tags" (ej. `#viaje-2025`) a las transacciones para agrupar gastos que cruzan varias categorías.

### Utilidad y Seguridad
- [ ] **Cálculo de Patrimonio Neto:**
    - [ ] Widget principal en el Dashboard que muestre Activos Totales - Pasivos Totales.
- [ ] **Importación y Exportación de Datos:**
    - [ ] Herramienta para importar transacciones desde un archivo CSV (formato bancario estándar).
    - [ ] Opción para exportar todos los datos del usuario a CSV o JSON.
- [ ] **Soporte Multi-moneda:**
    - [ ] Capacidad para registrar transacciones y cuentas en diferentes monedas, con conversión automática.
- [ ] **Autenticación de Usuario:**
    - [ ] Sistema de inicio de sesión y registro de usuarios para proteger la información financiera.

## Módulo 8: Configuración y Gestión
- [x] **Página de Gestión:**
    - [x] Crear una nueva página `gestion.php` para administrar datos.
    - [x] Añadir enlace a la página de Gestión en el menú principal.
- [x] **Gestión de Categorías:**
    - [x] Interfaz para listar y agregar nuevas categorías de gastos.
    - [x] Permitir editar categorías existentes.
    - [x] Permitir eliminar categorías.
- [x] **Gestión de Formas de Pago:**
    - [x] Interfaz para listar y agregar nuevas formas de pago.
    - [x] Permitir editar formas de pago existentes.
    - [x] Permitir eliminar formas de pago.