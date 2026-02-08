<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Review.php';  // ¡IMPORTANTE! Agregar esta línea

class CommentController extends Controller {
    
    // Agregar una nueva review (usando el modelo actualizado)
    public function add() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        if (!$this->isPost()) {
            $this->redirect('/home');
            return;
        }
        
        $song_id = $this->getPost('song_id');
        $comment_text = $this->getPost('comment');
        $rating = $this->getPost('rating');
        $user_id = $_SESSION['user_id'];
        
        $errors = [];
        
        if (empty($song_id) || !is_numeric($song_id)) {
            $errors[] = "ID de canción inválido";
        }
        
        if (empty($comment_text) || strlen(trim($comment_text)) < 3) {
            $errors[] = "El comentario debe tener al menos 3 caracteres";
        }
        
        if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
            $errors[] = "La calificación debe ser entre 1 y 5 estrellas";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $this->redirect('/songs?id=' . $song_id);
            return;
        }
        
        try {
            $reviewModel = new Review();
            
            // Verificar si ya existe una review del usuario para esta canción
            $existingReview = $reviewModel->getUserReviewForSong($user_id, $song_id);
            
            if ($existingReview) {
                // Ya existe una review, actualizar
                $result = $reviewModel->update($existingReview['id'], $rating, $comment_text, $user_id);
                $action = "actualizada";
            } else {
                // No existe, insertar nueva
                $result = $reviewModel->create($user_id, $song_id, $rating, $comment_text);
                $action = "guardada";
            }
            
            if ($result) {
                // Actualizar el promedio de rating de la canción
                $this->updateSongRating($song_id);
                
                $_SESSION['success'] = "¡Tu reseña se ha $action correctamente!";
                
                // El webhook se envía automáticamente desde el modelo
            } else {
                $_SESSION['error'] = "Error al guardar la reseña";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al guardar la reseña: " . $e->getMessage();
        }
            
        $this->redirect('/songs?id=' . $song_id);
    }
    
    // Eliminar una review (usando el modelo actualizado) - ¡SOLO UN MÉTODO DELETE!
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        $review_id = $_GET['id'] ?? null;
        
        if (!$review_id || !is_numeric($review_id)) {
            $_SESSION['error'] = "ID de reseña inválido";
            $this->redirect('/home');
            return;
        }
        
        try {
            $reviewModel = new Review();
            
            // Obtener la review usando el método del modelo
            $review = $reviewModel->find($review_id); // Necesitas agregar este método al modelo
            
            // Si no existe find(), usa este enfoque alternativo:
            if (!method_exists($reviewModel, 'find')) {
                // Enfoque alternativo: buscar en las reviews de la canción
                $db = $this->getDatabase();
                $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
                $stmt->execute([$review_id]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$review) {
                $_SESSION['error'] = "Reseña no encontrada";
                $this->redirect('/home');
                return;
            }
            
            // Verificar permisos
            $user_id = $_SESSION['user_id'];
            $is_admin = $_SESSION['role'] ?? 'user';
            
            if ($review['user_id'] != $user_id && $is_admin != 'admin') {
                $_SESSION['error'] = "No tienes permiso para eliminar esta reseña";
                $this->redirect('/songs?id=' . $review['song_id']);
                return;
            }
            
            $song_id = $review['song_id'];
            
            // Eliminar usando el modelo (envía webhook automáticamente)
            if ($reviewModel->delete($review_id)) {
                // Actualizar el promedio de rating
                $this->updateSongRating($song_id);
                
                $_SESSION['success'] = "Reseña eliminada correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar la reseña";
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar la reseña: " . $e->getMessage();
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/home';
        $this->redirect($referer);
    }
    
    // Editar una review (formulario)
    public function edit() {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        // Obtener ID de la review
        $review_id = $_GET['id'] ?? null;
        
        if (!$review_id || !is_numeric($review_id)) {
            $_SESSION['error'] = "ID de reseña inválido";
            $this->redirect('/home');
            return;
        }
        
        try {
            $reviewModel = new Review();
            
            // Obtener la review usando el modelo
            $review = null;
            if (method_exists($reviewModel, 'find')) {
                $review = $reviewModel->find($review_id);
            } else {
                // Enfoque alternativo
                $db = $this->getDatabase();
                $stmt = $db->prepare("
                    SELECT r.*, u.username 
                    FROM reviews r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$review_id]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$review) {
                $_SESSION['error'] = "Reseña no encontrada";
                $this->redirect('/home');
                return;
            }
            
            // Verificar que el usuario sea el autor
            if ($review['user_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "Solo puedes editar tus propias reseñas";
                $this->redirect('/home');
                return;
            }
            
            $this->render('comments/edit.php', [
                'review' => $review
            ]);
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al cargar la reseña: " . $e->getMessage();
            $this->redirect('/home');
        }
    }
    
    // **ACTUALIZAR UNA REVIEW EN LA BASE DE DATOS** - Actualizado para usar el modelo
    public function update() {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        // Solo permitir método POST
        if (!$this->isPost()) {
            $this->redirect('/home');
            return;
        }
        
        // Obtener datos del formulario
        $review_id = $this->getPost('review_id');
        $song_id = $this->getPost('song_id');
        $comment_text = $this->getPost('comment');
        $rating = $this->getPost('rating');
        $user_id = $_SESSION['user_id'];
        
        // Validar datos
        $errors = [];
        
        if (empty($review_id) || !is_numeric($review_id)) {
            $errors[] = "ID de reseña inválido";
        }
        
        if (empty($song_id) || !is_numeric($song_id)) {
            $errors[] = "ID de canción inválido";
        }
        
        if (empty($comment_text) || strlen(trim($comment_text)) < 3) {
            $errors[] = "El comentario debe tener al menos 3 caracteres";
        }
        
        if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
            $errors[] = "La calificación debe ser entre 1 y 5 estrellas";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $this->redirect('/comment/edit?id=' . $review_id);
            return;
        }
        
        try {
            $reviewModel = new Review();
            
            // Verificar que la review existe y pertenece al usuario
            $review = null;
            if (method_exists($reviewModel, 'getUserReviewForSong')) {
                // Primero verificar si es la review del usuario
                $existingReview = $reviewModel->getUserReviewForSong($user_id, $song_id);
                if ($existingReview && $existingReview['id'] == $review_id) {
                    $review = $existingReview;
                }
            } else {
                // Enfoque alternativo
                $db = $this->getDatabase();
                $stmt = $db->prepare("SELECT user_id FROM reviews WHERE id = ?");
                $stmt->execute([$review_id]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$review) {
                $_SESSION['error'] = "Reseña no encontrada";
                $this->redirect('/home');
                return;
            }
            
            if ($review['user_id'] != $user_id) {
                $_SESSION['error'] = "No tienes permiso para editar esta reseña";
                $this->redirect('/songs?id=' . $song_id);
                return;
            }
            
            // Actualizar usando el modelo (envía webhook automáticamente)
            $result = $reviewModel->update($review_id, $rating, $comment_text, $user_id);
            
            if ($result) {
                // Actualizar el promedio de rating de la canción
                $this->updateSongRating($song_id);
                
                // Mensaje de éxito
                $_SESSION['success'] = "Reseña actualizada correctamente";
            } else {
                $_SESSION['error'] = "No se pudo actualizar la reseña";
            }
            
        } catch (PDOException $e) {
            // Error en la base de datos
            $_SESSION['error'] = "Error al actualizar la reseña: " . $e->getMessage();
        }
        
        $this->redirect('/songs?id=' . $song_id);
    }
    

    private function getDatabase() {
        static $db = null;
        
        if ($db === null) {
            try {
                $db = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS
                );
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        
        return $db;
    }
    
    // Método para actualizar el promedio de rating de una canción
    private function updateSongRating($song_id) {
        try {
            $db = $this->getDatabase();
            
            // Calcular promedio de ratings desde la tabla reviews
            $stmt = $db->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                FROM reviews 
                WHERE song_id = ?
            ");
            $stmt->execute([$song_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $avg_rating = $result['avg_rating'] ?? 0;
            $total_reviews = $result['total_reviews'] ?? 0;
            
            // Actualizar la tabla canciones (si tienes las columnas)
            try {
                $stmt = $db->prepare("
                    UPDATE canciones 
                    SET avg_rating = ?, total_reviews = ? 
                    WHERE id = ?
                ");
                $stmt->execute([round($avg_rating, 1), $total_reviews, $song_id]);
            } catch (PDOException $e) {
                // Las columnas no existen, puedes ignorar o crear las columnas:
                // ALTER TABLE canciones ADD avg_rating DECIMAL(3,1) DEFAULT 0;
                // ALTER TABLE canciones ADD total_reviews INT DEFAULT 0;
            }
            
        } catch (PDOException $e) {
            // Error silencioso
        }
    }
    
    // Método para obtener reviews de una canción
    public function getReviewsBySong($song_id) {
        try {
            $db = $this->getDatabase();
            
            $stmt = $db->prepare("
                SELECT r.*, u.username 
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.song_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$song_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUserReviewForSong($user_id, $song_id) {
        try {
            $db = $this->getDatabase();
            
            $stmt = $db->prepare("
                SELECT * FROM reviews 
                WHERE user_id = ? AND song_id = ?
                LIMIT 1
            ");
            $stmt->execute([$user_id, $song_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getReviewsByUser($user_id) {
        try {
            $db = $this->getDatabase();
            
            $stmt = $db->prepare("
                SELECT r.*, c.title as song_title, c.artist
                FROM reviews r
                JOIN canciones c ON r.song_id = c.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function buscarId($array, $id) {
        foreach ($array as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }
    
    private function loginRequired() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return false;
        }
        return true;
    }
}
?>