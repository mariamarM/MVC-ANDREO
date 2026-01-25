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
        /* Estilos específicos para About Us */
      
        
        main {
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .titlesong {
            padding: 40px 60px 20px;
        }
        
        .titlesong h1 {
            font-size: 72px;
            font-weight: 900;
            color: #ff0000;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -2px;
            line-height: 1;
        }
        
        /* Contenedores principales */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 0 60px 60px;
            position: relative;
        }
        
       
        
        /* Secciones */
        .about-section {
            border: 2px solid #ff0000;
            border-radius: 8px;
            padding: 30px;
            position: relative;
            min-height: 300px;
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
            color: #ff0000;
            margin: 0 0 20px 0;
            line-height: 1;
        }
        
        /* Texto de las secciones */
        .section-text {
            font-size: 18px;
            line-height: 1.6;
            color: #cccccc;
            margin: 0;
        }
        
        .section-text strong {
            color: #e60606;
            font-weight: 600;
        }
        
       
        
        /* Sección 02 - Estilo específico */
        .section-02 {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px 90px 32px 40px;
        }
        
        .empty-section {
            font-size: 24px;
            color: #666666;
            font-style: italic;
            text-align: center;
            padding: 40px;
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
            
            .section-left, .section-right {
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
                    Proyecto escolar sobre una biblioteca/libreria sobre musica, una especie de letterboxd y last.fm unido para llevar registro de albums y records de tus gustos musicales!
                </p>
            </div>
            
            <div class="about-section section-right section-02">
                <div class="section-number">02</div>
                <div class="empty-section">
El proyecto completo seria, posicionar eventos con relacion a los gustos del usuario, como DICE. Y asentar las animaciones, volumen de archivos respecto a las canciones, etc                </div>
            </div>
        </div>
        <div class="containerIcons">
            <img src="/img/dice.png" alt="Dice Icon" class="iconImage">
            <img src="/img/lastfm.png" alt="Last.fm Icon" class="iconImage">
            <img src="/img/github.png" alt="GitHub Icon" class="iconImage">
        </div>
                    <div class="vertical-divider"></div>

    </main>
</body>
</html> 