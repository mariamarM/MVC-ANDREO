<?php
// views/posts/show.php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// Obtener ID de la review
$review_id = $_GET['id'] ?? 0;

if (!$review_id) {
    // Si no hay ID pero hay una review nueva en sesión, usar esa
    if (isset($_SESSION['new_review'])) {
        $review_id = $_SESSION['new_review']['id'];
    } else {
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
    }
}

try {
    // Obtener la review con detalles
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist, c.album, c.genre,
               u.name as user_name, u.email as user_email
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        JOIN users u ON r.user_id = u.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = 'Review no encontrada';
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
    }
    
    // Limpiar la review nueva de la sesión si estamos viéndola
    if (isset($_SESSION['new_review']) && $_SESSION['new_review']['id'] == $review_id) {
        unset($_SESSION['new_review']);
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error en la base de datos: ' . $e->getMessage();
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review: <?= htmlspecialchars($review['song_title']) ?></title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        .review-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .song-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .song-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }
        .rating {
            font-size: 24px;
            color: #ffc107;
            margin: 20px 0;
        }
        .comment-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            line-height: 1.6;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <div class="review-card">
            <div class="song-header">
                <h1 class="song-title">
                    <i class="fas fa-music"></i> <?= htmlspecialchars($review['song_title']) ?>
                </h1>
                <p><i class="fas fa-user"></i> Artista: <?= htmlspecialchars($review['artist']) ?></p>
            </div>
            
            <div class="rating">
                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                <span style="font-size: 18px; color: #666;">
                    (<?= $review['rating'] ?>/5)
                </span>
            </div>
            
            <div class="comment-box">
                <?= nl2br(htmlspecialchars($review['comment'])) ?>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($review['user_name'], 0, 1)) ?>
                </div>
                <div>
                    <h4 style="margin: 0;"><?= htmlspecialchars($review['user_name']) ?></h4>
                    <p style="margin: 5px 0 0; color: #666;">
                        <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                    </p>
                </div>
            </div>
            
            <div class="actions">
                <a href="<?= BASE_URL ?>dashboardUser.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
                
                <?php if ($review['user_id'] == $_SESSION['user_id']): ?>
                <a href="<?= BASE_URL ?>views/posts/update.php?id=<?= $review['id'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="<?= BASE_URL ?>views/posts/delete.php?id=<?= $review['id'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('¿Eliminar esta review?');">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>