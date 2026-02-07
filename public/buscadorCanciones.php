<?php
require_once __DIR__ . '/../config/config.php';

// Cargar modelos
require_once __DIR__ . '/../models/Cancion.php';
require_once __DIR__ . '/../models/Review.php';

$cancionModel = new Cancion();
$reviewModel = new Review();

// Obtener todas las canciones de la base de datos
$allSongs = $cancionModel->getAll();
$songs = is_array($allSongs) ? $allSongs : [];

// Obtener porcentaje de reviews positivas para cada canción
$songsWithStats = [];
foreach ($songs as $song) {
    $stats = $reviewModel->getReviewStats($song['id']);
    
    // Calcular porcentaje positivo
    $positivePercentage = 0;
    if ($stats && $stats['total'] > 0) {
        $positivePercentage = ($stats['positive'] / $stats['total']) * 100;
    }
    
    $songsWithStats[] = [
        'song' => $song,
        'stats' => $stats,
        'positive_percentage' => $positivePercentage,
        'total_reviews' => $stats['total'] ?? 0
    ];
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Virtual Closet</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<style>
    :root {
        --color-rojo: #ff0000;
        --color-bg: #F5F5F5;
        --color-texto: #302F2F;
        --color-gris: #dfdddd;
        --color-gris-oscuro: #989898;
        --color-verde: #1ead5a;
        --color-naranja: #ff9800;
        --color-rojo-oscuro: #d32f2f;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: var(--color-bg);
        color: var(--color-texto);
        font-family: "Manrope", sans-serif;
        min-height: 100vh;
        overflow: hidden;
    }

    h1,
    h3 {
        color: var(--color-rojo);
        font-family: "Manrope", sans-serif;
        font-size: 140px;
        font-style: normal;
        font-weight: 800;
        line-height: 0;
        letter-spacing: 0.15px;
        text-align: center;
    }

    h3 {
        color: #29ECF3;
        margin: 2% 10%;
        text-align: left;
        mix-blend-mode: difference;
    }

    .titlesong {
        font-size: 140px;
        font-weight: 900;
        margin-top: 40px;
        color: var(--color-rojo);
        text-align: center;
        text-transform: uppercase;
        letter-spacing: -2px;
        line-height: 1;
        padding: 40px 0 20px;
    }

    .container-search {
        display: flex;
        margin: 43px 30%;
        width: 756px;
        height: 62px;
        padding: 11px 23px 11px 693px;
        justify-content: flex-end;
        align-items: center;
        border-radius: 20px;
        border: 1px solid #DA1E28;
        background-color: transparent;
    }

    #searchInput {
        border: none;
        outline: none;
        font-size: 18px;
        width: 30%;
        color: var(--color-texto);
        position: absolute;
        left: 32%;
        background-color: transparent;
    }

    #searchButton {
        color: var(--color-rojo);
        border: none;
        cursor: pointer;
        font-size: 20px;
    }

    .container-filter {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        padding: 20px 40px;
        margin-bottom: 40px;
    }

    .genre-tag {
        background-color: var(--color-gris);
        color: var(--color-texto);
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid var(--color-gris-oscuro);
    }

    .genre-tag:hover {
        background-color: var(--color-rojo);
        color: white;
        border-color: var(--color-rojo);
    }

    .genre-tag.active {
        background-color: var(--color-rojo);
        color: white;
        border-color: var(--color-rojo);
    }

    .songs-container {
      display: flex;
    flex-direction: row;
    align-items: center;
    gap: 15px;
    padding: 0 40px;
    margin-bottom: 60px;
    max-height: 500px;
    overflow-y: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    flex-wrap: wrap;
    justify-content: center;
    }

    .songs-container::-webkit-scrollbar {
        display: none;
    }

    /* CONTENEDOR DE CANCIÓN CON PORCENTAJE */
    .song-row {
        display: flex;
        align-items: center;
        background-color: white;
        width: 20%;
        max-width: 800px;
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: 1px solid var(--color-gris-oscuro);
        text-decoration: none;
        color: inherit;
        gap: 20px;
        cursor: pointer;
        position: relative;
    }

    .song-row:hover {
        transform: translateY(-3px);
     
    }

    .song-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .song-content {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-right: 100px; /* Espacio para el badge de porcentaje */
    }

    .song-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--color-texto);
        margin-bottom: 5px;
        line-height: 1.2;
    }

    .song-artist {
        font-size: 16px;
        color: #666;
        margin-bottom: 5px;
    }

    .song-album {
        font-size: 14px;
        color: #888;
        margin-bottom: 8px;
    }

    /* PORCENTAJE DE REVIEWS POSITIVAS */
    .positive-percentage-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 10px;
        z-index: 2;
        transition: all 0.3s ease;
    }

    /* Colores según el porcentaje */
    .percentage-excellent {
        color: var(--color-verde);
    }

    .percentage-good {
        color: var(--color-naranja);
    }

    .percentage-poor {
        color: var(--color-rojo-oscuro);
    }

    .percentage-none {
        color: #9e9e9e;
    }

    .percentage-value {
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
    }

    .percentage-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
        opacity: 0.9;
    }

    /* Total reviews badge */
    .total-reviews-badge {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.05);
        color: #666;
        font-size: 8px;
        padding: 3px 8px;
        border-radius: 10px;
        font-weight: 600;
    }

    .song-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
    }

    .song-genre {
        font-size: 14px;
        color: #666;
        background: var(--color-gris);
        padding: 3px 10px;
        border-radius: 12px;
    }

    .song-duration {
        font-size: 16px;
        color: var(--color-rojo);
        font-weight: 600;
        min-width: 60px;
    }

    .indie-highlight {
        position: relative;
    }

    .indie-highlight::after {
        content: "";
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--color-rojo);
    }

    @media (max-width: 768px) {
        h1, h3 {
            font-size: 80px;
        }

        .titlesong {
            font-size: 80px;
        }

        .container-filter {
            padding: 20px;
        }

        .song-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            padding-bottom: 50px; /* Más espacio para badges en móvil */
        }

        .song-img {
            width: 60px;
            height: 60px;
        }

        .song-content {
            padding-right: 0;
            width: 100%;
        }

        .positive-percentage-badge {
            top: auto;
            bottom: 15px;
            right: 15px;
        }

        .total-reviews-badge {
            bottom: 15px;
            right: 90px; /* Mover para no solaparse con percentage badge */
        }

        .container-search {
            width: 90%;
            margin: 43px auto;
            padding: 11px 20px;
        }
        
        #searchInput {
            left: 5%;
            width: 80%;
        }
    }

    @media (max-width: 480px) {
        .container-search {
            width: 95%;
        }
        
        .songs-container {
            padding: 0 20px;
        }
        
        .positive-percentage-badge {
            min-width: 60px;
            padding: 6px 8px;
        }
        
        .percentage-value {
            font-size: 16px;
        }
    }
