<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cancion.php';
require_once __DIR__ . '/../models/Review.php';

// Inicializar variables
$error = null;
$albumSongs = [];
$albumReviews = [];
$albumInfo = [];
$highlightSongId = null;
$albumName = '';
$albumArtist = '';

// Obtener parámetros de la URL
$albumName = isset($_GET['album']) ? urldecode($_GET['album']) : '';
$highlightSongId = isset($_GET['song_id']) ? (int)$_GET['song_id'] : null;

// Validar que tenemos nombre de álbum
if (empty($albumName)) {
    $error = "No album specified";
} else {
    // Inicializar modelos
    $cancionModel = new Cancion();
    $reviewModel = new Review();
    
    try {
        // 1. OBTENER TODAS LAS CANCIONES DEL ÁLBUM desde la tabla canciones
        $albumSongs = $cancionModel->getSongsByAlbum($albumName);
        
        // Si no encontramos canciones, mostrar error
        if (empty($albumSongs)) {
            $error = "No songs found for album: " . htmlspecialchars($albumName);
        } else {
            // 2. OBTENER INFORMACIÓN DEL ÁLBUM desde la primera canción
            $firstSong = $albumSongs[0];
            $albumArtist = $firstSong['artist'] ?? 'Unknown Artist';
            $albumInfo = [
                'artist' => $albumArtist,
                'total_songs' => count($albumSongs),
                'genre' => $firstSong['genre'] ?? 'Various'
            ];
            
            // Si no hay song_id destacado, usar el primero
            if (!$highlightSongId && !empty($albumSongs[0]['id'])) {
                $highlightSongId = $albumSongs[0]['id'];
            }
            
            // 3. OBTENER REVIEWS DEL ÁLBUM desde la tabla reviews
            $albumReviews = $reviewModel->getReviewsByAlbum($albumName);
        }
        
    } catch (Exception $e) {
        $error = "Error loading album data: " . $e->getMessage();
        error_log("Error in SongsDetail.php: " . $e->getMessage());
    }
}

