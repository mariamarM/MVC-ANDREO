<?php
// /var/www/html/models/Database.php o donde lo tengas

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // Usar variables de entorno de Docker con valores por defecto
        $host = getenv('DB_HOST') ?: 'db-1';
        $dbname = getenv('DB_NAME') ?: 'blog_db';
        $user = getenv('DB_USER') ?: 'blog_user';
        $pass = getenv('DB_PASSWORD') ?: 'blog_pass';

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Lanzar excepción para que sea manejada por el login
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (Exception $e) {
                // Si falla, retornar null
                error_log($e->getMessage());
                return null;
            }
        }
        return self::$instance ? self::$instance->pdo : null;
    }
}
?>