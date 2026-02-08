<?php
// views/reviews/index.php - VERSIÓN ESTILOS PLANOS

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

try {
    $pdo = Database::getInstance();
    if ($pdo === null) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count_result = $stmt->fetch();
    $total_reviews = $count_result['total'] ?? 0;
    $total_pages = ceil($total_reviews / $limit);

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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reviews</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

    <style>
/* ===== ESTILOS PLANOS - BLANCO Y ROJO ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Manrope', Arial, sans-serif;
}

body {
    background-color: black;
    color: #333333;
    line-height: 1.5;
    overflow-x: hidden;
    height: 100vh;
}

.container {
    display: flex;
    height: calc(100vh - 120px); /* Altura total menos padding */
    padding: 100px 20px 20px;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 10%;
}

/* ===== HEADER ===== */
h1 {
    color: #e11d2e;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e11d2e;
    font-size: 70px;
    text-align: center;
    font-family: "Milker", sans-serif;
    display: flex;
    gap: 10px;
}

/* ===== BOTONES PLANOS ===== */
.back-btn {
    position: fixed; /* Cambiado a fixed para que sea fijo */
    top: 20px;
    left: 20px;
    display: inline-block;
    color: white;
    padding: 12px 24px;
    border-radius: 5px;
    text-decoration: none;
    margin-bottom: 30px;
    font-weight: 500;
    border: none;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
}

.back-btn:hover {
    background: rgba(225, 29, 46, 0.8);
    color: white;
}

.create-btn {
    display: inline-block;
    background: #F5F5F5;
    color: red;
    align-items: center;
    padding: 12px 24px;
    border-radius: 5px;
    text-decoration: none;
    margin-bottom: 30px;
    font-weight: 500;
    border: none;
}

.create-btn:hover {
    background: #e11d2e;
    color: white;
}

/* ===== STATS BAR ===== */
.stats-bar {
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-around;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #DA1E28;
}

.stat-label {
    color: #DA1E28;
    font-size: 24px;
    
}

.container-titulo {
    display: flex;
    flex-direction: column;
    align-items: left;
    flex: 0 0 30%; /* Ancho fijo para la columna izquierda */
    height: 100%;
}

/* ===== REVIEW CARDS - SCROLL VERTICAL INVISIBLE ===== */
.reviews-grid {
    display: flex;
    gap: 20px;
    justify-content: flex-start;
    flex-wrap: wrap;
    flex-direction: column;
    height: 100%;
    width: 70%;
    overflow-y: auto; /* Habilitar scroll vertical */
    padding-right: 10px; /* Espacio para el scroll invisible */
    padding-bottom: 20px; /* Espacio al final */
    
    /* Ocultar scrollbar en todos los navegadores */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
}

/* Ocultar scrollbar en Chrome, Safari y Opera */
.reviews-grid::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

/* Efecto de scroll suave */
.reviews-grid {
    scroll-behavior: smooth;
}

