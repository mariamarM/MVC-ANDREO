<?php
require_once __DIR__ . '/../config/config.php';

// Cargar modelo de canciones
require_once __DIR__ . '/../models/Cancion.php';
$cancionModel = new Cancion();

// Obtener parámetros de la URL
$albumName = isset($_GET['album']) ? urldecode($_GET['album']) : '';
$highlightSongId = $_GET['song_id'] ?? '';

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}

// Obtener todas las canciones de la base de datos
$allSongs = $cancionModel->getAll();
$albumSongs = [];

// Filtrar canciones por álbum
if (!empty($albumName)) {
    foreach ($allSongs as $song) {
        if (isset($song['album']) && $song['album'] === $albumName) {
            $albumSongs[] = $song;
        }
    }
    
    // Si hay una canción específica para destacar, moverla al principio
    if (!empty($highlightSongId)) {
        $highlightIndex = -1;
        
        // Buscar la canción por ID
        foreach ($albumSongs as $index => $song) {
            if (isset($song['id']) && $song['id'] == $highlightSongId) {
                $highlightIndex = $index;
                break;
            }
        }
        
        // Si se encontró la canción, moverla al principio
        if ($highlightIndex !== -1) {
            $highlightedSong = $albumSongs[$highlightIndex];
            unset($albumSongs[$highlightIndex]);
            array_unshift($albumSongs, $highlightedSong);
        }
    }
    
    // Obtener información del álbum (de la primera canción)
    $albumInfo = !empty($albumSongs) ? $albumSongs[0] : null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($albumName ?: 'Álbum'); ?> | Music Virtual Closet</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --color-rojo: #ff0000;
            --color-bg: #F5F5F5;
            --color-texto: #000000;
            --color-gris: #E0E0E0;
            --color-gris-oscuro: #CCCCCC;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Manrope", sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Fondo con imagen de portada y blur */
        .album-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: url('<?php echo BASE_URL; ?>img/album-cover.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(20px);
            opacity: 0.3;
        }

        .album-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: -1;
        }

        main {
            position: relative;
            z-index: 1;
            padding: 20px;
        }

        .back-button {
            display: inline-block;
            margin: 20px 0 40px;
            padding: 10px 25px;
            background: var(--color-rojo);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #e60000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .album-header {
            text-align: center;
            padding: 40px 20px;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .album-title {
            font-size: 48px;
            font-weight: 800;
            color: var(--color-rojo);
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .album-artist {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .album-year-genre {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .songs-list-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .songs-list-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-rojo);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-rojo);
        }

        .song-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .song-item.highlighted {
            background: rgba(255, 0, 0, 0.1);
            border-left: 4px solid var(--color-rojo);
            box-shadow: 0 2px 8px rgba(255, 0, 0, 0.2);
        }

        .song-item:hover {
            background: rgba(255, 0, 0, 0.05);
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
            font-size: 18px;
            font-weight: 600;
            color: #000;
            margin-bottom: 5px;
        }

        .song-details-artist {
            font-size: 14px;
            color: #666;
        }

        .song-duration {
            font-size: 16px;
            color: var(--color-rojo);
            font-weight: 600;
            min-width: 60px;
            text-align: right;
        }

        .no-album {
            text-align: center;
            padding: 100px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            max-width: 800px;
            margin: 100px auto;
        }

        .no-album h2 {
            color: var(--color-rojo);
            margin-bottom: 20px;
        }

        .no-album a {
            color: var(--color-rojo);
            text-decoration: none;
            font-weight: 600;
        }

        .no-album a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .album-title {
                font-size: 36px;
            }
            
            .album-artist {
                font-size: 20px;
            }
            
            .album-header, 
            .songs-list-container {
                padding: 20px;
                margin: 0 10px;
            }
            
            .song-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .song-duration {
                align-self: flex-end;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../views/layout/nav.php'; ?>
    
    <?php if (!empty($albumName) && !empty($albumSongs)): ?>
        <!-- Fondo con imagen -->
        <div class="album-background"></div>
        <div class="album-overlay"></div>
        
        <main>
            <a href="buscarCanciones.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Volver a búsqueda
            </a>
            
            <div class="album-header">
                <h1 class="album-title"><?php echo htmlspecialchars($albumName); ?></h1>
                <?php if (!empty($albumInfo['artist'])): ?>
                    <div class="album-artist"><?php echo htmlspecialchars($albumInfo['artist']); ?></div>
                <?php endif; ?>
                <div class="album-year-genre">
                    <?php if (!empty($albumInfo['release_year'])): ?>
                        <span>Año: <?php echo htmlspecialchars($albumInfo['release_year']); ?></span> • 
                    <?php endif; ?>
                    <?php if (!empty($albumInfo['genre'])): ?>
                        <span>Género: <?php echo htmlspecialchars($albumInfo['genre']); ?></span>
                    <?php endif; ?>
                </div>
                <p>Este álbum contiene <?php echo count($albumSongs); ?> canciones</p>
            </div>
            
            <div class="songs-list-container">
                <h3 class="songs-list-title">Lista de canciones</h3>
                
                <?php foreach ($albumSongs as $index => $song): ?>
                    <div class="song-item <?php echo ($index === 0) ? 'highlighted' : ''; ?>">
                        <div class="song-number"><?php echo $index + 1; ?></div>
                        <div class="song-details">
                            <div class="song-details-title"><?php echo htmlspecialchars($song['title'] ?? 'Sin título'); ?></div>
                            <div class="song-details-artist"><?php echo htmlspecialchars($song['artist'] ?? 'Artista desconocido'); ?></div>
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
        </main>
        
    <?php else: ?>
        <div class="no-album">
            <h2>Álbum no encontrado</h2>
            <p>No se pudo cargar la información del álbum.</p>
            <p><a href="buscarCanciones.php">Volver a la página de búsqueda</a></p>
        </div>
    <?php endif; ?>
    
    <script>
        // Añadir funcionalidad para hacer clic en canciones
        document.querySelectorAll('.song-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remover highlight de todas las canciones
                document.querySelectorAll('.song-item').forEach(song => {
                    song.classList.remove('highlighted');
                });
                
                // Añadir highlight a la canción clickeada
                this.classList.add('highlighted');
                
                // Aquí podrías agregar lógica para reproducir la canción
                const songTitle = this.querySelector('.song-details-title').textContent;
                console.log('Canción seleccionada:', songTitle);
                
                // Opcional: Scroll suave a la canción seleccionada
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    </script>
</body>
</html>