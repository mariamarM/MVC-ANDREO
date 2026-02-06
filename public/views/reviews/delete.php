<?php
// delete.php - VERSIÓN CORREGIDA

// 1. Incluir configuración y base de datos
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php';

// 2. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// 3. Obtener ID de la review
$review_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$review_id || $review_id <= 0) {
    $_SESSION['error'] = "ID de review inválido";
    header('Location: ' . BASE_URL . 'views/reviews/index.php');
    exit;
}

// 4. Obtener conexión PDO
try {
    $pdo = Database::getInstance();
    
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar que la review existe y pertenece al usuario
    $stmt = $pdo->prepare("SELECT id, user_id FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();
    
    if (!$review) {
        $_SESSION['error'] = "La review no existe";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
    
    // Verificar que el usuario es el propietario (o es admin)
    $is_owner = ($review['user_id'] == $_SESSION['user_id']);
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    
    if (!$is_owner && !$is_admin) {
        $_SESSION['error'] = "No tienes permiso para eliminar esta review";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error de conexión: " . $e->getMessage();
    header('Location: ' . BASE_URL . 'views/reviews/index.php');
    exit;
}

// 5. Procesar eliminación si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Eliminar la review
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        
        $_SESSION['success'] = "✅ Review eliminada correctamente";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Error al eliminar la review: " . $e->getMessage();
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
}

// 6. Mostrar página de confirmación (GET)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Review</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .confirm-delete {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        h2 {
            color: #e11d2e;
            margin-bottom: 20px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .btn-danger {
            background: #e11d2e;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c41727;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(225, 29, 46, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .review-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        
        .review-info p {
            margin: 5px 0;
            color: #333;
        }
        
        form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="confirm-delete">
        <h2>⚠️ ¿Eliminar Review?</h2>
        <p>Esta acción <strong>no se puede deshacer</strong>. Se eliminará permanentemente tu review.</p>
        
        <div class="review-info">
            <p><strong>ID de Review:</strong> #<?php echo $review_id; ?></p>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></p>
            <?php if ($is_admin && !$is_owner): ?>
                <p style="color: #dc3545;"><strong>⚠️ Nota:</strong> Estás eliminando una review de otro usuario (modo administrador)</p>
            <?php endif; ?>
        </div>
        
        <form method="POST" onsubmit="return confirm('¿Estás completamente seguro de eliminar esta review?');">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Sí, eliminar definitivamente
            </button>
            <a href="<?php echo BASE_URL; ?>views/reviews/index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
    
    <!-- Font Awesome para iconos -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>