<?php
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $host = getenv('DB_HOST') ?: 'db-1';
        $dbname = getenv('DB_NAME') ?: 'blog_db';
        $user = getenv('DB_USER') ?: 'blog_user';
        $pass = getenv('DB_PASSWORD') ?: 'blog_pass';

        $this->pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}