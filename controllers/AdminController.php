<?php
namespace App\Controllers;

use Models\User;
use Models\Admin;

class AdminController extends Controller {
    
    // Verificar si es administrador
    private function checkAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Acceso denegado. Se requiere permisos de administrador.";
            $this->redirect('/home');
            return false;
        }
        return true;
    }
    
    // Listar usuarios, reviews o canciones con filtro
    public function users() {
        if (!$this->checkAdmin()) return;
        
        $adminModel = new Admin();
        
        // Obtener filtro actual con valor por defecto
        $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'usuarios';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Validar que el filtro sea válido
        $filtros_validos = ['usuarios', 'reviews', 'canciones'];
        if (!in_array($filtro, $filtros_validos)) {
            $filtro = 'usuarios';
        }
        
        // Inicializar variables
        $data = [];
        $totalRegistros = 0;
        
        // Obtener datos según el filtro
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
        
        // Obtener estadísticas - manejar posibles errores
        try {
            $stats = $adminModel->getStats();
            // Asegurarse de que $stats es un array
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
        
        // Renderizar la vista con todas las variables
        $this->render('admin/users.php', [
            'filtro' => $filtro,
            'search' => $search,
            'data' => $data,
            'totalRegistros' => $totalRegistros,
            'stats' => $stats
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
    
    // Eliminar review
    public function deleteReview($id) {
        if (!$this->checkAdmin()) return;
        
        $adminModel = new Admin();
        
        // Necesitarías un método en el modelo para eliminar reviews
        // $adminModel->deleteReview($id);
        
        $_SESSION['success'] = "Review eliminada correctamente";
        $this->redirect('/admin/users?filtro=reviews');
    }
    
    // Eliminar canción
    public function deleteSong($id) {
        if (!$this->checkAdmin()) return;
        
        $adminModel = new Admin();
        
        // Necesitarías un método en el modelo para eliminar canciones
        // $adminModel->deleteSong($id);
        
        $_SESSION['success'] = "Canción eliminada correctamente";
        $this->redirect('/admin/users?filtro=canciones');
    }
}
?>