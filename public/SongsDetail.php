<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cancion.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/UserModel.php'; // Si tienes modelo User

// Inicializar variables
$error = null;
$success = null;
$albumSongs = [];
$albumReviews = [];
$albumInfo = [];
$highlightSongId = null;
$albumName = '';
$albumArtist = '';
$albumCover = '';
$isLoggedIn = false;
$currentUser = null;
$userReview = null; // Review existente del usuario

// Obtener parámetros de la URL
$albumName = isset($_GET['album']) ? urldecode($_GET['album']) : '';
$highlightSongId = isset($_GET['song_id']) ? (int)$_GET['song_id'] : null;

// Verificar sesión de usuario
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $userModel = new User(); // Si tienes modelo User
    $currentUser = $userModel->getById($_SESSION['user_id']);
}

// Validar que tenemos nombre de álbum
if (empty($albumName)) {
    $error = "No album specified";
} else {
    // Inicializar modelos
    $cancionModel = new Cancion();
    $reviewModel = new Review();
    
    try {
        // 1. OBTENER TODAS LAS CANCIONES DEL ÁLBUM
        $albumSongs = $cancionModel->getSongsByAlbum($albumName);
        
        // Si no encontramos canciones, mostrar error
        if (empty($albumSongs)) {
            $error = "No songs found for album: " . htmlspecialchars($albumName);
        } else {
            // 2. OBTENER INFORMACIÓN DEL ÁLBUM
            $firstSong = $albumSongs[0];
            $albumArtist = $firstSong['artist'] ?? 'Unknown Artist';
            
            // 3. OBTENER LA PORTADA DEL ÁLBUM
            if (!empty($firstSong['album_cover'])) {
                $albumCover = $firstSong['album_cover'];
            } else {
                foreach ($albumSongs as $song) {
                    if (!empty($song['album_cover'])) {
                        $albumCover = $song['album_cover'];
                        break;
                    }
                }
                if (empty($albumCover)) {
                    $albumCover = '/img/albums/default_album.jpg';
                }
            }
            
            $albumInfo = [
                'artist' => $albumArtist,
                'total_songs' => count($albumSongs),
                'genre' => $firstSong['genre'] ?? 'Various'
            ];
            
            // 4. Si no hay song_id destacado, usar el primero
            if (!$highlightSongId && !empty($albumSongs[0]['id'])) {
                $highlightSongId = $albumSongs[0]['id'];
            }
            
            // 5. OBTENER REVIEWS DEL ÁLBUM
            $albumReviews = $reviewModel->getReviewsByAlbum($albumName);
            
            // 6. Si el usuario está logueado, verificar si ya tiene una review
            if ($isLoggedIn && !empty($albumSongs)) {
                // Buscar si el usuario ya ha hecho una review para alguna canción del álbum
                foreach ($albumSongs as $song) {
                    $existingReview = $reviewModel->getUserReviewForSong($_SESSION['user_id'], $song['id']);
                    if ($existingReview) {
                        $userReview = $existingReview;
                        $userReview['song_title'] = $song['title'];
                        break;
                    }
                }
            }
            
            // 7. PROCESAR ENVÍO DE NUEVA REVIEW (si el usuario está logueado)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
                // Validar que tenemos los datos necesarios
                $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;
                $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
                $songId = isset($_POST['song_id']) ? intval($_POST['song_id']) : $highlightSongId;
                
                // Validaciones
                if (!$songId || $songId <= 0) {
                    $error = "Please select a song to review";
                } elseif (!$rating || $rating < 1 || $rating > 5) {
                    $error = "Please select a rating between 1 and 5 stars";
                } elseif (strlen($comment) < 10) {
                    $error = "Comment must be at least 10 characters long";
                } elseif (strlen($comment) > 1000) {
                    $error = "Comment cannot exceed 1000 characters";
                } else {
                    // Verificar que la canción pertenece al álbum
                    $songBelongsToAlbum = false;
                    foreach ($albumSongs as $song) {
                        if ($song['id'] == $songId) {
                            $songBelongsToAlbum = true;
                            $songTitle = $song['title'];
                            break;
                        }
                    }
                    
                    if (!$songBelongsToAlbum) {
                        $error = "Selected song does not belong to this album";
                    } else {
                        // Crear o actualizar la review
                        try {
                            if ($userReview) {
                                // Actualizar review existente
                                $result = $reviewModel->updateReview(
                                    $userReview['id'],
                                    $rating,
                                    $comment,
                                    $songId
                                );
                                $success = "Your review has been updated successfully!";
                            } else {
                                // Crear nueva review
                                $result = $reviewModel->createReview(
                                    $_SESSION['user_id'],
                                    $songId,
                                    $rating,
                                    $comment
                                );
                                $success = "Thank you for your review!";
                            }
                            
                            // Recargar reviews y userReview
                            $albumReviews = $reviewModel->getReviewsByAlbum($albumName);
                            if ($isLoggedIn) {
                                $userReview = $reviewModel->getUserReviewForSong($_SESSION['user_id'], $songId);
                                if ($userReview) {
                                    $userReview['song_title'] = $songTitle;
                                }
                            }
                            
                        } catch (Exception $e) {
                            $error = "Error saving review: " . $e->getMessage();
                        }
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        $error = "Error loading album data: " . $e->getMessage();
        error_log("Error in SongsDetail.php: " . $e->getMessage());
    }
}

// Asegurar que $albumName esté definida
$displayAlbumName = !empty($albumName) ? $albumName : 'Unknown Album';

// Definir BASE_URL si no está definida
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host . '/');
}

// Función para obtener la ruta de la imagen
function getImagePath($imagePath, $default = '/img/albums/default_album.jpg') {
    if (empty($imagePath)) {
        return BASE_URL . ltrim($default, '/');
    }
    if (strpos($imagePath, '/') !== 0) {
        $imagePath = '/' . $imagePath;
    }
    return BASE_URL . ltrim($imagePath, '/');
}

$albumCoverUrl = getImagePath($albumCover);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($displayAlbumName); ?> | Music Virtual Closet</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <script src="<?php echo BASE_URL; ?>/js/cursor-effect.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
:root {
    --color-rojo: #ff0000;
    --color-bg: #0a0a0a;
    --color-texto: #ffffff;
    --color-gris: #1a1a1a;
    --color-verde: #00c853;
    --color-azul: #29ECF3;
    --color-amarillo: #ffd700;
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
    overflow: hidden;
}

/* FONDO DEL ÁLBUM */
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
    background: linear-gradient(
        180deg,
        rgba(0, 0, 0, 0.9) 0%,
        rgba(0, 0, 0, 0.7) 30%,
        rgba(0, 0, 0, 0.4) 70%,
        rgba(0, 0, 0, 0.9) 100%
    );
    z-index: 1;
}

