<?php
// /var/www/html/views/reviews/create.php - VERSIÓN CORREGIDA
require_once dirname(__DIR__, 3) . '/Helpers/WebhookHelper.php';
// 1. CONFIGURACIÓN DE ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 2. INCLUIR CONFIGURACIÓN PRIMERO (IMPORTANTE)
$configFile = dirname(__DIR__, 3) . '/config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
    error_log("✅ config.php cargado desde: $configFile");
} else {
    error_log("❌ config.php NO encontrado en: $configFile");
    die("Error de configuración");
}

// 3. INCLUIR DATABASE DESPUÉS de config
require_once dirname(__DIR__, 3) . '/config/Database.php';

// 4. VERIFICAR SESIÓN - NO necesitas session_start() porque ya está en config.php
error_log("=== CREATE.PH INICIADO ===");
error_log("Session ID: " . session_id());
error_log("User ID en sesión: " . ($_SESSION['user_id'] ?? 'NO'));
error_log("BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NO DEFINIDO'));

// 5. Verificar si es una petición desde modal
$isModal = isset($_POST['modal_submit']);
error_log("Es modal: " . ($isModal ? 'Sí' : 'No'));
error_log("POST data: " . print_r($_POST, true));

// 6. VERIFICAR SESIÓN DEL USUARIO
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    error_log("❌ Usuario NO logueado o sesión vacía");
    
    if ($isModal) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Debes iniciar sesión para crear una review',
            'redirect' => BASE_URL . 'login.php'
        ]);
        exit;
    }
    
    // Redirección normal
    $_SESSION['error'] = 'Debes iniciar sesión para crear una review';
    if (defined('BASE_URL')) {
        header('Location: ' . BASE_URL . 'login.php');
    } else {
        header('Location: /login.php');
    }
    exit;
}

error_log("✅ Usuario logueado: ID=" . $_SESSION['user_id'] . ", Nombre=" . ($_SESSION['user_name'] ?? 'NO'));

// 7. OBTENER CONEXIÓN PDO
try {
    $pdo = Database::getInstance();
    
    if ($pdo === null) {
        throw new Exception("No se pudo obtener la conexión PDO");
    }
    
    error_log("✅ Conexión PDO obtenida exitosamente");
    
    // Probar conexión
    $test = $pdo->query("SELECT 1")->fetch();
    error_log("✅ Test query ejecutada");
    
} catch (Exception $e) {
    error_log("❌ Error obteniendo conexión PDO: " . $e->getMessage());
    
    if ($isModal) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]);
        exit;
    } else {
        die("Error de conexión a la base de datos: " . htmlspecialchars($e->getMessage()));
    }
}

// 8. PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_review'])) {
    error_log("✅ Procesando creación de review");
    
    // Configurar headers para JSON si es modal
    if ($isModal) {
        header('Content-Type: application/json');
    }
    
    try {
        // Validar y obtener datos
        $song_id = filter_input(INPUT_POST, 'song_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        
        error_log("Datos recibidos: song_id=$song_id, rating=$rating, comment=" . substr($comment, 0, 50) . "...");
        
        // Validaciones
        if (!$song_id || $song_id <= 0) {
            throw new Exception("Debes seleccionar una canción válida");
        }
        
        if (!$rating || $rating < 1 || $rating > 5) {
            throw new Exception("El rating debe ser un número entre 1 y 5");
        }
        
        if (empty($comment)) {
            throw new Exception("El comentario es obligatorio");
        }
        
        if (strlen($comment) < 3) {
            throw new Exception("El comentario debe tener al menos 3 caracteres");
        }
        
        // Verificar que la canción existe
        $stmt = $pdo->prepare("SELECT id, title, artist FROM canciones WHERE id = ?");
        $stmt->execute([$song_id]);
        $cancion = $stmt->fetch();
        
        if (!$cancion) {
            error_log("❌ Canción no encontrada: ID $song_id");
            throw new Exception("La canción seleccionada no existe");
        }
        
        error_log("✅ Canción encontrada: " . $cancion['title'] . " - " . $cancion['artist']);
        
        // OBTENER EMAIL DEL USUARIO (IMPORTANTE PARA EL WEBHOOK)
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT email, username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            error_log("❌ Usuario no encontrado en BD: ID $user_id");
            throw new Exception("Usuario no encontrado");
        }
        
        $user_email = $usuario['email'];
        $username = $usuario['username'];
        error_log("✅ Usuario encontrado: $username ($user_email)");
        
        // Verificar si ya existe review del usuario para esta canción
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND song_id = ?");
        $stmt->execute([$user_id, $song_id]);
        $existing_review = $stmt->fetch();
        
        if ($existing_review) {
            // Actualizar review existente
            error_log("Actualizando review existente ID: " . $existing_review['id']);
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$rating, $comment, $existing_review['id']]);
            $review_id = $existing_review['id'];
            $action = 'actualizada';
            $message = "Review actualizada exitosamente";
        } else {
            // Insertar nueva review
            error_log("Insertando nueva review");
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, song_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $song_id, $rating, $comment]);
            $review_id = $pdo->lastInsertId();
            $action = 'creada';
            $message = "Review creada exitosamente";
            error_log("✅ Review insertada con ID: $review_id");
        }
        
       
