<?php
require_once __DIR__ . '/../../../config/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Verificar rol
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboardUser.php');
    exit;
}
$usuarios = [];

try {
    // Intenta cargar usuarios desde el modelo Admin si lo tienes
    require_once __DIR__ . '/../../../models/Admin.php';
    $adminModel = new Admin();
    $usuarios = $adminModel->getAllUsers(); // O getUsersForSelect()
    
} catch (Exception $e) {
    // Si falla, intenta conexión directa
    try {
        // Ajusta según tu configuración de base de datos
        require_once __DIR__ . '/../../../config/database.php';
        
        // Si usas PDO
        if (class_exists('PDO')) {
            $pdo = new PDO("mysql:host=localhost;dbname=blog_db", "usuario", "password");
            $sql = "SELECT id, username, email, role FROM users ORDER BY username ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e2) {
        // Si todo falla, usa array vacío
        $usuarios = [];
        error_log("Error al cargar usuarios: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>js/cursor-effect.js" defer></script>
    <style>
        body {
            background: linear-gradient(236deg, #220808 63.05%, #940B0B 90.6%, #FF1717 102.38%);
            min-height: 100vh;
            color: #FFF;
            font-family: "Manrope", sans-serif;
            font-size: 18px;
            overflow: hidden;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .user-name {
            font-weight: 600;
            color: #fff;
            font-size: 16px;
        }

        .user-email {
            font-size: 14px;
            color: #aaa;
        }

        .admin-badge {
            background: linear-gradient(135deg, #e11d2e, #ff6b6b);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }



        .icon-btn {

            width: 44px;
            height: 44px;

            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
        }





        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(225, 29, 46, 0.4);
        }

        /* ===== CONTENIDO PRINCIPAL ===== */
        .admin-container {
            margin: 39px 30px;
            display: flex;
            width: 95%;
            height: 664px;
            padding: 27px 21px;
            align-items: flex-start;
            align-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-start;
            border-radius: 10px;
            background: rgba(15, 15, 19, 0.70);

        }



        /* ===== ESTADÍSTICAS ===== */
        .stats-section {
            display: flex;
            width: 75%;
            height: 310px;
            padding: 0px 20px;
            flex-direction: column;
            align-items: flex-start;
            gap: 25px;
            flex-shrink: 0;
            border-radius: 10px;
            border: 1px solid #DA1E28;
        }

        .section-title {
            display: flex;
            width: 91px;
            margin-left: -65%;
            align-items: center;
            gap: -1px;
            font-size: 18px;
            justify-content: space-between;
        }

        .section-title i {
            color: #e11d2e;
        }

        .stats-grid {
            display: flex;

            justify-content: space-between;
            width: 100%;
            height: 100%;
            flex-direction: row;

        }

        .stat-card {
            display: flex;
            width: 283px;
            height: 182px;
            padding: 10px 10px 10px 16px;
            flex-direction: column;
            align-items: flex-start;
            font-family: "Manrope", sans-serif;

            border-radius: 14px;
            background: #141418;
        }

        



        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            gap: 20px;
        }

        .stat-icon {
            width: 70px;
            height: 55px;
            padding: 3px 7px;
            border-radius: 12px;
            background: rgba(225, 29, 46, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #e11d2e;
        }

    .stat-change {
    color: #2ecc71;
    font-size: 14px;
    font-weight: 600;
    padding: 4px 12px;
    position: absolute;
    top: 28%;
}

/* Posiciones left diferentes para cada tarjeta */
.stats-grid .stat-card:nth-child(1) .stat-change {
    left: 13%;
}

.stats-grid .stat-card:nth-child(2) .stat-change {
    left: 31%; /* Un poco más a la derecha */
}

.stats-grid .stat-card:nth-child(3) .stat-change {
    left: 50%; /* Un poco más a la izquierda */
}

.stats-grid .stat-card:nth-child(4) .stat-change {
    left: 68%; /* Valor intermedio */
}

/* Para las flechas negativas */
.stat-change.negative {
    color: #e74c3c;
}

/* Opcional: ajustar las imágenes dentro */
.stat-change img {
    margin-right: 5px;
    vertical-align: middle;
}
        .stat-change.negative {
            color: #e74c3c;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            color: white;
        }

        .stat-label {
            color: #aaa;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== GRÁFICO DE INGRESOS ===== */
     .chart-section {
    width: 100%;
    margin-top: -30px;
}

.chart-container {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: -25px;
}

.chart-title {
    font-size: 16px;
    color: white;
}

/* ============ NUEVO ESTILO PARA EL SELECT ============ */
.chart-selector {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    min-width: 200px; /* Ancho mínimo */
    appearance: none; /* Quita estilo nativo */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px; /* Espacio para la flecha */
}

.chart-selector option {
    background: #1e1e24;
    color: white;
    padding: 10px;
}
/* ============ FIN NUEVO ESTILO ============ */

.chart-placeholder {
    height: 210px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

        .chart-footer {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding-top: 11px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .year-selector {
            display: flex;
            gap: 15px;
        }

        .year-btn {
            padding: 8px 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: #aaa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .year-btn:hover {
            background: rgba(225, 29, 46, 0.1);
            color: #e11d2e;
        }

        .year-btn.active {
            background: rgba(225, 29, 46, 0.2);
            color: #e11d2e;
            border-color: #e11d2e;
        }

        .audio-section {
            margin-bottom: 40px;
            border-radius: 10px;
            border: 1px solid #DA1E28;
            display: flex;
            width: 20%;
            height: 43%;
            font-size: 24px;
            color: #FFF;
            font-family: "Manrope", sans-serif;
            padding: 13px 21px 11px 12px;
            flex-direction: column;
            align-items: center;
            gap: 65px;
        }

        .audio-section p {
            color: rgba(255, 255, 255, 0.40);
            font-family: "Manrope", sans-serif;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 0;
            /* 0% */
            letter-spacing: 0.15px;
        }

        .users-table {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .table-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            padding: 20px;
            background: rgba(225, 29, 46, 0.1);
            font-weight: 600;
            color: #e11d2e;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .table-row:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .user-role {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .role-admin {
            background: rgba(225, 29, 46, 0.1);
            color: #e11d2e;
        }

        .role-user {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .nav-container {
                flex-direction: column;
                gap: 20px;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .nav-list {
                gap: 20px;
            }

            .table-header,
            .table-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .chart-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>

<body class="admin-page admin-dashboard">
    <?php include __DIR__ . '/../../../views/layout/navAdmin.php'; ?>

    <div class="admin-container">



        <section class="stats-section">

            <p>Engagement Overview</p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-music"></i>

                        </div>
                        <div class="stat-label">Tracks Listened</div>
                        <div class="stat-change">
                            <img src="./img/flechaverde.png">
                            +6.1%
                        </div>
                    </div>
                    <div class="stat-value">3,201</div>

                    <p style="margin-top: 10px; color: #aaa; font-size: 14px;">Canciones conocidas por playlists
                        externas</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-satellite"></i>
                        </div>
                        <div class="stat-label">Descubrimientos de Artistas</div>
                        <div class="stat-change">
                            <img src="./img/flechaverde.png">
                            +11%
                        </div>
                    </div>
                    <div class="stat-value">128</div>

                    <p style="margin-top: 10px; color: #aaa; font-size: 14px;">Artistas nuevos este mes en la aplicación
                    </p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-headset"></i>

                        </div>
                        <div class="stat-label">Minutos de Escucha</div>
                        <div class="stat-change negative">
                            <img src="./img/flecharoja.png">
                            -2.3%
                        </div>
                    </div>
                    <div class="stat-value">43,781</div>

                    <p style="margin-top: 10px; color: #aaa; font-size: 14px">Tiempo total de reproducción</p>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">

                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-label">Usuarios Logeados</div>
                        <div class="stat-change">
                            <img src="./img/flechaverde.png">
                            +8.5%
                        </div>
                    </div>
                    <div class="stat-value">345</div>

                    <p style="margin-top: 10px; color: #aaa; font-size: 14px">Usuarios activos este mes</p>
                </div>
            </div>
        </section>

        <section class="audio-section">
            <h2 class="section-title">
                <i class="fas fa-music"></i>
                Audio

            </h2>
            <p> .WAV TO MP3</p>
            <i class="fas fa-download"></i>

        </section>
       <!-- BUSCA ESTA SECCIÓN EN TU CÓDIGO: -->
<section class="chart-section">
    <div class="chart-container">
        <div class="chart-header">
            <h3 class="chart-title">Datos de Ingresos</h3>
            
          
            
            <!-- CON ESTO: -->
            <select class="chart-selector" id="userSelector">
                <option value="">-- Selecciona un usuario --</option>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                            <?php echo htmlspecialchars($usuario['username']); ?> 
                            (<?php echo htmlspecialchars($usuario['email']); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No hay usuarios registrados</option>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="chart-placeholder">
            <div style="text-align: center;">
                <img src="./img/BarLineChart.png" alt="Gráfico de ingresos">
            </div>
        </div>
        
        <div class="chart-footer">
            <div class="year-selector">
                <button class="year-btn">2020</button>
                <button class="year-btn active">2021</button>
                <button class="year-btn">2022</button>
            </div>
        </div>
    </div>
</section>


    </div>

    <script>
 
        const userSelector = document.getElementById('userSelector');
    if (userSelector) {
        userSelector.addEventListener('change', function() {
            const userId = this.value;
            if (userId) {
                console.log('Usuario seleccionado ID:', userId);
                console.log('Usuario:', this.options[this.selectedIndex].text);
                
                // Aquí puedes agregar código para cargar datos del usuario
                // Por ejemplo, usando fetch para obtener datos específicos
                /*
                fetch('/admin/get-user-data/' + userId)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Datos del usuario:', data);
                        // Actualizar gráfico o mostrar información
                    });
                */
            }
        });
    </script>
</body>

</html>