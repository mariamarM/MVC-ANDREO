<?php
// views/posts/create.php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'views/auth/login.php');
    exit;
}

// Inicializar variables
$error = '';
$success = '';
$review_id = null;

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar datos
        $required = ['song_id', 'rating', 'comment'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $error = "El campo $field es requerido";
                break;
            }
        }
        
        if (!$error) {
            $user_id = $_SESSION['user_id'];
            $song_id = intval($_POST['song_id']);
            $rating = intval($_POST['rating']);
            $comment = trim($_POST['comment']);
            
            // Validar rating
            if ($rating < 1 || $rating > 5) {
                $error = 'Rating debe ser entre 1 y 5';
            }
            
            // Verificar si la canción existe
            $stmt = $pdo->prepare("SELECT id, title, artist FROM canciones WHERE id = ?");
            $stmt->execute([$song_id]);
            $song = $stmt->fetch();
            
            if (!$song) {
                $error = 'La canción no existe';
            }
            
            // Verificar si ya existe review
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND song_id = ?");
            $stmt->execute([$user_id, $song_id]);
            if ($stmt->fetch()) {
                $error = 'Ya has publicado una review para esta canción';
            }
            
            if (!$error) {
                // Insertar la review
                $stmt = $pdo->prepare("INSERT INTO reviews (user_id, song_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $song_id, $rating, $comment]);
                
                $review_id = $pdo->lastInsertId();
                $success = '¡Review publicada correctamente!';
                
                // Guardar datos para mostrar
                $_SESSION['new_review'] = [
                    'id' => $review_id,
                    'song_title' => $song['title'],
                    'artist' => $song['artist'],
                    'rating' => $rating,
                    'comment' => $comment
                ];
            }
        }
        
    } catch (PDOException $e) {
        $error = 'Error en la base de datos: ' . $e->getMessage();
        error_log("Error al crear review: " . $e->getMessage());
    }
} else {
    // Si no es POST, redirigir al dashboard
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Creada</title>
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
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #f5c6cb;
        }
        .review-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h2><?= $success ?></h2>
            </div>
            
            <?php if (isset($_SESSION['new_review'])): ?>
                <div class="review-preview">
                    <h3>Vista previa de tu review:</h3>
                    <p><strong>Canción:</strong> <?= htmlspecialchars($_SESSION['new_review']['song_title']) ?></p>
                    <p><strong>Artista:</strong> <?= htmlspecialchars($_SESSION['new_review']['artist']) ?></p>
                    <p><strong>Calificación:</strong> 
                        <?= str_repeat('★', $_SESSION['new_review']['rating']) . str_repeat('☆', 5 - $_SESSION['new_review']['rating']) ?>
                    </p>
                    <p><strong>Comentario:</strong><br>
                        <?= nl2br(htmlspecialchars($_SESSION['new_review']['comment'])) ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <div>
                <a href="<?= BASE_URL ?>views/posts/show.php?id=<?= $review_id ?>" class="btn">
                    <i class="fas fa-eye"></i> Ver Review Completa
                </a>
                <a href="<?= BASE_URL ?>dashboardUser.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Volver al Dashboard
                </a>
                <a href="<?= BASE_URL ?>views/posts/create.php" class="btn">
                    <i class="fas fa-plus"></i> Crear Otra Review
                </a>
            </div>
            
            <script>
                // Redirigir automáticamente después de 10 segundos
                setTimeout(function() {
                    window.location.href = '<?= BASE_URL ?>views/posts/show.php?id=<?= $review_id ?>';
                }, 10000);
            </script>
            
        <?php elseif ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h2>Error al crear la review</h2>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
            
            <div>
                <a href="<?= BASE_URL ?>dashboardUser.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
                <button onclick="history.back()" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Intentar de Nuevo
                </button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>