<?php
require_once __DIR__ . '/../config/config.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Virtual Closet</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <script src="./js/cursor-effect.js" defer></script>

</head>

<body>
    <?php
    require '../views/layout/nav.php';
    ?>
    <main>
        <div class="containermusic">
            <div class="musicTop">
                <img src="">
                <div class="song">
                    <p class="song-title"> </p>
                    <p class="song-artist"></p>
                    <p class="song-ranking"></p>
                    <p class="song-timer"></p>
                </div>
            </div>
            <div class="musicTop">
                <img src="">
                <div class="song">
                    <p class="song-title"> </p>
                    <p class="song-artist"></p>
                    <p class="song-ranking"></p>
                    <p class="song-timer"></p>
                </div>
            </div>
            <div class="musicTop">
                <img src="">
                <div class="song">
                    <p class="song-title"> </p>
                    <p class="song-artist"></p>
                    <p class="song-ranking"></p>
                    <p class="song-timer"></p>
                </div>
            </div>
            <div class="musicTop">
                <img src="">
                <div class="song">
                    <p class="song-title"> </p>
                    <p class="song-artist"></p>
                    <p class="song-ranking"></p>
                    <p class="song-timer"></p>
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