</style>

<body>
    <?php
    $base_url = BASE_URL;
    require __DIR__ . '/../views/layout/nav.php';
    ?>
    <main>
        <div class="titlesong">
            <h1>SEARCH YOUR</h1>
            <h3>SONG</h3>
        </div>

        <div class="container-search">
            <input type="text" id="searchInput" placeholder="Search for songs, users, albums...">
            <button class="fa-fa-search" id="searchButton"><i class="fas fa-search"></i></button>
        </div>

        <div class="container-filter">
            <span class="genre-tag indie-highlight">rap</span>
            <span class="genre-tag">alternative</span>
            <span class="genre-tag">electronica</span>
            <span class="genre-tag">metal</span>
            <span class="genre-tag">shoegaze</span>
            <span class="genre-tag">pop</span>
            <span class="genre-tag">jazz</span>
            <span class="genre-tag">bosanova</span>
            <span class="genre-tag">balada</span>
            <span class="genre-tag">r&b</span>
            <span class="genre-tag">gothic</span>
            <span class="genre-tag">rock</span>
            <span class="genre-tag">techno</span>
            <span class="genre-tag">indie</span>
        </div>

        <div class="songs-container">
            <?php if (!empty($songsWithStats)): ?>
                <?php foreach ($songsWithStats as $item): 
                    $song = $item['song'];
                    $positivePercentage = $item['positive_percentage'];
                    $totalReviews = $item['total_reviews'];
                    
                    // Determinar clase CSS según el porcentaje
                    $percentageClass = 'percentage-none';
                    if ($positivePercentage > 0) {
                        if ($positivePercentage >= 80) {
                            $percentageClass = 'percentage-excellent';
                        } elseif ($positivePercentage >= 60) {
                            $percentageClass = 'percentage-good';
                        } else {
                            $percentageClass = 'percentage-poor';
                        }
                    }
                ?>
                    <a href="SongsDetail.php?album=<?php echo urlencode($song['album'] ?? ''); ?>&song_id=<?php echo $song['id'] ?? ''; ?>" 
                       class="song-row">
                        
                        <!-- Badge de porcentaje de reviews positivas -->
                        <div class="positive-percentage-badge <?php echo $percentageClass; ?>">
                            <div class="percentage-value">
                                <?php if ($positivePercentage > 0): ?>
                                    +<?php echo number_format($positivePercentage, 1); ?>%
                                <?php else: ?>
                                    NR
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Badge de total de reviews -->
                        <?php if ($totalReviews > 0): ?>
                            <div class="total-reviews-badge">
                                <?php echo $totalReviews; ?> review<?php echo $totalReviews != 1 ? 's' : ''; ?>
                            </div>
                        <?php endif; ?>
                        
                        <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover" class="song-img">
                        
                        <div class="song-content">
                            <div class="song-title"><?php echo htmlspecialchars($song['title'] ?? 'Sin título'); ?></div>
                            <div class="song-artist"><?php echo htmlspecialchars($song['artist'] ?? 'Artista desconocido'); ?></div>
                            <div class="song-album"><?php echo htmlspecialchars($song['album'] ?? 'Sin álbum'); ?></div>
                            
                            <div class="song-meta">
                                <div class="song-genre"><?php echo htmlspecialchars($song['genre'] ?? 'Sin género'); ?></div>
                                <div class="song-duration">
                                    <?php
                                    $duration = $song['duration'] ?? '00:00';
                                    $duration = trim($duration);
                                    
                                    if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $duration, $matches)) {
                                        $hours = (int) $matches[1];
                                        $minutes = (int) $matches[2];
                                        if ($hours > 0) {
                                            echo sprintf('%d:%02d', $hours, $minutes);
                                        } else {
                                            echo sprintf('%d:%02d', $minutes, $matches[3]);
                                        }
                                    } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $duration, $matches)) {
                                        echo sprintf('%d:%02d', $matches[1], $matches[2]);
                                    } else {
                                        echo '00:00';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="song-row" style="text-align: center; padding: 40px; flex-direction: column;">
                    <h3>No hay canciones disponibles</h3>
                    <p>No se encontraron canciones en la base de datos.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Interactividad para los filtros de género
        document.querySelectorAll('.genre-tag').forEach(tag => {
            tag.addEventListener('click', function () {
                // Remover la clase active de todos los tags
                document.querySelectorAll('.genre-tag').forEach(t => {
                    t.classList.remove('active');
                });

                // Agregar la clase active al tag clickeado
                this.classList.add('active');
                
                // Filtrar canciones por género
                const genre = this.textContent.toLowerCase();
                const songRows = document.querySelectorAll('.song-row');
                
                if (genre === 'todos' || genre === 'all') {
                    songRows.forEach(row => {
                        row.style.display = 'flex';
                    });
                } else {
                    songRows.forEach(row => {
                        const songGenre = row.querySelector('.song-genre').textContent.toLowerCase();
                        if (songGenre.includes(genre)) {
                            row.style.display = 'flex';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            });
        });

        // Interactividad para el botón de búsqueda
        document.getElementById('searchButton').addEventListener('click', function () {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const songRows = document.querySelectorAll('.song-row');

            if (searchTerm.trim() === '') {
                // Mostrar todas las canciones si la búsqueda está vacía
                songRows.forEach(row => {
                    row.style.display = 'flex';
                });
            } else {
                // Filtrar canciones
                songRows.forEach(row => {
                    const title = row.querySelector('.song-title').textContent.toLowerCase();
                    const artist = row.querySelector('.song-artist').textContent.toLowerCase();
                    const album = row.querySelector('.song-album').textContent.toLowerCase();
                    const genre = row.querySelector('.song-genre').textContent.toLowerCase();

                    if (title.includes(searchTerm) ||
                        artist.includes(searchTerm) ||
                        album.includes(searchTerm) ||
                        genre.includes(searchTerm)) {
                        row.style.display = 'flex';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });

        // Permitir búsqueda con la tecla Enter
        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });
        
        // Efecto hover mejorado para porcentajes
        document.querySelectorAll('.song-row').forEach(row => {
            row.addEventListener('mouseenter', function() {
                const percentageBadge = this.querySelector('.positive-percentage-badge');
                if (percentageBadge) {
                    percentageBadge.style.transform = 'scale(1.1)';
                }
            });
            
            row.addEventListener('mouseleave', function() {
                const percentageBadge = this.querySelector('.positive-percentage-badge');
                if (percentageBadge) {
                    percentageBadge.style.transform = 'scale(1)';
                }
            });
        });
        
        // Filtrar por porcentaje de reviews (opcional - se puede activar)
        function filterByRating(minPercentage) {
            const songRows = document.querySelectorAll('.song-row');
            
            songRows.forEach(row => {
                const percentageText = row.querySelector('.percentage-value').textContent;
                const percentage = parseFloat(percentageText.replace('+', '').replace('%', ''));
                
                if (!isNaN(percentage) && percentage >= minPercentage) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>