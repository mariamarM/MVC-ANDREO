<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}
$baseDir = dirname(__DIR__, 3); 
$configFile = $baseDir . '/config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php'); // <- CORREGIDO
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reviews</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .reviews-grid {
            display: grid;
            gap: 20px;
        }
        .review-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #667eea;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .review-song {
            font-weight: bold;
            font-size: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .review-artist {
            color: #666;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .review-rating {
            color: #ffc107;
            font-size: 20px;
            background: #fff9e6;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .review-comment {
            color: #555;
            line-height: 1.7;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 16px;
            border-left: 3px solid #667eea;
        }
        .review-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
            gap: 15px;
        }
        .review-date {
            color: #888;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .review-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .page-link {
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            transition: all 0.3s;
            min-width: 45px;
            text-align: center;
        }
        .page-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        .page-link:hover:not(.active) {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        .create-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .create-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .review-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            .review-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <a href="<?= BASE_URL ?>dashboardUser.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
        
        <h1><i class="fas fa-star"></i> Todas mis Reviews</h1>
        
        <a href="/views/reviews/create.php" class="create-btn">
            <i class="fas fa-plus"></i> Crear Nueva Review
        </a>
        
        <div class="reviews-grid">
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>No tienes reviews todavía</h3>
                    <p>Comparte tu opinión sobre tus canciones favoritas</p>
                    <a href="<?= BASE_URL ?>views/reviews/create.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Crear mi primera review
                    </a>
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
                                <span style="margin-left: 8px; color: #333;">(<?= $review['rating'] ?>/5)</span>
                            </div>
                        </div>
                        
                        <div class="review-artist">
                            <i class="fas fa-user"></i> Artista: <?= htmlspecialchars($review['artist']) ?>
                        </div>
                        
                        <div class="review-comment">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </div>
                        
                        <div class="review-footer">
                            <div class="review-date">
                                <i class="far fa-clock"></i> 
                                Creada el: <?= date('d/m/Y', strtotime($review['created_at'])) ?> 
                                a las <?= date('H:i', strtotime($review['created_at'])) ?>
                            </div>
                            
                            <div class="review-actions">
                                <a href="<?= BASE_URL ?>views/reviews/show.php?id=<?= $review['id'] ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </a>
                                <a href="<?= BASE_URL ?>views/reviews/update.php?id=<?= $review['id'] ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= BASE_URL ?>views/reviews/delete.php?id=<?= $review['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar esta review?');">
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
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>
            
            <?php 
            // Mostrar números de página
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?= $i ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="page-link">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación para las tarjetas
        const reviewCards = document.querySelectorAll('.review-card');
        reviewCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Confirmar eliminación
        const deleteLinks = document.querySelectorAll('a[href*="delete"]');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que quieres eliminar esta review?')) {
                    e.preventDefault();
                }
            });
        });
    });
    </script>
</body>
</html>