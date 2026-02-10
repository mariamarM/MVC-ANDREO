<?php
// RagController.php - VERSIÓN SIN LOGIN REQUERIDO

class RagController
{
    private $cancionModel;
    private $reviewModel;
    private $llm;

    public function __construct()
    {
        // Cargar modelos (sin UserModel ya que no necesitamos usuario)
        $this->loadModels();
    }
    
    private function loadModels()
    {
        // Cargar Cancion
        if (file_exists(__DIR__ . '/../models/Cancion.php')) {
            require_once __DIR__ . '/../models/Cancion.php';
            $this->cancionModel = new Cancion();
        } else {
            // Modelo dummy para Cancion
            $this->cancionModel = new class {
                public function getAll() { 
                    // Intentar conectar a la base de datos directamente
                    try {
                        require_once __DIR__ . '/../config/Database.php';
                        $db = Database::connect();
                        $stmt = $db->query("SELECT * FROM canciones LIMIT 50");
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        return [];
                    }
                }
                public function getById($id) { return null; }
                public function search($query) { return []; }
            };
        }
        
        // Cargar Review
        if (file_exists(__DIR__ . '/../models/Review.php')) {
            require_once __DIR__ . '/../models/Review.php';
            $this->reviewModel = new Review();
        } else {
            // Modelo dummy para Review
            $this->reviewModel = new class {
                public function getAll() { 
                    try {
                        require_once __DIR__ . '/../config/Database.php';
                        $db = Database::connect();
                        $stmt = $db->query("SELECT * FROM reviews LIMIT 50");
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        return [];
                    }
                }
            };
        }
        
        // Cargar LLM si existe
        if (file_exists(__DIR__ . '/../models/LLM.php')) {
            require_once __DIR__ . '/../models/LLM.php';
            $this->llm = new LLM();
        }
    }

    public function ask()
    {
        // SIN VERIFICACIÓN DE SESIÓN
        $title = 'Asistente Musical RAG';
        
        // Mostrar formulario
        require_once __DIR__ . '/../rag/ask.php';
    }
    
    public function answer()
    {
        // SIN VERIFICACIÓN DE SESIÓN
        
        // Solo aceptar POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /rag/ask');
            exit();
        }

        $question = $_POST['question'] ?? '';

        // Validar pregunta
        if (empty($question) || strlen(trim($question)) < 3) {
            // Si es API request, devolver JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Pregunta muy corta']);
                exit();
            }
            
            // Si es web normal, redirigir
            $_SESSION['error'] = 'Por favor, escribe una pregunta válida (mínimo 3 caracteres)';
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
    
    public function answerApi()
    {
        // SIN VERIFICACIÓN DE SESIÓN - acceso libre
        header('Content-Type: application/json');
        
        // Leer la pregunta
        $question = $_POST['question'] ?? '';
        
        if (empty($question) || strlen(trim($question)) < 3) {
            echo json_encode(['success' => false, 'error' => 'Escribe una pregunta (mínimo 3 caracteres)']);
            exit();
        }
        
        try {
            // Procesar la pregunta
            $result = $this->processRagQuery($question);
            
            echo json_encode([
                'success' => true,
                'answer' => $result['answer'] ?? 'No pude generar una respuesta.',
                'results_count' => count($result['results'] ?? [])
            ]);
            exit();
            
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
            exit();
        }
    }
    
    private function processRagQuery($question)
    {
        // 1. Buscar documentos relevantes
        $relevantDocs = $this->retrieveRelevantDocuments($question);
        
        // 2. Obtener estadísticas
        $stats = $this->getPlatformStats();
        
        // 3. Generar respuesta (con LLM si existe, sino con método simple)
        if (isset($this->llm) && $this->llm instanceof LLM) {
            $answer = $this->llm->generate($question, $relevantDocs, $stats);
        } else {
            $answer = $this->generateAnswer($question, $relevantDocs);
        }

        return [
            'answer' => $answer,
            'results' => $relevantDocs
        ];
    }
    
