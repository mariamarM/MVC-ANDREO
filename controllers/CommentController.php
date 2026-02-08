<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../services/WebhookService.php';

class CommentController extends Controller
{

    // Agregar una nueva review
    public function add()
    {
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
                $review_id = $existingReview['id']; // ← ID de review existente
            } else {
                // No existe, insertar nueva
                $review_id = $reviewModel->create($user_id, $song_id, $rating, $comment_text);
                $result = ($review_id !== false);
                $action = "guardada";
            }

            if ($result) {
                // Actualizar el promedio de rating de la canción
                $this->updateSongRating($song_id);
error_log("=== WEBHOOK DEBUG INICIADO ===");
    error_log("Review ID: " . $review_id);
    error_log("Action: " . $action);
    error_log("User ID: " . $user_id);
    error_log("User Email: " . ($_SESSION['user_email'] ?? 'NO EMAIL'));
    
    // SOLO para reviews NUEVAS
    if ($action === "guardada") {
        error_log("🟢 Esta es una review NUEVA, debería enviar webhook");
        
        try {
            // 1. Cargar WebhookService
            error_log("Cargando WebhookService...");
            require_once __DIR__ . '/../services/WebhookService.php';
            $webhookService = new \App\Services\WebhookService();
            error_log("✅ WebhookService cargado");
            
            // 2. Preparar datos MÍNIMOS para probar
            $webhookData = [
                'review_id' => $review_id,
                'song_id' => $song_id,
                'rating' => $rating,
                'comment' => $comment_text,
                'user_email' => $_SESSION['user_email'] ?? 'test@test.com',
                'song_title' => 'Test Song', // Temporal
                'artist' => 'Test Artist'    // Temporal
            ];
            
            error_log("Enviando webhook con datos: " . json_encode($webhookData));
            
            // 3. ENVIAR FORZOSAMENTE
            $result = $webhookService->sendReviewCreated($webhookData);
            
            error_log("📤 Resultado webhook: " . json_encode($result));
            
            if ($result['success']) {
                error_log("✅ WEBHOOK ENVIADO EXITOSAMENTE a n8n");
            } else {
                error_log("❌ ERROR webhook: " . ($result['error'] ?? 'Desconocido'));
            }
            
        } catch (Exception $e) {
            error_log("💥 EXCEPCIÓN en webhook: " . $e->getMessage());
        }
        
    } else {
        error_log("⚠️  Esta es una review ACTUALIZADA, NO se envía webhook");
    }
    
    error_log("=== WEBHOOK DEBUG FINALIZADO ===");
    
    $_SESSION['success'] = "¡Tu reseña se ha $action correctamente!";
                // ========== ENVIAR WEBHOOK SOLO PARA REVIEWS NUEVAS ==========
                if ($action === "guardada") {
                    // Crear instancia
                    $webhookService = new \App\Services\WebhookService();

                    // Preparar datos (igual que ya tienes)
                    $webhookData = [
                        'review_id' => $review_id,
                        'song_id' => $song_id,
                        'song_title' => $song['title'] ?? 'Canción',
                        'artist' => $song['artist'] ?? 'Artista',  // Nota: 'artist' no 'song_artist'
                        'album' => $song['album'] ?? 'Álbum',
                        'duration' => $song['duration'] ?? '--:--',
                        'rating' => $rating,
                        'comment' => $comment_text,
                        'user_id' => $user_id,
                        'user_email' => $user['email'] ?? 'usuario@ejemplo.com',
                        'username' => $user['username'] ?? 'Usuario',
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    // Enviar
                    $result = $webhookService->sendReviewCreated($webhookData);
                }
                // ========== FIN WEBHOOK ==========

                $_SESSION['success'] = "¡Tu reseña se ha $action correctamente!";
            } else {
                $_SESSION['error'] = "Error al guardar la reseña";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al guardar la reseña: " . $e->getMessage();
        }

        $this->redirect('/songs?id=' . $song_id);
    }

    // Eliminar una review
    public function delete()
    {
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

            // Obtener la review
            $db = $this->getDatabase();
            $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // ========== ENVIAR WEBHOOK DE ELIMINACIÓN ==========
            // Obtener información del usuario
            $stmt = $db->prepare("SELECT email, username FROM users WHERE id = ?");
            $stmt->execute([$review['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener información de la canción
            $stmt = $db->prepare("SELECT title, artist FROM canciones WHERE id = ?");
            $stmt->execute([$song_id]);
            $song = $stmt->fetch(PDO::FETCH_ASSOC);

            $webhookService = new \App\Services\WebhookService();
            $webhookService->sendReviewDeleted([
                'review_id' => $review_id,
                'song_id' => $song_id,
                'song_title' => $song['title'] ?? 'Canción',
                'song_artist' => $song['artist'] ?? 'Artista',
                'user_id' => $review['user_id'],
                'user_email' => $user['email'] ?? 'usuario@ejemplo.com',
                'username' => $user['username'] ?? 'Usuario',
                'deleted_by' => $user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);
            // ========== FIN WEBHOOK ==========

            // Eliminar la review
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
    public function edit()
    {
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
            $db = $this->getDatabase();

            // Obtener la review
            $stmt = $db->prepare("
                SELECT r.*, u.username 
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$review_id]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$review) {
                $_SESSION['error'] = "Reseña no encontrada";
                $this->redirect('/home');
                return;
            }

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

    // Actualizar una review
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/home');
            return;
        }

        $review_id = $this->getPost('review_id');
        $song_id = $this->getPost('song_id');
        $comment_text = $this->getPost('comment');
        $rating = $this->getPost('rating');
        $user_id = $_SESSION['user_id'];

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
            $db = $this->getDatabase();

            // Verificar que la review existe y pertenece al usuario
            $stmt = $db->prepare("SELECT user_id FROM reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // Actualizar
            $stmt = $db->prepare("
                UPDATE reviews 
                SET comment = ?, rating = ?, updated_at = NOW() 
                WHERE id = ?
            ");

            $result = $stmt->execute([$comment_text, $rating, $review_id]);

            if ($result) {
                // Actualizar el promedio de rating de la canción
                $this->updateSongRating($song_id);

                $_SESSION['success'] = "Reseña actualizada correctamente";
            } else {
                $_SESSION['error'] = "No se pudo actualizar la reseña";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al actualizar la reseña: " . $e->getMessage();
        }

        $this->redirect('/songs?id=' . $song_id);
    }

    private function getDatabase()
    {
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

    private function updateSongRating($song_id)
    {
        try {
            $db = $this->getDatabase();

            $stmt = $db->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                FROM reviews 
                WHERE song_id = ?
            ");
            $stmt->execute([$song_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $avg_rating = $result['avg_rating'] ?? 0;
            $total_reviews = $result['total_reviews'] ?? 0;

            try {
                $stmt = $db->prepare("
                    UPDATE canciones 
                    SET avg_rating = ?, total_reviews = ? 
                    WHERE id = ?
                ");
                $stmt->execute([round($avg_rating, 1), $total_reviews, $song_id]);
            } catch (PDOException $e) {
                // Las columnas no existen
            }

        } catch (PDOException $e) {
            // Error silencioso
        }
    }

    public function getReviewsBySong($song_id)
    {
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

    public function getUserReviewForSong($user_id, $song_id)
    {
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

    public function getReviewsByUser($user_id)
    {
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

    public function buscarId($array, $id)
    {
        foreach ($array as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    public function loginRequired()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return false;
        }
        return true;
    }
}
?>