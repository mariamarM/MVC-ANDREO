<?php
require_once '../views/layout/nav.php';
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

$path = str_replace(dirname($script_name), '', $request_uri);
$path = trim($path, '/');
switch ($path) {
   case 'user':
        $controller = new UserController();
        $controller->profile();
        break;
    case 'home':
        $controller = new HomeController();
        $controller->index();
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
        $controller = new App\Controllers\UserController();
        $controller->logout();
        break;
    case 'lastweek':
   
        break;
    case 'aboutus':
      
        break;
         
    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}
?>