    private function retrieveRelevantDocuments($question)
    {
        $results = [];
        
        // Normalizar la pregunta para búsqueda
        $searchQuery = strtolower(trim($question));
        
        // 1. Buscar canciones
        $canciones = $this->searchSongs($searchQuery);
        foreach ($canciones as $cancion) {
            $results[] = [
                'tipo' => 'cancion',
                'titulo' => $cancion['title'] ?? $cancion['titulo'] ?? 'Sin título',
                'artista' => $cancion['artist'] ?? $cancion['artista'] ?? 'Artista desconocido',
                'genero' => $cancion['genre'] ?? $cancion['genero'] ?? 'Desconocido',
                'ano' => $cancion['year'] ?? $cancion['ano'] ?? '',
                'album' => $cancion['album'] ?? '',
                'score' => $this->calculateRelevance($searchQuery, 
                    ($cancion['title'] ?? $cancion['titulo'] ?? '') . ' ' . 
                    ($cancion['artist'] ?? $cancion['artista'] ?? '') . ' ' . 
                    ($cancion['genre'] ?? $cancion['genero'] ?? '')
                )
            ];
        }
        
        // 2. Buscar reviews
        $reviews = $this->searchReviews($searchQuery);
        foreach ($reviews as $review) {
            // Obtener información de la canción
            $cancionInfo = $this->getCancionInfo($review['cancion_id'] ?? 0);
            
            $results[] = [
                'tipo' => 'review',
                'cancion_titulo' => $cancionInfo['title'] ?? $cancionInfo['titulo'] ?? 'Canción desconocida',
                'usuario' => 'Usuario de la comunidad', // Sin login, nombre genérico
                'puntuacion' => $review['rating'] ?? $review['puntuacion'] ?? 0,
                'comentario' => $review['comment'] ?? $review['comentario'] ?? '',
                'fecha' => $review['date'] ?? $review['fecha'] ?? date('Y-m-d'),
                'score' => $this->calculateRelevance($searchQuery, $review['comment'] ?? $review['comentario'] ?? '')
            ];
        }
        
        // Ordenar por relevancia (más alto primero)
        usort($results, function($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });
        
        // Limitar resultados
        return array_slice($results, 0, 15);
    }
    
    private function searchSongs($query)
    {
        $allSongs = $this->cancionModel->getAll();
        
        if (empty($allSongs) || !is_array($allSongs)) {
            return [];
        }
        
        $filtered = [];
        $queryWords = explode(' ', $query);
        
        foreach ($allSongs as $song) {
            $text = strtolower(
                ($song['title'] ?? $song['titulo'] ?? '') . ' ' .
                ($song['artist'] ?? $song['artista'] ?? '') . ' ' .
                ($song['genre'] ?? $song['genero'] ?? '') . ' ' .
                ($song['album'] ?? '')
            );
            
            $matches = 0;
            foreach ($queryWords as $word) {
                if (strlen($word) > 2 && strpos($text, $word) !== false) {
                    $matches++;
                }
            }
            
            if ($matches > 0) {
                $song['relevance'] = $matches;
                $filtered[] = $song;
            }
        }
        
        // Ordenar por relevancia
        usort($filtered, function($a, $b) {
            return ($b['relevance'] ?? 0) <=> ($a['relevance'] ?? 0);
        });
        
        return array_slice($filtered, 0, 10);
    }
    
    private function searchReviews($query)
    {
        $allReviews = $this->reviewModel->getAll();
        
        if (empty($allReviews) || !is_array($allReviews)) {
            return [];
        }
        
        $filtered = [];
        $queryWords = explode(' ', $query);
        
        foreach ($allReviews as $review) {
            $text = strtolower(
                ($review['comment'] ?? $review['comentario'] ?? '')
            );
            
            $matches = 0;
            foreach ($queryWords as $word) {
                if (strlen($word) > 2 && strpos($text, $word) !== false) {
                    $matches++;
                }
            }
            
            if ($matches > 0) {
                $review['relevance'] = $matches;
                $filtered[] = $review;
            }
        }
        
        // Ordenar por relevancia
        usort($filtered, function($a, $b) {
            return ($b['relevance'] ?? 0) <=> ($a['relevance'] ?? 0);
        });
        
        return array_slice($filtered, 0, 10);
    }
    
    private function calculateRelevance($query, $text)
    {
        if (empty($text)) {
            return 0;
        }
        
        $query = strtolower(trim($query));
        $text = strtolower(trim($text));
        
        $score = 0;
        $queryWords = explode(' ', $query);
        
        foreach ($queryWords as $word) {
            if (strlen($word) > 2) {
                // Coincidencia parcial
                if (strpos($text, $word) !== false) {
                    $score += 0.5;
                }
                
                // Coincidencia exacta (palabra completa)
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
                    $score += 1.0;
                }
            }
        }
        
