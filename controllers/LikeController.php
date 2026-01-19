<?php
// controllers/LikeController.php
require_once __DIR__ . '/../models/Like.php';

class LikeController {
    private $likeModel;
    
    public function __construct($pdo) {
        $this->likeModel = new Like($pdo);
    }
    
    /**
     * API para manejar likes (toggle)
     */
    public function toggleLike() {
        session_start();
        header('Content-Type: application/json');
        
        // Verificar sesión
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        // Validar datos
        $required = ['content_type', 'content_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Campo $field requerido"]);
                exit;
            }
        }
        
        $user_id = $_SESSION['user_id'];
        $content_type = $_POST['content_type']; // 'review' o 'song'
        $content_id = intval($_POST['content_id']);
        
        // Validar content_type
        if (!in_array($content_type, ['review', 'song'])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de contenido no válido']);
            exit;
        }
        
        // Verificar si el contenido existe
        if (!$this->contentExists($content_type, $content_id)) {
            echo json_encode(['success' => false, 'message' => 'Contenido no encontrado']);
            exit;
        }
        
        // Verificar si ya tiene like
        $hasLiked = $this->likeModel->hasLiked($user_id, $content_type, $content_id);
        
        if ($hasLiked) {
            // Quitar like
            $result = $this->likeModel->removeLike($user_id, $content_type, $content_id);
            $action = 'removed';
        } else {
            // Dar like
            $result = $this->likeModel->addLike($user_id, $content_type, $content_id);
            $action = 'added';
        }
        
        // Obtener nuevo conteo
        $likeCount = $this->likeModel->countLikes($content_type, $content_id);
        
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'],
            'action' => $action,
            'like_count' => $likeCount,
            'has_liked' => !$hasLiked // Estado después del toggle
        ]);
    }
    
    /**
     * Verificar si el contenido existe
     */
    private function contentExists($content_type, $content_id) {
        global $pdo;
        
        try {
            switch ($content_type) {
                case 'review':
                    $table = 'reviews';
                    break;
                case 'song':
                    $table = 'canciones';
                    break;
                default:
                    return false;
            }
            
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE id = ?");
            $stmt->execute([$content_id]);
            return (bool)$stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al verificar contenido: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener conteo de likes
     */
    public function getLikeCount() {
        header('Content-Type: application/json');
        
        if (empty($_GET['content_type']) || empty($_GET['content_id'])) {
            echo json_encode(['success' => false, 'message' => 'Parámetros requeridos']);
            exit;
        }
        
        $content_type = $_GET['content_type'];
        $content_id = intval($_GET['content_id']);
        
        $likeCount = $this->likeModel->countLikes($content_type, $content_id);
        $userHasLiked = false;
        
        // Verificar si hay sesión para saber si el usuario dio like
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            $userHasLiked = $this->likeModel->hasLiked(
                $_SESSION['user_id'], 
                $content_type, 
                $content_id
            );
        }
        
        echo json_encode([
            'success' => true,
            'like_count' => $likeCount,
            'user_has_liked' => $userHasLiked
        ]);
    }
    
    /**
     * Obtener lista de usuarios que dieron like
     */
    public function getLikers() {
        header('Content-Type: application/json');
        
        if (empty($_GET['content_type']) || empty($_GET['content_id'])) {
            echo json_encode(['success' => false, 'message' => 'Parámetros requeridos']);
            exit;
        }
        
        $content_type = $_GET['content_type'];
        $content_id = intval($_GET['content_id']);
        $limit = $_GET['limit'] ?? 10;
        
        $likers = $this->likeModel->getLikers($content_type, $content_id, $limit);
        
        echo json_encode([
            'success' => true,
            'likers' => $likers,
            'total' => count($likers)
        ]);
    }
}