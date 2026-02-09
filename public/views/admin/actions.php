<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Admin.php';

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

// Instanciar modelo para obtener datos
$adminModel = new Admin();
$users = $adminModel->getUsersForSelect();
$songs = $adminModel->getSongsForSelect();
$albums = $adminModel->getAlbumsForSelect();
$genres = $adminModel->getGenresForSelect();
$stats = $adminModel->getStats();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Acciones</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
   <style>
body {
    background: linear-gradient(236deg, #220808 63.05%, #940B0B 90.6%, #FF1717 102.38%);
    min-height: 100vh;
    color: #FFF;
    font-family: "Manrope", sans-serif;
    font-size: 18px;
    overflow: hidden;
    margin: 0;
    padding: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.user-name {
    font-weight: 600;
    color: #fff;
    font-size: 16px;
}

.user-email {
    font-size: 14px;
    color: #aaa;
}

.admin-badge {
    background: linear-gradient(135deg, #e11d2e, #ff6b6b);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

.adminContainer {
    margin: 39px 30px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    width: 95%;
    padding: 27px 21px;
    border-radius: 10px;
    background: rgba(15, 15, 19, 0.70);
    align-content: flex-start;
    height: auto;
    min-height: 664px;
    position: relative; /* Necesario para z-index de hijos */
}

/* MODALES CON POSICIÓN ABSOLUTA */
.modal-section {
    background: rgba(30, 30, 36, 0.9);
    border-radius: 8px;
    border: 1px solid rgba(255, 23, 23, 0.3);
    width: 30%;
    position: relative; /* Para posicionar el contenido */
    margin-bottom: 0;
    z-index: 1; /* Nivel base */
    transition: z-index 0.3s ease; /* Transición suave */
}

.modal-section.expanded {
    z-index: 100; /* Mayor z-index cuando está expandido */
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 12px 15px;
    transition: all 0.3s;
    background: rgba(40, 40, 46, 0.8);
    user-select: none;
    min-height: 50px;
    position: relative; /* Para estar sobre el contenido */
    z-index: 2; /* Header siempre visible */
}

.modal-header:hover {
    background: rgba(255, 23, 23, 0.15);
}

.modal-title {
    font-size: 16px;
    font-weight: 600;
    color: #FFF;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-title i {
    color: #FF1717;
    font-size: 14px;
}

.toggle-icon {
    font-size: 18px;
    transition: transform 0.3s ease;
    color: #FF1717;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

/* CONTENIDO FLOTANTE - POSICIÓN ABSOLUTA */
.modal-content {
    position: absolute; /* ¡IMPORTANTE! */
    top: 100%; /* Justo debajo del header */
    left: 0;
    right: 0;
    max-height: 0;
    overflow: hidden;
    background: rgba(20, 20, 25, 0.98);
    transition: max-height 0.3s ease;
    z-index: 10; /* Encima de otros elementos */
    border-radius: 0 0 8px 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7); /* Sombra para destacar */
    border-top: 1px solid transparent;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.modal-content.active {
    max-height: 350px; /* Altura máxima */
    overflow-y: auto; /* Scroll si es necesario */
    padding: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    opacity: 1;
    transform: translateY(0);
}

/* Alturas específicas para cada modal */
#newSongModal.active,
#updateSongModal.active {
    max-height: 350px;
}

#changeRoleModal.active {
    max-height: 320px;
}

#allSongsModal.active {
    max-height: 400px;
}

#createAdminModal.active {
    max-height: 320px;
}

/* Estilos de formularios (mantener compactos) */
.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #FFF;
    font-weight: 500;
    font-size: 13px;
    line-height: 1.2;
}

.form-control {
    width: 100%;
    padding: 8px 10px;
    border-radius: 5px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
    color: #FFF;
    font-size: 13px;
    transition: all 0.3s;
    box-sizing: border-box;
    line-height: 1.3;
    height: 36px;
}

.form-control:focus {
    outline: none;
    border-color: #FF1717;
    box-shadow: 0 0 0 2px rgba(255, 23, 23, 0.2);
    background: rgba(255, 255, 255, 0.15);
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
    font-size: 12px;
}

textarea.form-control {
    min-height: 70px;
    height: auto;
    padding: 8px 10px;
    line-height: 1.4;
}

