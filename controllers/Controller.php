<?php
abstract class Controller {
    
    protected function render($view, $data = []) {
        // Extraer variables para la vista
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view;
        
       
        require_once __DIR__ . '/../views/layout/header.php';
        require $viewPath;
        require_once __DIR__ . '/../views/layout/footer.php';
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