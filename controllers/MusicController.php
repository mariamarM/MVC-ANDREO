<?php
// controllers/MusicController.php - CON DEBUG

require_once __DIR__ . '/Controller.php';

class MusicController extends Controller {
    
   public function home() {
        error_log("=== MusicController::home() INICIADO ===");
        
        // 1. Cargar configuración
        if (!defined('BASE_URL')) {
            require_once __DIR__ . '/../config/config.php';
        }
        
        // 2. Cargar modelo
        require_once __DIR__ . '/../models/Cancion.php';
        $cancionModel = new Cancion();
        
        // 3. Obtener canciones
        $songs = $cancionModel->getAll();
        error_log("Canciones obtenidas del modelo: " . count($songs));
        
        // 4. Limitar a 4
        $featuredSongs = !empty($songs) ? array_slice($songs, 0, 4) : [];
        error_log("Canciones limitadas a mostrar: " . count($featuredSongs));
        
        // 5. Crear el array de datos CORRECTAMENTE
        $data = [
            'songs' => $featuredSongs
        ];
        
        // DEBUG: Verificar array
        error_log("Datos a pasar a vista: " . print_r($data, true));
        
        // 6. Renderizar vista con los datos
        $this->renderPublic('home.php', $data);
        
        error_log("=== MusicController::home() FINALIZADO ===");
    }
}
?>