<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Admin.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Conectar a la base de datos
try {
    $adminModel = new Admin();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Procesar según el tipo de acción
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create-song':
        processCreateSong($adminModel);
        break;
    
    case 'update-song':
        processUpdateSong($adminModel);
        break;
    
    case 'create-user':
        processCreateUser($adminModel);
        break;
    
    case 'create-admin':
        processCreateAdmin($adminModel);
        break;
    
    case 'edit-user':
        processEditUser($adminModel);
        break;
    
    case 'change-role':
        processChangeRole($adminModel);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// ============================================
// FUNCIONES DE PROCESAMIENTO
// ============================================

/**
 * Procesar creación de nueva canción
 */
function processCreateSong($adminModel) {
    // Validar campos requeridos
    $errors = [];
    
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    
    if (empty($title)) $errors[] = 'El título es requerido';
    if (empty($artist)) $errors[] = 'El artista es requerido';
    if (empty($duration) || !preg_match('/^\d{1,2}:[0-5][0-9]$/', $duration)) {
        $errors[] = 'La duración debe estar en formato mm:ss';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Preparar datos de la canción
    $songData = [
        'title' => $title,
        'artist' => $artist,
        'album' => trim($_POST['album'] ?? ''),
        'release_year' => !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null,
        'genre' => trim($_POST['genre'] ?? ''),
        'duration' => $duration . ':00', // Formato TIME de MySQL
        'file_path' => '', // Aquí procesarías el archivo subido
        'album_cover' => ''
    ];
    
    // Procesar archivo si se subió
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] === 0) {
        $uploadResult = handleFileUpload($_FILES['file_path']);
        if ($uploadResult['success']) {
            $songData['file_path'] = $uploadResult['path'];
        }
    }
    
    // Insertar en la base de datos
    try {
        $success = $adminModel->createSong($songData);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Canción creada exitosamente',
                'song_id' => $adminModel->getLastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la canción']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Procesar actualización de canción existente
 */
function processUpdateSong($adminModel) {
    $errors = [];
    
    $songId = (int)($_POST['song_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    
    if ($songId <= 0) $errors[] = 'ID de canción inválido';
    if (empty($title)) $errors[] = 'El título es requerido';
    if (empty($artist)) $errors[] = 'El artista es requerido';
    if (empty($duration) || !preg_match('/^\d{1,2}:[0-5][0-9]$/', $duration)) {
        $errors[] = 'La duración debe estar en formato mm:ss';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Preparar datos para actualización
    $songData = [
        'title' => $title,
        'artist' => $artist,
        'album' => trim($_POST['album'] ?? ''),
        'release_year' => !empty($_POST['release_year']) ? (int)$_POST['release_year'] : null,
        'genre' => trim($_POST['genre'] ?? ''),
        'duration' => $duration . ':00',
        'file_path' => '', // Mantener el existente por defecto
        'album_cover' => ''
    ];
    
    // Procesar nuevo archivo si se subió
    if (isset($_FILES['song_file']) && $_FILES['song_file']['error'] === 0) {
        $uploadResult = handleFileUpload($_FILES['song_file'], 'songs/');
        if ($uploadResult['success']) {
            $songData['file_path'] = $uploadResult['path'];
        }
    }
    
    // Actualizar en la base de datos
    try {
        $success = $adminModel->updateSong($songId, $songData);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Canción actualizada exitosamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la canción']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Procesar creación de nuevo usuario (rol: user)
 */
function processCreateUser($adminModel) {
    $errors = [];
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password_hash'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($username)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    if ($password !== $confirmPassword) $errors[] = 'Las contraseñas no coinciden';
    
    // Verificar si el usuario o email ya existen
    if (empty($errors)) {
        if ($adminModel->usernameExists($username)) {
            $errors[] = 'El nombre de usuario ya está en uso';
        }
        if ($adminModel->emailExists($email)) {
            $errors[] = 'El email ya está registrado';
        }
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Preparar datos del usuario
    $userData = [
        'username' => $username,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user' // Rol fijo para usuarios normales
    ];
    
    // Insertar en la base de datos
    try {
        $success = $adminModel->createAdminUser($userData); // Reutilizar método
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Usuario creado exitosamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Procesar creación de nuevo administrador (rol: admin)
 */
function processCreateAdmin($adminModel) {
    $errors = [];
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password_hash'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($username)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    if ($password !== $confirmPassword) $errors[] = 'Las contraseñas no coinciden';
    
    // Verificar si el usuario o email ya existen
    if (empty($errors)) {
        if ($adminModel->usernameExists($username)) {
            $errors[] = 'El nombre de usuario ya está en uso';
        }
        if ($adminModel->emailExists($email)) {
            $errors[] = 'El email ya está registrado';
        }
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Preparar datos del administrador
    $adminData = [
        'username' => $username,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'admin' // Rol fijo para administradores
    ];
    
    // Insertar en la base de datos
    try {
        $success = $adminModel->createAdminUser($adminData);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Administrador creado exitosamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el administrador']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Procesar edición de usuario existente
 */
function processEditUser($adminModel) {
    $errors = [];
    
    $userId = (int)($_POST['user_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($userId <= 0) $errors[] = 'ID de usuario inválido';
    if (empty($username)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    // Verificar si el email ya existe (excluyendo este usuario)
    if (empty($errors) && $adminModel->emailExists($email, $userId)) {
        $errors[] = 'El email ya está registrado por otro usuario';
    }
    
    // Verificar si el username ya existe (excluyendo este usuario)
    if (empty($errors) && $adminModel->usernameExists($username, $userId)) {
        $errors[] = 'El nombre de usuario ya está en uso';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Preparar datos para actualización
    $updateData = [
        'username' => $username,
        'email' => $email
    ];
    
    // Solo actualizar contraseña si se proporcionó una nueva
    if (!empty($password) && strlen($password) >= 8) {
        $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Actualizar en la base de datos
    try {
        // Necesitarás un método updateUser en tu modelo Admin
        $success = $adminModel->updateUser($userId, $updateData);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Usuario actualizado exitosamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Procesar cambio de rol de usuario
 */
function processChangeRole($adminModel) {
    $errors = [];
    
    $userId = (int)($_POST['user_id'] ?? 0);
    $newRole = trim($_POST['new_role'] ?? '');
    
    if ($userId <= 0) $errors[] = 'ID de usuario inválido';
    if (!in_array($newRole, ['user', 'admin'])) {
        $errors[] = 'Rol inválido';
    }
    
    // No permitir cambiar el rol propio
    if ($userId == $_SESSION['user_id']) {
        $errors[] = 'No puedes cambiar tu propio rol';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        return;
    }
    
    // Cambiar rol en la base de datos
    try {
        $success = $adminModel->updateUserRole($userId, $newRole);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Rol actualizado exitosamente'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el rol']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Manejar subida de archivos
 */
function handleFileUpload($file, $subdirectory = 'uploads/') {
    $uploadDir = __DIR__ . '/../../../' . $subdirectory;
    
    // Crear directorio si no existe
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Validar tipo de archivo
    $allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-_\.]/', '', basename($file['name']));
    $filePath = $uploadDir . $fileName;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'path' => $subdirectory . $fileName,
            'filename' => $fileName
        ];
    }
    
    return ['success' => false, 'message' => 'Error al subir el archivo'];
}

// Método auxiliar para obtener último ID insertado (añadir al modelo si no existe)
if (!method_exists($adminModel, 'getLastInsertId')) {
    // Podrías añadir este método a tu clase Admin
    // Por ahora usamos una solución temporal
    function getLastInsertId($adminModel) {
        // Dependiendo de tu implementación de PDO
        return $adminModel->db->lastInsertId();
    }
}
?>