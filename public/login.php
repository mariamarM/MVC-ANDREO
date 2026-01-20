<?php
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        try {
            // Buscar usuario con su role
            $stmt = $pdo->prepare("
                SELECT id, username, password_hash, role 
                FROM users 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Iniciar sesión con todos los datos
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Redirigir según el role
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . 'dashboardUser.php');
                }
                exit;
            } else {
                $error = "Invalid username or password";
            }
        } catch (PDOException $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Music Virtual Closet</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <main>
        <div class="login-container">
            <h2>Login to Music Virtual Closet</h2>
            <?php if (isset($_GET['registered'])): ?>
    <p style="color: green;">Account created successfully. Please log in.</p>
<?php endif; ?>
            <form action="/views/auth/login.php" method="POST">
                <div class="form-group
">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group
">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Log In</button>
            </form>
        </div>
    </main>
</body>
</html>