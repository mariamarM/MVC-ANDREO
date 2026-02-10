<?php
// app/controllers/CommentController.php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Cancion.php';
require_once __DIR__ . '/../services/WebhookService.php';

class CommentController extends Controller
{
    // ✅ AGREGAR UNA NUEVA REVIEW (CORREGIDO)
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
                $review_id = $existingReview['id'];
                $is_new_review = false;
            } else {
                // No existe, insertar nueva
                $review_id = $reviewModel->create($user_id, $song_id, $rating, $comment_text);
                $result = ($review_id !== false);
                $action = "guardada";
                $is_new_review = true;
            }

            if ($result) {
                // Actualizar el promedio de rating de la canción
                $this->updateSongRating($song_id);
                
                // ✅ WEBHOOK: Solo para reviews NUEVAS
                if ($is_new_review) {
                    $this->sendReviewCreatedWebhook($review_id, $song_id, $user_id, $rating, $comment_text, $action);
                } else {
                    $this->sendReviewUpdatedWebhook($review_id, $song_id, $user_id, $rating, $comment_text, $action);
                }

                $_SESSION['success'] = "¡Tu reseña se ha $action correctamente!";
            } else {
                $_SESSION['error'] = "Error al guardar la reseña";
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al guardar la reseña: " . $e->getMessage();
        }

        $this->redirect('/songs?id=' . $song_id);
    }

    // ✅ ELIMINAR UNA REVIEW (CORREGIDO)
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

            // Obtener datos completos de la review ANTES de eliminar
            $reviewDetails = $reviewModel->getReviewWithDetails($review_id);
            
            if (!$reviewDetails) {
                $_SESSION['error'] = "Reseña no encontrada";
                $this->redirect('/home');
                return;
            }

            // Verificar permisos
            $user_id = $_SESSION['user_id'];
            $is_admin = $_SESSION['role'] ?? 'user';

            if ($reviewDetails['user_id'] != $user_id && $is_admin != 'admin') {
                $_SESSION['error'] = "No tienes permiso para eliminar esta reseña";
                $this->redirect('/songs?id=' . $reviewDetails['song_id']);
                return;
            }

            $song_id = $reviewDetails['song_id'];

            // ✅ ENVIAR WEBHOOK DE ELIMINACIÓN ANTES de borrar
            $this->sendReviewDeletedWebhook($reviewDetails);

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

    // ✅ WEBHOOK: Review creada
    private function sendReviewCreatedWebhook($review_id, $song_id, $user_id, $rating, $comment, $action)
    {
        try {
            $reviewModel = new Review();
            $cancionModel = new Cancion();
            $userModel = new User();
            
            // Obtener datos completos
            $review = $reviewModel->findById($review_id);
            $cancion = $cancionModel->getSongInfo($song_id);
            $user = $userModel->getUserInfo($user_id);
            
            if (!$review || !$cancion || !$user) {
                error_log("❌ No se pudieron obtener datos para webhook");
                return;
            }
            
            // Preparar datos para webhook
            $webhookData = [
                'review_id' => $review_id,
                'song_id' => $song_id,
                'song_title' => $cancion['title'] ?? 'Canción',
                'artist' => $cancion['artist'] ?? 'Artista',
                'album' => $cancion['album'] ?? 'Álbum',
                'rating' => $rating,
                'comment' => $comment,
                'user_id' => $user_id,
                'user_email' => $user['email'] ?? 'usuario@ejemplo.com',
                'username' => $user['username'] ?? 'Usuario',
                'created_at' => date('Y-m-d H:i:s'),
                'action' => $action
            ];
            
            // Enviar webhook
            $webhookService = new \App\Services\WebhookService();
            $result = $webhookService->sendReviewCreated($webhookData);
            
            error_log("📤 Webhook enviado para review creada: " . 
                ($result['success'] ? '✅ Éxito' : '❌ Error: ' . ($result['error'] ?? '')));
                
        } catch (Exception $e) {
            error_log("💥 Error enviando webhook de creación: " . $e->getMessage());
        }
    }

    // ✅ WEBHOOK: Review actualizada
    private function sendReviewUpdatedWebhook($review_id, $song_id, $user_id, $rating, $comment, $action)
    {
        try {
            $reviewModel = new Review();
            $cancionModel = new Cancion();
            $userModel = new User();
            
            // Obtener datos
            $review = $reviewModel->findById($review_id);
            $cancion = $cancionModel->getSongInfo($song_id);
            $user = $userModel->getUserInfo($user_id);
            
            $webhookData = [
                'review_id' => $review_id,
                'song_id' => $song_id,
                'song_title' => $cancion['title'] ?? 'Canción',
                'artist' => $cancion['artist'] ?? 'Artista',
                'rating' => $rating,
                'comment' => $comment,
                'user_id' => $user_id,
                'user_email' => $user['email'] ?? 'usuario@ejemplo.com',
                'username' => $user['username'] ?? 'Usuario',
                'updated_at' => date('Y-m-d H:i:s'),
                'action' => $action
            ];
            
            // Enviar webhook (podrías crear un método sendReviewUpdated en WebhookService)
            $webhookService = new \App\Services\WebhookService();
            $result = $webhookService->sendReviewCreated($webhookData); // Usar el mismo por ahora
            
            error_log("📤 Webhook enviado para review actualizada");
                
        } catch (Exception $e) {
            error_log("💥 Error enviando webhook de actualización: " . $e->getMessage());
        }
    }

    // ✅ WEBHOOK: Review eliminada
    private function sendReviewDeletedWebhook($reviewDetails)
    {
        try {
            $webhookData = [
                'review_id' => $reviewDetails['id'],
                'song_id' => $reviewDetails['song_id'],
                'song_title' => $reviewDetails['song_title'] ?? 'Canción',
                'artist' => $reviewDetails['artist'] ?? 'Artista',
                'rating' => $reviewDetails['rating'],
                'comment' => $reviewDetails['comment'],
                'user_id' => $reviewDetails['user_id'],
                'user_email' => $reviewDetails['user_email'] ?? 'usuario@ejemplo.com',
                'username' => $reviewDetails['username'] ?? 'Usuario',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $_SESSION['user_id'] ?? null
            ];
            
            $webhookService = new \App\Services\WebhookService();
            $result = $webhookService->sendReviewDeleted($webhookData);
            
            error_log("📤 Webhook enviado para review eliminada");
                
        } catch (Exception $e) {
            error_log("💥 Error enviando webhook de eliminación: " . $e->getMessage());
        }
    }

    // ✅ MÉTODOS AUXILIARES (ya los tienes)
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

    // ✅ MÉTODOS EXISTENTES (mantener igual)
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