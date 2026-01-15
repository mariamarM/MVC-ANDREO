<?php
require_once __DIR__ . '/Controller.php';

class UserController extends Controller {
    
    // Perfil de usuario
    public function profile() {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        // Datos del usuario desde sesión
        $userData = [
            'name' => $_SESSION['username'],
            'email' => $_SESSION['user_email'] ?? 'usuario@ejemplo.com'
        ];
        
        $this->render('user/profile.php', [
            'user' => $userData
        ]);
    }
    
    // Mostrar formulario de login
    public function login() {
        // Si ya está logueado, ir a home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        // Si es POST, procesar login
        if ($this->isPost()) {
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            
            // **SOLO UN EJEMPLO** - Para probar
            // Usa: email = test@test.com, password = 123456
            if ($email === 'test@test.com' && $password === '123456') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'Usuario Demo';
                $_SESSION['user_email'] = 'test@test.com';
                $this->redirect('/home');
                return;
            } else {
                $error = "Email o contraseña incorrectos. Prueba con: test@test.com / 123456";
                $this->render('auth/login.php', ['error' => $error]);
                return;
            }
        }
        
        // Si es GET, mostrar formulario
        $this->render('auth/login.php');
    }
    
    // Mostrar formulario de registro
    public function register() {
        // Si es POST, procesar registro
        if ($this->isPost()) {
            $username = $this->getPost('username');
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $confirmPassword = $this->getPost('confirm_password');
            
            $errors = [];
            
            // Validaciones simples
            if (empty($username)) {
                $errors[] = "El nombre de usuario es requerido";
            }
            if (empty($email)) {
                $errors[] = "El email es requerido";
            }
            if (empty($password)) {
                $errors[] = "La contraseña es requerida";
            }
            if ($password !== $confirmPassword) {
                $errors[] = "Las contraseñas no coinciden";
            }
            
            // Si no hay errores, "registrar"
            if (empty($errors)) {
                // En un caso real, aquí guardarías en la base de datos
                // Por ahora solo simulamos
                $_SESSION['user_id'] = rand(100, 999); // ID aleatorio
                $_SESSION['username'] = $username;
                $_SESSION['user_email'] = $email;
                
                $this->redirect('/home');
                return;
            }
            
            // Si hay errores, mostrar formulario con errores
            $this->render('auth/register.php', ['errors' => $errors]);
            return;
        }
        
        // Si es GET, mostrar formulario vacío
        $this->render('auth/register.php');
    }
    
    // Cerrar sesión
    public function logout() {
        session_destroy();
        $this->redirect('/home');
    }
}
?>