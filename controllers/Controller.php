<?php
abstract class Controller {
    
    protected function render($view, $data = []) {
        // Extraer variables para la vista
        $data = is_array($data) ? $data : [];
        
        // Extraer variables para la vista
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view;
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Vista no encontrada: " . $viewPath);
        }
    }
       
       
    
    
        protected function renderPublic($view, $data = []) {
       if (!is_array($data)) {
            error_log("⚠️ ADVERTENCIA: renderPublic recibió datos no array. Convirtiendo a array vacío.");
            $data = [];
        }
        
        // Extraer variables
        extract($data);
        
        // Cargar config si no está cargada
        if (!defined('BASE_URL')) {
            require_once __DIR__ . '/../config/config.php';
        }
        
        $viewPath = __DIR__ . '/../public/' . $view;
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Buscar en views como alternativa
            $altPath = __DIR__ . '/../views/' . $view;
            if (file_exists($altPath)) {
                require $altPath;
            } else {
                die("Vista no encontrada: " . $view);
            }
        }
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