<?php
// controllers/MusicController.php - CON DEBUG

require_once __DIR__ . '/Controller.php';

class MusicController extends Controller {
    
    public function home() {
        error_log("=== MusicController::home() INICIADO ===");
        
        // Cargar configuración
        if (!defined('BASE_URL')) {
            require_once __DIR__ . '/../config/config.php';
        }
        
        // Cargar modelo
        require_once __DIR__ . '/../models/Cancion.php';
        $cancionModel = new Cancion();
        
        // PRIMERO: Probar consulta SIMPLE
        error_log("Probando consulta SIMPLE...");
        $songsSimple = $cancionModel->getAllSimple();
        
        // LUEGO: Probar consulta normal
        error_log("Probando consulta NORMAL...");
        $songs = $cancionModel->getAll();
        
        // Usar la que funcione
        $songsToUse = !empty($songs) ? $songs : $songsSimple;
        
        error_log("Canciones disponibles para usar: " . count($songsToUse));
        
        // Limitar a 4
        $featuredSongs = !empty($songsToUse) ? array_slice($songsToUse, 0, 4) : [];
        
        error_log("Canciones a mostrar: " . count($featuredSongs));
        
        // Pasar a vista
        $this->renderPublic('home.php', [
            'songs' => $featuredSongs
        ]);
        
        error_log("=== MusicController::home() FINALIZADO ===");
    }
}
?>