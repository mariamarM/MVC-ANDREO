<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Usuarios</title>
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-cogs"></i> Panel de Administración</h1>
        <div class="user-info">
            <span class="user-email" id="current-user-email">admin@example.com</span>
            <button class="logout-btn" id="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </div>
    </div>
    
    <div class="container">
        <!-- Sidebar with tabs -->
        <div class="sidebar">
            <ul class="nav-tabs">
                <li class="nav-tab active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </li>
                <li class="nav-tab" data-tab="users">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </li>
                <li class="nav-tab" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </li>
                <li class="nav-tab" data-tab="login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </li>
            </ul>
        </div>
        
        <!-- Content area -->
        <div class="content">
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content active">
                <h2 class="dashboard-title">Dashboard</h2>
                <p class="dashboard-subtitle">Resumen general del sistema de administración</p>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" id="total-users">4</div>
                        <div class="stat-label">Usuarios Totales</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-number" id="admin-users">1</div>
                        <div class="stat-label">Administradores</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-number" id="regular-users">3</div>
                        <div class="stat-label">Usuarios Regulares</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Sistema Activo</div>
                    </div>
                </div>
                
                <div class="users-table">
                    <div class="table-header">
                        <div>Email</div>
                        <div>Nombre</div>
                        <div>Rol</div>
                        <div>Estado</div>
                    </div>
                    
                    <div class="table-row">
                        <div class="user-email-cell">admin@example.com</div>
                        <div>Administrador Principal</div>
                        <div><span class="user-role admin">Administrador</span></div>
                        <div><span style="color:#27ae60;">Activo</span></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="user-email-cell">test@test.com</div>
                        <div>Usuario de Prueba 1</div>
                        <div><span class="user-role user">Usuario</span></div>
                        <div><span style="color:#27ae60;">Activo</span></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="user-email-cell">test@test.com</div>
                        <div>Usuario de Prueba 2</div>
                        <div><span class="user-role user">Usuario</span></div>
                        <div><span style="color:#27ae60;">Activo</span></div>
                    </div>
                    
                    <div class="table-row">
                        <div class="user-email-cell">test@test.com</div>
                        <div>Usuario de Prueba 3</div>
                        <div><span class="user-role user">Usuario</span></div>
                        <div><span style="color:#27ae60;">Activo</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div id="users-tab" class="tab-content">
                <div class="users-header">
                    <h2 class="section-title">Gestión de Usuarios</h2>
                    <button class="add-user-btn" id="add-user-btn">
                        <i class="fas fa-user-plus"></i> Agregar Usuario
                    </button>
                </div>
                
                <div class="users-table">
                    <div class="table-header">
                        <div>Email</div>
                        <div>Nombre</div>
                        <div>Rol</div>
                        <div>Acciones</div>
                    </div>
                    
                    <div id="users-table-body">
                        <!-- Users will be loaded here dynamically -->
                    </div>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content">
                <h2 class="dashboard-title">Configuración del Sistema</h2>
                <p class="dashboard-subtitle">Ajustes y preferencias del panel de administración</p>
                
                <div class="login-container">
                    <h3 class="login-title">Preferencias</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Tema de la interfaz</label>
                        <select class="form-select" id="theme-select">
                            <option value="light">Claro</option>
                            <option value="dark">Oscuro</option>
                            <option value="auto">Automático</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Idioma</label>
                        <select class="form-select" id="language-select">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="pt">Portugués</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notificaciones por email</label>
                        <select class="form-select" id="notifications-select">
                            <option value="all">Todas</option>
                            <option value="important">Solo importantes</option>
                            <option value="none">Ninguna</option>
                        </select>
                    </div>
                    
                    <button class="login-btn" id="save-settings-btn">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </div>
            
            <!-- Login Tab -->
            <div id="login-tab" class="tab-content">
                <div class="login-container">
                    <h2 class="login-title">Iniciar Sesión</h2>
                    <p class="login-subtitle">Accede al panel de administración</p>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="login-email" placeholder="tu@email.com" value="test@test.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-input" id="login-password" placeholder="Tu contraseña" value="password123">
                    </div>
                    
                    <button class="login-btn" id="login-submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                    
                    <div style="text-align:center; margin-top:20px; color:#7f8c8d;">
                        <p>Credenciales de prueba:<br>
                        Email: test@test.com<br>
                        Contraseña: password123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for Add/Edit User -->
    <div class="modal-overlay" id="user-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Agregar Usuario</h3>
                <button class="close-btn" id="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="modal-email" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-input" id="modal-name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-input" id="modal-password">
                        <small style="color:#7f8c8d;">Dejar en blanco para no cambiar</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Rol</label>
                        <select class="form-select" id="modal-role" required>
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" id="cancel-modal">Cancelar</button>
                <button class="btn btn-save" id="save-user">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        // Sample user data
        let users = [
            { id: 1, email: "admin@example.com", name: "Administrador Principal", role: "admin", password: "admin123" },
            { id: 2, email: "test@test.com", name: "Usuario de Prueba 1", role: "user", password: "password123" },
            { id: 3, email: "test@test.com", name: "Usuario de Prueba 2", role: "user", password: "password123" },
            { id: 4, email: "test@test.com", name: "Usuario de Prueba 3", role: "user", password: "password123" }
        ];
        
        let currentUser = users[0]; // Default admin user
        let isLoggedIn = true;
        let editingUserId = null;
        
        // DOM Elements
        const tabElements = document.querySelectorAll('.nav-tab');
        const tabContentElements = document.querySelectorAll('.tab-content');
        const logoutBtn = document.getElementById('logout-btn');
        const addUserBtn = document.getElementById('add-user-btn');
        const userModal = document.getElementById('user-modal');
        const closeModalBtn = document.getElementById('close-modal');
        const cancelModalBtn = document.getElementById('cancel-modal');
        const saveUserBtn = document.getElementById('save-user');
        const userForm = document.getElementById('user-form');
        const loginSubmitBtn = document.getElementById('login-submit-btn');
        const currentUserEmail = document.getElementById('current-user-email');
        const saveSettingsBtn = document.getElementById('save-settings-btn');
        
        // Tab switching
        tabElements.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Update active tab
                tabElements.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update active content
                tabContentElements.forEach(content => content.classList.remove('active'));
                document.getElementById(`${tabId}-tab`).classList.add('active');
                
                // If switching to users tab, refresh the table
                if (tabId === 'users') {
                    renderUsersTable();
                }
                
                // If switching to dashboard, update stats
                if (tabId === 'dashboard') {
                    updateDashboardStats();
                }
            });
        });
        
        // Logout functionality
        logoutBtn.addEventListener('click', () => {
            isLoggedIn = false;
            currentUser = null;
            currentUserEmail.textContent = "Invitado";
            
            // Switch to login tab
            tabElements.forEach(t => t.classList.remove('active'));
            tabContentElements.forEach(content => content.classList.remove('active'));
            
            document.querySelector('.nav-tab[data-tab="login"]').classList.add('active');
            document.getElementById('login-tab').classList.add('active');
        });
        
        // Login functionality
        loginSubmitBtn.addEventListener('click', () => {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            
            // Simple authentication
            const user = users.find(u => u.email === email && u.password === password);
            
            if (user) {
                isLoggedIn = true;
                currentUser = user;
                currentUserEmail.textContent = user.email;
                
                // Switch to dashboard tab
                tabElements.forEach(t => t.classList.remove('active'));
                tabContentElements.forEach(content => content.classList.remove('active'));
                
                document.querySelector('.nav-tab[data-tab="dashboard"]').classList.add('active');
                document.getElementById('dashboard-tab').classList.add('active');
                
                updateDashboardStats();
                
                alert("Inicio de sesión exitoso");
            } else {
                alert("Credenciales incorrectas. Usa test@test.com / password123");
            }
        });
        
        // Open modal for adding user
        addUserBtn.addEventListener('click', () => {
            editingUserId = null;
            document.getElementById('modal-title').textContent = "Agregar Usuario";
            document.getElementById('modal-email').value = "";
            document.getElementById('modal-name').value = "";
            document.getElementById('modal-password').value = "";
            document.getElementById('modal-role').value = "user";
            userModal.classList.add('active');
        });
        
        // Close modal
        closeModalBtn.addEventListener('click', () => {
            userModal.classList.remove('active');
        });
        
        cancelModalBtn.addEventListener('click', () => {
            userModal.classList.remove('active');
        });
        
        // Save user (add or edit)
        saveUserBtn.addEventListener('click', () => {
            const email = document.getElementById('modal-email').value;
            const name = document.getElementById('modal-name').value;
            const password = document.getElementById('modal-password').value;
            const role = document.getElementById('modal-role').value;
            
            if (!email || !name) {
                alert("Por favor completa todos los campos requeridos");
                return;
            }
            
            if (editingUserId) {
                // Edit existing user
                const userIndex = users.findIndex(u => u.id === editingUserId);
                if (userIndex !== -1) {
                    users[userIndex].email = email;
                    users[userIndex].name = name;
                    users[userIndex].role = role;
                    if (password) {
                        users[userIndex].password = password;
                    }
                }
            } else {
                // Add new user
                const newId = users.length > 0 ? Math.max(...users.map(u => u.id)) + 1 : 1;
                users.push({
                    id: newId,
                    email,
                    name,
                    role,
                    password: password || "password123"
                });
            }
            
            userModal.classList.remove('active');
            renderUsersTable();
            updateDashboardStats();
        });
        
        // Render users table
        function renderUsersTable() {
            const tableBody = document.getElementById('users-table-body');
            tableBody.innerHTML = "";
            
            users.forEach(user => {
                const row = document.createElement('div');
                row.className = 'table-row';
                row.innerHTML = `
                    <div class="user-email-cell">${user.email}</div>
                    <div>${user.name}</div>
                    <div><span class="user-role ${user.role}">${user.role === 'admin' ? 'Administrador' : 'Usuario'}</span></div>
                    <div class="actions">
                        <button class="action-btn edit-btn" data-id="${user.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="action-btn delete-btn" data-id="${user.id}" ${user.id === 1 ? 'disabled style="opacity:0.5;"' : ''}>
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit and delete buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const userId = parseInt(e.currentTarget.getAttribute('data-id'));
                    editUser(userId);
                });
            });
            
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e.currentTarget.disabled) return;
                    
                    const userId = parseInt(e.currentTarget.getAttribute('data-id'));
                    deleteUser(userId);
                });
            });
        }
        
        // Edit user
        function editUser(userId) {
            const user = users.find(u => u.id === userId);
            if (user) {
                editingUserId = userId;
                document.getElementById('modal-title').textContent = "Editar Usuario";
                document.getElementById('modal-email').value = user.email;
                document.getElementById('modal-name').value = user.name;
                document.getElementById('modal-password').value = "";
                document.getElementById('modal-role').value = user.role;
                userModal.classList.add('active');
            }
        }
        
        // Delete user
        function deleteUser(userId) {
            if (userId === 1) {
                alert("No se puede eliminar el administrador principal");
                return;
            }
            
            if (confirm("¿Estás seguro de que quieres eliminar este usuario?")) {
                users = users.filter(u => u.id !== userId);
                renderUsersTable();
                updateDashboardStats();
            }
        }
        
        // Update dashboard statistics
        function updateDashboardStats() {
            const totalUsers = users.length;
            const adminUsers = users.filter(u => u.role === 'admin').length;
            const regularUsers = users.filter(u => u.role === 'user').length;
            
            document.getElementById('total-users').textContent = totalUsers;
            document.getElementById('admin-users').textContent = adminUsers;
            document.getElementById('regular-users').textContent = regularUsers;
            
            // Update dashboard table
            const dashboardTable = document.querySelector('#dashboard-tab .users-table');
            const tableRows = dashboardTable.querySelectorAll('.table-row');
            
            // Remove all rows except the first one (header)
            for (let i = tableRows.length - 1; i >= 0; i--) {
                tableRows[i].remove();
            }
            
            // Add updated user rows
            users.forEach(user => {
                const row = document.createElement('div');
                row.className = 'table-row';
                row.innerHTML = `
                    <div class="user-email-cell">${user.email}</div>
                    <div>${user.name}</div>
                    <div><span class="user-role ${user.role}">${user.role === 'admin' ? 'Administrador' : 'Usuario'}</span></div>
                    <div><span style="color:#27ae60;">Activo</span></div>
                `;
                
                dashboardTable.appendChild(row);
            });
        }
        
        // Save settings
        saveSettingsBtn.addEventListener('click', () => {
            const theme = document.getElementById('theme-select').value;
            const language = document.getElementById('language-select').value;
            const notifications = document.getElementById('notifications-select').value;
            
            alert(`Configuración guardada:\nTema: ${theme}\nIdioma: ${language}\nNotificaciones: ${notifications}`);
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            renderUsersTable();
            updateDashboardStats();
            
            // Set current user email
            if (currentUser) {
                currentUserEmail.textContent = currentUser.email;
            }
        });
    </script>
</body>
</html>