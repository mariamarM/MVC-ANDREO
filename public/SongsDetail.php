<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($albumName); ?> | Music Virtual Closet</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-rojo: #ff0000;
            --color-bg: #0a0a0a;
            --color-texto: #ffffff;
            --color-gris: #1a1a1a;
            --color-verde: #00c853;
            --color-azul: #29ECF3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--color-bg);
            color: var(--color-texto);
            font-family: "Manrope", sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* VERSIÓN SIMPLIFICADA - SIN SCROLL COMPLEJO */
        .page-wrapper {
            position: relative;
            width: 100%;
            min-height: 100vh;
        }
        
        /* CONTENEDOR PRINCIPAL SIMPLE */
        .content-sections {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* CADA SECCIÓN OCUPA 100% DEL VIEWPORT */
        .section {
            min-width: 100vw;
            height: 100vh;
            padding: 40px;
            overflow-y: auto;
            flex-shrink: 0;
            transition: transform 0.5s ease;
        }
        
        .section::-webkit-scrollbar {
            display: none;
        }
        
        /* SECCIÓN CANCIONES */
        .songs-section {
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.98), rgba(20, 20, 20, 0.97));
        }
        
        /* SECCIÓN REVIEWS */
        .reviews-section {
            background: linear-gradient(135deg, rgba(15, 15, 15, 0.98), rgba(25, 25, 25, 0.97));
        }
        
        /* HEADER */
        .album-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--color-rojo);
        }
        
        .album-title {
            font-size: 72px;
            font-weight: 900;
            color: var(--color-texto);
            margin-bottom: 5px;
            letter-spacing: -1px;
            line-height: 1;
        }
        
        .album-artist {
            font-size: 24px;
            color: var(--color-azul);
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-texto);
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* LISTA DE CANCIONES */
        .songs-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .song-item {
            display: flex;
            align-items: center;
            padding: 18px 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .song-item.highlighted {
            background: rgba(255, 0, 0, 0.15);
            border-left: 4px solid var(--color-rojo);
        }
        
        .song-item:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateX(5px);
        }
        
        .song-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-rojo);
            min-width: 40px;
        }
        
        .song-details {
            flex-grow: 1;
        }
        
        .song-details-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--color-texto);
            margin-bottom: 5px;
        }
        
        .song-duration {
            font-size: 16px;
            color: var(--color-azul);
            font-weight: 500;
            min-width: 60px;
            text-align: right;
        }
        
        /* REVIEWS */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        
        .review-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 22px;
            border-left: 4px solid var(--color-verde);
            transition: all 0.3s ease;
        }
        
        .review-item:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(5px);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-rojo), var(--color-azul));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            color: white;
        }
        
        .review-user {
            flex-grow: 1;
        }
        
        .review-username {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-texto);
        }
        
        .review-song {
            font-size: 14px;
            color: #aaa;
            margin-top: 3px;
        }
        
        .review-rating {
            font-size: 18px;
            color: var(--color-verde);
            font-weight: 700;
            background: rgba(0, 200, 83, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
        }
        
        .review-comment {
            font-size: 16px;
            line-height: 1.5;
            color: #ddd;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border-left: 3px solid var(--color-azul);
        }
        
        .review-date {
            font-size: 12px;
            color: #777;
            text-align: right;
        }
        
        /* FOOTER */
        .album-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #aaa;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* NAVEGACIÓN SIMPLE */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            color: var(--color-texto);
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: color 0.3s;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }
        
        .back-button:hover {
            color: var(--color-rojo);
            background: rgba(255, 0, 0, 0.1);
        }
        
        /* PUNTOS DE NAVEGACIÓN MEJORADOS */
        .nav-dots {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 1000; /* MAYOR Z-INDEX */
        }
        
        .nav-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .nav-dot.active {
            background: var(--color-rojo);
            transform: scale(1.3);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.7);
            border: 2px solid white;
        }
        
        .nav-dot:hover {
            transform: scale(1.2);
            background: rgba(255, 0, 0, 0.5);
        }
        
        /* FLECHAS DE NAVEGACIÓN MEJORADAS */
        .nav-arrows {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 30px;
            z-index: 1000; /* MAYOR Z-INDEX */
        }
        
        .nav-arrow {
            background: rgba(255, 255, 255, 0.2);
            color: var(--color-texto);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-arrow:hover {
            background: var(--color-rojo);
            transform: scale(1.15);
            border-color: white;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
        }
        
        /* INDICADOR DE SECCIÓN */
        .section-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            display: none; /* Oculto por defecto */
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .album-title {
                font-size: 48px;
            }
            
            .section {
                padding: 20px;
            }
            
            .nav-arrows {
                bottom: 20px;
                gap: 20px;
            }
            
            .nav-arrow {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .nav-dots {
                right: 10px;
            }
            
            .nav-dot {
                width: 14px;
                height: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .album-title {
                font-size: 36px;
            }
            
            .nav-arrows {
                bottom: 15px;
                gap: 15px;
            }
            
            .nav-arrow {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .section-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <a href="buscarCanciones.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Search
    </a>
    
    <?php if (!empty($error)): ?>
        <div style="text-align: center; padding: 100px 20px;">
            <h2 style="color: var(--color-rojo);">Error</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
            <p><strong>Album buscado:</strong> <?php echo htmlspecialchars($albumName); ?></p>
            <p><a href="buscadorCanciones.php" style="color: var(--color-azul);">Return to search</a></p>
        </div>
    <?php elseif (empty($albumSongs)): ?>
        <div style="text-align: center; padding: 100px 20px;">
            <h2 style="color: var(--color-naranja);">Álbum vacío</h2>
            <p>No se encontraron canciones para el álbum: <?php echo htmlspecialchars($albumName); ?></p>
            <p><a href="buscarCanciones.php" style="color: var(--color-azul);">Return to search</a></p>
        </div>
    <?php else: ?>
    
    <!-- Indicador de sección -->
    <div class="section-indicator" id="sectionIndicator">
        Section <span id="currentSection">1</span>/2
    </div>
    
    <!-- Puntos de navegación -->
    <div class="nav-dots">
        <div class="nav-dot active" data-section="0" title="Songs"></div>
        <div class="nav-dot" data-section="1" title="Reviews"></div>
    </div>
    
    <!-- Contenedor principal SIMPLIFICADO -->
    <div class="content-sections" id="contentSections">
        <!-- Sección 1: Canciones -->
        <section class="section songs-section">
            <div class="album-header">
                <h1 class="album-title"><?php echo htmlspecialchars($albumName); ?></h1>
                <?php if (!empty($albumInfo['artist'])): ?>
                    <div class="album-artist"><?php echo htmlspecialchars($albumInfo['artist']); ?></div>
                <?php endif; ?>
                <?php if (!empty($albumInfo['total_songs'])): ?>
                    <div style="color: #aaa; font-size: 16px;">
                        <?php echo $albumInfo['total_songs']; ?> songs
                    </div>
                <?php endif; ?>
            </div>
            
            <h3 class="section-title">Songs from this album</h3>
            
            <div class="songs-list">
                <?php foreach ($albumSongs as $index => $song): ?>
                    <div class="song-item <?php echo ($song['id'] == $highlightSongId) ? 'highlighted' : ''; ?>">
                        <div class="song-number"><?php echo $index + 1; ?></div>
                        <div class="song-details">
                            <div class="song-details-title">
                                <strong><?php echo htmlspecialchars($song['title'] ?? 'Sin título'); ?></strong>
                            </div>
                        </div>
                        <div class="song-duration">
                            <?php
                            $duration = $song['duration'] ?? '00:00';
                            if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $duration, $matches)) {
                                $hours = (int) $matches[1];
                                $minutes = (int) $matches[2];
                                $seconds = $matches[3];
                                if ($hours > 0) {
                                    echo sprintf('%d:%02d', $hours, $minutes);
                                } else {
                                    echo sprintf('%d:%02d', $minutes, $seconds);
                                }
                            } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $duration, $matches)) {
                                echo sprintf('%d:%02d', $matches[1], $matches[2]);
                            } else {
                                echo '00:00';
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="album-footer">
                <?php if (!empty($albumInfo['artist'])): ?>
                    <p>All songs written, produced and arranged by <?php echo htmlspecialchars($albumInfo['artist']); ?></p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Sección 2: Reviews -->
        <section class="section reviews-section">
            <h3 class="section-title">Album Reviews</h3>
            
            <?php if (!empty($albumReviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($albumReviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-avatar">
                                    <?php echo strtoupper(substr($review['username'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div class="review-user">
                                    <div class="review-username"><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></div>
                                    <div class="review-song">on "<?php echo htmlspecialchars($review['song_title'] ?? 'Unknown Song'); ?>"</div>
                                </div>
                                <?php if (!empty($review['rating'])): ?>
                                    <div class="review-rating"><?php echo number_format($review['rating'], 1); ?>★</div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($review['comment'])): ?>
                                <div class="review-comment">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($review['created_at'])): ?>
                                <div class="review-date">
                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 100px 20px; color: #aaa;">
                    <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>No reviews yet</h3>
                    <p>Be the first to review this album!</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <!-- Flechas de navegación -->
    <div class="nav-arrows">
        <button class="nav-arrow" id="prevBtn" title="Previous section">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="nav-arrow" id="nextBtn" title="Next section">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    
    <?php endif; ?>
    
    <script>
        // VERSIÓN SIMPLIFICADA DEL JAVASCRIPT
        const contentSections = document.getElementById('contentSections');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const navDots = document.querySelectorAll('.nav-dot');
        const sectionIndicator = document.getElementById('sectionIndicator');
        const currentSectionSpan = document.getElementById('currentSection');
        
        let currentSection = 0;
        const totalSections = 2;
        let isAnimating = false;
        
        // Función para navegar a una sección
        function goToSection(sectionIndex) {
            if (isAnimating || sectionIndex < 0 || sectionIndex >= totalSections) return;
            
            isAnimating = true;
            currentSection = sectionIndex;
            
            // Mover el contenedor
            contentSections.style.transform = `translateX(-${sectionIndex * 100}vw)`;
            
            // Actualizar UI
            updateNavigation();
            
            // Mostrar indicador temporalmente
            showSectionIndicator();
            
            // Resetear animación
            setTimeout(() => {
                isAnimating = false;
            }, 500);
        }
        
        // Actualizar navegación
        function updateNavigation() {
            // Actualizar puntos
            navDots.forEach((dot, index) => {
                if (index === currentSection) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            // Actualizar indicador
            currentSectionSpan.textContent = currentSection + 1;
            
            // Mostrar/ocultar flechas según posición
            prevBtn.style.opacity = currentSection === 0 ? '0.5' : '1';
            prevBtn.style.cursor = currentSection === 0 ? 'not-allowed' : 'pointer';
            
            nextBtn.style.opacity = currentSection === totalSections - 1 ? '0.5' : '1';
            nextBtn.style.cursor = currentSection === totalSections - 1 ? 'not-allowed' : 'pointer';
        }
        
        // Mostrar indicador de sección
        function showSectionIndicator() {
            sectionIndicator.style.display = 'block';
            sectionIndicator.style.opacity = '1';
            
            setTimeout(() => {
                sectionIndicator.style.opacity = '0';
                setTimeout(() => {
                    sectionIndicator.style.display = 'none';
                }, 300);
            }, 1500);
        }
        
        // Event listeners
        prevBtn.addEventListener('click', () => {
            if (currentSection > 0) {
                goToSection(currentSection - 1);
            }
        });
        
        nextBtn.addEventListener('click', () => {
            if (currentSection < totalSections - 1) {
                goToSection(currentSection + 1);
            }
        });
        
        // Puntos de navegación
        navDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                if (index !== currentSection) {
                    goToSection(index);
                }
            });
        });
        
        // Navegación con teclado
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && currentSection > 0) {
                goToSection(currentSection - 1);
            } else if (e.key === 'ArrowRight' && currentSection < totalSections - 1) {
                goToSection(currentSection + 1);
            } else if (e.key === '1' || e.key === '&') {
                goToSection(0);
            } else if (e.key === '2' || e.key === 'é') {
                goToSection(1);
            }
        });
        
        // Swipe en móvil
        let touchStartX = 0;
        let touchEndX = 0;
        const swipeThreshold = 50;
        
        contentSections.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        contentSections.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0 && currentSection < totalSections - 1) {
                    // Swipe izquierda - siguiente
                    goToSection(currentSection + 1);
                } else if (diff < 0 && currentSection > 0) {
                    // Swipe derecha - anterior
                    goToSection(currentSection - 1);
                }
            }
        }
        
        // Rueda del mouse
        let wheelTimeout;
        contentSections.addEventListener('wheel', (e) => {
            clearTimeout(wheelTimeout);
            
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                // Scroll vertical - cambiar sección
                if (e.deltaY > 0 && currentSection < totalSections - 1) {
                    // Scroll abajo - siguiente
                    goToSection(currentSection + 1);
                    e.preventDefault();
                } else if (e.deltaY < 0 && currentSection > 0) {
                    // Scroll arriba - anterior
                    goToSection(currentSection - 1);
                    e.preventDefault();
                }
            }
            
            wheelTimeout = setTimeout(() => {}, 100);
        }, { passive: false });
        
        // Auto-scroll a canción destacada
        window.addEventListener('load', function() {
            const highlightedSong = document.querySelector('.song-item.highlighted');
            if (highlightedSong) {
                setTimeout(() => {
                    highlightedSong.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
            
            // Inicializar navegación
            updateNavigation();
            
            // Forzar redibujado para evitar problemas de render
            contentSections.style.transform = 'translateX(0)';
        });
        
        // Prevenir selección de texto durante swipe
        contentSections.addEventListener('selectstart', (e) => {
            if (isAnimating) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>