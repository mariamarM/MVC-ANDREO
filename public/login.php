<?php
// login.php - CORREGIDO DEFINITIVAMENTE
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

// DEBUG: Ver sesión actual
error_log("=== LOGIN INICIADO ===");
error_log("Session ID: " . session_id());
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NO'));

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    error_log("Ya logueado como: " . $_SESSION['user_name'] . " - Rol: " . $_SESSION['user_role']);
    
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'views/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'dashboardUser.php');
    }
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    error_log("Login intento: " . $username);
    
    if (empty($username) || empty($password)) {
        $error = 'Completa todos los campos';
    } else {
        try {
            $pdo = Database::getInstance();
            
            // Buscar usuario
            $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                error_log("Usuario encontrado: " . $user['username'] . ", Rol: " . $user['role']);
                
                // VERIFICAR CONTRASEÑA
                if (password_verify($password, $user['password_hash'])) {
                    // ¡LOGIN EXITOSO!
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    
                    // DEBUG
                    error_log("Login EXITOSO para: " . $user['username']);
                    error_log("Rol guardado en sesión: " . $_SESSION['user_role']);
                    error_log("Redirigiendo a admin: " . ($user['role'] === 'admin' ? 'SÍ' : 'NO'));
                    
                    // IMPORTANTE: Guardar sesión inmediatamente
                    session_write_close();
                    
                    // Redirigir según rol
                    if ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . 'views/admin/dashboard.php');
                    } else {
                        header('Location: ' . BASE_URL . 'dashboardUser.php');
                    }
                    exit;
                    
                } else {
                    $error = 'Contraseña incorrecta';
                    error_log("Password verify FALLÓ para: " . $username);
                }
            } else {
                $error = 'Usuario no encontrado';
                error_log("Usuario NO encontrado: " . $username);
            }
            
        } catch (Exception $e) {
            $error = 'Error de conexión';
            error_log("Error DB: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    
</head>
<body>
    <div class="login-box">
        <h2> Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['debug'])): ?>
        <div class="debug">
            <strong>Debug Session:</strong><br>
            ID: <?php echo session_id(); ?><br>
            User ID: <?php echo $_SESSION['user_id'] ?? 'NO'; ?><br>
            User Role: <?php echo $_SESSION['user_role'] ?? 'NO'; ?><br>
            BASE_URL: <?php echo BASE_URL; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Usuario o Email" required value="admin">
            <input type="password" name="password" placeholder="Contraseña" required value="admin123">
            <button type="submit">Entrar</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?php echo BASE_URL; ?>">← Volver</a> | 
            <a href="?debug=1">Debug</a>
        </p>
    </div>
</body>
</html>