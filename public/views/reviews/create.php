<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$baseDir = dirname(__DIR__, 3); 
$configFile = $baseDir . '/config/config.php';
require_once __DIR__ . '/../../../config/Database.php';


session_start();
error_log("Session ID: " . session_id());
error_log("User ID en sesión: " . ($_SESSION['user_id'] ?? 'NO'));

// Verificar si es una petición desde modal
$isModal = isset($_POST['modal_submit']);
error_log("Es modal: " . ($isModal ? 'Sí' : 'No'));

if (!isset($_SESSION['user_id'])) {
    error_log("Usuario NO logueado");
    if ($isModal) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
        exit;
    }
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

error_log("Usuario logueado: ID=" . $_SESSION['user_id'] . ", Nombre=" . ($_SESSION['user_name'] ?? 'NO'));

// OBTENER CONEXIÓN PDO
try {
    $pdo = Database::getInstance();
    error_log("✅ Conexión PDO obtenida exitosamente");
    
    // Probar consulta simple
    $test = $pdo->query("SELECT 1")->fetch();
    error_log("✅ Test query ejecutada: " . print_r($test, true));
    
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

// Verificar método POST
error_log("Método de request: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_review'])) {
    error_log("✅ Procesando creación de review");
    
    // Configurar headers para JSON si es modal
    if ($isModal) {
        header('Content-Type: application/json');
    }
    
    try {
        // Obtener datos
        $song_id = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');
        
        error_log("Datos recibidos: song_id=$song_id, rating=$rating, comment=" . substr($comment, 0, 50) . "...");
        
        // Validaciones
        if ($song_id <= 0) {
            throw new Exception("Debes seleccionar una canción");
        }
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception("El rating debe estar entre 1 y 5");
        }
        
        if (empty($comment)) {
            throw new Exception("El comentario es obligatorio");
        }
        
        // Verificar que la canción existe
        error_log("Verificando canción ID: $song_id");
        $stmt = $pdo->prepare("SELECT id, title, artist FROM canciones WHERE id = ?");
        $stmt->execute([$song_id]);
        $cancion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cancion) {
            error_log("❌ Canción no encontrada: ID $song_id");
            throw new Exception("La canción seleccionada no existe");
        }
        
        error_log("✅ Canción encontrada: " . $cancion['title'] . " - " . $cancion['artist']);
        
        // Verificar si ya existe review
        $user_id = $_SESSION['user_id'];
        error_log("Buscando review existente para user_id=$user_id, song_id=$song_id");
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND song_id = ?");
        $stmt->execute([$user_id, $song_id]);
        $existing_review = $stmt->fetch();
        
        if ($existing_review) {
            // Actualizar
            error_log("Actualizando review existente ID: " . $existing_review['id']);
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
            $stmt->execute([$rating, $comment, $existing_review['id']]);
            $review_id = $existing_review['id'];
            $action = 'actualizada';
        } else {
            // Insertar nueva
            error_log("Insertando nueva review");
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, song_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $song_id, $rating, $comment]);
            $review_id = $pdo->lastInsertId();
            $action = 'creada';
            error_log("✅ Review insertada con ID: $review_id");
        }
        
        // Obtener datos completos
        $stmt = $pdo->prepare("
            SELECT r.*, c.title as song_title, c.artist 
            FROM reviews r 
            JOIN canciones c ON r.song_id = c.id 
            WHERE r.id = ?
        ");
        $stmt->execute([$review_id]);
        $new_review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($isModal) {
            // Respuesta JSON
            $response = [
                'success' => true,
                'message' => "Review $action exitosamente",
                'review' => [
                    'id' => $new_review['id'],
                    'song_title' => $new_review['song_title'],
                    'artist' => $new_review['artist'],
                    'rating' => $new_review['rating'],
                    'comment' => $new_review['comment'],
                    'created_at' => $new_review['created_at']
                ]
            ];
            
            error_log("Enviando respuesta JSON: " . json_encode($response));
            echo json_encode($response);
            exit;
            
        } else {
            // Redirección normal
            $_SESSION['success'] = "Review $action exitosamente";
            header('Location: ' . BASE_URL . 'views/reviews/index.php');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("❌ Error PDO: " . $e->getMessage());
        error_log("❌ Error code: " . $e->getCode());
        
        if ($isModal) {
            echo json_encode([
                'success' => false, 
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ]);
            exit;
        } else {
            $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'views/reviews/create.php');
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
            header('Location: ' . BASE_URL . 'views/reviews/create.php');
            exit;
        }
    }
}

// Si llegamos aquí, es GET
error_log("Método GET - Mostrando formulario");

// Obtener canciones
try {
    $stmt = $pdo->query("SELECT id, title, artist FROM canciones ORDER BY title");
    $canciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Canciones obtenidas: " . count($canciones));
} catch (Exception $e) {
    $canciones = [];
    error_log("Error obteniendo canciones: " . $e->getMessage());
}

// NO mostrar HTML si es AJAX
if ($isModal) {
    error_log("ERROR: Petición AJAX pero llegó a sección HTML");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crear Review</title>
    <style>body{font-family:Arial; padding:20px;}</style>
</head>
<body>
    <h1>Crear Review (Formulario normal)</h1>
    <p>Este es el formulario para GET. Para AJAX usa el modal.</p>
</body>
</html>