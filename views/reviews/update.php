<?php
// views/posts/update.php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// Obtener ID de la review a editar
$review_id = $_GET['id'] ?? 0;

if (!$review_id) {
    $_SESSION['error'] = 'ID de review no especificado';
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

try {
    // Obtener la review
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist, c.id as song_id 
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = 'Review no encontrada o no tienes permiso para editarla';
        header('Location: ' . BASE_URL . 'dashboardUser.php');
        exit;
    }
    
    // Obtener canciones para el select
    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
    $songs = $stmt->fetchAll();
    
    // Procesar actualización si es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $song_id = intval($_POST['song_id']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        
        // Validar rating
        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Rating debe ser entre 1 y 5';
            header('Location: ' . BASE_URL . "views/posts/update.php?id=$review_id");
            exit;
        }
        
        // Verificar si la canción existe
        $stmt = $pdo->prepare("SELECT id FROM canciones WHERE id = ?");
        $stmt->execute([$song_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'La canción no existe';
            header('Location: ' . BASE_URL . "views/posts/update.php?id=$review_id");
            exit;
        }
        
        // Actualizar la review
        $stmt = $pdo->prepare("UPDATE reviews SET song_id = ?, rating = ?, comment = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$song_id, $rating, $comment, $review_id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = 'Review actualizada correctamente';
        header('Location: ' . BASE_URL . 'views/posts/index.php');
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
    <title>Editar Review</title>
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
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
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
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-edit"></i> Editar Review</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="song_id"><i class="fas fa-music"></i> Canción:</label>
                <select id="song_id" name="song_id" required>
                    <option value="">Selecciona una canción</option>
                    <?php foreach ($songs as $song): ?>
                        <option value="<?= $song['id'] ?>" 
                                <?= $song['id'] == $review['song_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($song['title']) ?> - <?= htmlspecialchars($song['artist']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rating"><i class="fas fa-star"></i> Calificación:</label>
                <select id="rating" name="rating" required>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" 
                                <?= $i == $review['rating'] ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>)
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment"><i class="fas fa-comment"></i> Comentario:</label>
                <textarea id="comment" name="comment" rows="5" required><?= htmlspecialchars($review['comment']) ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?= BASE_URL ?>views/posts/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>