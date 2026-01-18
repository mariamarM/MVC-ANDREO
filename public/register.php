<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MVC Register</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
</head>
<body>

<form method="post" action="<?= BASE_URL ?>register_action.php">
    <h2>Register</h2>

    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Favorite Music Genre</label>
    <input type="text" name="music_genre">

    <button type="submit">Register</button>
</form>

</body>
</html>
