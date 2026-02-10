

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
    <!-- INCLUIR EL POPUP DEL ASISTENTE -->
    <?php 
    // Incluir Font Awesome
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    
    // Incluir el popup
    $popupFile = __DIR__ . '/../../views/rag/chat_popup.php';
    if (file_exists($popupFile)) {
        include $popupFile;
    } else {
        echo '<!-- Archivo chat_popup.php no encontrado -->';
    }
    ?>
    <?php endif; ?>

    <li class="nav-item">
    <a href="<?php echo BASE_URL; ?>rag/ask" onclick="openRagChat()" class="nav-link">
        <i class="fas fa-robot"></i>
        <span class="d-none d-md-inline">Get asistence</span>
    </a>
</li>

<script>
function openRagChat() {
    // Abre el chat programáticamente
    if (typeof toggleChat === 'function') {
        if (!isChatOpen) {
            toggleChat();
        }
        // Enfocar el input
        document.getElementById('ragChatInput').focus();
    } else {
        // Si el chat no está cargado, redirigir a la página completa
        window.location.href = '/rag/ask';
    }
}
</script>