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

    </main>

</body>

</html>