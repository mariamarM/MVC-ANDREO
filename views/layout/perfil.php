<div class="container">
    <h1>Mi Perfil</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="profile-info">
        <div class="info-card">
            <h3>Información Personal</h3>
            <p><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
            
            <div class="profile-actions">
                <a href="<?php echo BASE_URL; ?>logout" class="btn btn-logout">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reviews del usuario -->
    <?php if (!empty($reviews)): ?>
        <div class="user-reviews mt-4">
            <h3>Mis Reseñas (<?php echo count($reviews); ?>)</h3>
            
            <div class="reviews-grid">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <h4>
                            <a href="<?php echo BASE_URL; ?>songs?id=<?php echo $review['song_id']; ?>">
                                <?php echo htmlspecialchars($review['song_title']); ?>
                            </a>
                        </h4>
                        <p><strong>Artista:</strong> <?php echo htmlspecialchars($review['artist']); ?></p>
                        
                        <div class="rating">
                            <?php echo str_repeat('★', $review['rating']); ?>
                            <span class="rating-text">(<?php echo $review['rating']; ?>/5)</span>
                        </div>
                        
                        <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        
                        <p class="review-date">
                            <small>
                                Publicado el: <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                            </small>
                        </p>
                        
                        <div class="review-actions">
                            <a href="<?php echo BASE_URL; ?>comment/edit?id=<?php echo $review['id']; ?>" 
                               class="btn-edit">Editar</a>
                            <a href="<?php echo BASE_URL; ?>comment/delete?id=<?php echo $review['id']; ?>" 
                               onclick="return confirm('¿Eliminar esta reseña?')" 
                               class="btn-delete">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="no-reviews mt-4">
            <h3>Mis Reseñas</h3>
            <p>Aún no has publicado ninguna reseña.</p>
            <a href="<?php echo BASE_URL; ?>songs" class="btn">Explorar Canciones</a>
        </div>
    <?php endif; ?>
</div>