.btn-submit {
    background: linear-gradient(45deg, #FF1717, #940B0B);
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 8px;
    width: 100%;
    height: 38px;
}

.btn-submit:hover {
    background: linear-gradient(45deg, #940B0B, #FF1717);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 23, 23, 0.4);
}

.btn-submit i {
    font-size: 12px;
}

.select-control {
    width: 100%;
    padding: 8px 10px;
    border-radius: 5px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
    color: #FFF;
    font-size: 13px;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 12px;
    height: 36px;
}

.select-control option {
    background: #1e1e24;
    color: #FFF;
    padding: 6px;
    font-size: 13px;
}

.file-upload {
    position: relative;
    overflow: hidden;
    border-radius: 5px;
    margin-top: 5px;
}

.file-label {
    display: block;
    padding: 8px 10px;
    background: rgba(255, 23, 23, 0.1);
    border: 2px dashed rgba(255, 23, 23, 0.5);
    border-radius: 5px;
    color: #FFF;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    font-size: 12px;
    line-height: 1.2;
}

/* Tabla compacta */
#songSearch {
    padding: 8px 10px;
    font-size: 13px;
    margin-bottom: 10px;
    height: 36px;
}

#allSongsModal table {
    font-size: 12px;
    width: 100%;
}

#allSongsModal th {
    padding: 8px 10px;
    background: rgba(255, 23, 23, 0.2);
    font-size: 11px;
    font-weight: 600;
    text-align: left;
    border-bottom: 2px solid rgba(255, 23, 23, 0.5);
}

#allSongsModal td {
    padding: 6px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Cerrar modal al hacer clic fuera */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99;
    display: none;
}

/* Responsive */
@media (max-width: 1200px) {
    .modal-section {
        width: 48%;
    }
}

@media (max-width: 768px) {
    .adminContainer {
        margin: 15px;
        width: calc(100% - 30px);
        padding: 15px;
        gap: 12px;
    }
    
    .modal-section {
        width: 100%;
    }
    
    .modal-content.active {
        max-height: 280px;
    }
    
    #newSongModal.active,
    #updateSongModal.active {
        max-height: 320px;
    }
    
    #allSongsModal.active {
        max-height: 350px;
    }
}

@media (max-width: 480px) {
    .adminContainer {
        margin: 10px;
        width: calc(100% - 20px);
        padding: 10px;
        gap: 10px;
    }
    
    .modal-content.active {
        max-height: 250px;
    }
    
    #newSongModal.active,
    #updateSongModal.active {
        max-height: 300px;
    }
    
    #allSongsModal.active {
        max-height: 320px;
    }
}
</style>
</head>

<body class="admin-page admin-dashboard">
    <?php include __DIR__ . '/../../../views/layout/navAdmin.php'; ?>

    <div class="adminContainer">
       

        <!-- Modal 1: Registrar Nueva Canción -->
        <div class="modal-section">
            <div class="modal-header" onclick="toggleModal('newSongModal', this)">
                <span class="modal-title">
                    Registrar Nueva Canción
                </span>
                <span class="toggle-icon">+</span>
            </div>
            <div class="modal-content" id="newSongModal">
                <form action="/admin/create-song" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Título de la Canción *</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Insertar título de la canción" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="artist">Artista *</label>
                        <input type="text" id="artist" name="artist" class="form-control" placeholder="Nombre del artista o banda" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="album">Álbum</label>
                        <select id="album" name="album" class="select-control">
                            <option value="">-- Seleccionar álbum existente --</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo htmlspecialchars($album); ?>">
                                    <?php echo htmlspecialchars($album); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="_new">+ Crear nuevo álbum</option>
                        </select>
                        <input type="text" id="newAlbum" name="new_album" class="form-control" placeholder="Nombre del nuevo álbum" style="display: none; margin-top: 8px;">
                    </div>
                    
                    <div class="form-group">
                        <label for="release_year">Año de Lanzamiento</label>
                        <input type="number" id="release_year" name="release_year" class="form-control" min="1900" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="genre">Género</label>
                        <select id="genre" name="genre" class="select-control">
                            <option value="">-- Seleccionar género --</option>
                            <?php foreach ($genres as $genre): ?>
                                <option value="<?php echo htmlspecialchars($genre); ?>">
                                    <?php echo htmlspecialchars($genre); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="_new">+ Añadir nuevo género</option>
                        </select>
                        <input type="text" id="newGenre" name="new_genre" class="form-control" placeholder="Nuevo género" style="display: none; margin-top: 8px;">
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Duración (mm:ss) *</label>
                        <input type="text" id="duration" name="duration" class="form-control" placeholder="03:45" required pattern="[0-9]{1,2}:[0-5][0-9]">
                    </div>
                    
                    <div class="form-group">
                        <label for="file_path">Archivo de Audio</label>
                        <div class="file-upload">
                            <label class="file-label">
                                <i class="fas fa-music"></i> Seleccionar archivo de audio
                            </label>
                            <input type="file" id="file_path" name="file_path" accept=".mp3,.wav,.ogg">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Registrar Canción
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal 2: Cambiar Rol de Usuario -->
        
        <div class="modal-section">
            <div class="modal-header" onclick="toggleModal('changeRoleModal', this)">
                <span class="modal-title">
                    Cambiar Rol de Usuario
                </span>
                <span class="toggle-icon">+</span>
            </div>
            <div class="modal-content" id="changeRoleModal">
                <form action="/admin/change-role" method="post">
                    <div class="form-group">
                        <label for="user_id">Seleccionar Usuario *</label>
                        <select id="user_id" name="user_id" class="select-control" required>
                            <option value="">-- Selecciona un usuario --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?> 
                                    (<?php echo htmlspecialchars($user['email']); ?>)
                                    - <?php echo $user['role']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_role">Nuevo Rol *</label>
                        <select id="new_role" name="new_role" class="select-control" required>
                            <option value="">-- Selecciona un rol --</option>
                            <option value="user">Usuario Normal</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Motivo del Cambio (Opcional)</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3" placeholder="Explica brevemente el motivo del cambio de rol..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-sync-alt"></i> Actualizar Rol
                    </button>
                </form>
            </div>
        </div>

        <!-- REEMPLAZA el Modal 3: Ver Todas las Canciones con esto: -->

