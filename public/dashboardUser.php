<?php
// dashboardUser.php
require_once __DIR__ . '/../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access denied</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>views/css/views.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #cfcfcf;
            text-align: center;
            width: 360px;
        }
        .box a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../views/layout/nav.php'; ?>
    <div class="box">
        <h2>Get logged in</h2>
        <p>You need an account to access your dashboard.</p>
        <a href="<?php echo BASE_URL; ?>login.php">Log in</a>
        <a href="<?php echo BASE_URL; ?>register.php">Create account</a>
    </div>
</body>
</html>
<?php
    exit;
}

// ============================================
// USUARIO LOGUEADO - OBTENER DATOS
// ============================================

// Obtener reviews del usuario
$reviews = [];
$recent_likes = [];

try {
    // 1. Obtener reviews del usuario
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist 
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Obtener likes recientes (si tienes el modelo)
    if (file_exists(__DIR__ . '/../models/Like.php')) {
        require_once __DIR__ . '/../models/Like.php';
        $likeModel = new Like($pdo);
        $recent_likes = $likeModel->getUserLikes($_SESSION['user_id'], 5);
    }
    
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
    error_log($error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
 
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<main>
    <h1><i class="fas fa-user-circle"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1>
    
    <!-- Botones de acción -->
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>views/reviews/create.php" class="btn">
            <i class="fas fa-plus"></i> Crear una review
        </a>
        <a href="<?= BASE_URL ?>views/reviews/index.php" class="btn">
            <i class="fas fa-list"></i> Ver todas mis reviews
        </a>
      
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Sección de reviews recientes -->
    <section>
        <h2><i class="fas fa-star"></i> Tus reviews recientes</h2>
        
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star" style="font-size: 36px; margin-bottom: 10px;"></i>
                <p>Aún no has creado ninguna review</p>
                <p>Crea tu primera review haciendo clic en el botón de arriba</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-song">
                        <i class="fas fa-music"></i> 
                        <?= htmlspecialchars($review['song_title']) ?> 
                        - <?= htmlspecialchars($review['artist']) ?>
                    </div>
                    <div class="review-rating">
                        <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                        (<?= $review['rating'] ?>/5)
                    </div>
                    <div class="review-comment">
                        <?= nl2br(htmlspecialchars(substr($review['comment'], 0, 150))) ?>
                        <?php if (strlen($review['comment']) > 150): ?>...<?php endif; ?>
                    </div>
                    <div class="review-date">
                        <i class="far fa-clock"></i> 
                        <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="<?= BASE_URL ?>views/posts/show.php?id=<?= $review['id'] ?>" 
                           style="font-size: 14px; color: #007bff; text-decoration: none;">
                            <i class="fas fa-eye"></i> Ver detalles
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="<?= BASE_URL ?>views/posts/index.php" 
                   style="color: #007bff; text-decoration: none;">
                    <i class="fas fa-arrow-right"></i> Ver todas mis reviews
                </a>
            </div>
        <?php endif; ?>
    </section>
    
    <!-- Sección de likes recientes -->
    <?php if (!empty($recent_likes)): ?>
    <section>
        <h2><i class="fas fa-heart"></i> Tus likes recientes</h2>
        <div class="likes-list">
            <?php foreach ($recent_likes as $like): ?>
                <div class="like-item">
                    <i class="<?= $like['content_type'] == 'review' ? 'fas fa-comment' : 'fas fa-music' ?>"></i>
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 3px;">
                            <?= htmlspecialchars($like['content_title']) ?>
                        </strong>
                        <small style="color: #666;">
                            <?= htmlspecialchars($like['content_subtitle']) ?>
                        </small>
                    </div>
                    <small style="color: #999;">
                        <?= date('d/m/Y', strtotime($like['created_at'])) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Sección de estadísticas -->
    <section>
        <h2><i class="fas fa-chart-bar"></i> Tus estadísticas</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #007bff; font-weight: bold;">
                    <?= count($reviews) ?>
                </div>
                <div style="color: #666;">Reviews totales</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #28a745; font-weight: bold;">
                    <?php 
                    $avg_rating = 0;
                    if (!empty($reviews)) {
                        $sum = array_sum(array_column($reviews, 'rating'));
                        $avg_rating = round($sum / count($reviews), 1);
                    }
                    echo $avg_rating;
                    ?>
                </div>
                <div style="color: #666;">Rating promedio</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #ff6b6b; font-weight: bold;">
                    <?= count($recent_likes) ?>
                </div>
                <div style="color: #666;">Likes dados</div>
            </div>
        </div>
    </section>
</main>

<script>
// Script para mejorar la UX
document.addEventListener('DOMContentLoaded', function() {
    // Añadir animaciones a los elementos
    const reviewItems = document.querySelectorAll('.review-item');
    reviewItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Confirmación para acciones importantes
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta review?')) {
                e.preventDefault();
            }
        });
    });
    
    // Actualizar la página cada 5 minutos para nuevos datos
    setTimeout(() => {
        console.log('Actualizando datos del dashboard...');
    }, 300000); // 5 minutos
});
</script>
</body>
</html>