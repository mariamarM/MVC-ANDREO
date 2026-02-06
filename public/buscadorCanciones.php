<?php
require_once __DIR__ . '/../config/config.php';

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
        --color-azul: #29ECF3;
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
        justify-content: center;
        margin: 40px auto;
        width: 80%;
        max-width: 600px;
    }

    #searchInput {
        flex-grow: 1;
        padding: 15px 20px;
        font-size: 18px;
        border: 2px solid var(--color-rojo);
        background-color: transparent;
        color: var(--color-texto);
        border-radius: 30px 0 0 30px;
        outline: none;
    }

    #searchButton {
        background-color: var(--color-rojo);
        color: var(--color-texto);
        border: none;
        padding: 0 25px;
        border-radius: 0 30px 30px 0;
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
        background-color: var(--color-azul);
        color: var(--color-texto);
        border-color: var(--color-azul);
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
        align-items: center;
        gap: 25px;
        padding: 0 40px;
        margin-bottom: 60px;
    }

    .song-card {
        background-color: white;
        width: 100%;
        max-width: 800px;
        padding: 20px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    }

    .song-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-texto);
        margin-bottom: 5px;
    }

    .album-title {
        font-size: 18px;
        color: #666;
        margin-bottom: 10px;
    }

    .song-duration {
        font-size: 18px;
        color: var(--color-azul);
        font-weight: 600;
    }

    .song-rating {
        margin: 10px 0;
    }

    .star {
        color: var(--color-azul);
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
        background-color: var(--color-azul);
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
        
        .song-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .song-duration {
            align-self: flex-end;
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
            <input type="text" id="searchInput" placeholder="Search for songs...">
            <button class="fa-fa-search" id="searchButton"><i class="fas fa-search"></i></button>
        </div>
        
        <div class="container-filter">
            <span class="genre-tag indie-highlight">indie</span>
            <span class="genre-tag">indie</span>
            <span class="genre-tag">indie</span>
            <span class="genre-tag">metal</span>
            <span class="genre-tag">shoegaze</span>
            <span class="genre-tag">pop</span>
            <span class="genre-tag">jazz</span>
            <span class="genre-tag">bosanova</span>
            <span class="genre-tag">grunge</span>
            <span class="genre-tag">r&b</span>
            <span class="genre-tag">gothic</span>
            <span class="genre-tag">rock</span>
            <span class="genre-tag">techno</span>
            <span class="genre-tag">indie</span>
        </div>
        
        <div class="songs-container">
            <div class="song-card">
                <div class="song-info">
                    <div class="song-title">SONG TITLE</div>
                    <div class="album-title">Album title</div>
                    <div class="song-rating">
                        <span class="star">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                    </div>
                </div>
                <div class="song-duration">03:21</div>
            </div>
            
            <div class="song-card">
                <div class="song-info">
                    <div class="song-title">SONG TITLE</div>
                    <div class="album-title">Album title</div>
                    <div class="song-rating">
                        <span class="star">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                    </div>
                </div>
                <div class="song-duration">03:21</div>
            </div>
            
            <div class="song-card">
                <div class="song-info">
                    <div class="song-title">SONG Title</div>
                    <div class="album-title">Album title</div>
                    <div class="song-rating">
                        <span class="star">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                    </div>
                </div>
                <div class="song-duration">03:21</div>
            </div>
            
            <div class="song-card">
                <div class="song-info">
                    <div class="song-title">SONG Title</div>
                    <div class="album-title">Album title</div>
                    <div class="song-rating">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                </div>
                <div class="song-duration">03:21</div>
            </div>
        </div>
    </main>

    <script>
        // Interactividad para los filtros de género
        document.querySelectorAll('.genre-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                // Remover la clase active de todos los tags
                document.querySelectorAll('.genre-tag').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Agregar la clase active al tag clickeado
                this.classList.add('active');
            });
        });
        
        // Interactividad para el botón de búsqueda
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim() !== '') {
                alert(`Searching for: ${searchTerm}`);
                // Aquí normalmente enviarías la búsqueda al servidor
            }
        });
        
        // Permitir búsqueda con la tecla Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });
    </script>
</body>

</html>