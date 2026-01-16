<?php
class Playlist extends Model {
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
}
?>