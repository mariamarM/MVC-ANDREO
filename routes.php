<?php
// routes.php en /var/www/html/

// Ajustar las rutas según tu estructura
require_once __DIR__ . '/controllers/MusicController.php';
require_once __DIR__ . '/controllers/UserController.php';

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Eliminar la parte del directorio del script si está en /public/
$path = str_replace('/public', '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

switch ($path) {
    case '':
    case 'home':
         // 1. Cargar configuración
        require_once __DIR__ . '/config/config.php';
        
        // 2. Cargar modelo y obtener canciones
        require_once __DIR__ . '/models/Cancion.php';
        $cancionModel = new Cancion();
        $songs = $cancionModel->getAll();
        $featuredSongs = !empty($songs) ? array_slice($songs, 0, 4) : [];
        
        // 3. Definir variables para la vista
        $songs = $featuredSongs;
        
        // 4. Incluir home.php directamente
        require_once __DIR__ . '/public/home.php';
        break;
        
    case 'music':
    case 'songs':
        $controller = new MusicController();
        $controller->index();
        break;
        
    case 'user':
        $controller = new UserController();
        $controller->profile();
        break;
        
    case 'login':
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processLogin();
        } else {
            $controller->login();
        }
        break;
        
    case 'register':
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processRegister();
        } else {
            $controller->register();
        }
        break;
        
    case 'logout':
        $controller = new UserController();
        $controller->logout();
        break;
        
    case 'playlists':
    case 'lastweek':
    case 'aboutus':
        // Páginas pendientes de implementar
        echo "Página en construcción";
        break;
         
    default:
        http_response_code(404);
        echo "Página no encontrada: " . htmlspecialchars($path);
        break;
}
?>