.background-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: blur(5px) brightness(0.7);
    transform: scale(1);
    z-index: 0;
}

/* BACK BUTTON */
.back-button {
    position: fixed;
    top: 30px;
    left: 30px;
    z-index: 1000;
    color: var(--color-texto);
    text-decoration: none;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    background: rgba(0, 0, 0, 0.6);
    padding: 12px 24px;
    border-radius: 25px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-weight: 500;
    letter-spacing: 1px;
}



/* CONTENEDOR PRINCIPAL */
.main-container {
    position: relative;
    z-index: 2;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* HEADER DEL ÁLBUM */
.album-header {
    padding: 120px 40px 40px;
    text-align: center;
    margin-bottom: 40px;
}

.album-title {
    font-size: 120px;
    font-weight: 900;
    color: var(--color-texto);
    margin-bottom: 10px;
    letter-spacing: -3px;
    line-height: 1;
    text-transform: uppercase;
    text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.8);
    font-family: 'Arial Black', sans-serif;
}

.album-artist {
    font-size: 24px;
    color: var(--color-texto);
    font-weight: 400;
    letter-spacing: 3px;
    text-transform: uppercase;
    opacity: 0.9;
    margin-top: 10px;
}

/* CONTENEDOR DE SECCIONES EN HORIZONTAL */
.horizontal-scroll-container {
    display: flex;
    width: 200vw;
    height: calc(100vh - 200px);
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.section {
    width: 100vw;
    height: 100%;
    padding: 0 40px;
    overflow-y: auto;
    flex-shrink: 0;
}

.section::-webkit-scrollbar {
    display: none;
}

/* SECCIÓN DE CANCIONES */
.songs-section {
    background: transparent;
}

/* TÍTULO DE LA SECCIÓN */
.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-texto);
    margin-bottom: 30px;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0.8;
    text-align: center;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: var(--color-texto);
    opacity: 0.5;
}

