<?php
// dashboardUser.php - VERSIÓN SIMPLIFICADA

// 1. Incluir config (que ahora inicia sesión)
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ' . BASE_URL . 'views/admin/dashboard.php');
    exit;
}

// 3. Verificación SIMPLE con opción de forzar
if (empty($_SESSION['user_id'])) {
    // Opción para forzar en desarrollo
       // Opción para forzar en desarrollo
    if (isset($_GET['force']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Usuario Forzado';
        $_SESSION['user_role'] = 'user';
    } else {
        // Mostrar opciones de login/register minimalistas
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Acceso Requerido</title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
            <script src="<?php echo BASE_URL; ?>/js/cursor-effect.js" defer></script>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: "Manrope", sans-serif;
                    background: #f5f5f5;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }
                
                .unauthorized-container {
                    flex: 1;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 20px;
                }
                
                .access-box {
                    background: white;
                    padding: 40px;
                    width: 100%;
                    max-width: 400px;
                    text-align: center;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .access-box h2 {
                    margin: 0 0 20px 0;
                    color: #333;
                    font-weight: 600;
                    font-size: 24px;
                }
                
                .access-box p {
                    color: #666;
                    margin: 0 0 30px 0;
                    line-height: 1.5;
                }
                
                .buttons-container {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    margin-bottom: 20px;
                }
                
                .btn {
                    padding: 14px;
                    text-decoration: none;
                    font-weight: 500;
                    border-radius: 4px;
                    transition: all 0.2s ease;
                    border: none;
                    cursor: pointer;
                    font-size: 16px;
                }
                
                .btn-login {
                    background: #ff0000;
                    color: white;
                }
                
                .btn-login:hover {
                    background: #e60000;
                }
                
                .btn-register {
                    background: transparent;
                    color: #333;
                    border: 1px solid #ddd;
                }
                
                .btn-register:hover {
                    background: #f9f9f9;
                    border-color: #ccc;
                }
                
                .dev-link {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }
                
                .dev-link a {
                    color: #666;
                    text-decoration: none;
                    font-size: 14px;
                }
                
                .dev-link a:hover {
                    text-decoration: underline;
                }
                
                @media (max-width: 480px) {
                    .access-box {
                        padding: 30px 20px;
                        margin: 0 10px;
                    }
                    
                    .btn {
                        padding: 12px;
                    }
                }
            </style>
        </head>
        <body>
            <?php require_once __DIR__ . '/../views/layout/nav.php'; ?>
            
            <div class="unauthorized-container">
                <div class="access-box">
                    <h2>Acceso requerido</h2>
                    <p>Necesitas iniciar sesión para continuar</p>
                    
                    <div class="buttons-container">
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-login">
                            Iniciar sesión
                        </a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-register">
                            Crear cuenta
                        </a>
                    </div>
                    
                    <div class="dev-link">
                        <?php if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1'): ?>
                            <a href="?force=1">Forzar acceso (solo desarrollo)</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// 4. SI LLEGA AQUÍ, EL USUARIO ESTÁ LOGUEADO
// Continúa con tu código NORMAL para obtener reviews...

$reviews = [];
$recent_likes = [];

try {
    require_once __DIR__ . '/../config/Database.php';
    $pdo = Database::getInstance();
    
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT r.*, c.title as song_title, c.artist 
            FROM reviews r 
            JOIN canciones c ON r.song_id = c.id 
            WHERE r.user_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ... resto de tu código
    }
} catch (Exception $e) {
    error_log("Error dashboard: " . $e->getMessage());
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access denied</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <script src="<?php echo BASE_URL; ?>/js/cursor-effect.js" defer></script>

 <style>
    h1 {
        font-size: 140px;
        font-weight: 900;
        margin-top: 20px;
        color: #ff0000;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: -2px;
        line-height: 1;
    }

    p {
        color: #DA1E28;
        font-family: "Konkhmer Sleokchher";
        font-size: 21px;
        font-style: normal;
        font-weight: 400;
        line-height: 0;
        /* 0% */
        letter-spacing: 0.15px;
    }

    .containerLog {
        display: flex;
        width: 554px;
        height: 287px;
        padding: 47px 10px;
        flex-direction: column;
        align-items: center;
        gap: 53px;
    }

    h2 {
        color: #000;
        font-family: Milker;
        font-size: 30px;
        font-style: normal;
        font-weight: 400;
        line-height: 0;
        /* 0% */
        letter-spacing: 0.15px;
    }

    h5 {
        color: #000;
        text-align: center;
        font-family: "Konkhmer Sleokchher";
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 28px;
        /* 155.556% */
        letter-spacing: 0.15px;
    }

    .containerLog {
        display: flex;
        width: 554px;
        height: 287px;
        padding: 47px 10px;
        flex-direction: column;
        align-items: center;
        gap: 53px;
        border-radius: 20px;
        border: 1px solid #F00;
    }

    .btnsLog a {
        display: flex;
        padding: 10px 21px;
        align-items: flex-start;
        gap: 19px;
    }
</style>
</head>
<body class="userdashboard">
<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>
   <div class="containerPL">
            <p>These section is to see what you heard off these week</p>
            <div class="contenedorlog">
                <h2>GET YOUR ACCOUNT
                </h2>
                <h5>To see these webpage you need to have an account :(</h5>
                <div class="btnsLog">
                    <a href="<?php echo BASE_URL; ?>login.php" class="btnLogin">Log in</a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btnRegister">Get an account</a>
                </div>
            </div>
        </div>
</body>
</html>
<?php
    exit;
}

$reviews = [];
$recent_likes = [];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.title as song_title, c.artist 
        FROM reviews r 
        JOIN canciones c ON r.song_id = c.id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (file_exists(__DIR__ . '/../models/Like.php')) {
        require_once __DIR__ . '/../models/Like.php';
        $likeModel = new Like($pdo);
        $recent_likes = $likeModel->getUserLikes($_SESSION['user_id'], 5);
    }
    
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
    error_log($error);
}

$canciones = [];
try {
    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
    $canciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar canciones: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="./css/app.css">
    <script src="/js/cursor-effect.js" defer></script>

</head>
<style>
/* ===== RESET ===== */
* {
    box-sizing: border-box;
}
.action-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;
     position:absolute; 
 
      background: #e11d2e;
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 30px;
    cursor: pointer;
}
body {
    margin: 0;
    background: #f2f2f2;
    font-family: 'Inter', Arial, sans-serif;
}

/* ===== MAIN GRID ===== */
main {
    max-width: 67%;
    margin: 40px auto;
    padding: 40px;
 
    display: grid;
    grid-template-columns: 300px 1fr;
    grid-template-areas:
        "user stats"
        "user reviews";
    gap: 40px;
}

/* ===== USUARIO (usa el H1 REAL) ===== */
main > h1 {
    grid-area: user;
    background: #e11d2e;
    color: white;
    padding: 30px 25px;
    border-radius: 45px 95px 55px 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;

    font-size: 22px;
}

main > h1 i {
    font-size: 90px;
    margin-bottom: 20px;
    opacity: 0.9;
}



/* ===== ESTADÍSTICAS (última section) ===== */
main section:last-of-type {
    grid-area: stats;
}

main section:last-of-type > div {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

main section:last-of-type > div > div {
    border-radius: 30px;
    border: 2px solid #eee;
    padding: 25px;
    background: white;
    text-align: center;
}

/* ===== REVIEWS (primera section) ===== */
main section:nth-of-type(1) {
    grid-area: reviews;
    background: #2f2f2f;
    padding: 30px;
    border-radius: 35px 25px 40px 30px;
    color: white;
}

main section:nth-of-type(1) h2 {
    color: white;
    margin-bottom: 20px;
}

/* ===== REVIEW ITEM ===== */
.review-item {
    background: #555;
    padding: 18px;
    border-radius: 20px;
    margin-bottom: 14px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    main {
        grid-template-columns: 1fr;
        grid-template-areas:
            "user"
            "stats"
            "reviews";
    }

    main section:last-of-type > div {
        grid-template-columns: 1fr;
    }
}
</style>


<body class="userdashboard">

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<main>
   <h1><i class="fas fa-user-circle"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1> 
    
    <div class="reviewbtn">
        <button id="openReviewModal"  class="action-buttons" >
            <i class="fas fa-plus"></i> Crear una review
        </button>
        
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <section style="margin-top:-20px;">
        <h2><i class="fas fa-star"></i> Tus reviews recientes</h2>
        
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star" style="font-size: 36px; margin-bottom: 10px;"></i>
                <p>Aún no has creado ninguna review</p>
                <p>Crea tu primera review haciendo clic en el botón de arriba</p>
            </div>
        <?php else: ?>
<div id="reviews-container" >
    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <i class="fas fa-star" style="font-size: 36px; margin-bottom: 10px;"></i>
            <p>Aún no has creado ninguna review</p>
            <p>Crea tu primera review haciendo clic en el botón de arriba</p>
        </div>
    <?php else: ?>
        <?php 
        $reviews_to_show = array_slice($reviews, 0, 2);
        ?>
        
        <?php foreach ($reviews_to_show as $review): ?>
            <div class="review-item" id="review-<?= $review['id'] ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 8px 0; color: #fff;">
                            <?= htmlspecialchars($review['song_title']) ?> - <?= htmlspecialchars($review['artist']) ?>
                        </h4>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <div style="color: #ffc107; font-size: 18px;">
                                <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                                <span style="color: #ccc; margin-left: 5px;">(<?= $review['rating'] ?>/5)</span>
                            </div>
                            <small style="color: #aaa;">
                                <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                            </small>
                        </div>
                        <p style="color: #ddd; margin: 0; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($review['comment'])) ?>
                        </p>
                    </div>
                    <div style="margin-left: 15px;">
      
<a href="<?php echo BASE_URL; ?>views/reviews/update.php?id=<?php echo $review['id']; ?>" 
   style="color: #ffc107; margin-right: 10px;"
   title="Editar">
    <i class="fas fa-edit"></i>
</a>
<a href="<?php echo BASE_URL; ?>views/reviews/delete.php?id=<?php echo $review['id']; ?>" 
   style="color: #ff6b6b;"
   onclick="return confirm('¿Eliminar esta review?');"
   title="Eliminar">
    <i class="fas fa-trash"></i>
</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($reviews) > 2): ?>
            <div style="text-align: center; margin-top: 15px; padding: 10px; background: #444; border-radius: 10px;">
                <p style="color: #aaa; margin: 0;">
                    <i class="fas fa-info-circle"></i>
                    Tienes <?= count($reviews) - 2 ?> reviews más. 
<a href="<?php echo BASE_URL; ?>views/reviews/index.php" style="color: #4da6ff;">
    Ver todas 
</a>                 
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
            
       
        <?php endif; ?>
    </section>
    
    <?php if (!empty($recent_likes)): ?>
    <section>
        <h2><i class="fas fa-heart"></i> Tus likes recientes</h2>
        <div class="likes-list">
            <?php foreach ($recent_likes as $like): ?>
                <div class="like-item">
                    <i class="<?= $like['content_type'] == 'review' ? 'fas fa-comment' : 'fas fa-music' ?>"></i>
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 3px;">
                            <?= htmlspecialchars($like['content_title']) ?>
                        </strong>
                        <small style="color: #666;">
                            <?= htmlspecialchars($like['content_subtitle']) ?>
                        </small>
                    </div>
                    <small style="color: #999;">
                        <?= date('d/m/Y', strtotime($like['created_at'])) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <section>
        <h2><i class="fas fa-chart-bar"></i> Tus estadísticas</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #007bff; font-weight: bold;" id="total-reviews">
                    <?= count($reviews) ?>
                </div>
                <div style="color: #666;">Reviews totales</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #28a745; font-weight: bold;" id="avg-rating">
                    <?php 
                    $avg_rating = 0;
                    if (!empty($reviews)) {
                        $sum = array_sum(array_column($reviews, 'rating'));
                        $avg_rating = round($sum / count($reviews), 1);
                    }
                    echo $avg_rating;
                    ?>
                </div>
                <div style="color: #666;">Rating promedio</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; color: #ff6b6b; font-weight: bold;">
                    <?= count($recent_likes) ?>
                </div>
                <div style="color: #666;">Likes dados</div>
            </div>
        </div>
    </section>
</main>

<div id="reviewModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-star"></i> Crear Nueva Review</h3>
            <button class="modal-close" id="closeReviewModal">&times;</button>
        </div>
        <form id="createReviewForm" action="<?php echo BASE_URL; ?>views/reviews/create.php"method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="song_id"><i class="fas fa-music"></i> Canción *</label>
                    <select id="song_id" name="song_id" >
                        <option value="">Selecciona una canción</option>
                        <?php foreach ($canciones as $cancion): ?>
                            <option value="<?= $cancion['id'] ?>">
                                <?= htmlspecialchars($cancion['title']) ?> - <?= htmlspecialchars($cancion['artist']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Rating *</label>
                    <div class="rating-container">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating-star" data-rating="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="rating" name="rating" value="0" required>
                    <small id="rating-text" style="color: #666; margin-top: 5px; display: block;">
                        Selecciona un rating
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="comment"><i class="fas fa-comment"></i> Comentario *</label>
                    <textarea id="comment" name="comment" 
                              placeholder="Escribe tu opinión sobre esta canción..." 
                              rows="5" required></textarea>
                </div>
                
                <input type="hidden" name="create_review" value="1">
                <input type="hidden" name="modal_submit" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-secondary" id="cancelReviewModal">
                    Cancelar
                </button>
                <button type="submit" class="btn-modal btn-primary">
                    <i class="fas fa-check"></i> Guardar Review
                </button>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = '<?= BASE_URL ?>';
    
    const modal = document.getElementById('reviewModal');
    const openBtn = document.getElementById('openReviewModal');
    const closeBtn = document.getElementById('closeReviewModal');
    const cancelBtn = document.getElementById('cancelReviewModal');
    
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating');
    const ratingText = document.getElementById('rating-text');
    const songSelect = document.getElementById('song_id');
    const commentTextarea = document.getElementById('comment');
    
    let selectedRating = 0;
    
    openBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForm();
    }
    
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            ratingInput.value = selectedRating;
            
            stars.forEach((s, index) => {
                if (index < selectedRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
            
            const ratingTexts = [
                'Muy mala',
                'Mala',
                'Regular',
                'Buena',
                'Excelente'
            ];
            if (ratingText) {
                ratingText.textContent = `${selectedRating}/5 - ${ratingTexts[selectedRating - 1]}`;
                ratingText.style.color = '#333';
            }
        });
        
        star.addEventListener('mouseover', function() {
            const hoverRating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
                if (index < hoverRating) {
                    s.style.color = '#ffc107';
                }
            });
        });
        
        star.addEventListener('mouseout', function() {
            stars.forEach((s, index) => {
                if (!selectedRating || index >= selectedRating) {
                    s.style.color = '';
                }
            });
        });
    });
    
    const reviewForm = document.getElementById('createReviewForm');
    
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!songSelect || !songSelect.value) {
                alert('Por favor, selecciona una canción');
                return;
            }
            
            if (!selectedRating) {
                alert('Por favor, selecciona un rating');
                return;
            }
            
            if (!commentTextarea || !commentTextarea.value.trim()) {
                alert('Por favor, escribe un comentario');
                return;
            }
            
            const formData = new FormData(this);
            
            // Mostrar datos que se enviarán
            console.log('=== DATOS A ENVIAR ===');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            try {
               console.log('Enviando datos a:', this.action);

// Y cambia el action ANTES de enviar:
// CORREGIR EN EL SCRIPT
const formAction = BASE_URL + 'views/reviews/create.php';
const response = await fetch(formAction, {  // Usa formAction en lugar de this.action
    method: 'POST',
    body: formData,
        credentials: 'include',
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
  
});
                
                // 1. PRIMERO obtener la respuesta como TEXTO
                const responseText = await response.text();
                console.log('=== RESPUESTA CRUDA DEL SERVIDOR ===');
                console.log('Status:', response.status, response.statusText);
                console.log('Content-Type:', response.headers.get('Content-Type'));
                console.log('Primeros 500 caracteres de la respuesta:');
                console.log(responseText.substring(0, 500) + (responseText.length > 500 ? '...' : ''));
                
                // 2. Verificar si la respuesta parece HTML (error)
                if (responseText.trim().startsWith('<!DOCTYPE') || 
                    responseText.includes('<html') || 
                    responseText.includes('Parse error') ||
                    responseText.includes('Fatal error') ||
                    responseText.includes('Warning:') ||
                    responseText.includes('Notice:')) {
                    
                    console.error('❌ ERROR: El servidor devolvió HTML/error en lugar de JSON');
                    
                    // Extraer mensaje de error si es posible
                    let errorMessage = 'Error del servidor';
                    if (responseText.includes('Fatal error')) {
                        const match = responseText.match(/Fatal error:([^<]+)/);
                        if (match) errorMessage = 'Error fatal: ' + match[1].trim();
                    } else if (responseText.includes('Parse error')) {
                        const match = responseText.match(/Parse error:([^<]+)/);
                        if (match) errorMessage = 'Error de sintaxis: ' + match[1].trim();
                    }
                    
                    alert('❌ ' + errorMessage + '\n\nRevisa la consola para más detalles.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                // 3. Intentar parsear como JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('✅ JSON parseado correctamente:', data);
                } catch (jsonError) {
                    console.error('❌ ERROR parsing JSON:', jsonError);
                    console.error('Respuesta completa que no pudo parsearse:');
                    console.error(responseText);
                    
                    alert('❌ Error: El servidor no devolvió un JSON válido.\n\nRevisa la consola para ver la respuesta completa.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                // 4. Procesar respuesta
                if (data.success) {
                    closeModal();
                    alert('✅ ' + (data.message || 'Review creada exitosamente!'));
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    alert('❌ Error: ' + (data.message || 'Error desconocido'));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
                
            } catch (error) {
                console.error('❌ Error de conexión o fetch:', error);
                
                // Determinar tipo de error
                let errorMessage = 'Error de conexión';
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Error de red o la URL no es accesible';
                } else if (error.name === 'SyntaxError') {
                    errorMessage = 'Error en la respuesta del servidor';
                }
                
                alert('❌ ' + errorMessage + '.\n\nPor favor, intenta de nuevo.\n\nDetalles: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    function resetForm() {
        if (reviewForm) {
            reviewForm.reset();
        }
        stars.forEach(star => {
            star.classList.remove('active');
            star.style.color = '';
        });
        selectedRating = 0;
        if (ratingInput) ratingInput.value = '0';
        if (ratingText) {
            ratingText.textContent = 'Selecciona un rating';
            ratingText.style.color = '#666';
        }
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });
    
    // Función para probar la conexión manualmente
    window.testReviewConnection = function() {
        console.log('=== PRUEBA MANUAL DE CONEXIÓN ===');
        
        const testData = new FormData();
        testData.append('song_id', '1');
        testData.append('rating', '5');
        testData.append('comment', 'Esta es una prueba de conexión');
        testData.append('create_review', '1');
        testData.append('modal_submit', '1');
        
        console.log('Probando con datos:', {
            song_id: 1,
            rating: 5,
            comment: 'Esta es una prueba de conexión'
        });
        
        fetch('/views/reviews/create.php', {
            method: 'POST',
            body: testData
        })
        .then(response => response.text())
        .then(text => {
            console.log('Respuesta del servidor:');
            console.log(text.substring(0, 1000));
            
            try {
                const json = JSON.parse(text);
                console.log('JSON parseado:', json);
                alert('✅ Conexión OK: ' + (json.success ? 'Éxito' : 'Error: ' + json.message));
            } catch(e) {
                console.error('No es JSON:', e);
                alert('❌ Error: El servidor no devolvió JSON válido');
            }
        })
        .catch(error => {
            console.error('Error de fetch:', error);
            alert('❌ Error de conexión: ' + error.message);
        });
    };
    
    
});
</script>
</body>
</html>