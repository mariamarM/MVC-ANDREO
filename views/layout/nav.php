<?php
// nav.php - VERSIÓN SIMPLE SIN LOGIN
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
            <li><a href="<?php echo BASE_URL; ?>aboutus.php">about us</a></li>


        </ul>
    </div>
    <li  class="nav-link" >
        <a href="#" onclick="openRagAssistant(event)">
            Get assistance
        </a>
    </li>
</nav>
<!-- Botón del asistente IA - ACCESO LIBRE -->

<!-- INCLUIR FONT AWESOME -->

<!-- INCLUIR EL POPUP DEL ASISTENTE (SIEMPRE) -->


<script>
    // Depuración en consola
    console.log("=== NAV DEBUG ===");
    console.log("✅ Nav cargado");
    console.log("✅ Botón 'Get assistance' disponible");
    console.log("✅ Función openRagAssistant:", typeof openRagAssistant);

    // Función de respaldo si algo falla
    if (typeof openRagAssistant === 'undefined') {
        console.log("⚠️  openRagAssistant no definida, creando versión de emergencia");

        window.openRagAssistant = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            console.log("🆘 Usando función de emergencia");
            alert("Asistente Musical\n\nAcceso libre - Puedes preguntar sobre música sin login.\n\nRedirigiendo a la página completa...");
            window.location.href = '/rag/ask';
            return false;
        };
    }
</script>