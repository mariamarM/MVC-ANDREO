<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access denied</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #cfcfcf;
            text-align: center;
            width: 360px;
        }
        .box a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../views/layout/nav.php'; ?>
    <div class="box">
        <h2>Get logged in</h2>
        <p>You need an account to access your dashboard.</p>
        <a href="<?php echo BASE_URL; ?>login.php">Log in</a>
        <a href="<?php echo BASE_URL; ?>register.php">Create account</a>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
    
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<main>
    <h1><i class="fas fa-user-circle"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1>
    
    <div class="action-buttons">
        <button id="openReviewModal" class="btn">
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
    
    <section>
        <h2><i class="fas fa-star"></i> Tus reviews recientes</h2>
        
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star" style="font-size: 36px; margin-bottom: 10px;"></i>
                <p>Aún no has creado ninguna review</p>
                <p>Crea tu primera review haciendo clic en el botón de arriba</p>
            </div>
        <?php else: ?>
<div id="reviews-container">
    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <i class="fas fa-star" style="font-size: 36px; margin-bottom: 10px;"></i>
            <p>Aún no has creado ninguna review</p>
            <p>Crea tu primera review haciendo clic en el botón de arriba</p>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-item" id="review-<?= $review['id'] ?>">
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="/views/reviews/index.php" 
                   style="color: #007bff; text-decoration: none;">
                    <i class="fas fa-arrow-right"></i> Ver todas mis reviews
                </a>
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
        <form id="createReviewForm" action="/views/reviews/create.php" method="POST">
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
            
            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            try {
                console.log('Enviando datos a:', this.action);
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                // SI O SI PARSEAR A JSON SINO NO LO LE
                const data = await response.json();
                console.log('Respuesta:', data);
                
                if (data.success) {
                    closeModal();
                    
                    alert('✅ Review creada exitosamente!');
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    alert('❌ Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error de conexión. Por favor, intenta de nuevo.');
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
});
</script>
</body>
</html>