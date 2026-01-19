<?php
// views/posts/index.php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// Obtener reviews del usuario con paginación
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Contar total de reviews
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_reviews = $stmt->fetch()['total'];
    $total_pages = ceil($total_reviews / $limit);
    
    // Obtener reviews de esta página
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist 
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $reviews = [];
    $total_pages = 1;
    error_log("Error al obtener reviews: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mis Reviews</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .reviews-grid {
            display: grid;
            gap: 20px;
        }
        .review-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-song {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }
        .review-artist {
            color: #666;
            margin-bottom: 10px;
        }
        .review-rating {
            color: #ffc107;
            font-size: 18px;
        }
        .review-comment {
            color: #555;
            line-height: 1.6;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #999;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
        }
        .page-link.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .page-link:hover:not(.active) {
            background: #f8f9fa;
        }
        .create-btn {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-star"></i> Mis Reviews</h1>
        
        <a href="<?= BASE_URL ?>views/posts/create.php" class="create-btn">
            <i class="fas fa-plus"></i> Crear Nueva Review
        </a>
        
        <div class="reviews-grid">
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>No tienes reviews todavía</h3>
                    <p>Crea tu primera review haciendo clic en el botón de arriba</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-song">
                                <i class="fas fa-music"></i> <?= htmlspecialchars($review['song_title']) ?>
                            </div>
                            <div class="review-rating">
                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                (<?= $review['rating'] ?>/5)
                            </div>
                        </div>
                        
                        <div class="review-artist">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($review['artist']) ?>
                        </div>
                        
                        <div class="review-comment">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <small style="color: #999;">
                                <i class="far fa-clock"></i> 
                                <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                            </small>
                            
                            <div class="review-actions">
                                <a href="<?= BASE_URL ?>views/posts/update.php?id=<?= $review['id'] ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= BASE_URL ?>views/posts/delete.php?id=<?= $review['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Seguro que quieres eliminar esta review?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>