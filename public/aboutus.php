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
    <title>Music Virtual Closet ABOUT US</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>

    <style>
        .titlesong h1 {
            font-size: 100px;
            font-weight: 900;
            margin-top: 20px;
            color: #ff0000;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: -2px;
            line-height: 1;
        }

        .about-content {
            position: absolute;
            left: 10%;
            top: 20%;
            max-width: 40%;
            gap: 20px;
            display: flex;
            flex-direction: column;
        }

        .about-section {
            border: 2px solid #ff0000;
            border-radius: 8px;
            background-color: #ff0000;
            padding: 20px 30px;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
        }

        .section-left {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-radius: 20px 90px 32px 40px;

        }

        .section-right {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: 1px solid #ff0000;
        }

        /* Números de sección */
        .section-number {
            font-size: 48px;
            font-weight: 900;
            color: #FFFFFF;
            margin: 0 0 20px 0;
            line-height: 1;
        }

        /* Texto de las secciones */
        .section-text {
            font-size: 18px;
            line-height: 1.6;
            color: #FFFFFF;
            margin: 0;
        }

        .section-text strong {
            color: #ff0000;
            font-weight: 600;
        }



        /* Sección 02 - Estilo específico */
        .section-02 {

            border-radius: 20px 90px 32px 40px;
        }

        .empty-section {
            font-size: 18px;
            line-height: 1.6;
            color: #FFFFFF;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .about-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .vertical-divider {
                display: none;
            }

            .about-section {
                border-radius: 8px;
                border: 2px solid #ff0000;
            }

            .section-left,
            .section-right {
                border: 2px solid #ff0000;
                border-radius: 8px;
            }

            .titlesong h1 {
                font-size: 56px;
            }
        }

        @media (max-width: 768px) {
            .titlesong {
                padding: 30px 20px 15px;
            }

            .about-content {
                padding: 0 20px 40px;
            }

            .about-section {
                padding: 20px;
                min-height: 250px;
            }

            .titlesong h1 {
                font-size: 42px;
            }

            .section-number {
                font-size: 36px;
            }

            .section-text {
                font-size: 16px;
            }
        }
        .containerIcons {
            position: absolute;
            bottom: 23%;
            right: 15%;
            display: flex;
            gap: 40px;
        }
        .lineavertical{
            text-align: center;
width:99%;
            height:1px;
            border-top:2px solid #c2c2c2;
            position:absolute;
            bottom:23%;
        }
    </style>
</head>

<body>
    <?php
    $base_url = BASE_URL;
    require __DIR__ . '/../views/layout/nav.php';
    ?>

    <main>
        <div class="titlesong">
            <h1>ABOUT US</h1>
        </div>

        <div class="about-content">

            <div class="about-section section-left">
                <div class="section-number">01</div>
                <p class="section-text">
                    Proyecto escolar sobre una biblioteca/libreria sobre musica, una especie de letterboxd y last.fm
                    unido para llevar registro de albums y records de tus gustos musicales!
                </p>
            </div>

            <div class="about-section section-right section-02">
                <div class="section-number">02</div>
                <div class="empty-section">
                    El proyecto completo seria, posicionar eventos con relacion a los gustos del usuario, como DICE. Y
                    asentar las animaciones, volumen de archivos respecto a las canciones, etc </div>
            </div>
        </div>
        <div class="containerIcons">
            <img src="/img/dice.png" alt="Dice Icon" class="iconImage">
            <img src="/img/lastfm.png" alt="Last.fm Icon" class="iconImage">
            <img src="/img/github.png" alt="GitHub Icon" class="iconImage">
        </div>
<div class="lineavertical"></div>
    </main>
</body>

</html>