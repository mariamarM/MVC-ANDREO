<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Access denied</title>
    <link rel="stylesheet" href="./css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #cfcfcf;
            text-align: center;
            width: 360px;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<div class="box">
    <h2>Get logged in</h2>
    <p>You need an account to access your dashboard.</p>

    <a href="<?php echo BASE_URL; ?>login.php">Log in</a>
    <a href="<?php echo BASE_URL; ?>register.php">Create account</a>
</div>

</body>
</html>
<?php
exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="./css/app.css">
    <link rel="stylesheet" href="/views/css/views.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        main {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        section {
            margin-top: 30px;
        }
        h2 {
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #eee;
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../views/layout/nav.php'; ?>

<main>
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>!</h1>

    <section>
        <h2>Tus reviews recientes</h2>
        <ul>
<!--crear un boton de hacer una revuew de una cancion-->
            <li><a href="<?= BASE_URL ?>create_review.php">Crear una review</a></li>

            
        </ul>
    </section>

    <section>
        <h2>Tus canciones recientes escuchadas</h2>
        <ul>
            <li>:(</li>
        </ul>
    </section>
</main>

</body>
</html>
