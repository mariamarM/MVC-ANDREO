<?php
// views/posts/delete.php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// Obtener ID de la review a eliminar
$review_id = $_GET['id'] ?? 0;

if (!$review_id) {
    $_SESSION['error'] = 'ID de review no especificado';
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

try {
    // Verificar que la review existe y pertenece al usuario
    $stmt = $pdo->prepare("SELECT r.*, c.title as song_title FROM reviews r 
                          JOIN canciones c ON r.song_id = c.id 
                          WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = 'Review no encontrada o no tienes permiso para eliminarla';
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
    }
    
    // Si es POST, eliminar la review
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$review_id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = 'Review eliminada correctamente';
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
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
    <title>Eliminar Review</title>
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
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .review-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 5px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-trash"></i> Eliminar Review</h1>
        
        <p>¿Estás seguro de que quieres eliminar esta review?</p>
        
        <div class="review-info">
            <p><strong>Canción:</strong> <?= htmlspecialchars($review['song_title']) ?></p>
            <p><strong>Calificación:</strong> <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></p>
            <p><strong>Comentario:</strong> <?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></p>
        </div>
        
        <form method="POST" action="">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Sí, eliminar
            </button>
            <a href="<?= BASE_URL ?>dashboardUser.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</body>
</html>