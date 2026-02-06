<?php
// update.php - VERSIÓN CORREGIDA

// 1. Incluir configuración y base de datos
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php'; // ¡IMPORTANTE!

// 2. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php'); // Corregí la ruta
    exit;
}

// 3. Obtener ID de la review con validación
$review_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$review_id || $review_id <= 0) {
    $_SESSION['error'] = '❌ ID de review inválido';
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

// 4. Obtener conexión PDO
try {
    $pdo = Database::getInstance();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // 5. Obtener la review con verificación de propiedad
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
    
    // 6. Obtener canciones para el select
    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
    $songs = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error de conexión: ' . $e->getMessage();
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

// 7. Procesar actualización si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar datos
        $song_id = filter_input(INPUT_POST, 'song_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        
        // Validaciones
        if (!$song_id || $song_id <= 0) {
            throw new Exception('Debes seleccionar una canción válida');
        }
        
        if (!$rating || $rating < 1 || $rating > 5) {
            throw new Exception('El rating debe ser un número entre 1 y 5');
        }
        
        if (empty($comment) || strlen($comment) < 3) {
            throw new Exception('El comentario debe tener al menos 3 caracteres');
        }
        
        // Verificar que la canción existe
        $stmt = $pdo->prepare("SELECT id FROM canciones WHERE id = ?");
        $stmt->execute([$song_id]);
        if (!$stmt->fetch()) {
            throw new Exception('La canción seleccionada no existe');
        }
        
        // Actualizar la review
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET song_id = ?, rating = ?, comment = ?, updated_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$song_id, $rating, $comment, $review_id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = '✅ Review actualizada correctamente';
        header('Location: ' . BASE_URL . 'views/reviews/index.php'); // Corregí la ruta
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = '❌ Error: ' . $e->getMessage();
        header('Location: ' . BASE_URL . "views/reviews/update.php?id=$review_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Review</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #e11d2e;
            border-bottom: 3px solid #ffc107;
            padding-bottom: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 5px solid;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        label i {
            color: #e11d2e;
            margin-right: 10px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #e11d2e;
            box-shadow: 0 0 0 3px rgba(225, 29, 46, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }
        
        .btn {
            padding: 14px 28px;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #e11d2e, #c41727);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 29, 46, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(225, 29, 46, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .review-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #ffc107;
        }
        
        .review-info p {
            margin: 5px 0;
            color: #555;
        }
        
        .review-info strong {
            color: #333;
        }
        
        .rating-stars {
            display: inline-block;
            font-size: 20px;
            color: #ffc107;
            margin-left: 10px;
        }
        
        /* Estilos específicos para el select de rating */
        #rating {
            max-width: 300px;
            background: white;
            cursor: pointer;
        }
        
        #rating option {
            padding: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }
            
            .btn-group {
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
        <h1><i class="fas fa-edit"></i> Editar Review</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Información de la review actual -->
        <div class="review-info">
            <p><strong>Review ID:</strong> #<?php echo $review_id; ?></p>
            <p><strong>Canción actual:</strong> <?php echo htmlspecialchars($review['song_title']); ?> - <?php echo htmlspecialchars($review['artist']); ?></p>
            <p><strong>Rating actual:</strong> <?php echo $review['rating']; ?>/5 
                <span class="rating-stars">
                    <?php echo str_repeat('★', $review['rating']); ?><?php echo str_repeat('☆', 5 - $review['rating']); ?>
                </span>
            </p>
            <p><strong>Fecha creación:</strong> <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="song_id"><i class="fas fa-music"></i> Canción:</label>
                <select id="song_id" name="song_id" required>
                    <option value="">Selecciona una canción</option>
                    <?php foreach ($songs as $song): ?>
                        <option value="<?php echo $song['id']; ?>" 
                                <?php echo ($song['id'] == $review['song_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($song['title']); ?> - <?php echo htmlspecialchars($song['artist']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rating"><i class="fas fa-star"></i> Calificación:</label>
                <select id="rating" name="rating" required>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" 
                                <?php echo ($i == $review['rating']) ? 'selected' : ''; ?>>
                            <?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?> (<?php echo $i; ?>/5)
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment"><i class="fas fa-comment"></i> Comentario:</label>
                <textarea id="comment" name="comment" rows="5" required 
                          placeholder="Escribe tu opinión sobre esta canción..."><?php echo htmlspecialchars($review['comment']); ?></textarea>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?php echo BASE_URL; ?>views/reviews/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Auto-hide alerts después de 5 segundos -->
    <script>
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 5000);
    </script>
</body>
</html>