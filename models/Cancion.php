<?php
// models/Cancion.php - VERSIÓN CORREGIDA

require_once __DIR__ . '/Model.php';

class Cancion extends Model {
    
    public function getAll() {
        try {
            error_log("=== Cancion::getAll() INICIADO ===");
            
            // **CONSULTA CORREGIDA** - Si release_year puede ser NULL, usa COALESCE
            $sql = "SELECT * FROM canciones ORDER BY COALESCE(release_year, 0) DESC";
            error_log("Ejecutando consulta: " . $sql);
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("✅ Canciones encontradas: " . count($result));
            
            // DEBUG: Ver estructura de la primera canción
            if (!empty($result)) {
                error_log("Primera canción estructura:");
                foreach ($result[0] as $key => $value) {
                    error_log("  $key = " . $value . " (tipo: " . gettype($value) . ")");
                }
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("❌ ERROR en getAll(): " . $e->getMessage());
            // Ver error específico
            error_log("Código error: " . $e->getCode());
            return [];
        }
    }
    
    // Método ALTERNATIVO si hay problemas con ORDER BY
    public function getAllSimple() {
        try {
            error_log("Ejecutando consulta SIMPLE: SELECT * FROM canciones");
            
            $stmt = $this->db->query("SELECT * FROM canciones");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Canciones SIMPLE encontradas: " . count($result));
            return $result;
            
        } catch (PDOException $e) {
            error_log("ERROR SIMPLE: " . $e->getMessage());
            return [];
        }
    }
        public function getAlbumInfo($albumName) {
        $sql = "SELECT 
                    album,
                    artist,
                    COUNT(*) as total_songs
                FROM canciones 
                WHERE album = :album 
                GROUP BY album, artist
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':album', $albumName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
        public function getSongsByAlbum($albumName) {
        $sql = "SELECT * FROM canciones WHERE album = :album ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':album', $albumName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>