<!-- Modal 3: Editar Usuario Existente -->
<div class="modal-section">
    <div class="modal-header" onclick="toggleModal('editUserModal', this)">
        <span class="modal-title">
            Editar Usuario
        </span>
        <span class="toggle-icon">+</span>
    </div>
    <div class="modal-content" id="editUserModal">
        <form action="/admin/update-user" method="post">
            <div class="form-group">
                <label for="edit_user_id">Seleccionar Usuario *</label>
                <select id="edit_user_id" name="user_id" class="select-control" required onchange="loadUserData(this.value)">
                    <option value="">-- Selecciona un usuario --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?> 
                            (<?php echo htmlspecialchars($user['email']); ?>)
                            - <?php echo $user['role']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_username">Nombre de Usuario *</label>
                <input type="text" id="edit_username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Correo Electrónico *</label>
                <input type="email" id="edit_email" name="email" class="form-control" required>
            </div>
            
           
            
            <div class="form-group">
                <label for="edit_password">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                <input type="password" id="edit_password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                <small style="color: rgba(255,255,255,0.6); font-size: 11px;">
                    Solo completa si quieres cambiar la contraseña
                </small>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Actualizar Usuario
            </button>
        </form>
    </div>
</div>






   <!-- Modal 5: Actualizar Canción Existente -->
        <div class="modal-section">
            <div class="modal-header" onclick="toggleModal('updateSongModal', this)">
                <span class="modal-title">
                    Actualizar Canción Existente
                </span>
                <span class="toggle-icon">+</span>
            </div>
            <div class="modal-content" id="updateSongModal">
                <form action="/admin/update-song" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="update_song_id">Seleccionar Canción *</label>
                        <select id="update_song_id" name="song_id" class="select-control" required onchange="loadSongData(this.value)">
                            <option value="">-- Selecciona una canción --</option>
                            <?php foreach ($songs as $song): ?>
                                <option value="<?php echo $song['id']; ?>">
                                    <?php echo htmlspecialchars($song['title']); ?> - 
                                    <?php echo htmlspecialchars($song['artist']); ?>
                                    <?php if ($song['album']): ?> (<?php echo htmlspecialchars($song['album']); ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_title">Título *</label>
                        <input type="text" id="update_title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_artist">Artista *</label>
                        <input type="text" id="update_artist" name="artist" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_album">Álbum</label>
                        <input type="text" id="update_album" name="album" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="update_release_year">Año</label>
                        <input type="number" id="update_release_year" name="release_year" class="form-control" min="1900" max="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="update_genre">Género</label>
                        <input type="text" id="update_genre" name="genre" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="update_duration">Duración (mm:ss) *</label>
                        <input type="text" id="update_duration" name="duration" class="form-control" required pattern="[0-9]{1,2}:[0-5][0-9]">
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-sync-alt"></i> Actualizar Canción
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal 4: Crear Nuevo Administrador -->
        <div class="modal-section">
            <div class="modal-header" onclick="toggleModal('createAdminModal', this)">
                <span class="modal-title">
                    Crear Nuevo Administrador
                </span>
                <span class="toggle-icon">+</span>
            </div>
            <div class="modal-content" id="createAdminModal">
                <form action="/admin/create-admin" method="post">
                    <div class="form-group">
                        <label for="admin_username">Nombre de Administrador *</label>
                        <input type="text" id="admin_username" name="username" class="form-control" placeholder="Ingresa el nombre de usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Correo Electrónico *</label>
                        <input type="email" id="admin_email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Contraseña *</label>
                        <input type="password" id="admin_password" name="password_hash" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repite la contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Crear Administrador
                    </button>
                </form>
            </div>
        </div>

     
        <!-- AÑADE este nuevo Modal 6: Crear Nuevo Usuario Normal -->
<div class="modal-section">
    <div class="modal-header" onclick="toggleModal('createUserModal', this)">
        <span class="modal-title">
            Crear Nuevo Usuario
        </span>
        <span class="toggle-icon">+</span>
    </div>
    <div class="modal-content" id="createUserModal">
        <form action="/admin/create-user" method="post">
            <div class="form-group">
                <label for="new_username">Nombre de Usuario *</label>
                <input type="text" id="new_username" name="username" class="form-control" placeholder="Ingresa el nombre de usuario" required>
            </div>
            
            <div class="form-group">
                <label for="new_email">Correo Electrónico *</label>
                <input type="email" id="new_email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Contraseña *</label>
                <input type="password" id="new_password" name="password_hash" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
            
            <div class="form-group">
                <label for="new_confirm_password">Confirmar Contraseña *</label>
                <input type="password" id="new_confirm_password" name="confirm_password" class="form-control" placeholder="Repite la contraseña" required>
            </div>
            
          
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Crear Usuario
            </button>
        </form>
    </div>
</div>
    </div>

 <script>
    let activeModal = null;
    
    function toggleModal(modalId, headerElement) {
        const modal = document.getElementById(modalId);
        const modalSection = headerElement.closest('.modal-section');
        const toggleIcon = headerElement.querySelector('.toggle-icon');
        
        // Si hay un modal activo y no es este, cerrarlo
        if (activeModal && activeModal !== modal) {
            closeModal(activeModal);
        }
        
        // Alternar el modal actual
        if (modal.classList.contains('active')) {
            closeModal(modal);
            activeModal = null;
        } else {
            openModal(modal, modalSection, toggleIcon);
            activeModal = modal;
        }
    }
    
    function openModal(modal, modalSection, toggleIcon) {
        modal.classList.add('active');
        modalSection.classList.add('expanded');
        toggleIcon.textContent = '−';
        toggleIcon.style.transform = 'rotate(180deg)';
        
        // Crear backdrop si no existe
        let backdrop = document.getElementById('modalBackdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'modalBackdrop';
            backdrop.className = 'modal-backdrop';
            backdrop.onclick = closeAllModals;
            document.body.appendChild(backdrop);
        }
        backdrop.style.display = 'block';
    }
    
    function closeModal(modal) {
        if (!modal) return;
        
        const modalSection = modal.closest('.modal-section');
        const toggleIcon = modalSection.querySelector('.toggle-icon');
        
        modal.classList.remove('active');
        modalSection.classList.remove('expanded');
        toggleIcon.textContent = '+';
        toggleIcon.style.transform = 'rotate(0deg)';
    }
    
    function closeAllModals() {
        document.querySelectorAll('.modal-content.active').forEach(modal => {
            closeModal(modal);
        });
        
        // Ocultar backdrop
        const backdrop = document.getElementById('modalBackdrop');
        if (backdrop) {
            backdrop.style.display = 'none';
        }
        
        activeModal = null;
    }
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    
    // Función para filtrar canciones
    function filterSongs() {
        const input = document.getElementById('songSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('songList');
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                if (cells[j] && cells[j].textContent.toLowerCase().includes(filter)) {
                    found = true;
                    break;
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    }
    
    // Manejar la selección de nuevo álbum
    document.getElementById('album')?.addEventListener('change', function() {
        const newAlbumInput = document.getElementById('newAlbum');
        if (this.value === '_new') {
            newAlbumInput.style.display = 'block';
            newAlbumInput.required = true;
        } else {
            newAlbumInput.style.display = 'none';
            newAlbumInput.required = false;
        }
    });
    
    // Manejar la selección de nuevo género
    document.getElementById('genre')?.addEventListener('change', function() {
        const newGenreInput = document.getElementById('newGenre');
        if (this.value === '_new') {
            newGenreInput.style.display = 'block';
            newGenreInput.required = true;
        } else {
            newGenreInput.style.display = 'none';
            newGenreInput.required = false;
        }
    });
    
    // Validación de formularios
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#FF1717';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Validar confirmación de contraseña
            const password = this.querySelector('input[name="password_hash"]');
            const confirmPassword = this.querySelector('input[name="confirm_password"]');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                confirmPassword.style.borderColor = '#FF1717';
                alert('Las contraseñas no coinciden.');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos correctamente.');
            }
        });
    });
</script>
</body>
</html>