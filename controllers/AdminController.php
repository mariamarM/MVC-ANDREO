<?php
namespace App\Controllers;

use Models\User;

class AdminController extends Controller {
    
    // Verificar si es administrador
    private function checkAdmin() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = "Acceso denegado. Se requiere permisos de administrador.";
            $this->redirect('/home');
            return false;
        }
        return true;
    }
    
    // Dashboard de administrador
    public function dashboard() {
        if (!$this->checkAdmin()) return;
        
        $userModel = new User();
        $totalUsers = $userModel->count();
        
        $this->render('admin/dashboard.php', [
            'totalUsers' => $totalUsers
        ]);
    }
    
    // Listar todos los usuarios
    public function users() {
        if (!$this->checkAdmin()) return;
        
        $userModel = new User();
        $users = $userModel->getAll();
        
        $this->render('admin/users/index.php', [
            'users' => $users
        ]);
    }
    
    // Editar usuario (formulario)
    public function editUser($id) {
        if (!$this->checkAdmin()) return;
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = "Usuario no encontrado";
            $this->redirect('/admin/users');
            return;
        }
        
        $this->render('admin/users/edit.php', [
            'user' => $user
        ]);
    }
    
    // Actualizar usuario
    public function updateUser() {
        if (!$this->checkAdmin()) return;
        
        if (!$this->isPost()) {
            $this->redirect('/admin/users');
            return;
        }
        
        $id = $this->getPost('id');
        $username = $this->getPost('username');
        $email = $this->getPost('email');
        $role = $this->getPost('role');
        
        $errors = [];
        
        if (empty($username)) $errors[] = "Nombre es requerido";
        if (empty($email)) $errors[] = "Email es requerido";
        if (!in_array($role, ['user', 'admin'])) $errors[] = "Rol no válido";
        
        if (empty($errors)) {
            $userModel = new User();
            
            // Verificar si email ya existe (excluyendo este usuario)
            if ($userModel->emailExists($email, $id)) {
                $errors[] = "El email ya está en uso";
            }
            
            // Verificar si username ya existe
            if ($userModel->usernameExists($username, $id)) {
                $errors[] = "El nombre de usuario ya está en uso";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $this->redirect('/admin/users/edit/' . $id);
            return;
        }
        
        // Actualizar en base de datos
        $userModel = new User();
        
        // Solo actualizar rol (en un sistema real actualizarías más campos)
        if ($userModel->updateRole($id, $role)) {
            $_SESSION['success'] = "Usuario actualizado correctamente";
        } else {
            $_SESSION['error'] = "Error al actualizar usuario";
        }
        
        $this->redirect('/admin/users');
    }
    
    // Eliminar usuario
    public function deleteUser($id) {
        if (!$this->checkAdmin()) return;
        
        // No permitir eliminarse a sí mismo
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = "No puedes eliminarte a ti mismo";
            $this->redirect('/admin/users');
            return;
        }
        
        $userModel = new User();
        
        if ($userModel->delete($id)) {
            $_SESSION['success'] = "Usuario eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar usuario";
        }
        
        $this->redirect('/admin/users');
    }
}

?>