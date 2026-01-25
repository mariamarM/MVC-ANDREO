<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <script src="<?= BASE_URL ?>/js/cursor-effect.js" defer></script>
    <style>
    /* ===== Caja de registro similar a login ===== */
    .register-box {
        display: flex;
        width: 50%;
        padding: 4% 25%;
        border-radius: 28px;
        flex-direction: column;
    }

    .register-box label {
        margin-top: 20px;
        color: #e11d2e;
        font-size: 16px;
        margin-bottom: 10px;
        font-weight: bold;
    }

    /* ===== TITULO ===== */
    .register-box h2 {
        color: #e11d2e;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: bold;
    }

    .register-box h2::after {
        content: "/";
        margin-left: 8px;
    }

    /* ===== SEPARADOR ===== */
    .register-box h2 + hr {
        border: none;
        height: 2px;
        background: #e11d2e;
        margin: 20px 0 40px;
    }

    /* ===== ERRORES ===== */
    .register-box .error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* ===== FORM ===== */
    .register-box form {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .register-box input {
        width: 100%;
        padding: 14px 16px;
        border: 1.8px solid #e11d2e;
        border-radius: 6px;
        font-size: 16px;
    }

    .register-box input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(225, 29, 46, 0.2);
    }

    /* ===== BOTÓN REGISTER ===== */
    .register-box button {
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

    .register-box button::after {
        content: " /";
    }

    .register-box button:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(225, 29, 46, 0.35);
    }

    /* ===== LINKS ===== */
    .register-box p {
        margin-top: 30px;
    }

    .register-box a {
        color: #e11d2e;
        text-decoration: underline;
        font-weight: bold;
    }

    /* ===== Responsive account link ===== */
    .account {
        color: #e11d2e;
        font-weight: bold;
        text-decoration: none;
        white-space: nowrap;
        position: absolute;
        bottom: 25%;
        font-size: 20px;
        right: 62%;
    }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../views/layout/nav.php'; ?>

    <div class="register-box">
        <h2>Register</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>register_action.php">
            <label for="name">Name /</label>
            <input type="text" name="name" id="name" placeholder="Nombre completo" required>

            <label for="email">Email /</label>
            <input type="email" name="email" id="email" placeholder="Correo electrónico" required>

            <label for="password">Password /</label>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>

            <label for="music_genre">Favorite Music Genre /</label>
            <input type="text" name="music_genre" id="music_genre" placeholder="Género musical favorito">

            <button type="submit">Register</button>
        </form>

        <div class="account">
            <a href="login.php">Already have an account//</a>
        </div>
    </div>
</body>

</html>