/* LISTA DE CANCIONES - DISEÑO MINIMALISTA */
.songs-list-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.song-item {
    display: flex;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.song-item:last-child {
    border-bottom: none;
}

.song-item:hover {
    padding-left: 10px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}

.song-item.highlighted {
    background: rgba(255, 0, 0, 0.15);
    border-left: 4px solid var(--color-rojo);
    padding-left: 16px;
}

.song-content {
    flex: 1;
    min-width: 0;
}

.song-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--color-texto);
    margin-bottom: 5px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: -0.5px;
}

.song-album-info {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 400;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 8px;
    opacity: 0.8;
}

.song-duration {
    font-size: 20px;
    color: var(--color-texto);
    font-weight: 600;
    min-width: 80px;
    text-align: right;
    letter-spacing: 1px;
}

/* FOOTER DEL ÁLBUM */
.album-footer {
    margin-top: 60px;
    padding: 30px 0;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
    line-height: 1.6;
    font-style: italic;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

/* SECCIÓN DE REVIEWS */
.reviews-section {
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    padding: 40px;
}

.reviews-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* REVIEWS HORIZONTALES */
.reviews-scroll-container {
    display: flex;
    overflow-x: auto;
    gap: 25px;
    padding: 20px 10px 40px;
    scrollbar-width: thin;
    scrollbar-color: var(--color-azul) transparent;
}

.reviews-scroll-container::-webkit-scrollbar {
    height: 6px;
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
    min-width: 300px;
    max-width: 300px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.review-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--color-azul);
}

.review-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.review-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-rojo), var(--color-azul));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.review-user {
    flex-grow: 1;
    min-width: 0;
}

.review-username {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-texto);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.review-song {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.review-rating {
    font-size: 16px;
    color: var(--color-amarillo);
    font-weight: 700;
    background: rgba(255, 215, 0, 0.1);
    padding: 4px 10px;
    border-radius: 15px;
    flex-shrink: 0;
}

.review-comment {
    font-size: 14px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 10px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border-left: 2px solid var(--color-azul);
}

.review-date {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.5);
    text-align: right;
}

/* FORMULARIO DE REVIEW */
.review-form-container {
    margin-bottom: 40px;
}

.review-form-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 30px;
    border: 1px solid var(--color-azul);
    margin-bottom: 30px;
}

.review-form-title {
    color: var(--color-azul);
    margin-bottom: 20px;
    font-size: 20px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 8px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.form-select {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s;
}

.form-select:focus {
    border-color: var(--color-azul);
}

.star-rating {
    display: flex;
    gap: 5px;
    margin-bottom: 5px;
}

.star-btn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.3);
    transition: all 0.3s;
    padding: 0;
    line-height: 1;
}

.star-btn:hover {
    transform: scale(1.2);
}

.form-textarea {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 16px;
    resize: vertical;
    min-height: 100px;
    outline: none;
    transition: border-color 0.3s;
}

