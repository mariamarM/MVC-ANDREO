<?php
require_once __DIR__ . '/../config/config.php';

// Si ya está logueado
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . 'dashboardUser.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'empty';
    } else {
        try {
            // Buscar por username o email
            $stmt = $pdo->prepare(
                "SELECT id, username, password_hash, role 
                 FROM users 
                 WHERE username = :user OR email = :user 
                 LIMIT 1"
            );
            $stmt->execute(['user' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login OK
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_URL . 'dashboardUser.php');
                }
                exit;
            } else {
                $error = 'invalid';
            }
        } catch (PDOException $e) {
            $error = 'database';
        }
    }

    header('Location: ' . BASE_URL . 'login.php?error=' . $error);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if (!empty($_GET['error'])): ?>
    <p style="color:red;">
        <?php
        switch ($_GET['error']) {
            case 'empty':
                echo 'Completa todos los campos';
                break;
            case 'invalid':
                echo 'Usuario o contraseña incorrectos';
                break;
            case 'database':
                echo 'Error de conexión con la base de datos';
                break;
            default:
                // Si viene cualquier cosa rara, no mostramos nada
                echo '';
        }
        ?>
    </p>
<?php endif; ?>


<form method="POST">
    <input type="text" name="username" placeholder="Usuario o email" required>
    <br><br>
    <input type="password" name="password" placeholder="Contraseña" required>
    <br><br>
    <button type="submit">Entrar</button>
</form>

</body>
</html>
