<?php

?>
<head>
    <link rel="stylesheet" href="/css/views.css">
</head>
<nav>
    <div class="containerNav">
       <div class="userCon">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="btnLog">
                    <a href="<?php echo BASE_URL; ?>user">Mi Perfil</a>
                </div>
                <div class="btnLog">
                    <a href="<?php echo BASE_URL; ?>logout">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <div class="btnLog">
                    <a href="<?php echo BASE_URL; ?>login">Log in</a>
                </div>
                <div class="btnLog">
                    <a href="<?php echo BASE_URL; ?>register">Sign up</a>
                </div>
            <?php endif; ?>
        </div>
        <ul class="nav-list">
            <li><a href="/home.php">user</a></li>
            <li><a href="/login.php">songs</a></li>
            <li><a href="/register.php">playlists</a></li>
            <li><a href="/about.php">lastweek</a></li>
            <li><a href="/contact.php">about us</a></li>
        </ul>
    </div>

</nav>