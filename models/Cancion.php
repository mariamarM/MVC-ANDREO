<?php
class Cancion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM canciones ORDER BY release_year DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRandomSongs($limit = 3) {
        $stmt = $this->db->query("SELECT * FROM canciones ORDER BY RAND() LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM canciones WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>