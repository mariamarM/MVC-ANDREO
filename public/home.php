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
        margin: 3% 10%;
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
                <img src="<?php echo BASE_URL; ?>images/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 2</p>
                    <p class="song-artist">Artist 2</p>
                    <p class="song-ranking">#2</p>
                    <p class="song-timer">4:20</p>
                </div>
            </div>
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>images/placeholder.jpg" alt="Song cover">
                <div class="song">
                    <p class="song-title">Song Title 3</p>
                    <p class="song-artist">Artist 3</p>
                    <p class="song-ranking">#3</p>
                    <p class="song-timer">3:15</p>
                </div>
            </div>
            <div class="musicTop">
                <img src="<?php echo BASE_URL; ?>images/placeholder.jpg" alt="Song cover">
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

</html>