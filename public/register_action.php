<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $music_genre = trim($_POST['music_genre'] ?? ''); // Añade esto si tienes el campo en el formulario
    
    // Verificar si se seleccionó el checkbox de administrador
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validar campos obligatorios
    if ($name === '' || $email === '' || $password === '') {
        header('Location: ' . BASE_URL . 'register.php?error=empty');
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . BASE_URL . 'register.php?error=email');
        exit;
    }

    // Obtener conexión a la base de datos
    $pdo = Database::getInstance();

    // Verificar si el email ya existe
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ' . BASE_URL . 'register.php?error=exists');
        exit;
    }

    // Hash de la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Determinar el rol basado en el checkbox
    $role = $is_admin ? 'admin' : 'user';

    // PRIMERO: Verifica qué campos tiene exactamente tu tabla
    // Ejecuta esto en tu base de datos para ver la estructura:
    // DESCRIBE users;
    
    // Basándome en lo que mencionaste: id, username, role, password_hash, createdAt, updated_at
    // NOTA: También mencionaste "email" pero no está en tu lista original
    
    // OPCIÓN 1: Si tu tabla tiene estos campos exactos:
    // id, username, email, password_hash, role, created_at, updated_at
    
    try {
        // Preparar la consulta según los campos que realmente tienes
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, created_at) 
                               VALUES (?, ?, ?, ?, NOW())');
        
        if ($stmt->execute([$name, $email, $passwordHash, $role])) {
            // Guardar datos en sesión
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            
            // Debug: Verifica qué se está guardando
            error_log("Usuario registrado: ID=" . $pdo->lastInsertId() . ", Nombre=" . $name . ", Rol=" . $role);
            
            // Redirigir según el rol
            if ($role === 'admin') {
                header('Location: ' . BASE_URL . '/../views/admin/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . 'dashboardUser.php');
            }
            exit;
        } else {
            // Error en la ejecución de la consulta
            error_log("Error en execute() al registrar usuario");
            header('Location: ' . BASE_URL . 'register.php?error=database');
            exit;
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        error_log("Error PDO al registrar usuario: " . $e->getMessage());
        error_log("Código de error: " . $e->getCode());
        
        // Intenta una consulta alternativa si hay error de columnas
        try {
            // OPCIÓN 2: Si falta algún campo
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, created_at) 
                                   VALUES (?, ?, ?, NOW())');
            if ($stmt->execute([$name, $email, $passwordHash])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                
                header('Location: ' . BASE_URL . 'dashboardUser.php');
                exit;
            }
        } catch (PDOException $e2) {
            error_log("Error en segunda consulta: " . $e2->getMessage());
        }
        
        header('Location: ' . BASE_URL . 'register.php?error=database');
        exit;
    }
} else {
    // Si se accede directamente sin POST, redirigir al registro
    header('Location: ' . BASE_URL . 'register.php');
    exit;
}