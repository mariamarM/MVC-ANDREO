<?php
require_once __DIR__ . '/../../../config/config.php';

// Si no tienes session_start() en config.php, añádelo aquí
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Verificar rol
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}

// Incluir el modelo y obtener datos directamente
require_once __DIR__ . '/../../../models/Admin.php';
$adminModel = new Admin();

// Obtener parámetros GET
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'usuarios';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Validar filtro
$filtros_validos = ['usuarios', 'reviews', 'canciones'];
if (!in_array($filtro, $filtros_validos)) {
    $filtro = 'usuarios';
}

// Obtener datos según el filtro
$data = [];
if ($filtro == 'usuarios') {
    if (!empty($search)) {
        $data = $adminModel->searchUsers($search);
    } else {
        $data = $adminModel->getAllUsers();
    }
} elseif ($filtro == 'reviews') {
    if (!empty($search)) {
        $data = $adminModel->searchReviews($search);
    } else {
        $data = $adminModel->getAllReviews();
    }
} elseif ($filtro == 'canciones') {
    if (!empty($search)) {
        $data = $adminModel->searchSongs($search);
    } else {
        $data = $adminModel->getAllSongs();
    }
}

$totalRegistros = is_array($data) ? count($data) : 0;
// Obtener estadísticas
try {
    $stats = $adminModel->getStats();
    if (!$stats || !is_array($stats)) {
        $stats = [
            'total_users' => 0,
            'total_reviews' => 0,
            'total_songs' => 0
        ];
    }
} catch (Exception $e) {
    $stats = [
        'total_users' => 0,
        'total_reviews' => 0,
        'total_songs' => 0
    ];
}
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'desc';