        return min($score, 10.0);
    }
    
    private function getCancionInfo($cancionId)
    {
        if (!$cancionId || $cancionId == 0) {
            return [];
        }
        
        try {
            if (method_exists($this->cancionModel, 'getById')) {
                return $this->cancionModel->getById($cancionId) ?: [];
            }
        } catch (Exception $e) {
            error_log("Error obteniendo canción $cancionId: " . $e->getMessage());
        }
        
        return [];
    }
    
    private function getPlatformStats()
    {
        $stats = [
            'total_canciones' => 0,
            'total_reviews' => 0,
            'total_usuarios' => 0,
            'rating_promedio' => 'N/A'
        ];
        
        try {
            // Total canciones
            if (method_exists($this->cancionModel, 'getAll')) {
                $canciones = $this->cancionModel->getAll();
                $stats['total_canciones'] = is_array($canciones) ? count($canciones) : 0;
            }
            
            // Total reviews
            if (method_exists($this->reviewModel, 'getAll')) {
                $reviews = $this->reviewModel->getAll();
                $stats['total_reviews'] = is_array($reviews) ? count($reviews) : 0;
            }
            
            // Rating promedio
            if ($stats['total_reviews'] > 0 && method_exists($this->reviewModel, 'getAll')) {
                $reviews = $this->reviewModel->getAll();
                if (is_array($reviews)) {
                    $totalRating = 0;
                    $count = 0;
                    
                    foreach ($reviews as $review) {
                        $rating = $review['rating'] ?? $review['puntuacion'] ?? null;
                        if (is_numeric($rating)) {
                            $totalRating += $rating;
                            $count++;
                        }
                    }
                    
                    if ($count > 0) {
                        $stats['rating_promedio'] = number_format($totalRating / $count, 2);
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    private function generateAnswer($question, $results)
    {
        // Si tienes la clase LLM, úsala
        if (isset($this->llm) && $this->llm instanceof LLM) {
            $stats = $this->getPlatformStats();
            return $this->llm->generate($question, $results, $stats);
        }
        
        // Respuesta simple si no hay LLM
        $questionLower = strtolower($question);
        $response = "🎵 **Asistente Musical** 🎵\n\n";
        $response .= "Basado en tu pregunta \"" . htmlspecialchars($question) . "\", he encontrado:\n\n";
        
        $canciones = array_filter($results, function($item) {
            return ($item['tipo'] ?? '') === 'cancion';
        });
        
        $reviews = array_filter($results, function($item) {
            return ($item['tipo'] ?? '') === 'review';
        });
        
        if (!empty($canciones)) {
            $response .= "**🎶 Canciones relacionadas (" . count($canciones) . "):**\n";
            foreach (array_slice($canciones, 0, 3) as $cancion) {
                $response .= "• **" . ($cancion['titulo'] ?? 'Sin título') . "** - " . 
                            ($cancion['artista'] ?? 'Artista desconocido');
                if (!empty($cancion['genero'])) {
                    $response .= " (" . $cancion['genero'] . ")";
                }
                $response .= "\n";
            }
            if (count($canciones) > 3) {
                $response .= "• ... y " . (count($canciones) - 3) . " más\n";
            }
            $response .= "\n";
        }
        
        if (!empty($reviews)) {
            $response .= "**⭐ Opiniones de la comunidad (" . count($reviews) . "):**\n";
            foreach (array_slice($reviews, 0, 2) as $review) {
                $comentario = $review['comentario'] ?? '';
                $response .= "• \"" . substr($comentario, 0, 80) . 
                            (strlen($comentario) > 80 ? "..." : "") . "\"\n";
                $response .= "  (" . ($review['puntuacion'] ?? '0') . "/5 estrellas)\n";
            }
            $response .= "\n";
        }
        
        if (empty($canciones) && empty($reviews)) {
            $response = "🤔 **No encontré información específica sobre \"" . htmlspecialchars($question) . "\"**\n\n";
            $response .= "**💡 Sugerencias:**\n";
            $response .= "• Busca por nombre de canción o artista\n";
            $response .= "• Consulta por géneros musicales (rock, pop, reggaeton)\n";
            $response .= "• Pregunta sobre reviews o recomendaciones\n";
            $response .= "• Ejemplo: \"canciones de pop\" o \"mejores reviews\"\n";
        }
        
        $response .= "\n**🎧 ¡Sigue explorando música!**";
        
        return $response;
    }
}