// Asegurar que $albumName esté definida para el título
$displayAlbumName = !empty($albumName) ? $albumName : 'Unknown Album';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($displayAlbumName); ?> | Music Virtual Closet</title>
    
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
        
        /* FONDO CON BLUR EFFECT */
        .album-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, 
                rgba(0, 0, 0, 0.85) 0%, 
                rgba(20, 20, 20, 0.80) 50%,
                rgba(0, 0, 0, 0.85) 100%);
            z-index: 1;
        }
        
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: blur(10px) brightness(0.4);
            transform: scale(1.1);
            z-index: 0;
        }
        
        /* CONTENEDOR PRINCIPAL */
        .main-container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* SECCIONES EN SCROLL HORIZONTAL */
        .horizontal-scroll-container {
            display: flex;
            width: 200vw; /* 2 secciones */
            height: calc(100vh - 80px);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 80px;
        }
        
        .section {
            width: 100vw;
            height: 100%;
            padding: 40px;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .section::-webkit-scrollbar {
            display: none;
        }
        
        /* SECCIÓN CANCIONES */
        .songs-section {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }
        
        /* SECCIÓN REVIEWS */
        .reviews-section {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
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
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .album-artist {
            font-size: 24px;
            color: var(--color-azul);
            font-weight: 500;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-texto);
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        
        /* LISTA DE CANCIONES - AHORA HORIZONTAL */
        .songs-scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 20px 10px 40px;
            scrollbar-width: thin;
            scrollbar-color: var(--color-rojo) transparent;
        }
        
        .songs-scroll-container::-webkit-scrollbar {
            height: 8px;
        }
        
        .songs-scroll-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .songs-scroll-container::-webkit-scrollbar-thumb {
            background: var(--color-rojo);
            border-radius: 10px;
        }
        
        .song-card {
            min-width: 280px;
            max-width: 280px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            cursor: pointer;
        }
        
        .song-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--color-rojo);
            box-shadow: 0 10px 20px rgba(255, 0, 0, 0.2);
        }
        
        .song-card.highlighted {
            background: rgba(255, 0, 0, 0.15);
            border-color: var(--color-rojo);
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.3);
        }
        
        .song-number {
            font-size: 14px;
            color: var(--color-rojo);
            font-weight: 700;
            margin-bottom: 10px;
            opacity: 0.8;
        }
        
        .song-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--color-texto);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .song-duration {
            font-size: 16px;
            color: var(--color-azul);
            font-weight: 500;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* REVIEWS - SCROLL HORIZONTAL */
        .reviews-scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 25px;
            padding: 20px 10px 40px;
            scrollbar-width: thin;
            scrollbar-color: var(--color-azul) transparent;
        }
        
        .reviews-scroll-container::-webkit-scrollbar {
            height: 8px;
        }
        
        .reviews-scroll-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .reviews-scroll-container::-webkit-scrollbar-thumb {
            background: var(--color-azul);
            border-radius: 10px;
        }
        
        .review-card {
            min-width: 320px;
            max-width: 320px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--color-azul);
            box-shadow: 0 10px 20px rgba(41, 236, 243, 0.2);
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
            flex-shrink: 0;
        }
        
        .review-user {
            flex-grow: 1;
            min-width: 0;
        }
        
        .review-username {
            font-size: 18px;
            font-weight: 600;
            color: var(--color-texto);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .review-song {
            font-size: 14px;
            color: #aaa;
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .review-rating {
            font-size: 18px;
            color: var(--color-verde);
            font-weight: 700;
            background: rgba(0, 200, 83, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            flex-shrink: 0;
        }
        
        .review-comment {
            font-size: 16px;
            line-height: 1.5;
            color: #ddd;
            margin-bottom: 10px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border-left: 3px solid var(--color-azul);
        }
        
        .review-date {
            font-size: 12px;
            color: #777;
            text-align: right;
        }
        
        /* NAVEGACIÓN */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            color: var(--color-texto);
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            background: rgba(0, 0, 0, 0.7);
            padding: 12px 24px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .back-button:hover {
            color: var(--color-rojo);
            background: rgba(255, 0, 0, 0.2);
            border-color: var(--color-rojo);
            transform: translateX(-5px);
        }
        
        /* INDICADORES DE SECCIÓN */
        .section-indicators {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 1000;
        }
        
        .section-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
        }
        
        .section-indicator.active {
            background: var(--color-rojo);
            transform: scale(1.3);
            border-color: white;
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.7);
        }
        
        .section-indicator:nth-child(2).active {
            background: var(--color-azul);
            box-shadow: 0 0 15px rgba(41, 236, 243, 0.7);
        }
        
        .section-indicator:hover {
            transform: scale(1.2);
            background: rgba(255, 255, 255, 0.5);
        }
        
        .section-indicator::after {
            content: attr(data-title);
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        .section-indicator:hover::after {
            opacity: 1;
        }
        
        /* CONTADOR DE SECCIÓN */
        .section-counter {
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
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* NO DATA STATES */
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            border: 2px dashed rgba(255, 255, 255, 0.1);
            margin-top: 20px;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .no-data h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--color-texto);
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .album-title {
                font-size: 48px;
            }
            
            .section {
                padding: 20px;
            }
            
            .song-card {
                min-width: 250px;
                max-width: 250px;
                padding: 20px;
            }
            
            .review-card {
                min-width: 280px;
                max-width: 280px;
                padding: 20px;
            }
            
            .section-indicators {
                right: 10px;
            }
            
            .back-button {
                padding: 10px 20px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .album-title {
                font-size: 36px;
            }
            
            .album-artist {
                font-size: 20px;
            }
            
            .section-title {
                font-size: 24px;
            }
            
            .song-card {
                min-width: 220px;
                max-width: 220px;
            }
            
            .review-card {
                min-width: 260px;
                max-width: 260px;
            }
            
            .section-counter {
                top: 70px;
                right: 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo con blur effect -->
    <div class="album-background">
        <div class="background-overlay"></div>
        <img src="https://source.unsplash.com/random/1920x1080/?music,album,concert" 
             alt="Album Background" 
             class="background-image">
    </div>
    
    <a href="buscadorCanciones.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Search
    </a>
    
    <div class="section-counter" id="sectionCounter">
        <span id="currentSection">1</span>/2 • <span id="sectionName">Songs</span>
    </div>
    
    <?php if (!empty($error)): ?>
        <div style="text-align: center; padding: 100px 20px; position: relative; z-index: 2;">
            <h2 style="color: var(--color-rojo);">Error</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
            <p><a href="buscarCanciones.php" style="color: var(--color-azul); text-decoration: underline;">Return to search</a></p>
        </div>
    <?php elseif (empty($albumSongs)): ?>
        <div style="text-align: center; padding: 100px 20px; position: relative; z-index: 2;">
            <h2 style="color: var(--color-naranja);">Álbum vacío</h2>
            <p>No se encontraron canciones para el álbum: <?php echo htmlspecialchars($albumName); ?></p>
            <p><a href="buscarCanciones.php" style="color: var(--color-azul); text-decoration: underline;">Return to search</a></p>
        </div>
    <?php else: ?>
    
    <!-- Indicadores de sección -->
    <div class="section-indicators">
        <div class="section-indicator active" data-section="0" data-title="Songs" onclick="goToSection(0)"></div>
        <div class="section-indicator" data-section="1" data-title="Reviews" onclick="goToSection(1)"></div>
    </div>
    
    <!-- Contenedor principal con scroll horizontal -->
    <div class="main-container">
        <div class="horizontal-scroll-container" id="scrollContainer">
            <!-- Sección 1: Canciones -->
            <section class="section songs-section">
                <div class="album-header">
                    <h1 class="album-title"><?php echo htmlspecialchars($displayAlbumName); ?></h1>
                    <?php if (!empty($albumArtist)): ?>
                        <div class="album-artist"><?php echo htmlspecialchars($albumArtist); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($albumInfo['total_songs'])): ?>
                        <div style="color: #aaa; font-size: 16px; margin-top: 10px;">
                            <?php echo $albumInfo['total_songs']; ?> songs • <?php echo $albumInfo['genre'] ?? 'Various'; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h3 class="section-title">Album Tracks</h3>
                
                <?php if (!empty($albumSongs)): ?>
                    <div class="songs-scroll-container">
                        <?php foreach ($albumSongs as $index => $song): ?>
                            <div class="song-card <?php echo ($song['id'] == $highlightSongId) ? 'highlighted' : ''; ?>"
                                 onclick="location.href='?album=<?php echo urlencode($albumName); ?>&song_id=<?php echo $song['id']; ?>'">
                                <div class="song-number">Track #<?php echo $index + 1; ?></div>
                                <div class="song-title"><?php echo htmlspecialchars($song['title'] ?? 'Sin título'); ?></div>
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
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-music"></i>
                        <h3>No songs found</h3>
                        <p>This album appears to be empty</p>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Sección 2: Reviews -->
            <section class="section reviews-section">
                <h3 class="section-title">Album Reviews</h3>
                
                <?php if (!empty($albumReviews)): ?>
                    <div class="reviews-scroll-container">
                        <?php foreach ($albumReviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-avatar">
                                        <?php echo strtoupper(substr($review['username'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div class="review-user">
                                        <div class="review-username"><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></div>
                                        <div class="review-song">on "<?php echo htmlspecialchars($review['song_title'] ?? $albumSongs[0]['title'] ?? 'Unknown Song'); ?>"</div>
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
                    <div class="no-data">
                        <i class="fas fa-comments"></i>
                        <h3>No reviews yet</h3>
                        <p>Be the first to review this album!</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
    
    <?php endif; ?>
    
    <script>
        // CONFIGURACIÓN
        const scrollContainer = document.getElementById('scrollContainer');
        const sectionIndicators = document.querySelectorAll('.section-indicator');
        const sectionCounter = document.getElementById('sectionCounter');
        const currentSectionSpan = document.getElementById('currentSection');
        const sectionNameSpan = document.getElementById('sectionName');
        
        let currentSection = 0;
        const totalSections = 2;
        const sectionNames = ['Songs', 'Reviews'];
        let isAnimating = false;
        
        // FUNCIÓN PARA IR A UNA SECCIÓN
        function goToSection(sectionIndex) {
            if (isAnimating || sectionIndex < 0 || sectionIndex >= totalSections) return;
            
            isAnimating = true;
            currentSection = sectionIndex;
            
            // Mover el contenedor
            scrollContainer.style.transform = `translateX(-${sectionIndex * 100}vw)`;
            
            // Actualizar UI
            updateNavigation();
            
            // Resetear animación
            setTimeout(() => {
                isAnimating = false;
            }, 500);
        }
        
        // ACTUALIZAR NAVEGACIÓN
        function updateNavigation() {
            // Actualizar indicadores
            sectionIndicators.forEach((indicator, index) => {
                if (index === currentSection) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
            
            // Actualizar contador
            currentSectionSpan.textContent = currentSection + 1;
            sectionNameSpan.textContent = sectionNames[currentSection];
        }
        
        // NAVEGACIÓN CON TECLADO
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && currentSection > 0) {
                goToSection(currentSection - 1);
            } else if (e.key === 'ArrowRight' && currentSection < totalSections - 1) {
                goToSection(currentSection + 1);
            } else if (e.key === '1') {
                goToSection(0);
            } else if (e.key === '2') {
                goToSection(1);
            }
        });
        
        // SWIPE EN MÓVIL
        let touchStartX = 0;
        let touchEndX = 0;
        const swipeThreshold = 50;
        
        scrollContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        scrollContainer.addEventListener('touchend', (e) => {
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
        
        // SCROLL CON RUEDA DEL MOUSE
        let wheelTimeout;
        scrollContainer.addEventListener('wheel', (e) => {
            clearTimeout(wheelTimeout);
            
            // Solo cambiar sección con scroll vertical
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                if (e.deltaY > 0 && currentSection < totalSections - 1) {
                    goToSection(currentSection + 1);
                    e.preventDefault();
                } else if (e.deltaY < 0 && currentSection > 0) {
                    goToSection(currentSection - 1);
                    e.preventDefault();
                }
            }
            
            wheelTimeout = setTimeout(() => {}, 100);
        }, { passive: false });
        
        // INICIALIZAR
        document.addEventListener('DOMContentLoaded', function() {
            updateNavigation();
            
            // Destacar canción si está especificada
            const highlightedSong = document.querySelector('.song-card.highlighted');
            if (highlightedSong) {
                setTimeout(() => {
                    highlightedSong.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest',
                        inline: 'center' 
                    });
                }, 500);
            }
        });
        
        // EFECTO PARALLAX PARA EL FONDO
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const background = document.querySelector('.background-image');
            if (background) {
                background.style.transform = `scale(1.1) translateY(${scrolled * 0.5}px)`;
            }
        });
    </script>
</body>
</html>