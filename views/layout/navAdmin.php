<?php
// navAdmin.php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}
?>
<nav class="admin-nav">
    <div class="nav-container">

        <div class="user-info">
            <i class="fas fa-user fa-4x"></i>
            <div class="user-details">
                <div class="user-name">
                    <?php echo ($_SESSION['username']); ?>
                </div>
                <div class="user-email">
                    <?php echo ($_SESSION['email']); ?>
                </div>
            </div>

            <div class="user-actions">
                <div class="icon-btn" title="Notificaciones">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="icon-btn" title="Configuración">
                    <i class="fas fa-cog"></i>
                </div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                   
                </a>
            </div>
        </div>

        <ul class="nav-list">
            <li>
                <a href="<?php echo BASE_URL; ?>admin/users.php">
                    <i class="fas fa-users"></i>

                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-home"></i>

                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fas fa-cog"></i>

                </a>
            </li>
        </ul>


    </div>
</nav>

<style>
    .admin-nav {

        padding: 0 5%;
        position: fixed;
        bottom: 0;
        z-index: 1000;
    }

    .nav-container {
      display: flex;
    justify-content: flex-start;
    gap: 39%;
    max-width: 31%;
    margin: 0 4%;
    align-items: flex-start;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-brand i {
        width: 54px;
        height: 54px;
        aspect-ratio: 1/1;
        fill: #F5F5F5;
    }

    .nav-brand span {
        color: white;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 1px;
        background: linear-gradient(90deg, #e11d2e, #ff6b6b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-list {
        display: flex;
        width: 100%;
        height: 98px;
        padding: 10px 83px;
        justify-content: center;
        align-items: center;
        gap: 67px;
        border-radius: 15px;
        border: 1px solid #989898;
        background: rgba(118, 118, 118, 0.40);
        backdrop-filter: blur(40px);
        text-decoration: none;
    }

    .nav-list li {
        list-style: none;

    }

    .nav-list a {
        color: white;

        text-decoration: none;
        padding: 10px 0;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-list a i {
        font-size: 36px;
        color: #F5F5F5;
        transition: all 0.3s ease;
    }

    .nav-list a:hover {
        color: #ff6b6b;
    }

    .nav-list a:hover i {
        color: #ff6b6b;
        transform: scale(1.1);
    }

    .user-actions {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-direction: column;
    }

    .user-info {
         display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 20px;
    border-radius: 12px;
    justify-content: flex-start;

    }
</style>