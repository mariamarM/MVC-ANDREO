<?php

// Cargar controladores
require_once __DIR__ . '/controllers/MusicController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Obtener path limpio
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace('/public', '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// --- Rutas normales (usuario) ---
switch ($path) {
    case '':
    case 'home':
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/models/Cancion.php';

        $cancionModel = new Cancion();
        $allSongs = $cancionModel->getAll();
        $featuredSongs = !empty($allSongs) ? array_slice($allSongs, 0, 4) : [];

        $data = ['songs' => $featuredSongs];
        extract($data);

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

    case 'aboutus':
        echo "Página en construcción";
        break;

    default:
if (str_starts_with($path, 'admin/')) {
    $segments = explode('/', $path);
    $controller = new AdminController();

    // admin/users/delete/ID
    if (($segments[1] ?? '') === 'users' && ($segments[2] ?? '') === 'delete') {
        $id = $segments[3] ?? null;
        if ($id && is_numeric($id)) {
            $controller->deleteUser((int)$id);
            exit;
        } else {
            header("Location: /public/admin/users");
            exit;
        }
    }

    // admin/reviews/delete/ID
    if (($segments[1] ?? '') === 'reviews' && ($segments[2] ?? '') === 'delete') {
        $id = $segments[3] ?? null;
        if ($id && is_numeric($id)) {
            $controller->deleteReview((int)$id);
            exit;
        }
    }

    // admin/songs/delete/ID
    if (($segments[1] ?? '') === 'songs' && ($segments[2] ?? '') === 'delete') {
        $id = $segments[3] ?? null;
        if ($id && is_numeric($id)) {
            $controller->deleteSong((int)$id);
            exit;
        }
    }
}else{
        // --- Default 404 ---
        http_response_code(404);
        echo "Página no encontrada: " . htmlspecialchars($path);}
        break;
}
?>