.form-textarea:focus {
    border-color: var(--color-azul);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-submit {
    flex: 1;
    padding: 14px;
    background: var(--color-azul);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-submit:hover {
    background: #1cb0f6;
    transform: translateY(-2px);
}

.btn-delete {
    padding: 14px 24px;
    background: rgba(255, 0, 0, 0.2);
    color: var(--color-rojo);
    border: 1px solid var(--color-rojo);
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-delete:hover {
    background: rgba(255, 0, 0, 0.3);
    transform: translateY(-2px);
}

/* INDICADORES DE NAVEGACIÓN */
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
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.section-indicator.active {
    background: var(--color-rojo);
    transform: scale(1.5);
    border-color: white;
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.7);
}

.section-indicator:nth-child(2).active {
    background: var(--color-azul);
    box-shadow: 0 0 15px rgba(41, 236, 243, 0.7);
}

.section-indicator:hover {
    transform: scale(1.3);
    background: rgba(255, 255, 255, 0.5);
}

/* MENSAJES */
.error-message {
    background: rgba(255, 0, 0, 0.1);
    color: #ff6b6b;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid var(--color-rojo);
}

.success-message {
    background: rgba(0, 200, 83, 0.1);
    color: var(--color-verde);
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid var(--color-verde);
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.1);
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

/* LOGIN PROMPT */
.login-prompt {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    margin-bottom: 40px;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.login-prompt i {
    font-size: 36px;
    color: var(--color-azul);
    margin-bottom: 15px;
}

.login-prompt h4 {
    color: var(--color-texto);
    margin-bottom: 10px;
    font-size: 18px;
}

.login-prompt p {
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 20px;
    font-size: 14px;
}

.btn-login {
    display: inline-block;
    padding: 12px 30px;
    background: var(--color-rojo);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 14px;
}

.btn-login:hover {
    background: #ff3333;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .album-title {
        font-size: 60px;
        letter-spacing: -2px;
    }
    
    .album-header {
        padding: 100px 20px 30px;
    }
    
    .section {
        padding: 0 20px;
    }
    
    .song-title {
        font-size: 22px;
    }
    
    .song-duration {
        font-size: 18px;
    }
    
    .review-card {
        min-width: 280px;
        max-width: 280px;
    }
    
    .section-indicators {
        right: 10px;
    }
    
    .back-button {
        top: 20px;
        left: 20px;
        padding: 10px 20px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .album-title {
        font-size: 48px;
    }
    
    .album-artist {
        font-size: 18px;
    }
    
    .song-title {
        font-size: 20px;
    }
    
    .review-card {
        min-width: 260px;
        max-width: 260px;
        padding: 20px;
    }
    
    .review-form-card {
        padding: 20px;
    }
    
    .section-indicators {
        display: none; /* Ocultar en móvil muy pequeño */
    }
}
.section-counter {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.6);
    color: var(--color-texto);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    z-index: 1000;
    transition: all 0.3s ease;
}
</style>

<body>
    <?php require_once __DIR__ . '/../views/layout/nav.php'; ?>
    <div class="album-background">
        <div class="background-overlay"></div>
        <img src="<?php echo !empty($albumCover) ? BASE_URL . ltrim($albumCover, '/') : BASE_URL . 'img/albums/default_album.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($displayAlbumName); ?> Album Cover" 
             class="background-image"
             onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>img/albums/default_album.jpg';">
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
            <p><a href="buscarCanciones.php" style="color: var(--color-azul); text-decoration: underline;">Return to
                    search</a></p>
        </div>
    <?php elseif (empty($albumSongs)): ?>
        <div style="text-align: center; padding: 100px 20px; position: relative; z-index: 2;">
            <h2 style="color: var(--color-naranja);">Álbum vacío</h2>
            <p>No se encontraron canciones para el álbum: <?php echo htmlspecialchars($albumName); ?></p>
            <p><a href="buscarCanciones.php" style="color: var(--color-azul); text-decoration: underline;">Return to
                    search</a></p>
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
                           <!-- Sección 2: Reviews -->
            <section class="section reviews-section">
                <h3 class="section-title">Album Reviews</h3>
                
                <?php if ($isLoggedIn): ?>
                <!-- FORMULARIO PARA ESCRIBIR REVIEW (solo para usuarios logueados) -->
                <div class="review-form-container" style="margin-bottom: 30px;">
                    <div class="review-form-card" style="background: rgba(255, 255, 255, 0.05); border-radius: 15px; padding: 25px; border: 1px solid var(--color-azul);">
                        <h4 style="color: var(--color-azul); margin-bottom: 15px; font-size: 20px;">
                            <?php echo $userReview ? 'Edit Your Review' : 'Write a Review'; ?>
                        </h4>
                        
                        <?php if ($error && isset($_POST['submit_review'])): ?>
                            <div class="error-message" style="background: rgba(255, 0, 0, 0.1); color: #ff6b6b; padding: 10px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid var(--color-rojo);">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="success-message" style="background: rgba(0, 200, 83, 0.1); color: var(--color-verde); padding: 10px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid var(--color-verde);">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="reviewForm">
                            <!-- Seleccionar canción -->
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: #ddd; margin-bottom: 8px; font-size: 14px;">
                                    Select song to review:
                                </label>
                                <select name="song_id" required style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; font-size: 16px;">
                                    <option value="">-- Choose a song --</option>
                                    <?php foreach ($albumSongs as $song): ?>
                                        <option value="<?php echo $song['id']; ?>" 
                                                <?php echo ($userReview && $userReview['song_id'] == $song['id']) ? 'selected' : ''; ?>
                                                <?php echo (!$userReview && $song['id'] == $highlightSongId) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($song['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Sistema de rating con estrellas -->
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: #ddd; margin-bottom: 8px; font-size: 14px;">
                                    Your rating:
                                </label>
                                <div class="star-rating" style="display: flex; gap: 5px; margin-bottom: 5px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <button type="button" 
                                                class="star-btn" 
                                                data-value="<?php echo $i; ?>"
                                                style="background: none; border: none; font-size: 24px; cursor: pointer; color: #555; transition: color 0.3s;">
                                            ★
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" 
                                       value="<?php echo $userReview ? $userReview['rating'] : '0'; ?>" required>
                                <div id="ratingText" style="color: var(--color-azul); font-size: 14px; margin-top: 5px;">
                                    <?php echo $userReview ? "Current rating: " . $userReview['rating'] . " stars" : "Select a rating (1-5 stars)"; ?>
                                </div>
                            </div>
                            
                            <!-- Comentario -->
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; color: #ddd; margin-bottom: 8px; font-size: 14px;">
                                    Your comment:
                                </label>
                                <textarea name="comment" 
                                          rows="4" 
                                          placeholder="Share your thoughts about this song..."
                                          required
                                          style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; font-size: 16px; resize: vertical;"><?php echo $userReview ? htmlspecialchars($userReview['comment']) : ''; ?></textarea>
                                <div style="color: #888; font-size: 12px; margin-top: 5px;">
                                    10-1000 characters
                                </div>
                            </div>
                            
                            <!-- Botones -->
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" 
                                        name="submit_review" 
                                        style="flex: 1; padding: 12px; background: var(--color-azul); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                    <?php echo $userReview ? 'Update Review' : 'Submit Review'; ?>
                                </button>
                                
                                <?php if ($userReview): ?>
                                <button type="button" 
                                        onclick="if(confirm('Are you sure you want to delete your review?')) { location.href='?album=<?php echo urlencode($albumName); ?>&delete_review=<?php echo $userReview['id']; ?>'; }"
                                        style="padding: 12px 20px; background: rgba(255, 0, 0, 0.2); color: var(--color-rojo); border: 1px solid var(--color-rojo); border-radius: 8px; font-size: 16px; cursor: pointer; transition: background 0.3s;">
                                    Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- MENSAJE PARA USUARIOS NO LOGUEADOS -->
                <div class="login-prompt" style="background: rgba(255, 255, 255, 0.05); border-radius: 15px; padding: 25px; text-align: center; margin-bottom: 30px; border: 2px dashed rgba(255, 255, 255, 0.2);">
                    <p style="color: #aaa; margin-bottom: 20px;">Log in to write your own review for this album</p>
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       style="display: inline-block; padding: 10px 25px; background: var(--color-rojo); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; transition: transform 0.3s;">
                        Log In to Review
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- LISTA DE REVIEWS EXISTENTES -->
                <?php if (!empty($albumReviews)): ?>
                    <div class="reviews-scroll-container">
                        <?php foreach ($albumReviews as $review): ?>
                            <div class="review-card <?php echo ($userReview && $userReview['id'] == $review['id']) ? 'user-review' : ''; ?>">
                                <div class="review-header">
                                    <div class="review-avatar">
                                        <?php echo strtoupper(substr($review['username'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div class="review-user">
                                        <div class="review-username">
                                            <?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?>
                                            <?php if ($userReview && $userReview['id'] == $review['id']): ?>
                                                <span style="color: var(--color-azul); font-size: 12px; margin-left: 8px;">(Your review)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="review-song">on "<?php echo htmlspecialchars($review['song_title'] ?? $albumSongs[0]['title'] ?? 'Unknown Song'); ?>"</div>
                                    </div>
                                    <div class="review-rating"><?php echo number_format($review['rating'], 1); ?>★</div>
                                </div>
                                
                                <?php if (!empty($review['comment'])): ?>
                                    <div class="review-comment">
                                        <?php echo htmlspecialchars($review['comment']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="review-date">
                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    <?php if ($review['updated_at'] != $review['created_at']): ?>
                                        <span style="color: #777; font-size: 10px; margin-left: 5px;">(edited)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-comments"></i>
                        <h3>No reviews yet</h3>
                        <p><?php echo $isLoggedIn ? 'Be the first to review this album!' : 'No one has reviewed this album yet.'; ?></p>
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

            wheelTimeout = setTimeout(() => { }, 100);
        }, { passive: false });

        // INICIALIZAR
        document.addEventListener('DOMContentLoaded', function () {
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
        window.addEventListener('scroll', function () {
            const scrolled = window.pageYOffset;
            const background = document.querySelector('.background-image');
            if (background) {
                background.style.transform = `scale(1.1) translateY(${scrolled * 0.5}px)`;
            }
        });
    </script>
</body>

</html>