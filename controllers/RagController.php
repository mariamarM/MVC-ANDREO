<?php
// RagController.php
require_once __DIR__ . '/../models/Cancion.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../n8n/utils/LLM.php';  // Añade esto

class RagController
{
    private $cancionModel;
    private $reviewModel;
    private $usuarioModel;
    private $llm;

    public function __construct()
    {
        $this->cancionModel = new Cancion();
        $this->reviewModel = new Review();
        $this->usuarioModel = new User();
        $this->llm = new LLM();  // Inicializar el LLM
    }

    // ... (tus otros métodos ask() y answer() permanecen igual) ...

    public function ask()
    {
        // Verificar sesión
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $title = 'Asistente Musical RAG';
        $username = $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario';

        // Mostrar formulario
        require_once __DIR__ . '/../views/rag/ask.php';
    }
    public function answer()
{
    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Debes iniciar sesión para usar el asistente';
        header('Location: /login');
        exit();
    }

    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /rag/ask');
        exit();
    }

    $question = $_POST['question'] ?? '';

    // Validar pregunta
    if (empty($question) || strlen(trim($question)) < 3) {
        $_SESSION['error'] = 'Por favor, escribe una pregunta válida (mínimo 3 caracteres)';
        header('Location: /rag/ask');
        exit();
    }

    // Limitar longitud
    if (strlen($question) > 500) {
        $_SESSION['error'] = 'La pregunta es demasiado larga (máximo 500 caracteres)';
        header('Location: /rag/ask');
        exit();
    }

    try {
        // Procesar la pregunta
        $result = $this->processRagQuery($question);
        
        // Obtener estadísticas
        $stats = $this->getPlatformStats();
        
        // Preparar datos para la vista
        $title = 'Respuesta del Asistente Musical';
        $answer = $result['answer'] ?? 'Lo siento, no pude generar una respuesta.';
        $results = $result['results'] ?? [];
        
        // Calcular estadísticas para la vista
        $total_results = count($results);
        $has_canciones = false;
        $has_reviews = false;
        
        // Contar tipos de resultados
        foreach ($results as $item) {
            if (isset($item['tipo']) && $item['tipo'] === 'cancion') {
                $has_canciones = true;
            }
            if (isset($item['tipo']) && $item['tipo'] === 'review') {
                $has_reviews = true;
            }
        }
        
        // Mostrar la vista de respuesta
        require_once __DIR__ . '/../views/rag/answer.php';
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error en RagController::answer(): " . $e->getMessage());
        
        // Mostrar error amigable
        $_SESSION['error'] = 'Ocurrió un error procesando tu pregunta. Por favor, intenta de nuevo.';
        header('Location: /rag/ask');
        exit();
    }
}
    private function processRagQuery($question)
    {
        // 1. Buscar documentos relevantes
        $relevantDocs = $this->retrieveRelevantDocuments($question);
        
        // 2. Obtener estadísticas
        $stats = $this->getPlatformStats();
        
        // 3. Usar el LLM para generar respuesta
        $answer = $this->llm->generate($question, $relevantDocs, $stats);

        return [
            'answer' => $answer,
            'results' => $relevantDocs
        ];
    }

    public function answerApi()
    {
        // Asegurar que tenemos sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'error' => 'Debes iniciar sesión'
            ]);
            exit();
        }
        
        // Leer la pregunta
        $question = $_POST['question'] ?? '';
        
        if (empty($question) || strlen(trim($question)) < 3) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'error' => 'Pregunta muy corta'
            ]);
            exit();
        }
        
        // Procesar pregunta
        $result = $this->processRagQuery($question);
        
        // Formatear respuesta
        $answer = $result['answer'] ?? 'Lo siento, no pude procesar tu pregunta.';
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'answer' => $answer,
            'results_count' => count($result['results'] ?? [])
        ]);
        exit();
    }


    private function searchSongs($query)
    {
        // Búsqueda simple en la base de datos
        $query = strtolower($query);
        
        // Si tu modelo Cancion tiene un método de búsqueda, úsalo:
        if (method_exists($this->cancionModel, 'search')) {
            return $this->cancionModel->search($query);
        }
        
        // Si no, busca manualmente
        $allSongs = $this->cancionModel->getAll();
        $filtered = [];

        foreach ($allSongs as $song) {
            $text = strtolower(($song['titulo'] ?? '') . ' ' . 
                              ($song['artista'] ?? '') . ' ' . 
                              ($song['genero'] ?? '') . ' ' . 
                              ($song['album'] ?? ''));
            
            if (strpos($text, $query) !== false || $this->checkKeywords($query, $text)) {
                $filtered[] = $song;
            }
        }

        return $filtered;
    }

    private function searchReviews($query)
    {
        $query = strtolower($query);
        
        // Si tu modelo Review tiene un método de búsqueda, úsalo:
        if (method_exists($this->reviewModel, 'search')) {
            return $this->reviewModel->search($query);
        }
        
        // Si no, busca manualmente
        $allReviews = $this->reviewModel->getAll();
        $filtered = [];

        foreach ($allReviews as $review) {
            $text = strtolower($review['comentario'] ?? '');
            if (strpos($text, $query) !== false || $this->checkKeywords($query, $text)) {
                $filtered[] = $review;
            }
        }

        return $filtered;
    }

    private function checkKeywords($query, $text)
    {
        $keywords = ['mejor', 'recomend', 'top', 'rating', 'estrella', 'opin', 'qué', 'cómo', 'cuál', 'dónde', 'cuándo'];
        $queryWords = explode(' ', $query);

        foreach ($queryWords as $word) {
            if (strlen($word) > 2) {
                foreach ($keywords as $keyword) {
                    if (strpos($word, $keyword) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function calculateRelevance($query, $text)
    {
        if (empty($text)) return 0;
        
        // Algoritmo simple de relevancia
        $query = strtolower(trim($query));
        $text = strtolower(trim($text));

        $score = 0;
        $queryWords = explode(' ', $query);

        foreach ($queryWords as $word) {
            if (strlen($word) > 2) {
                // Coincidencias parciales
                if (strpos($text, $word) !== false) {
                    $score += 0.5;
                }
                
                // Bonus por coincidencia exacta (palabra completa)
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
                    $score += 1.0;
                }
            }
        }

        return min($score, 10.0);
    }

    private function generateAnswer($question, $documents)
    {
        // Lógica simple para generar respuesta
        $question = strtolower(trim($question));

        // Contar canciones y reviews encontradas
        $canciones = array_filter($documents, fn($d) => ($d['tipo'] ?? '') === 'cancion');
        $reviews = array_filter($documents, fn($d) => ($d['tipo'] ?? '') === 'review');

        $respuesta = "Basado en tu pregunta, he encontrado información relevante:\n\n";

        if (!empty($canciones)) {
            $respuesta .= "🎵 Canciones relacionadas (" . count($canciones) . "):\n";
            foreach (array_slice($canciones, 0, 3) as $cancion) {
                $respuesta .= "• " . ($cancion['titulo'] ?? 'Sin título') . " - " . ($cancion['artista'] ?? 'Artista desconocido');
                if (!empty($cancion['genero']))
                    $respuesta .= " (" . $cancion['genero'] . ")";
                $respuesta .= "\n";
            }
            if (count($canciones) > 3) {
                $respuesta .= "... y " . (count($canciones) - 3) . " más.\n";
            }
            $respuesta .= "\n";
        }

        if (!empty($reviews)) {
            $respuesta .= "⭐ Opiniones de usuarios (" . count($reviews) . "):\n";
            $topReviews = array_filter($reviews, fn($r) => ($r['puntuacion'] ?? 0) >= 4);

            if (!empty($topReviews)) {
                $respuesta .= "Las mejores valoraciones:\n";
                foreach (array_slice($topReviews, 0, 2) as $review) {
                    $comentario = $review['comentario'] ?? '';
                    $respuesta .= "• \"" . substr($comentario, 0, 100) . (strlen($comentario) > 100 ? "..." : "") . "\"\n";
                    $puntuacion = $review['puntuacion'] ?? 0;
                    $respuesta .= "  ⭐ " . str_repeat('★', $puntuacion) . str_repeat('☆', 5 - $puntuacion) . 
                                 " (" . ($review['usuario'] ?? 'Anónimo') . ")\n";
                }
            } else if (!empty($reviews)) {
                $respuesta .= "Opiniones encontradas:\n";
                foreach (array_slice($reviews, 0, 2) as $review) {
                    $comentario = $review['comentario'] ?? '';
                    $respuesta .= "• \"" . substr($comentario, 0, 80) . (strlen($comentario) > 80 ? "..." : "") . "\"\n";
                }
            }
            $respuesta .= "\n";
        }

        // Añadir recomendaciones basadas en el tipo de pregunta
        if (strpos($question, 'recomend') !== false) {
            $respuesta .= "💡 Recomendación personal: ";
            if (!empty($canciones)) {
                $topSong = reset($canciones);
                $respuesta .= "Te sugiero escuchar \"" . ($topSong['titulo'] ?? '') . "\" de " . ($topSong['artista'] ?? '');
                if (($topSong['score'] ?? 0) > 5) {
                    $respuesta .= " ya que tiene alta relevancia con tu búsqueda.";
                } else {
                    $respuesta .= ".";
                }
            } else {
                $respuesta .= "Prueba a buscar por género o artista específico para mejores recomendaciones.";
            }
            $respuesta .= "\n\n";
        }

        // Estadísticas
        if (strpos($question, 'estadística') !== false || strpos($question, 'cuántos') !== false || 
            strpos($question, 'cantidad') !== false) {
            $stats = $this->getPlatformStats();
            $respuesta .= "📊 Estadísticas de la plataforma:\n";
            $respuesta .= "• Canciones totales: " . $stats['total_canciones'] . "\n";
            $respuesta .= "• Reviews totales: " . $stats['total_reviews'] . "\n";
            $respuesta .= "• Rating promedio: " . $stats['rating_promedio'] . "/5\n";
        }

        if (empty($canciones) && empty($reviews)) {
            $respuesta = "No encontré información específica relacionada con tu pregunta.\n\n";
            $respuesta .= "Sugerencias:\n";
            $respuesta .= "1. Intenta con términos más generales\n";
            $respuesta .= "2. Busca por género o artista específico\n";
            $respuesta .= "3. Prueba con preguntas como:\n";
            $respuesta .= "   - \"¿Qué canciones de pop tienen buenas reviews?\"\n";
            $respuesta .= "   - \"¿Recomiendas música para estudiar?\"\n";
            $respuesta .= "   - \"¿Cuáles son las canciones mejor valoradas?\"\n";
        }

        return $respuesta;
    }

    private function getPlatformStats()
    {
        $total_canciones = 0;
        $total_reviews = 0;
        $total_usuarios = 0;
        $rating_promedio = 'N/A';

        try {
            $total_canciones = is_array($this->cancionModel->getAll()) ? count($this->cancionModel->getAll()) : 0;
            $total_reviews = is_array($this->reviewModel->getAll()) ? count($this->reviewModel->getAll()) : 0;
            $total_usuarios = is_array($this->usuarioModel->getAll()) ? count($this->usuarioModel->getAll()) : 0;

            // Calcular rating promedio
            $reviews = $this->reviewModel->getAll();
            $rating_total = 0;
            $rating_count = 0;

            foreach ($reviews as $review) {
                if (isset($review['puntuacion'])) {
                    $rating_total += $review['puntuacion'];
                    $rating_count++;
                }
            }

            $rating_promedio = $rating_count > 0 ? number_format($rating_total / $rating_count, 2) : 'N/A';
        } catch (Exception $e) {
            // Si hay error, usar valores por defecto
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
        }

        return [
            'total_canciones' => $total_canciones,
            'total_reviews' => $total_reviews,
            'total_usuarios' => $total_usuarios,
            'rating_promedio' => $rating_promedio
        ];
    }
}