// =================== ENVIAR WEBHOOK A N8N ===================
// Preparar datos para el webhook
$webhookData = [
    'review_id' => $review_id,                    // ✅ Ya lo tienes
    'song_id' => $song_id,                        // ✅ De $_POST['song_id']
    'song_title' => $cancion['title'],            // ✅ De tu query a canciones
    'artist' => $cancion['artist'],               // ✅ De tu query a canciones
    'rating' => $rating,                          // ✅ De $_POST['rating']
    'comment' => $comment,                        // ✅ De $_POST['comment']
    'user_id' => $user_id,                        // ✅ De $_SESSION['user_id']
    'user_email' => $user_email,                  // ✅ De tu query a users
    'username' => $username,                      // ✅ De tu query a users
    'created_at' => date('Y-m-d H:i:s'),          // ✅ Ya lo usas
    'action' => $action                           // ✅ 'creada' o 'actualizada'
];

// Enviar webhook a n8n usando el Helper
try {
    // Incluir el helper
    $helperPath = dirname(__DIR__, 2) . '/helpers/WebhookHelper.php';
    
    if (file_exists($helperPath)) {
        require_once $helperPath;
        
        if ($action === 'creada') {
            $webhookResult = WebhookHelper::sendReviewCreated($webhookData);
        } else {
            $webhookResult = WebhookHelper::sendReviewUpdated($webhookData);
        }
        
        // Log el resultado
        error_log("✅ Webhook enviado usando Helper: " . ($webhookResult['success'] ? 'Éxito' : 'Falló'));
        if (!$webhookResult['success']) {
            error_log("❌ Error webhook: " . ($webhookResult['error'] ?? 'Desconocido'));
        }
    } else {
        error_log("⚠️ WebhookHelper.php no encontrado en: $helperPath");
    }
} catch (Exception $e) {
    error_log("⚠️ Error enviando webhook: " . $e->getMessage());
    // No detener la aplicación por fallo de webhook
}
// =================== FIN WEBHOOK ===================
        // Obtener datos completos de la nueva/actualizada review
        $stmt = $pdo->prepare("
            SELECT r.*, c.title as song_title, c.artist 
            FROM reviews r 
            JOIN canciones c ON r.song_id = c.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$review_id]);
        $new_review = $stmt->fetch();
        
        if (!$new_review) {
            throw new Exception("Error al recuperar los datos de la review");
        }
        
        // Preparar respuesta
        $response = [
            'success' => true,
            'message' => $message,
            'review' => [
                'id' => $new_review['id'],
                'song_title' => $new_review['song_title'],
                'artist' => $new_review['artist'],
                'rating' => $new_review['rating'],
                'comment' => $new_review['comment'],
                'created_at' => $new_review['created_at'],
                'formatted_date' => date('d/m/Y H:i', strtotime($new_review['created_at']))
            ],
            'webhook_sent' => isset($webhookResult) ? $webhookResult['success'] : false
        ];
        
        error_log("✅ Review $action exitosamente - ID: $review_id");
        
        if ($isModal) {
            // Respuesta JSON para modal
            echo json_encode($response);
            exit;
            
        } else {
            // Redirección normal
            $_SESSION['success'] = $message;
            if (defined('BASE_URL')) {
                header('Location: ' . BASE_URL . 'views/reviews/index.php');
            } else {
                header('Location: /views/reviews/index.php');
            }
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("❌ Error PDO: " . $e->getMessage());
        error_log("❌ Error SQL: " . $e->getCode());
        
        $error_message = 'Error de base de datos';
        if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
            $error_message .= ': ' . $e->getMessage();
        }
        
        if ($isModal) {
            echo json_encode([
                'success' => false, 
                'message' => $error_message
            ]);
            exit;
        } else {
            $_SESSION['error'] = $error_message;
            if (defined('BASE_URL')) {
                header('Location: ' . BASE_URL . 'views/reviews/create.php');
            } else {
                header('Location: /views/reviews/create.php');
            }
            exit;
        }
        
    } catch (Exception $e) {
        error_log("❌ Error general: " . $e->getMessage());
        
        if ($isModal) {
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
            exit;
        } else {
            $_SESSION['error'] = $e->getMessage();
            if (defined('BASE_URL')) {
                header('Location: ' . BASE_URL . 'views/reviews/create.php');
            } else {
                header('Location: /views/reviews/create.php');
            }
            exit;
        }
    }
}

// 9. SI LLEGA AQUÍ, ES UNA PETICIÓN GET (no debería llegar si es modal)
error_log("⚠️  Método GET o no es creación de review");

if ($isModal) {
    error_log("❌ ERROR: Petición modal pero no es POST con create_review");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: Método no permitido'
    ]);
    exit;
}

// 10. Obtener canciones para formulario GET (si se necesita)
try {
    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
    $canciones = $stmt->fetchAll();
    error_log("Canciones obtenidas: " . count($canciones));
} catch (Exception $e) {
    $canciones = [];
    error_log("Error obteniendo canciones: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Crear Nueva Review</h1>
    <p>Este formulario es para acceso directo. Para usar el modal, ve al dashboard.</p>
</body>
</html>