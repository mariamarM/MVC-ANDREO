<!-- <?php
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

</head>
<style>
    h1,
    h3 {
        color: #DB2525;
        font-family: "Manrope", sans-serif;
        font-size: 140px;
        font-style: normal;
        font-weight: 800;
        line-height: 0;
        letter-spacing: 0.15px;
        text-align: center;
    }


    h3 {
        color: #89f5d4;
        margin: -2% 10%;
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
        background-color: #DB2525;
        color: #FFF;
        font-family: "Manrope", sans-serif;
        font-size: 24px;
        font-style: normal;
        font-weight: 400;
    }

    .song {
        display: flex;
        width: 250px;
        height: 100px;
        padding: 10px 10px 10px 0;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .song p {
        color: #DB2525;
        font-family: "Manrope", sans-serif;
        font-size: 30px;
        font-style: normal;
        font-weight: 400;
    }
</style>

<body>
    <?php
    $base_url = BASE_URL;
    require __DIR__ . '/../views/layout/nav.php';
    ?>
    <main>
        <h1>GREATEST HITS</h1>
        <h3>MVC</h3>
        <div class="containermusic">
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 1</p>
                    <p class="song-artist">Artist 1</p>
                    <p class="song-ranking">#1</p>
                    <p class="song-timer">3:45</p>
                </div>
            </div>
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 2</p>
                    <p class="song-artist">Artist 2</p>
                    <p class="song-ranking">#2</p>
                    <p class="song-timer">4:20</p>
                </div>
            </div>
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 3</p>
                    <p class="song-artist">Artist 3</p>
                    <p class="song-ranking">#3</p>
                    <p class="song-timer">3:15</p>
                </div>
            </div>
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 4</p>
                    <p class="song-artist">Artist 4</p>
                    <p class="song-ranking">#4</p>
                    <p class="song-timer">5:10</p>
                </div>
            </div>
        </div>
        <article class="articlehome">
            <section class="music-section">
                <h2>ARTISTS</h2>
            </section>
            <section class="music-section">
                <h2>ALBUMS</h2>
            </section>
            <section class="music-section">
                <h2>GENRE</h2>
            </section>
            <section class="music-section">
                <h2>POPULAR REVIEWS</h2>
            </section>
        </article>
    </main>

</body>

</html> -->

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

    <style>
        /* ESTILOS EXISTENTES */
        h1,
        h3 {
            color: #DB2525;
            font-family: "Manrope", sans-serif;
            font-size: 140px;
            font-style: normal;
            font-weight: 800;
            line-height: 0;
            letter-spacing: 0.15px;
            text-align: center;
        }

        h3 {
            color: #89f5d4;
            margin: -2% 10%;
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
            background-color: #DB2525;
            color: #FFF;
            font-family: "Manrope", sans-serif;
            font-size: 24px;
            font-style: normal;
            font-weight: 400;
        }

        .song {
            display: flex;
            width: 250px;
            height: 100px;
            padding: 10px 10px 10px 0;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .song p {
            color: #DB2525;
            font-family: "Manrope", sans-serif;
            font-size: 30px;
            font-style: normal;
            font-weight: 400;
        }

        /* ESTILOS DE LA ANIMACIÓN */
        .anim-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #DB2525; /* Rojo que coincide con tu tema */
            z-index: 9999;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .text-line {
            width: 100%;
            height: 10vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #DB2525;
            position: relative;
        }

        .text-content {
            display: flex;
            white-space: nowrap;
            animation: slideText 15s linear infinite;
        }

        .text-item {
            flex-shrink: 0;
            color: white;
            font-family: "Manrope", sans-serif;
            font-size: 5vw;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.15px;
            padding: 0 1.5rem;
        }

        /* Animaciones */
        @keyframes slideText {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        @keyframes lineEntrance {
            0% {
                transform: translateY(-100%);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(100vh);
                opacity: 0;
            }
        }

        @keyframes fillScreen {
            0% {
                height: 10vh;
            }
            100% {
                height: 100vh;
            }
        }

        @keyframes fadeOutContainer {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                visibility: hidden;
                display: none;
            }
        }

        @keyframes fadeInContent {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        /* Ocultar contenido inicialmente */
        main {
            opacity: 0;
            animation: fadeInContent 0.8s 3s forwards;
        }

        /* Asegurar que el body no tenga scroll durante la animación */
        body.animating {
            overflow: hidden;
        }
    </style>
</head>

<body class="animating">
    <!-- Contenedor de animación -->
    <div class="anim-container" id="animContainer"></div>

    <!-- Tu contenido existente (oculto inicialmente) -->
    <div id="mainContent">
        <?php
        $base_url = BASE_URL;
        require __DIR__ . '/../views/layout/nav.php';
        ?>
        <main>
            <h1>GREATEST HITS</h1>
            <h3>MVC</h3>
            <div class="containermusic">
                <div class="musicTop">
                    <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                    <div class="song">
                        <p class="song-title">Song Title 1</p>
                        <p class="song-artist">Artist 1</p>
                        <p class="song-ranking">#1</p>
                        <p class="song-timer">3:45</p>
                    </div>
                </div>
                <div class="musicTop">
                    <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                    <div class="song">
                        <p class="song-title">Song Title 2</p>
                        <p class="song-artist">Artist 2</p>
                        <p class="song-ranking">#2</p>
                        <p class="song-timer">4:20</p>
                    </div>
                </div>
                <div class="musicTop">
                    <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                    <div class="song">
                        <p class="song-title">Song Title 3</p>
                        <p class="song-artist">Artist 3</p>
                        <p class="song-ranking">#3</p>
                        <p class="song-timer">3:15</p>
                    </div>
                </div>
                <div class="musicTop">
                    <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                    <div class="song">
                        <p class="song-title">Song Title 4</p>
                        <p class="song-artist">Artist 4</p>
                        <p class="song-ranking">#4</p>
                        <p class="song-timer">5:10</p>
                    </div>
                </div>
            </div>
            <article class="articlehome">
                <section class="music-section">
                    <h2>ARTISTS</h2>
                </section>
                <section class="music-section">
                    <h2>ALBUMS</h2>
                </section>
                <section class="music-section">
                    <h2>GENRE</h2>
                </section>
                <section class="music-section">
                    <h2>POPULAR REVIEWS</h2>
                </section>
            </article>
        </main>
    </div>

    <script>
        // Script de la animación
        document.addEventListener('DOMContentLoaded', function() {
            const animContainer = document.getElementById('animContainer');
            const totalDuration = 3000; // 3 segundos
            const lineHeight = 10; // Altura de cada línea en vh
            const totalLines = Math.ceil(100 / lineHeight); // Líneas para llenar la pantalla
            
            function createAnimation() {
                // Limpiar contenido previo
                animContainer.innerHTML = '';
                
                const text = "GREATEST HITS";
                const repetitions = 15;
                
                // Calcular delay entre líneas
                const lineDelay = totalDuration / totalLines;
                
                // Crear líneas de texto
                for (let i = 0; i < totalLines; i++) {
                    const line = document.createElement('div');
                    line.className = 'text-line';
                    
                    // Crear contenido de texto
                    const textContent = document.createElement('div');
                    textContent.className = 'text-content';
                    
                    // Agregar múltiples repeticiones del texto
                    for (let j = 0; j < repetitions; j++) {
                        const textElement = document.createElement('span');
                        textElement.className = 'text-item';
                        textElement.textContent = text;
                        textContent.appendChild(textElement);
                    }
                    
                    // Duplicar el contenido para animación continua
                    const cloneContent = textContent.cloneNode(true);
                    textContent.appendChild(cloneContent);
                    
                    line.appendChild(textContent);
                    animContainer.appendChild(line);
                    
                    // Animar cada línea con delay escalonado
                    setTimeout(() => {
                        line.style.animation = `lineEntrance ${totalDuration/1000}s linear forwards`;
                        line.style.opacity = '1';
                    }, i * (lineDelay / 2));
                }
                
                // Al final, llenar toda la pantalla y desvanecer
                setTimeout(() => {
                    // Crear capa final que llena toda la pantalla
                    const fillScreen = document.createElement('div');
                    fillScreen.style.position = 'absolute';
                    fillScreen.style.top = '0';
                    fillScreen.style.left = '0';
                    fillScreen.style.width = '100%';
                    fillScreen.style.height = '10vh';
                    fillScreen.style.background = '#DB2525';
                    fillScreen.style.animation = `fillScreen 0.5s ease-out forwards`;
                    animContainer.appendChild(fillScreen);
                    
                    // Desvanecer contenedor completo
                    setTimeout(() => {
                        animContainer.style.animation = 'fadeOutContainer 0.5s forwards';
                        // Permitir scroll nuevamente
                        document.body.classList.remove('animating');
                    }, 500);
                    
                }, totalDuration);
            }
            
            // Iniciar animación al cargar la página
            createAnimation();
            
            // Opcional: Botón para repetir animación
            // Puedes agregar un botón en tu HTML con onclick="replayAnimation()"
            window.replayAnimation = function() {
                document.body.classList.add('animating');
                animContainer.style.animation = '';
                animContainer.style.opacity = '1';
                animContainer.style.visibility = 'visible';
                animContainer.style.display = 'flex';
                
                // Ocultar contenido temporalmente
                document.querySelector('main').style.opacity = '0';
                
                // Recrear animación
                setTimeout(() => {
                    createAnimation();
                    // Volver a mostrar contenido después
                    setTimeout(() => {
                        document.querySelector('main').style.animation = 'fadeInContent 0.8s 3s forwards';
                    }, 100);
                }, 100);
            };
        });
    </script>
</body>

</html>