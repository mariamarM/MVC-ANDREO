<?php
abstract class Controller {
    
    protected function render($view, $data = []) {
        // Extraer variables para la vista
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view;
        
       
       
    }
    
        protected function renderPublic($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../public/' . $view;
        
        // Cargar config si no está cargada
        if (!defined('BASE_URL')) {
            require_once __DIR__ . '/../config/config.php';
        }
        
        require $viewPath;
    }
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    // esto solo te va aceptar si es post en plan verificar si lo es y ya no lo guarda en ningun sitio
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function getPost($key, $default = null) {
        if (isset($_POST[$key])) {
            return htmlspecialchars(trim($_POST[$key]));
        }
        return $default;
    }
}
?>