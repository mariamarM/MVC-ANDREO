<?php
define('DB_HOST', 'db-1');
define('DB_NAME', 'blog_db');
define('DB_USER', 'blog_user');
define('DB_PASS', 'blog_pass');

define('BASE_URL', 'http://localhost:8081/public/');
define('SITE_NAME', 'MVC-ANDREO');

session_start();

$pdo = null;

try {
    // Crear la conexión PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Opcional: Verificar conexión
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // En desarrollo, mostrar el error
    if (isset($_GET['debug']) || true) {  // <-- temporalmente true para ver errores
        die("<h1>Error de Conexión a Base de Datos</h1>
             <p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
             <p><strong>Host:</strong> " . DB_HOST . "</p>
             <p><strong>Base de datos:</strong> " . DB_NAME . "</p>
             <p><strong>Usuario:</strong> " . DB_USER . "</p>
             <p><a href='http://localhost:8083' target='_blank'>Abrir phpMyAdmin</a></p>");
    }
    
    // En producción
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Error del sistema. Por favor, intente más tarde.");
}

// Configuración para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que la conexión se creó
if (!$pdo) {
    die("Error: No se pudo crear la conexión a la base de datos.");
}
?>