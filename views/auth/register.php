<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

$pdo = Database::getInstance();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    header('Location: ' . BASE_URL . 'register.php?error=empty');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . BASE_URL . 'register.php?error=email');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    header('Location: ' . BASE_URL . 'register.php?error=exists');
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
);
$stmt->execute([$username, $email, $passwordHash]);

// ✅ Guardar datos en sesión para login automático
$_SESSION['user_id'] = $pdo->lastInsertId();
$_SESSION['user_name'] = $username;

// ✅ Redirigir al dashboard
header('Location: ' . BASE_URL . 'dashboardUser.php');
exit;
