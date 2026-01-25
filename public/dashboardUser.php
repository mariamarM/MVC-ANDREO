<?php
require_once __DIR__ . '/../config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}
if (!isset($_SESSION['user_id'])) {
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
<body >
    <?php require_once __DIR__ . './views/layout/nav.php'; ?>
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
    top:20% ;
    right:40%;
      background: #e11d2e;
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 30px;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(225,29,46,0.35);
}
body {
    margin: 0;
    background: #f2f2f2;
    font-family: 'Inter', Arial, sans-serif;
}

/* ===== MAIN GRID ===== */
main {
    max-width: 1300px;
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
    border-radius: 45px 35px 55px 30px;

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


<body>

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
const formAction = '/views/reviews/create.php'; // Ruta ABSOLUTA

const response = await fetch(formAction, {  // Usa formAction en lugar de this.action
    method: 'POST',
    body: formData,
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