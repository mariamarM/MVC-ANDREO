<?php
// /var/www/html/config/config.php

// Siempre iniciar sesión al principio del config
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Configuración de la base de datos para Docker
define('DB_HOST', getenv('DB_HOST') ?: 'db-1');
define('DB_NAME', getenv('DB_NAME') ?: 'blog_db');
define('DB_USER', getenv('DB_USER') ?: 'blog_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'blog_pass');

// URL base
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8081') . '/');

// // Intentar crear conexión PDO pero NO morir si falla
// try {
//     $pdo = new PDO(
//         "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
//         DB_USER,
//         DB_PASS,
//         [
//             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//             PDO::ATTR_EMULATE_PREPARES => false
//         ]
//     );
// } catch (PDOException $e) {
//     // Solo registrar el error, no morir
//     error_log("Database connection error: " . $e->getMessage());
//     $pdo = null; // Asegurar que $pdo sea null si falla
// }
?>