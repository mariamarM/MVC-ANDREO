<?php
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$review_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist, c.album, c.genre,
               u.username as author_name
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = "Review no encontrada";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al cargar la review";
    header('Location: ' . BASE_URL . 'views/reviews/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?= htmlspecialchars($review['song_title']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <a href="<?= BASE_URL ?>views/reviews/index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Volver a mis reviews
        </a>
        
        <div class="review-detail">
            <h1><i class="fas fa-star"></i> Review de: <?= htmlspecialchars($review['song_title']) ?></h1>
            
            <div class="song-info">
                <h2><i class="fas fa-music"></i> Información de la canción</h2>
                <p><strong>Artista:</strong> <?= htmlspecialchars($review['artist']) ?></p>
                <p><strong>Álbum:</strong> <?= htmlspecialchars($review['album'] ?? 'N/A') ?></p>
                <p><strong>Género:</strong> <?= htmlspecialchars($review['genre'] ?? 'N/A') ?></p>
            </div>
            
            <div class="review-rating-large">
                <h2>Tu valoración:</h2>
                <div class="stars">
                    <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                </div>
                <p class="rating-text"><?= $review['rating'] ?>/5 estrellas</p>
            </div>
            
            <div class="review-comment-full">
                <h2><i class="fas fa-comment"></i> Tu comentario:</h2>
                <div class="comment-box">
                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                </div>
            </div>
            
            <div class="review-meta">
                <p><i class="far fa-clock"></i> Creada: <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></p>
                <p><i class="fas fa-user"></i> Autor: <?= htmlspecialchars($review['author_name']) ?></p>
            </div>
            
            <div class="review-actions">
                <a href="<?= BASE_URL ?>views/reviews/update.php?id=<?= $review['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Review
                </a>
                <a href="<?= BASE_URL ?>views/reviews/delete.php?id=<?= $review['id'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('¿Eliminar esta review?');">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
</body>
</html>