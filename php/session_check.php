<?php
// Iniciar la sesión en cada página
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprobar si el usuario está logueado. Si no, redirigir a login.php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

/**
 * Verifica si el usuario logueado tiene el rol requerido.
 * Si no tiene el permiso, muestra una página de acceso denegado y termina la ejecución.
 *
 * @param string $rol_requerido El nombre del rol que se necesita para acceder.
 */
function check_permission($rol_requerido) {
    // Asumimos que el rol del usuario está en la sesión.
    // Esta información se debe cargar al momento del login.
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol_requerido) {
        http_response_code(403);
        echo "<!DOCTYPE html><html lang='es'><head><title>Acceso Denegado</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "<style>body { display: flex; align-items: center; justify-content: center; height: 100vh; text-align: center; }</style>";
        echo "</head><body><div class='container'>";
        echo "<h1 class='display-1'>403</h1><h2>Acceso Denegado</h2>";
        echo "<p>No tienes los permisos necesarios para acceder a esta página.</p>";
        echo "<a href='index.php' class='btn btn-primary'>Volver al inicio</a>";
        echo "</div></body></html>";
        exit;
    }
}
