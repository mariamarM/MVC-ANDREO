<?php
namespace App\Controllers;

abstract class Controller {
    
    // Renderizar vista
    private function render($view, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . '/../views/' . $view;
        
      
        
        require_once __DIR__ . '/../views/layout/header.php';
        
        require $viewPath;
        
        require_once __DIR__ . '/../views/layout/footer.php';
    }
    //esto es lo que hace que lo reedirige en plan segun la variable del url donde este
    private function redirect($url) {
        header("Location: " . $url);
        exit;
    }
}