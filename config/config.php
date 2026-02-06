<?php
// /var/www/html/config/config.php - CON SESIÓN AÑADIDA

// ===== PARTE NUEVA: INICIAR SESIÓN SIEMPRE =====
if (session_status() === PHP_SESSION_NONE) {
    // Configurar para desarrollo local (Docker)
    ini_set('session.cookie_lifetime', 86400);
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_httponly', 0);      // 0 en desarrollo Docker
    ini_set('session.use_only_cookies', 0);     // 0 en desarrollo
    ini_set('session.cookie_samesite', 'Lax');
    
    // Nombre específico para evitar conflictos
    session_name('music_app_sid');
    
    // Iniciar sesión
    session_start();
}

// ===== TU CÓDIGO ACTUAL (NO LO CAMBIES) =====
// Configuración de la base de datos para Docker
define('DB_HOST', getenv('DB_HOST') ?: 'db-1');
define('DB_NAME', getenv('DB_NAME') ?: 'blog_db');
define('DB_USER', getenv('DB_USER') ?: 'blog_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'blog_pass');

// URL base
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8081') . '/');

// NO añadas PDO aquí, ya lo tienes en Database.php
?>