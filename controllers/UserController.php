<?php
require_once __DIR__ . '/Controller.php';

class UserController extends Controller {
    
    // Mostrar formulario de login
    public function login() {
        // Si ya está logueado, redirigir a home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        // Mostrar formulario
        $this->render('auth/login.php');
    }
    
    // Procesar login
    public function processLogin() {
        // Si ya está logueado, redirigir
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        // Solo permitir POST
        if (!$this->isPost()) {
            $this->redirect('/login');
            return;
        }
        
        // Obtener datos del formulario
        $email = $this->getPost('email');
        $password = $this->getPost('password');
        
        // Validaciones básicas
        $errors = [];
        
        if (empty($email)) {
            $errors[] = "El email es requerido";
        }
        
        if (empty($password)) {
            $errors[] = "La contraseña es requerida";
        }
        
        if (!empty($errors)) {
            $this->render('auth/login.php', ['errors' => $errors]);
            return;
        }
        
        // Procesar login
        $userModel = new User();
        $user = $userModel->login($email, $password);
        
        if ($user) {
            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            // Redirigir a home
            $_SESSION['success'] = "¡Bienvenido de nuevo, " . $user['username'] . "!";
            $this->redirect('/home');
        } else {
            // Credenciales incorrectas
            $errors[] = "Email o contraseña incorrectos";
            $this->render('auth/login.php', ['errors' => $errors]);
        }
    }
    
    // Mostrar formulario de registro
    public function register() {
        // Si ya está logueado, redirigir
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        $this->render('auth/register.php');
    }
    
    // Procesar registro
    public function processRegister() {
        // Si ya está logueado, redirigir
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        // Solo permitir POST
        if (!$this->isPost()) {
            $this->redirect('/register');
            return;
        }
        
        // Obtener datos del formulario
        $username = $this->getPost('username');
        $email = $this->getPost('email');
        $password = $this->getPost('password');
        $confirm_password = $this->getPost('confirm_password');
        
        // Validaciones
        $errors = [];
        
        // Validar username
        if (empty($username)) {
            $errors[] = "El nombre de usuario es requerido";
        } elseif (strlen($username) < 3) {
            $errors[] = "El nombre de usuario debe tener al menos 3 caracteres";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "El nombre de usuario solo puede contener letras, números y guiones bajos";
        }
        
        // Validar email
        if (empty($email)) {
            $errors[] = "El email es requerido";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del email no es válido";
        }
        
        // Validar contraseña
        if (empty($password)) {
            $errors[] = "La contraseña es requerida";
        } elseif (strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres";
        }
        
        // Confirmar contraseña
        if ($password !== $confirm_password) {
            $errors[] = "Las contraseñas no coinciden";
        }
        
        // Si hay errores, mostrar formulario
        if (!empty($errors)) {
            $this->render('auth/register.php', [
                'errors' => $errors,
                'username' => $username,
                'email' => $email
            ]);
            return;
        }
        
        // Registrar usuario en la base de datos
        $userModel = new User();
        $userId = $userModel->register($username, $email, $password);
        
        if ($userId) {
            // Auto-login después del registro
            $user = $userModel->findById($userId);
            
            if ($user) {
                // Guardar en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Mensaje de éxito
                $_SESSION['success'] = "¡Registro exitoso! Bienvenido a nuestra plataforma.";
                
                // Redirigir a home
                $this->redirect('/home');
            } else {
                $errors[] = "Error al iniciar sesión automáticamente";
                $this->render('auth/register.php', ['errors' => $errors]);
            }
        } else {
            $errors[] = "Error al registrar usuario. El email o nombre de usuario ya existen.";
            $this->render('auth/register.php', [
                'errors' => $errors,
                'username' => $username,
                'email' => $email
            ]);
        }
    }
    
    // Perfil de usuario
    public function profile() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para ver tu perfil";
            $this->redirect('/login');
            return;
        }
        
        // Obtener datos del usuario desde la base de datos
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);
        
        if (!$user) {
            // Usuario no encontrado, cerrar sesión
            session_destroy();
            $this->redirect('/login');
            return;
        }
        
        // Obtener reviews del usuario si tienes CommentController
        $reviews = [];
        if (file_exists(__DIR__ . '/CommentController.php')) {
            require_once __DIR__ . '/CommentController.php';
            $commentController = new CommentController();
            $reviews = $commentController->getReviewsByUser($_SESSION['user_id']);
        }
        
        $this->render('user/profile.php', [
            'user' => $user,
            'reviews' => $reviews
        ]);
    }
    
    // Cerrar sesión
    public function logout() {
        $_SESSION = [];
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir a home con mensaje
        $_SESSION['success'] = "Has cerrado sesión correctamente";
        $this->redirect('/home');
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']);
    }
}
?>