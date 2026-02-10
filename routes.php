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
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/models/Cancion.php';

        $cancionModel = new Cancion();
        $allSongs = $cancionModel->getAll();  // Cambia el nombre
        $featuredSongs = !empty($allSongs) ? array_slice($allSongs, 0, 4) : [];



        // Pasar a home.php usando extract o variable global
        $songs = $featuredSongs;  // Esto no funciona bien

        // En su lugar, usa EXTRACT o GLOBALS
        $data = ['songs' => $featuredSongs];
        extract($data);  // Esto crea $songs en el scope actual

        // O también
        $GLOBALS['songs'] = $featuredSongs;

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

    case 'rag/ask':
        require_once __DIR__ . '/controllers/RagController.php';
        $controller = new RagController();
        $controller->ask();
        break;

    case 'rag/answer-api':
    require_once __DIR__ . '/controllers/RagController.php';
    $controller = new RagController();
    $controller->answerApi();
    break;
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