<?php
// controllers/RagController.php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Retriever.php';
require_once __DIR__ . '/../n8n/utils/LLM.php';

class RagController extends Controller {
    private $retriever;
    private $llm;
    
    public function __construct() {
        parent::__construct();
        $this->retriever = new Retriever();
        $this->llm = new LLM();
    }
    
    /**
     * Muestra la página para hacer preguntas
     */
    public function ask() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para usar el asistente musical.";
            header('Location: /login');
            exit;
        }
        
        $data = [
            'title' => 'Asistente Musical RAG',
            'username' => $_SESSION['username'] ?? 'Usuario'
        ];
        
        $this->render('rag/ask.php', $data);
    }
    
    /**
     * Procesa la pregunta y muestra la respuesta
     */
    public function answer() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para usar el asistente musical.";
            header('Location: /login');
            exit;
        }
        
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /rag/ask');
            exit;
        }
        
        // Obtener y validar la pregunta
        $question = trim($_POST['question'] ?? '');
        
        if (empty($question)) {
            $_SESSION['error'] = "Por favor, escribe una pregunta.";
            header('Location: /rag/ask');
            exit;
        }
        
        if (strlen($question) < 3) {
            $_SESSION['error'] = "La pregunta debe tener al menos 3 caracteres.";
            header('Location: /rag/ask');
            exit;
        }
        
        if (strlen($question) > 500) {
            $_SESSION['error'] = "La pregunta es demasiado larga (máximo 500 caracteres).";
            header('Location: /rag/ask');
            exit;
        }
        
        // Procesar la pregunta
        try {
            // 1. Buscar información relevante
            $results = $this->retriever->search($question);
            
            // 2. Obtener estadísticas para contexto
            $stats = $this->retriever->getStats();
            
            // 3. Generar respuesta
            $answer = $this->llm->generate($question, $results, $stats);
            
            // 4. Preparar datos para la vista
            $data = [
                'title' => 'Respuesta del Asistente',
                'question' => htmlspecialchars($question),
                'answer' => $answer,
                'results' => $results,
                'stats' => $stats,
                'total_results' => count($results),
                'has_canciones' => !empty(array_filter($results, function($item) {
                    return $item['tipo'] === 'cancion';
                })),
                'has_reviews' => !empty(array_filter($results, function($item) {
                    return $item['tipo'] === 'review';
                }))
            ];
            
            // Guardar en historial (opcional)
            $this->saveToHistory($_SESSION['user_id'], $question, $answer);
            
            $this->render('rag/answer.php', $data);
            
        } catch (Exception $e) {
            // Manejar errores
            $_SESSION['error'] = "Error al procesar tu pregunta: " . $e->getMessage();
            header('Location: /rag/ask');
            exit;
        }
    }
    protected function render($view, $data = []) {
        // Asegurar que BASE_URL está definida
        if (!defined('BASE_URL')) {
            define('BASE_URL', '/');
        }
        
        // Extraer variables
        extract($data);
        
        // Ruta absoluta a la vista
        $viewPath = __DIR__ . '/../views/' . $view;
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            // Si no existe, mostrar error
            die("Vista no encontrada: " . $viewPath);
        }
    }
    
   
    /**
     * Guarda la consulta en el historial (opcional)
     */
    private function saveToHistory($userId, $question, $answer) {
        // Podrías crear una tabla 'rag_history' si quieres guardar historial
        // Por ahora solo lo dejamos como opcional
        return true;
    }
    
    /**
     * Muestra el historial de consultas (opcional)
     */
    public function history() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Aquí podrías implementar la vista de historial si creas la tabla
        $data = ['title' => 'Historial de Consultas'];
        $this->render('rag/history.php', $data);
    }
}