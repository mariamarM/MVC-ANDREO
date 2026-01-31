<?php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Verificar rol
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Usuarios</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>

</head>
<style>
    body {
        background: linear-gradient(236deg, #220808 63.05%, #940B0B 90.6%, #FF1717 102.38%);
        min-height: 100vh;
        color: #FFF;
        font-family: "Manrope", sans-serif;
        font-size: 18px;
        overflow: hidden;
    }

    .admin-container {
        margin: 43px 30px;
        display: flex;
        width: 95%;
        height: 664px;
        padding: 27px 21px;
        align-items: flex-start;
        align-content: flex-start;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-start;
        border-radius: 10px;
        background: rgba(15, 15, 19, 0.70);

    }
</style>

<body class="admin-page admin-dashboard">
    <?php include __DIR__ . '/../../../views/layout/navAdmin.php'; ?>


    <div class="admin-container">
        <div class="userstabla">

        </div>

        <div class="infoDetallada">

        </div>
        <div class="generarLyrcs">

        </div>
        <div class="crearadmin">
            
        </div>
    </div>

</body>

</html>