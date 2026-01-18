<?php


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php'; // ahora PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        header('Location: ' . BASE_URL . 'register.php?error=empty');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . 'register.php?error=email');
        exit;
    }

    $pdo = Database::getInstance();

    // Verificar si el email ya existe
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ' . BASE_URL . 'register.php?error=exists');
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
    if ($stmt->execute([$name, $email, $passwordHash])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $name;
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
    } else {
        echo "Error al registrar";
    }
}
