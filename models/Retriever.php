<?php
// models/Retriever.php

require_once __DIR__ . '/Model.php';

class Retriever extends Model {
    
    /**
     * Busca contenido relevante en canciones y reviews
     */
    public function search($query) {
        $results = [];
        
        // 1. Buscar en canciones
        $canciones = $this->searchCanciones($query);
        
        // 2. Buscar en reviews
        $reviews = $this->searchReviews($query);
        
        // 3. Combinar resultados
        $allResults = array_merge($canciones, $reviews);
        
        // 4. Ordenar por score (relevancia)
        usort($allResults, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($allResults, 0, 8); // Top 8 resultados
    }
    
    /**
     * Busca en la tabla canciones
     */
    private function searchCanciones($query) {
        $sql = "
            SELECT 
                'cancion' as tipo,
                id,
                title as titulo,
                artist as artista,
                album,
                release_year as ano,
                genre as genero,
                duration as duracion,
                CONCAT(
                    'Canción: ', title, 
                    ' - Artista: ', artist,
                    IF(album IS NOT NULL, CONCAT(' (Álbum: ', album, ')'), ''),
                    '. Género: ', genre,
                    '. Año: ', release_year
                ) as contenido,
                MATCH(title, artist, album, genre) AGAINST(? IN NATURAL LANGUAGE MODE) as score
            FROM canciones
            WHERE MATCH(title, artist, album, genre) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY score DESC
            LIMIT 5
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca en la tabla reviews con información de usuario y canción
     */
    private function searchReviews($query) {
        $sql = "
            SELECT 
                'review' as tipo,
                r.id,
                r.comment as comentario,
                r.rating as puntuacion,
                c.title as cancion_titulo,
                c.artist as cancion_artista,
                u.username as usuario,
                r.created_at as fecha,
                CONCAT(
                    'Review de ', u.username, 
                    ' sobre la canción \"', c.title, '\" de ', c.artist,
                    ': ', LEFT(r.comment, 200),
                    ' (Puntuación: ', r.rating, '/5)'
                ) as contenido,
                MATCH(r.comment) AGAINST(? IN NATURAL LANGUAGE MODE) as score
            FROM reviews r
            JOIN canciones c ON r.song_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE MATCH(r.comment) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY score DESC
            LIMIT 5
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene estadísticas generales para el contexto
     */
    public function getStats() {
        $stats = [];
        
        // Total canciones
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM canciones");
        $stats['total_canciones'] = $stmt->fetch()['total'];
        
        // Total reviews
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM reviews");
        $stats['total_reviews'] = $stmt->fetch()['total'];
        
        // Total usuarios
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        $stats['total_usuarios'] = $stmt->fetch()['total'];
        
        // Rating promedio
        $stmt = $this->db->query("SELECT AVG(rating) as promedio FROM reviews WHERE rating IS NOT NULL");
        $stats['rating_promedio'] = round($stmt->fetch()['promedio'], 2);
        
        return $stats;
    }
    
    /**
     * Obtiene las canciones mejor valoradas
     */
    public function getTopCanciones($limit = 5) {
        $sql = "
            SELECT 
                c.id,
                c.title,
                c.artist,
                c.genre,
                ROUND(AVG(r.rating), 2) as promedio_rating,
                COUNT(r.id) as total_reviews
            FROM canciones c
            LEFT JOIN reviews r ON c.id = r.song_id
            GROUP BY c.id
            HAVING promedio_rating IS NOT NULL
            ORDER BY promedio_rating DESC, total_reviews DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca canciones por género
     */
    public function getCancionesPorGenero($genero) {
        $sql = "
            SELECT 
                c.id,
                c.title,
                c.artist,
                c.release_year,
                ROUND(AVG(r.rating), 2) as promedio_rating
            FROM canciones c
            LEFT JOIN reviews r ON c.id = r.song_id
            WHERE LOWER(c.genre) LIKE LOWER(?)
            GROUP BY c.id
            ORDER BY promedio_rating DESC
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$genero%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}