/* Indicador visual de que hay más contenido */
.reviews-grid::after {
    content: '';
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    background: rgba(225, 29, 46, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.reviews-grid.scrolling::after {
    opacity: 1;
}

.review-card {
    background: #dee2e6;
    padding: 25px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    flex-shrink: 0; /* Evitar que las cards se compriman */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    border-color: #e11d2e;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.review-song {
    font-weight: bold;
    font-size: 20px;
    color: #333333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.review-song i {
    color: #e11d2e;
}

.review-artist {
    color: #666666;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.review-rating {
    color: #e11d2e;
    font-size: 20px;
    background: #fff0f0;
    padding: 8px 16px;
    border-radius: 5px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 5px;
}

.review-comment {
    color: #555555;
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 5px;
    border-left: 3px solid #e11d2e;
    line-height: 1.6;
    max-height: 200px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #e11d2e #f8f9fa;
}

.review-comment::-webkit-scrollbar {
    width: 6px;
}

.review-comment::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.review-comment::-webkit-scrollbar-thumb {
    background: #e11d2e;
    border-radius: 3px;
}

.review-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 15px;
    gap: 30px;
    border-top: 1px solid #dee2e6;
}

.review-date {
    color: #888888;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.review-actions {
    display: flex;
    gap: 10px;
}

/* ===== BOTONES DE ACCIÓN ===== */
.btn {
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-warning {
    background: #ffc107;
    color: #333333;
}

.btn-warning:hover {
    background: #e0a800;
    transform: translateY(-2px);
}

.btn-danger {
    background: #e11d2e;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-2px);
}

/* ===== ESTADO VACÍO ===== */
.empty-state {
    text-align: center;
    padding: 60px 30px;
    color: #666666;
    background: white;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    margin: 30px 0;
    width: 100%;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #cccccc;
}

.empty-state h3 {
    color: #e11d2e;
    margin-bottom: 10px;
}

/* ===== PAGINACIÓN ===== */
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
    width: 100%;
    position: absolute;
    bottom: 20px;
    left: 0;
}

.page-link {
    padding: 10px 16px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    text-decoration: none;
    color: #e11d2e;
    font-weight: 500;
    background: white;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #f8f9fa;
    border-color: #e11d2e;
    transform: translateY(-2px);
}

.page-link.active {
    background: #e11d2e;
    color: white;
    border-color: #e11d2e;
}

/* ===== SCROLL DRAG FUNCTIONALITY ===== */
.reviews-grid {
    cursor: grab; /* Indicar que se puede arrastrar */
    user-select: none; /* Prevenir selección de texto */
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.reviews-grid.dragging {
    cursor: grabbing; /* Cambiar cursor cuando se está arrastrando */
    scroll-behavior: auto !important; /* Desactivar scroll suave mientras se arrastra */
}

/* Efecto de scroll con inercia */
@keyframes scrollInertia {
    0% { transform: translateY(0); }
    100% { transform: translateY(var(--scroll-distance)); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .container {
        flex-direction: column;
        gap: 30px;
        height: auto;
        padding: 100px 20px 60px;
    }
    
    .container-titulo {
        flex: none;
        width: 100%;
    }
    
    .reviews-grid {
        width: 100%;
        height: 60vh;
    }
    
    h1 {
        font-size: 50px;
    }
}

@media (max-width: 768px) {
    .container {
        padding: 80px 15px 60px;
    }
    
    h1 {
        font-size: 40px;
    }
    
    .review-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .review-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .review-actions {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    
    .stats-bar {
        flex-direction: column;
        gap: 15px;
        align-items: center;
    }
    
    .pagination {
        position: relative;
        bottom: auto;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .back-btn {
        top: 15px;
        left: 15px;
        padding: 10px 20px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 32px;
    }
    
    .review-card {
        padding: 15px;
    }
    
    .review-song {
        font-size: 18px;
    }
    
    .review-rating {
        font-size: 16px;
        padding: 6px 12px;
    }
    
    .btn {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .stat-number {
        font-size: 24px;
    }
    
    .stat-label {
        font-size: 18px;
    }
}
</style>
</head>

<body>
    <?php
    $nav_path = __DIR__ . '/../layout/nav.php';
    if (file_exists($nav_path)) {
        require_once $nav_path;
    }
    ?>
    <a href="<?php echo BASE_URL; ?>dashboardUser.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
    <div class="container">

        <div class="container-titulo">
            <h1>Todas mis Reviews</h1>

            <!-- Barra de estadísticas -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">Reviews totales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_pages; ?></div>
                    <div class="stat-label">Páginas</div>
                </div>
                <div class="stat-item">
                    <a href="<?php echo BASE_URL; ?>views/reviews/create.php" class="create-btn">
                        <i class="fas fa-plus"></i> Crear Nueva Review
                    </a>
                </div>
            </div>
        </div>
        <div class="reviews-grid">
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>No tienes reviews todavía</h3>
                    <p>Comparte tu opinión sobre tus canciones favoritas</p>
                    <a href="<?php echo BASE_URL; ?>views/reviews/create.php" class="btn btn-primary"
                        style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Crear mi primera review
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-song">
                                <i class="fas fa-music"></i> <?php echo htmlspecialchars($review['song_title']); ?>
                            </div>
                            <div class="review-rating">
                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                <span style="margin-left: 8px;">(<?php echo $review['rating']; ?>/5)</span>
                            </div>
                        </div>

                        <div class="review-artist">
                            <i class="fas fa-user"></i> Artista: <?php echo htmlspecialchars($review['artist']); ?>
                        </div>

                        <div class="review-comment">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </div>

                        <div class="review-footer">
                            <div class="review-date">
                                <i class="far fa-clock"></i>
                                <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                            </div>

                            <div class="review-actions">
                                <a href="<?php echo BASE_URL; ?>views/reviews/show.php?id=<?php echo $review['id']; ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="<?php echo BASE_URL; ?>views/reviews/update.php?id=<?php echo $review['id']; ?>"
                                    class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?php echo BASE_URL; ?>views/reviews/delete.php?id=<?php echo $review['id']; ?>"
                                    class="btn btn-danger" onclick="return confirm('¿Eliminar esta review?');">
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
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Script simple sin animaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Solo confirmación de eliminación
            var deleteLinks = document.querySelectorAll('a[href*="delete"]');
            deleteLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    if (!confirm('¿Estás seguro de eliminar esta review?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>

</html>