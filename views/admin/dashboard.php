<?php
// /views/admin/dashboard.php - CON DEBUG
require_once __DIR__ . '/../../config/config.php';

echo "<!-- DEBUG: Dashboard admin cargado -->";
echo "<!-- Session ID: " . session_id() . " -->";
echo "<!-- User ID en sesión: " . ($_SESSION['user_id'] ?? 'NO') . " -->";
echo "<!-- User Role en sesión: " . ($_SESSION['user_role'] ?? 'NO') . " -->";

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo "<h2>❌ NO estás logueado</h2>";
    echo "<p>Redirigiendo al login...</p>";
    echo "<script>setTimeout(() => window.location.href = '" . BASE_URL . "login.php', 2000);</script>";
    exit;
}

// Verificar rol
if ($_SESSION['user_role'] !== 'admin') {
    echo "<h2>❌ NO eres administrador</h2>";
    echo "<p>Tu rol es: <strong>" . ($_SESSION['user_role'] ?? 'NO ROL') . "</strong></p>";
    echo "<p>Redirigiendo...</p>";
    echo "<script>setTimeout(() => window.location.href = '" . BASE_URL . "', 2000);</script>";
    exit;
}

// Si llegamos aquí, ES ADMIN
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .admin-header { background: #d32f2f; color: white; padding: 20px; }
        .debug-info { background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🎵 PANEL DE ADMINISTRACIÓN</h1>
        <p>✅ ¡ACCESO CONCEDIDO!</p>
    </div>
    
    <div class="debug-info">
        <h3>✅ SESIÓN VERIFICADA:</h3>
        <p>Usuario ID: <?php echo $_SESSION['user_id']; ?></p>
        <p>Nombre: <?php echo $_SESSION['user_name']; ?></p>
        <p>Rol: <strong style="color: red;"><?php echo $_SESSION['user_role']; ?></strong> ← ADMIN</p>
        <p>Session ID: <?php echo session_id(); ?></p>
    </div>
    
    <div style="margin-top: 20px; padding: 20px; background: #f5f5f5; border-radius: 10px;">
        <h2>Opciones de administración:</h2>
        <ul>
            <li><a href="#">Gestionar Usuarios</a></li>
            <li><a href="#">Gestionar Canciones</a></li>
            <li><a href="#">Moderar Reviews</a></li>
            <li><a href="#">Configuración</a></li>
        </ul>
    </div>
    
    <hr>
    <p><a href="<?php echo BASE_URL; ?>">← Volver al sitio</a> | 
    <a href="logout.php">Cerrar sesión</a></p>
</body>
</html>