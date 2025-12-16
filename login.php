<?php
// Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variable para almacenar mensajes de error
$error_message = '';

// Si el usuario ya está logueado, redirigir a index.php
if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

// --- BLOQUE PARA CREAR USUARIO INICIAL (COMENTAR DESPUÉS DE USAR) ---
// Para crear tu primer usuario, descomenta este bloque, navega a login.php, y luego vuelve a comentarlo.
/*
require_once 'php/db_connection.php';
$nombre_usuario = 'admin';
$email = 'tu@email.com'; // <-- ¡Cambia esto por tu email real!
$password_plano = 'admin123'; // <-- ¡Cambia esto por una contraseña segura!
$id_rol = 1; // 1 para 'admin'

// Verificar si el usuario ya existe
$checkSql = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$nombre_usuario]);
if ($checkStmt->fetch()) {
    echo "El usuario 'admin' ya existe. Comenta o elimina este bloque de código en login.php.";
} else {
    $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (id_rol, nombre_usuario, email, password_hash, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$id_rol, $nombre_usuario, $email, $password_hash])) {
        echo "Usuario 'admin' creado con éxito. Ahora puedes comentar o eliminar este bloque de código y refrescar la página.";
    } else {
        echo "Error al crear el usuario 'admin'.";
    }
}
exit;
*/
// --- FIN DEL BLOQUE PARA CREAR USUARIO ---


// Procesar el formulario de login solo si se envió por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    require_once 'php/db_connection.php';

    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($password)) {
        $error_message = 'Por favor, complete todos los campos.';
    } else {
        // Consulta para obtener usuario y su rol
        $sql = "SELECT u.id_usuario, u.nombre_usuario, u.password_hash, r.nombre_rol 
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.nombre_usuario = ? AND u.activo = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre_usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password_hash'])) {
            // ¡Éxito! Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            // Guardar datos clave en la sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
            $_SESSION['rol'] = $user['nombre_rol'];

            // Redirigir al dashboard
            header("Location: index.php");
            exit;
        } else {
            // Usuario no encontrado o contraseña incorrecta
            $error_message = 'Nombre de usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SistFinanciero - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>