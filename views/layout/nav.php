




<nav>
    <div class="containerNav">

        <ul class="nav-list">
   <li><a href="<?php echo BASE_URL; ?>dashboardUser.php">MyMusic</a></li>
<li><a href="<?php echo BASE_URL; ?>login.php">songs</a></li>
<li><a href="<?php echo BASE_URL; ?>register.php">playlists</a></li>
<li><a href="<?php echo BASE_URL; ?>about.php">lastweek</a></li>
<li><a href="<?php echo BASE_URL; ?>contact.php">about us</a></li>
  </ul>

    </div>

</nav>
<div class="userCon">
            <!-- <a href="/home.php">MyMusic</a>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a> -->
        
        <?php if (isset($_SESSION['user_id'])): ?>
    <li><a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-power-off" style="color: #d64000;"></i></a></li>

<?php endif; ?>
</div>