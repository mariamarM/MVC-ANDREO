<?php
// views/reviews/create.php
require_once __DIR__ . '/../../config/config.php';

// SIEMPRE empezar con output buffering
ob_start();

// Verificar si es una petición desde modal
$isModal = isset($_POST['modal_submit']);

if (!isset($_SESSION['user_id'])) {
    if ($isModal) {
        // Para AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
        exit;
    }
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_review'])) {
    try {
        // Validaciones básicas
        $song_id = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');
        
        // Validar campos requeridos
        // if ($song_id <= 0) {
        //     throw new Exception("Debes seleccionar una canción");
        // }
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception("El rating debe estar entre 1 y 5");
        }
        
        if (empty($comment)) {
            throw new Exception("El comentario es obligatorio");
        }
        
        if (strlen($comment) > 1000) {
            throw new Exception("El comentario no puede exceder los 1000 caracteres");
        }
        
        // Verificar que la canción existe
        $stmt = $pdo->prepare("SELECT id, title, artist FROM canciones WHERE id = ?");
        $stmt->execute([$song_id]);
        $cancion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        
        // Insertar la review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, song_id, rating, comment) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$_SESSION['user_id'], $song_id, $rating, $comment]);
        $review_id = $pdo->lastInsertId();
        
        // Obtener los datos completos de la review
        $stmt = $pdo->prepare("
            SELECT r.*, c.title as song_title, c.artist 
            FROM reviews r 
            JOIN canciones c ON r.song_id = c.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$review_id]);
        $new_review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($isModal) {
            // Respuesta JSON para AJAX
            header('Content-Type: application/json');
            ob_end_clean(); // Limpiar buffer
            echo json_encode([
                'success' => true,
                'message' => 'Review creada exitosamente',
                'review' => [
                    'id' => $new_review['id'],
                    'song_title' => $new_review['song_title'],
                    'artist' => $new_review['artist'],
                    'rating' => $new_review['rating'],
                    'comment' => $new_review['comment'],
                    'created_at' => $new_review['created_at']
                ]
            ]);
            exit;
        } else {
            // Para envío normal del formulario
            $_SESSION['success'] = "Review creada exitosamente";
            header('Location: ' . BASE_URL . 'views/reviews/index.php');
            exit;
        }
        
    } catch (Exception $e) {
        if ($isModal) {
            // Error en JSON para AJAX
            header('Content-Type: application/json');
            ob_end_clean(); // Limpiar buffer
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
            exit;
        } else {
            // Error en envío normal
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . 'views/reviews/create.php');
            exit;
        }
    }
}

// Si llegamos aquí, es una petición GET (mostrar formulario)
ob_end_clean(); // Limpiar buffer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crear Review</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layout/nav.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-star"></i> Crear Nueva Review</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="song_id"><i class="fas fa-music"></i> Canción</label>
                <select id="song_id" name="song_id" required>
                    <option value="">Selecciona una canción</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
                    while ($cancion = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                        <option value="<?= $cancion['id'] ?>">
                            <?= htmlspecialchars($cancion['title']) ?> - <?= htmlspecialchars($cancion['artist']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rating"><i class="fas fa-star"></i> Rating (1-5)</label>
                <select id="rating" name="rating" required>
                    <option value="">Selecciona un rating</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> ★</option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment"><i class="fas fa-comment"></i> Comentario</label>
                <textarea id="comment" name="comment" 
                          placeholder="Escribe tu opinión sobre esta canción..." 
                          required></textarea>
            </div>
            
            <input type="hidden" name="create_review" value="1">
            
            <div class="form-group">
                <button type="submit" class="btn">
                    <i class="fas fa-check"></i> Crear Review
                </button>
                <a href="<?= BASE_URL ?>user.php" class="btn">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>