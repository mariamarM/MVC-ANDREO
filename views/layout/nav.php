

<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}
?>
<nav>
    <div class="containerNav">

        <ul class="nav-list">
   <li><a href="<?php echo BASE_URL; ?>dashboardUser.php">MyMusic</a></li>
<li><a href="<?php echo BASE_URL; ?>buscadorCanciones.php">songs</a></li>
<li><a href="<?php echo BASE_URL; ?>dashboardUser.php">playlists</a></li>
<!-- <li><a href="<?php echo BASE_URL; ?>lastfm.php">lastweek</a></li> -->
<li><a href="<?php echo BASE_URL; ?>aboutus.php">about us</a></li>
  </ul>

    </div>

</nav>
<?php if (isset($_SESSION['user_id'])): ?>
    <ul class="userCon">
        <li>
            <a href="<?= BASE_URL ?>logout.php" title="Cerrar sesión">
                <i class="fas fa-power-off"></i>
            </a>
        </li>
    </ul>
    <?php endif; ?>