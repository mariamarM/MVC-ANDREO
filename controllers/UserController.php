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
        
        
        $userData = [
            'name' => $_SESSION['username'] ?? 'Usuario',
            'email' => 'usuario@ejemplo.com'
        ];
        
        $this->render('user/profile.php', [
            'user' => $userData
        ]);
    }
    
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }
        
        if ($this->isPost()) {
            $email = $this->getPost('email');
            $password = $this->getPost('password');
        
            if ($email === 'test@test.com' && $password === '123456') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'Usuario Test';
                $this->redirect('/home');
                return;
            } else {
                $error = "Email o contraseña incorrectos";
                $this->render('auth/login.php', ['error' => $error]);
                return;
            }
        }
        
        $this->render('auth/login.php');
    }
    
    public function register() {
        if ($this->isPost()) {
            $username = $this->getPost('username');
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $confirmPassword = $this->getPost('confirm_password');
            
            $errors = [];
            
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
            
            // Si no hay errores, "registrar" (en realidad solo simular)
            if (empty($errors)) {
                // En un caso real, aquí guardarías en la base de datos
                $_SESSION['user_id'] = 2;
                $_SESSION['username'] = $username;
                $this->redirect('/home');
                return;
            }
            
           //que te enseñe los errores como super basico
            $this->render('auth/register.php', ['errors' => $errors]);
            return;
        }
        
        // cuando le das a enviar te enseña en plan vacio el formulario
        $this->render('auth/register.php');
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/home');
    }
}
?>