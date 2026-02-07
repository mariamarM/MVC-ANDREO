<?php
require_once __DIR__ . '/../config/config.php';

// Cargar modelo de canciones
require_once __DIR__ . '/../models/Cancion.php';
$cancionModel = new Cancion();

// Obtener todas las canciones de la base de datos
$allSongs = $cancionModel->getAll();
$songs = is_array($allSongs) ? $allSongs : [];

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

    .articlehome {
        position: absolute;
        right: 10%;
        top: 15%;
    }

    section,
    h2 {
        border-radius: 4px 40px 4px 10px;
        width: 100%;
        background-color: var(--color-rojo);
        color: #FFF;
        font-family: "Manrope", sans-serif;
        font-size: 24px;
        font-style: normal;
        font-weight: 400;
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
        flex-wrap: wrap;
        justify-content: center;
        align-items: stretch;
        gap: 25px;
        padding: 0 40px;
        margin-bottom: 60px;
        max-height: 500px;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .songs-container::-webkit-scrollbar {
        display: none;
    }

    .song-card {
        background-color: white;
        width: calc(50% - 25px);
        min-width: 300px;
        max-width: 400px;
        padding: 20px;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--color-gris-oscuro);
    }

    .song-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .song-info {
        flex-grow: 1;
        margin-bottom: 15px;
    }

    .song-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-texto);
        margin-bottom: 5px;
    }

    .song-artist {
        font-size: 18px;
        color: #666;
        margin-bottom: 5px;
    }

    .song-album {
        font-size: 16px;
        color: #888;
        margin-bottom: 10px;
    }

    .song-duration {
        font-size: 18px;
        color: var(--color-rojo);
        font-weight: 600;
        text-align: right;
    }

    .song-rating {
        margin: 10px 0;
    }

    .star {
        color: var(--color-rojo);
        font-size: 20px;
    }

    .star.empty {
        color: var(--color-gris-oscuro);
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

    .song-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--color-gris);
    }

    @media (max-width: 768px) {

        h1,
        h3 {
            font-size: 80px;
        }

        .titlesong {
            font-size: 80px;
        }

        .container-filter {
            padding: 20px;
        }

        .song-card {
            width: 100%;
            max-width: 100%;
        }

        .songs-container {
            max-height: 400px;
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
            <?php if (!empty($songs)): ?>
                <?php foreach ($songs as $index => $song): ?>
                    <div class="song-card">
                        <div class="song-info">
                            <div class="song-title"><?php echo htmlspecialchars($song['title'] ?? 'Sin título'); ?></div>
                            <div class="song-artist"><?php echo htmlspecialchars($song['artist'] ?? 'Artista desconocido'); ?>
                            </div>
                            <div class="song-album"><?php echo htmlspecialchars($song['album'] ?? 'Sin álbum'); ?></div>
                            <div class="song-rating">
                                <?php
                                // Generar estrellas basadas en rating (si existe)
                                $rating = $song['rating'] ?? 0;
                                $maxStars = 5;
                                for ($i = 1; $i <= $maxStars; $i++): ?>
                                    <span class="star <?php echo $i > $rating ? 'empty' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="song-footer">
                            <div class="song-genre"><?php echo htmlspecialchars($song['genre'] ?? 'Sin género'); ?></div>
                            <div class="song-duration">
                                <?php
                                // Formatear duración
                                $duration = $song['duration'] ?? '00:00';

                                // Si la duración está en formato HH:MM:SS
                                if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $duration, $matches)) {
                                    $hours = (int) $matches[1];
                                    $minutes = (int) $matches[2];
                                    $seconds = $matches[3];

                                    // Si hay horas, mostrar en formato HH:MM:SS
                                    if ($hours > 0) {
                                        echo sprintf('%d:%02d', $hours, $minutes);
                                    }
                                    // Si no hay horas, mostrar en formato MM:SS
                                    else {
                                        echo sprintf('%d:%02d', $minutes, $seconds);
                                    }
                                }
                                // Si la duración está en formato MM:SS
                                elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $duration, $matches)) {
                                    $minutes = (int) $matches[1];
                                    $seconds = $matches[2];
                                    echo sprintf('%d:%02d', $minutes, $seconds);
                                }
                                // Si el formato no es reconocido
                                else {
                                    echo '00:00';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="song-card" style="width: 100%; text-align: center; padding: 40px;">
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
            });
        });

        // Interactividad para el botón de búsqueda
        document.getElementById('searchButton').addEventListener('click', function () {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const songCards = document.querySelectorAll('.song-card');

            if (searchTerm.trim() === '') {
                // Mostrar todas las canciones si la búsqueda está vacía
                songCards.forEach(card => {
                    card.style.display = 'flex';
                });
            } else {
                // Filtrar canciones
                songCards.forEach(card => {
                    const title = card.querySelector('.song-title').textContent.toLowerCase();
                    const artist = card.querySelector('.song-artist').textContent.toLowerCase();
                    const album = card.querySelector('.song-album').textContent.toLowerCase();
                    const genre = card.querySelector('.song-genre').textContent.toLowerCase();

                    if (title.includes(searchTerm) ||
                        artist.includes(searchTerm) ||
                        album.includes(searchTerm) ||
                        genre.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
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
    </script>
</body>

</html>