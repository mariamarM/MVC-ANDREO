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
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <script src="<?php echo BASE_URL; ?>/js/cursor-effect.js" defer></script>
</head>
<style>

.login-box {
display: flex;
    width: 50%;
    padding: 9% 25%;
    border-radius: 28px;

    flex-direction: column;
}
label{
    margin-top:20px;
        color: #e11d2e;
    font-size: 16px;
    margin-bottom: 10px;
    font-weight: bold;
}
/* ===== TITULO ===== */
.login-box h2 {
    color: #e11d2e;
    font-size: 28px;
    margin-bottom: 10px;
    font-weight: bold;
}

.login-box h2::after {
    content: "/";
    margin-left: 8px;
}

/* ===== SEPARADOR ===== */
.login-box h2 + hr {
    border: none;
    height: 2px;
    background: #e11d2e;
    margin: 20px 0 40px;
}

/* ===== ERRORES ===== */
.error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* ===== FORM ===== */
.login-box form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

/* ===== LABEL SIMULADO ===== */
.login-box input {
    width: 100%;
    padding: 14px 16px;
    border: 1.8px solid #e11d2e;
    border-radius: 6px;
    font-size: 16px;
}

.login-box input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(225, 29, 46, 0.2);
}

/* ===== BOTÓN LOGIN ===== */
.login-box button {
    margin-top: 10px;
    background: #e11d2e;
    color: white;
    border: none;
    padding: 18px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.login-box button::after {
    content: " /";
}

.login-box button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(225,29,46,0.35);
}

/* ===== LINKS ===== */
.login-box p {
    margin-top: 30px;
}

.login-box a {
    color: #e11d2e;
    text-decoration: underline;
    font-weight: bold;
}







/* ===== RESPONSIVE ===== */

.account{
        color: #e11d2e;
    font-weight: bold;
    text-decoration: none;
    white-space: nowrap;
    position:absolute;
    bottom:25%;
    font-size: 20px;
    right:66%;
}
</style>

<body>
    <?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

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
            <label for="username">Usuario o Email /</label>
            <input type="text" name="username" placeholder="Usuario o Email" required >
            <label for="password">Contraseña /</label>
            <input type="password" name="password" placeholder="Contraseña" required >
            <button type="submit">Entrar</button>
        </form>
        <div class="account">
             <a href="register.php">Create account//</a>
        </div>
        <!-- <p style="text-align: center; margin-top: 20px;">
            <a href="<?php echo BASE_URL; ?>">← Volver</a> | 
            <a href="?debug=1">Debug</a>
        </p> -->
    </div>
</body>
</html>