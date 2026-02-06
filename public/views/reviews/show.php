<?php
// show.php - VERSIÓN CORREGIDA

// 1. Incluir configuración y base de datos
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php'; // ¡IMPORTANTE!

// 2. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// 3. Obtener ID de la review con validación
$review_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$review_id || $review_id <= 0) {
    $_SESSION['error'] = "ID de review inválido";
    header('Location: ' . BASE_URL . 'views/reviews/index.php');
    exit;
}

// 4. Obtener conexión PDO y la review
try {
    $pdo = Database::getInstance();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Obtener la review con información completa
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist, c.album, c.genre,
               u.username as author_name
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = "Review no encontrada";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
    
    // Verificar que el usuario es el propietario (o es admin)
    $is_owner = ($review['user_id'] == $_SESSION['user_id']);
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    
    if (!$is_owner && !$is_admin) {
        $_SESSION['error'] = "No tienes permiso para ver esta review";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error al cargar la review: " . $e->getMessage();
    header('Location: ' . BASE_URL . 'views/reviews/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review: <?php echo htmlspecialchars($review['song_title']); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateX(-5px);
        }
        
        .review-detail {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .review-detail h1 {
            color: #e11d2e;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .song-info, .review-rating-large, .review-comment-full, .review-meta {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #e11d2e;
        }
        
        h2 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h2 i {
            color: #e11d2e;
        }
        
        .song-info p, .review-meta p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .song-info strong {
            color: #555;
            min-width: 80px;
            display: inline-block;
        }
        
        .stars {
            font-size: 32px;
            color: #ffc107;
            margin: 15px 0;
        }
        
        .rating-text {
            font-size: 18px;
            color: #666;
            font-weight: bold;
        }
        
        .comment-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 10px;
            line-height: 1.8;
            font-size: 16px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .review-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            background: #e9ecef;
        }
        
        .review-meta p {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }
        
        .review-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }
        
        .btn-danger {
            background: #e11d2e;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c41727;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(225, 29, 46, 0.3);
        }
        
        .permission-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .review-detail {
                padding: 20px;
            }
            
            .review-meta {
                flex-direction: column;
            }
            
            .review-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Incluir navbar si existe
    $nav_path = __DIR__ . '/../layout/nav.php';
    if (file_exists($nav_path)) {
        require_once $nav_path;
    }
    ?>
    
    <div class="container">
        <a href="<?php echo BASE_URL; ?>views/reviews/index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Volver a mis reviews
        </a>
        
        <!-- Nota de permisos si es admin viendo review de otro -->
        <?php if ($is_admin && !$is_owner): ?>
            <div class="permission-notice">
                <i class="fas fa-user-shield"></i>
                <strong>Modo administrador:</strong> Estás viendo una review de otro usuario.
            </div>
        <?php endif; ?>
        
        <div class="review-detail">
            <h1><i class="fas fa-star"></i> Review de: <?php echo htmlspecialchars($review['song_title']); ?></h1>
            
            <div class="song-info">
                <h2><i class="fas fa-music"></i> Información de la canción</h2>
                <p><strong>Artista:</strong> <?php echo htmlspecialchars($review['artist']); ?></p>
                <?php if (!empty($review['album'])): ?>
                    <p><strong>Álbum:</strong> <?php echo htmlspecialchars($review['album']); ?></p>
                <?php endif; ?>
                <?php if (!empty($review['genre'])): ?>
                    <p><strong>Género:</strong> <?php echo htmlspecialchars($review['genre']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="review-rating-large">
                <h2>Tu valoración:</h2>
                <div class="stars">
                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                </div>
                <p class="rating-text"><?php echo $review['rating']; ?>/5 estrellas</p>
            </div>
            
            <div class="review-comment-full">
                <h2><i class="fas fa-comment"></i> Tu comentario:</h2>
                <div class="comment-box">
                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                </div>
            </div>
            
            <div class="review-meta">
                <p><i class="far fa-clock"></i> Creada: <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></p>
                <p><i class="fas fa-user"></i> Autor: <?php echo htmlspecialchars($review['author_name']); ?></p>
            </div>
            
            <div class="review-actions">
                <?php if ($is_owner || $is_admin): ?>
                    <a href="<?php echo BASE_URL; ?>views/reviews/update.php?id=<?php echo $review['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Review
                    </a>
                <?php endif; ?>
                
                <?php if ($is_owner || $is_admin): ?>
                    <a href="<?php echo BASE_URL; ?>views/reviews/delete.php?id=<?php echo $review['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('¿Estás seguro de eliminar esta review?');">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>