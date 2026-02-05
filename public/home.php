<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($songs) || !is_array($songs)) {
    // Cargar configuración
    if (!defined('BASE_URL')) {
        require_once __DIR__ . '/../config/config.php';
    }

    // Cargar modelo y obtener canciones
    require_once __DIR__ . '/../models/Cancion.php';
    $cancionModel = new Cancion();
    $allSongs = $cancionModel->getAll();
    $songs = !empty($allSongs) ? array_slice($allSongs, 0, 4) : [];

}



// 3. Asegurar que $songs es array
if (!is_array($songs)) {
    error_log("❌ ERROR: \$songs no es array, forzando array vacío");
    $songs = [];
}

// 4. Definir BASE_URL si no existe
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . "://" . $host . "/";
    define('BASE_URL', $base_url);
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . "/";


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
        :root {
            --color-rojo: #ff0000;
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
            mix-blend-mode: difference;
        }

        h3 {
            color: var(--color-rojo);
            margin: -2% 10%;
            text-align: left;

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

        .song {
            display: block;
            width: 100%;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .song p {
            margin: 0;
            padding: 0;
            line-height: 1;
            /* Altura de línea mínima */
        }

        .song-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: -2px;
            /* Ajuste negativo para eliminar espacio */
        }

        .song-artist {
            font-size: 18px;
            color: #666;
            margin-bottom: -2px;
        }

        .song-ranking {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: -2px;
        }

        .song-timer {
            font-size: 18px;
            color: #555;
        }

        /* ESTILOS DE LA ANIMACIÓN */
        .anim-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--color-rojo);
            /* Rojo que coincide con tu tema */
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
            background: var(--color-rojo);
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


        main {
            opacity: 0;
            animation: fadeInContent 0.8s 3s forwards;
        }

        body.animating {
            overflow: hidden;
        }
    </style>
</head>

<body class="animating">
    <div class="anim-container" id="animContainer"></div>

    <div id="mainContent">
        <?php
        $base_url = BASE_URL;
        require __DIR__ . '/../views/layout/nav.php';
        ?>
        <main>
            <h1>GREATEST HITS</h1>
            <h3>MVC</h3> 
            <div class="containermusic">
                <?php if (!empty($songs) && is_array($songs)): ?>

                    <?php foreach ($songs as $index => $song): ?>
                        <div class="musicTop">
                            <img src="<?php echo BASE_URL; ?>img/placeholder.jpg" alt="Song cover">
                            <div class="song">
                                <p class="song-title"><?php echo htmlspecialchars($song['title']); ?></p>
                                <p class="song-artist"><?php echo htmlspecialchars($song['artist']); ?></p>
                                <p class="song-ranking">#<?php echo $index + 1; ?></p>
                                <p class="song-timer">
                                    <?php
                                    // Formatear duración de HH:MM:SS a MM:SS
                                    $duration = $song['duration'];
                                    if (preg_match('/^(\d+):(\d{2}):(\d{2})$/', $duration, $matches)) {
                                        $hours = (int) $matches[1];
                                        $minutes = (int) $matches[2];
                                        $seconds = $matches[3];

                                        // Convertir horas a minutos
                                        if ($hours > 0) {
                                            $minutes = ($hours * 60) + $minutes;
                                        }

                                        echo $minutes . ':' . $seconds;
                                    } else {
                                        echo htmlspecialchars($duration);
                                    }
                                    ?>
                                </p>
                                <p><small>Álbum: <?php echo htmlspecialchars($song['album']); ?></small></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div style="background: #ffebee; padding: 20px; text-align: center; border: 2px solid #f44336;">
                        <h3>No hay canciones disponibles</h3>
                        <p>Tipo: <?php echo gettype($songs); ?> |
                            ¿Array?: <?php echo is_array($songs) ? 'Sí' : 'No'; ?> |
                            Elementos: <?php echo is_array($songs) ? count($songs) : 'N/A'; ?>
                        </p>
                    </div>
                <?php endif; ?>
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
        document.addEventListener('DOMContentLoaded', function () {
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
                        line.style.animation = `lineEntrance ${totalDuration / 1000}s linear forwards`;
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
                    fillScreen.style.background = 'var(--color-rojo)';
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
            window.replayAnimation = function () {
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