$statsArray = [
    ['label' => 'Total Users', 'value' => $stats['total_users'], 'icon' => 'fa-users', 'key' => 'total_users'],
    ['label' => 'Total Reviews', 'value' => $stats['total_reviews'], 'icon' => 'fa-comments', 'key' => 'total_reviews'],
    ['label' => 'Total Songs', 'value' => $stats['total_songs'], 'icon' => 'fa-music', 'key' => 'total_songs']
];
if ($orden == 'asc') {
    usort($statsArray, function ($a, $b) {
        return $a['value'] <=> $b['value'];
    });
} else {
    usort($statsArray, function ($a, $b) {
        return $b['value'] <=> $a['value'];
    });
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

    .contenedorTablas,
    .infoDetallada,
    .generarLyrcs,
    .crearadmin {
        border-radius: 10px;
        border: 1px solid #DA1E28;
        padding: 10px 20px;
    }

    .contenedorTablas {
        width: 75%;
        height: 600px;
        overflow-y: auto;
         scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none;
    }
.contenedorTablas::-webkit-scrollbar {
    display: none;
}
    .stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    table {
        width: 90%;
        border-collapse: collapse;
    }

    /* Estilos para la tabla con pastillas grises */
    .admin-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        margin-top: 20px;
    }

    .admin-table thead th {
        background: rgba(255, 23, 23, 0.3);
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: white;
        border: none;
        font-size: 16px;
    }

    .admin-table tbody tr {
        background: transparent;
    }

    /* Pastilla gris para cada celda */
    .admin-table tbody td {
        background: rgba(40, 40, 45, 0.8);
        padding: 15px;
        color: #e0e0e0;
        border: none;
        vertical-align: middle;
    }

    /* Esquinas redondeadas */
    .admin-table tbody td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        border-left: 2px solid rgba(255, 23, 23, 0.3);
    }

    .admin-table tbody td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        border-right: 2px solid rgba(255, 23, 23, 0.3);
    }

    /* Separador entre celdas */
    .admin-table tbody td:not(:last-child) {
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Efecto hover */
    .admin-table tbody tr:hover td {
        background: rgba(50, 50, 55, 0.9);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Badges para roles */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-admin {
        background: linear-gradient(135deg, #FF1717, #940B0B);
        color: white;
    }

    .badge-user {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

    /* Botones de acción */
    .action-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        margin: 0 4px;
    }

    .edit-btn {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

    .delete-btn {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
    }

    .stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Estilo para el select de filtro */
    .filtro-select {
        position: absolute;
        top: 12%;
        left: 66%;
        background: transparent;
        border: none;
        color: white;
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        font-weight: 500;
        padding: 8px 15px 8px 0;
        cursor: pointer;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: auto;
        min-width: 150px;
    }

    /* Flecha personalizada para el select */
    .filtro-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right center;
        background-size: 16px;
        padding-right: 30px;
    }

    /* Estilo para las opciones dentro del select */
    .filtro-select option {
        background: #1a1a1a;
        color: white;
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        padding: 10px;
    }

    /* Focus state */
    .filtro-select:focus {
        outline: none;
        box-shadow: none;
    }

    .filtro-select::-webkit-scrollbar {
        width: 8px;
    }

    .filtro-select::-webkit-scrollbar-track {
        background: #1a1a1a;
    }

    .filtro-select::-webkit-scrollbar-thumb {
        background: #FF1717;
        border-radius: 4px;
    }

    /* Para Firefox */
    .filtro-select {
        scrollbar-width: thin;
        scrollbar-color: #FF1717 #1a1a1a;
    }

    .titulo {
        color: #FFF;
        font-family: "Manrope", sans-serif;
        font-size: 24px;
        font-style: normal;
        font-weight: 400;

        letter-spacing: 0.15px;
    }

    .infoDetallada {
        width: 90%;
        height: 300px;
        text-align: center;
        padding: 20px;
        color: #FFF;
    }

    .contenedor-vertical {
        display: flex;
        flex-direction: column;
        gap: 20px;
        width: 22%;
        ;
    }

    .tituloinfo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 20px;
        font-weight: 600;
        margin-top: -20px;

    }

    .tituloinfo i {
        color: #DA1E28;
    }

    .icon {
        font-size: 70px;
        margin: 20px 0;
        color: #DA1E28;
    }

    .crearadmin {
        display: flex;
        flex-direction: row;
        font-family: 'Manrope', sans-serif;
        font-size: 18px;
        font-weight: 600;
        color: white;
        padding: 20px;

    }

    .add-admin-btn {
        background: transparent;
        /* Sin fondo */
        border: none;
        /* Sin borde */
        padding: 0;
        /* Sin padding interno */
        color: white;
        /* Texto blanco */
        font-family: 'Manrope', sans-serif;
        font-size: 21px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        text-decoration: none;
        /* Quitar subrayado si es enlace */
        box-shadow: none;
        /* Sin sombra/relieve */
    }

    .add-admin-btn .fa-plus {
        color: #FF1717;
        /* Rojo */
        font-size: 24px;
        transition: all 0.3s ease;
    }

    .crearadmin:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(255, 23, 23, 0.4);
    }



    /* Overlay del modal con efecto blurry */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Contenido del modal */
    .modal-content {
        background: rgba(30, 30, 35, 0.95);
        border-radius: 20px;
        width: 90%;
        max-width: 500px;
        border: 1px solid rgba(255, 23, 23, 0.2);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header del modal */
    .modal-header {
        background: linear-gradient(135deg, rgba(255, 23, 23, 0.2), rgba(148, 11, 11, 0.2));
        padding: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header h2 {
        color: white;
        font-family: 'Manrope', sans-serif;
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .close-modal {
        background: transparent;
        border: none;
        color: white;
        font-size: 32px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
    }

   

    /* Body del modal */
    .modal-body {
        padding: 30px;
    }

    /* Estilos del formulario */
    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        color: #e0e0e0;
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group input {
        width: 100%;
        padding: 15px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: white;
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        transition: all 0.3s;
    }

    .form-group input:focus {
        outline: none;
        border-color: #FF1717;
        box-shadow: 0 0 0 2px rgba(255, 23, 23, 0.2);
    }

    .form-group input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    /* Botones del formulario */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-cancel,
    .btn-create {
        flex: 1;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-family: 'Manrope', sans-serif;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-cancel {
        background: rgba(255, 255, 255, 0.1);
        color: #e0e0e0;
    }

    .btn-cancel:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .btn-create {
        background: linear-gradient(135deg, #FF1717, #940B0B);
        color: white;
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #ff2e2e, #a80e0e);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 23, 23, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .modal-header h2 {
            font-size: 20px;
        }
    }

    /* Añadir al final de tu CSS existente */

    /* Estilos para el menú desplegable */
    .tituloinfo {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 20px;
        font-weight: 600;
        margin-top: -20px;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: rgba(30, 30, 35, 0.95);
        border: 1px solid rgba(255, 23, 23, 0.3);
        border-radius: 8px;
        padding: 10px 0;
        min-width: 180px;
        z-index: 100;
        display: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
    }

    .dropdown-menu.active {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        color: #e0e0e0;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 15px;
    }


    .dropdown-item i {
        width: 20px;
        text-align: center;
        color: #DA1E28;
    }

    .dropdown-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 5px 0;
    }

    .tituloinfo i.fa-ellipsis-v {
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }


    /* Estilos para los items de estadísticas */
    .estadistica-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .estadistica-item:last-child {
        border-bottom: none;
    }

    .estadistica-icon {
        font-size: 24px;
        color: #DA1E28;
        width: 40px;
    }

    .estadisticas {
        display: flex;
             flex-direction: column;
                gap: 30px;
    }

    .estadistica-info {
        display: flex;
   
        gap: 30px;
        align-items: center;
        padding-left: 15px;
    }

    .estadistica-label {
        font-size: 20px;
        color: #5f5f5f;
        font-family: "Manrope", sans-serif;
        display: block;
    }

    .estadistica-value {
        font-size: 20px;
        font-weight: 600;
        color: white;
        display: block;
        color: white;
    }

    .orden-indicator {
        font-size: 12px;
        color: #DA1E28;
        margin-left: 5px;
        font-weight: bold;
    }
</style>

<body class="admin-page admin-dashboard">
    <?php include __DIR__ . '/../../../views/layout/navAdmin.php'; ?>

    <div class="admin-container">
        <div class="contenedorTablas">
            <div class="titulo">
                <p>Users, reviews y canciones</p>
            </div>
            <div class="filtro-container">

                <div class="search-container">
                    <select class="filtro-select" id="filtroSelect" onchange="changeFilter(this.value)">
                        <option value="usuarios" <?php echo $filtro == 'usuarios' ? 'selected' : ''; ?>>Usuarios</option>
                        <option value="reviews" <?php echo $filtro == 'reviews' ? 'selected' : ''; ?>>Reviews</option>
                        <option value="canciones" <?php echo $filtro == 'canciones' ? 'selected' : ''; ?>>Canciones
                        </option>
                    </select>


                </div>
            </div>

            <div class="tabla-container">
                <?php if ($filtro == 'usuarios'): ?>

                    <?php if ($totalRegistros > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span
                                                class="badge <?php echo $usuario['role'] == 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                                <?php echo ucfirst($usuario['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                                        <td>
                                            <a href="/admin/users/edit/<?php echo $usuario['id']; ?>" class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <button class="action-btn delete-btn"
                                                onclick="deleteUser(<?php echo $usuario['id']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No se encontraron usuarios</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($filtro == 'reviews'): ?>

                    <?php if ($totalRegistros > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Canción</th>
                                    <th>Rating</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $review): ?>
                                    <tr>
                                        <td><?php echo $review['id']; ?></td>
                                        <td><?php echo htmlspecialchars($review['username']); ?></td>
                                        <td><?php echo htmlspecialchars($review['song_title']); ?></td>
                                        <td>
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review['rating'] ? '★' : '☆';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($review['comment'], 0, 50)); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn delete-btn"
                                                onclick="deleteReview(<?php echo $review['id']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No se encontraron reviews</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($filtro == 'canciones'): ?>

                    <?php if ($totalRegistros > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Artista</th>
                                    <th>Álbum</th>
                                    <th>Género</th>
                                    <th>Año</th>

                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $cancion): ?>
                                    <tr>
                                        <td><?php echo $cancion['id']; ?></td>
                                        <td><?php echo htmlspecialchars($cancion['title']); ?></td>
                                        <td><?php echo htmlspecialchars($cancion['artist']); ?></td>
                                        <td><?php echo htmlspecialchars($cancion['album']); ?></td>
                                        <td><?php echo htmlspecialchars($cancion['genre']); ?></td>
                                        <td><?php echo $cancion['release_year']; ?></td>
                                        <td><button class="action-btn delete-btn"
                                                onclick="deleteSong(<?php echo $cancion['id']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No se encontraron canciones</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="contenedor-vertical">
            <!-- Reemplaza el div con clase "infoDetallada" por este código -->

            <div class="infoDetallada">
                <div class="tituloinfo">
                    <p>Detailed Info
                     
                    </p>
                    <i class="fas fa-ellipsis-v" id="dropdownToggle"></i>

                    <!-- Menú desplegable -->
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="?orden=desc<?php echo isset($_GET['filtro']) ? '&filtro=' . $_GET['filtro'] : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                            class="dropdown-item">
                            <i class="fas fa-sort-amount-down"></i> Mayor a menor
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="?orden=asc<?php echo isset($_GET['filtro']) ? '&filtro=' . $_GET['filtro'] : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                            class="dropdown-item">
                            <i class="fas fa-sort-amount-up"></i> Menor a mayor
                        </a>
                    </div>
                </div>

                <i class="fas fa-chart-line icon"></i>

                <div class="estadisticas">
                    <?php foreach ($statsArray as $stat): ?>


                        <div class="estadistica-info">
                            <span class="estadistica-label"><?php echo $stat['label']; ?></span>
                            <span class="estadistica-value"><?php echo $stat['value']; ?></span>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>

            <!-- <div class="generarLyrcs">
        </div> -->

            <div class="crearadmin">
                <!-- Botón para abrir modal -->
                <button class="add-admin-btn" id="openAdminModal">
                    <i class="fas fa-plus"></i> Crear Administrador
                </button>

                <!-- Modal para crear administrador -->
                <div class="modal-overlay" id="adminModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><i class="fas fa-user-shield"></i> Crear Nuevo Administrador</h2>
                            <button class="close-modal" id="closeAdminModal">&times;</button>
                        </div>

                        <div class="modal-body">
                            <form id="createAdminForm">
                                <div class="form-group">
                                    <label for="username">
                                        <i class="fas fa-user"></i> Nombre de Usuario
                                    </label>
                                    <input type="text" id="username" name="username"
                                        placeholder="Ingresa el nombre de usuario" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Correo Electrónico
                                    </label>
                                    <input type="email" id="email" name="email" placeholder="correo@ejemplo.com"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="password">
                                        <i class="fas fa-lock"></i> Contraseña
                                    </label>
                                    <input type="password" id="password" name="password"
                                        placeholder="Mínimo 8 caracteres" required minlength="8">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">
                                        <i class="fas fa-lock"></i> Confirmar Contraseña
                                    </label>
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        placeholder="Repite la contraseña" required>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-cancel" id="cancelAdminForm">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn-create">
                                        <i class="fas fa-user-plus"></i> Crear Administrador
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        // Añadir dentro de tu script existente, al final del document.addEventListener('DOMContentLoaded')

        // Añade este script para manejar el menú desplegable
        const dropdownToggle = document.getElementById('dropdownToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (dropdownToggle && dropdownMenu) {
            // Toggle del menú desplegable
            dropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('active');
            });

            // Cerrar el menú al hacer clic fuera
            document.addEventListener('click', function (e) {
                if (!dropdownMenu.contains(e.target) && e.target !== dropdownToggle) {
                    dropdownMenu.classList.remove('active');
                }
            });

            // Evitar que el menú se cierre al hacer clic dentro de él
            dropdownMenu.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            // Cerrar el menú al seleccionar una opción (opcional)
            const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function () {
                    dropdownMenu.classList.remove('active');
                });
            });
        }
        function changeFilter(filtro) {
            // Mantener parámetros actuales de búsqueda
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('filtro', filtro);
            window.location.search = urlParams.toString();
        }

        function deleteUser(id) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                // AJAX para eliminar usuario
                fetch(`/admin/users/delete/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar usuario');
                        console.error(error);
                    });
            }
        }

        function deleteReview(id) {
            if (confirm('¿Estás seguro de eliminar esta review?')) {
                // AJAX para eliminar review
                fetch(`/admin/reviews/delete/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar review');
                        console.error(error);
                    });
            }
        }

        function deleteSong(id) {
            if (confirm('¿Estás seguro de eliminar esta canción?')) {
                // AJAX para eliminar canción
                fetch(`/admin/songs/delete/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al eliminar canción');
                        console.error(error);
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const openModalBtn = document.getElementById('openAdminModal');
            const closeModalBtn = document.getElementById('closeAdminModal');
            const cancelBtn = document.getElementById('cancelAdminForm');
            const modal = document.getElementById('adminModal');
            const form = document.getElementById('createAdminForm');

            // Abrir modal
            openModalBtn.addEventListener('click', function () {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevenir scroll
            });

            // Cerrar modal
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                form.reset();
            }

            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Enviar formulario
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                // Validar que las contraseñas coincidan
                if (password !== confirmPassword) {
                    alert('Las contraseñas no coinciden');
                    return;
                }

                // Validar longitud de contraseña
                if (password.length < 8) {
                    alert('La contraseña debe tener al menos 8 caracteres');
                    return;
                }

                // Aquí iría la lógica para crear el administrador
                // Por ejemplo, una llamada AJAX

                console.log('Creando administrador...');
                console.log('Usuario:', document.getElementById('username').value);
                console.log('Email:', document.getElementById('email').value);

                // Simular envío exitoso
                alert('Administrador creado exitosamente');
                closeModal();
            });

            // Cerrar con Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>

</body>

</html>