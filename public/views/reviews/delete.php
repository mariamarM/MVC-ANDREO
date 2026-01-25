<?php
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$review_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$review_id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Review eliminada correctamente";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al eliminar la review";
        header('Location: ' . BASE_URL . 'views/reviews/index.php');
        exit;
    }
} else {
    // Mostrar confirmación
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Eliminar Review</title>
        <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    </head>
    <body>
        <div class="confirm-delete">
            <h2>¿Eliminar Review?</h2>
            <p>Esta acción no se puede deshacer.</p>
            <form method="POST">
                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                <a href="<?= BASE_URL ?>views/reviews/index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>