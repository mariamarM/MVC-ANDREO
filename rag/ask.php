<?php
// views/rag/ask.php - VERSIÓN ÚNICA COMPLETA
// Este archivo contiene TODO: nav + widget + estilos + JavaScript
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Asistente Musical RAG'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== ESTILOS DEL NAV ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0c29, #1a1a2e);
            color: white;
            min-height: 100vh;
        }

        nav {
            background: rgba(18, 18, 30, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .containerNav {
            flex: 1;
        }

        .nav-list {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-list li a {
            color: #e0e0ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .nav-list li a:hover {
            color: #4cc9f0;
            background: rgba(76, 201, 240, 0.1);
        }

        .nav-link {
            list-style: none;
        }

        .nav-link a {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white !important;
            padding: 0.7rem 1.5rem !important;
            border-radius: 50px !important;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
        }

        .nav-link a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 201, 240, 0.4);
        }

        /* ========== ESTILOS DEL WIDGET (Bottom Left, 300px, Ángulo recto) ========== */
        .rag-widget-container {
            position: fixed;
            bottom: 20px;
            left: 0;
            z-index: 9999;
            width: 300px;
            transform: translateX(-100%);
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .rag-widget-container.active {
            transform: translateX(20px);
        }

        .rag-widget-toggle {
            position: absolute;
            top: 50%;
            right: -44px;
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
            transform: rotate(-90deg) translateX(-50%);
            transform-origin: left top;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            border-left: 2px solid rgba(255, 255, 255, 0.3);
            z-index: 10000;
        }

        .rag-widget-toggle i {
            transform: rotate(90deg);
        }

        .rag-widget-toggle:hover {
            background: linear-gradient(135deg, #3ab8df, #3250e8);
        }

        .rag-widget-content {
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0 15px 15px 0;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            height: auto;
            max-height: 600px;
            display: flex;
            flex-direction: column;
            border-left: 4px solid #4361ee;
        }

        .rag-widget-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .rag-widget-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            background: linear-gradient(45deg, #4cc9f0, #4361ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .rag-widget-header p {
            color: #a0a0c0;
            font-size: 0.9rem;
        }

        .rag-widget-form {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .rag-widget-textarea {
            flex: 1;
            min-height: 100px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
            font-size: 0.95rem;
            font-family: inherit;
            resize: vertical;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .rag-widget-textarea:focus {
            outline: none;
            border-color: #4cc9f0;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.2);
        }

        .rag-widget-submit {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 0.95rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            width: 100%;
        }

        .rag-widget-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 201, 240, 0.3);
        }

        .rag-widget-response {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            max-height: 200px;
        }

        .rag-widget-response.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .response-content {
            color: #e0e0ff;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .rag-widget-loading {
            display: none;
            text-align: center;
            padding: 15px;
        }

        .rag-widget-loading.active {
            display: block;
        }

        .loading-spinner {
            width: 35px;
            height: 35px;
            border: 3px solid rgba(76, 201, 240, 0.3);
            border-top: 3px solid #4cc9f0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ========== ESTILOS DEL CONTENIDO PRINCIPAL ========== */
        .main-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .welcome-card h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #4cc9f0, #4361ee, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-card p {
            color: #b0b0d0;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                padding: 1rem;
            }
            
            .nav-list {
                margin-bottom: 1rem;
                gap: 1rem;
            }
            
            .rag-widget-container {
                width: 280px;
            }
            
            .rag-widget-container.active {
                transform: translateX(10px);
            }
            
            .rag-widget-toggle {
                right: -42px;
                padding: 10px 16px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- ========== NAVEGACIÓN ========== -->
    <nav>
        <div class="containerNav">
            <ul class="nav-list">
                <li><a href="/dashboardUser.php">MyMusic</a></li>
                <li><a href="/buscadorCanciones.php">songs</a></li>
                <li><a href="/dashboardUser.php">playlists</a></li>
                <li><a href="/aboutus.php">about us</a></li>
            </ul>
        </div>
        <li class="nav-link">
            <a href="#" onclick="openRagAssistant(event); return false;">
                <i class="fas fa-robot"></i> Get assistance
            </a>
        </li>
    </nav>

    <!-- ========== CONTENIDO PRINCIPAL DE LA PÁGINA ========== -->
    <div class="main-content">
        <div class="welcome-card">
            <h1><i class="fas fa-music"></i> Asistente Musical RAG</h1>
            <p>Haz clic en "Get assistance" en el menú para abrir el asistente</p>
            <p style="font-size: 1rem; color: #4cc9f0;">
                <i class="fas fa-arrow-left"></i> El widget aparecerá en la esquina inferior izquierda
            </p>
        </div>
    </div>

    <!-- ========== WIDGET FLOTANTE RAG ========== -->
    <div class="rag-widget-container" id="ragWidget">
        <button class="rag-widget-toggle" id="ragToggle">
            <i class="fas fa-robot"></i> Asistente Musical
        </button>
        
        <div class="rag-widget-content">
            <div class="rag-widget-header">
                <h3>Need some help?</h3>
                <p><i class="fas fa-music"></i> Tu asistente musical con IA</p>
            </div>
            
            <form method="POST" action="/rag/answer" class="rag-widget-form" id="ragForm">
                <textarea 
                    name="question" 
                    class="rag-widget-textarea" 
                    placeholder="Ej: ¿Qué canciones de pop tienen las mejores reviews? ¿Qué opinan los usuarios sobre Bad Bunny?"
                    required
                    id="ragQuestion"
                ></textarea>
                
                <div class="rag-widget-response" id="ragResponse">
                    <div class="response-content" id="responseContent">
                        <!-- La respuesta se cargará aquí -->
                    </div>
                </div>
                
                <div class="rag-widget-loading" id="ragLoading">
                    <div class="loading-spinner"></div>
                    <p>Procesando tu pregunta...</p>
                </div>
                
                <button type="submit" class="rag-widget-submit">
                    <i class="fas fa-search"></i> Consultar
                </button>
            </form>
        </div>
    </div>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        // ============================================
        // FUNCIÓN GLOBAL PARA ABRIR EL ASISTENTE
        // ============================================
        window.openRagAssistant = function(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            console.log("🎵 Abriendo asistente musical...");
            
            const ragWidget = document.getElementById('ragWidget');
            if (ragWidget) {
                ragWidget.classList.add('active');
                
                // Scroll suave hacia el widget
                ragWidget.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                console.error("❌ Widget no encontrado");
                alert("Error: No se pudo abrir el asistente.");
            }
            
            return false;
        };

        // ============================================
        // INICIALIZACIÓN CUANDO EL DOM ESTÁ LISTO
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log("🔧 Inicializando widget RAG...");
            
            // 1. Toggle del widget con su propio botón
            const ragToggle = document.getElementById('ragToggle');
            if (ragToggle) {
                ragToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const ragWidget = document.getElementById('ragWidget');
                    if (ragWidget) {
                        ragWidget.classList.toggle('active');
                    }
                });
            }
            
            // 2. Abrir automáticamente después de 1 segundo (opcional)
            setTimeout(() => {
                const ragWidget = document.getElementById('ragWidget');
                if (ragWidget) {
                    ragWidget.classList.add('active');
                }
            }, 1000);
            
            // 3. Manejo del formulario AJAX
            const ragForm = document.getElementById('ragForm');
            const ragResponse = document.getElementById('ragResponse');
            const responseContent = document.getElementById('responseContent');
            const ragLoading = document.getElementById('ragLoading');
            const ragQuestion = document.getElementById('ragQuestion');
            
            if (ragForm) {
                ragForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const question = formData.get('question');
                    
                    if (!question.trim()) return;
                    
                    // Mostrar loading
                    if (ragLoading) ragLoading.classList.add('active');
                    if (ragResponse) ragResponse.classList.remove('active');
                    
                    try {
                        const response = await fetch('/rag/answer-api', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams(formData)
                        });
                        
                        const data = await response.json();
                        
                        // Ocultar loading
                        if (ragLoading) ragLoading.classList.remove('active');
                        
                        // Mostrar respuesta
                        if (responseContent) {
                            responseContent.innerHTML = data.answer || 
                                `<p>${data.error || 'No se pudo obtener una respuesta.'}</p>`;
                        }
                        if (ragResponse) ragResponse.classList.add('active');
                        
                        // Auto-scroll a la respuesta
                        if (ragResponse) ragResponse.scrollTop = 0;
                        
                    } catch (error) {
                        console.error('Error:', error);
                        if (ragLoading) ragLoading.classList.remove('active');
                        if (responseContent) {
                            responseContent.innerHTML = '<p>Error al conectar con el servidor. Intenta nuevamente.</p>';
                        }
                        if (ragResponse) ragResponse.classList.add('active');
                    }
                });
            }
            
            // 4. Cerrar widget al hacer clic fuera
            document.addEventListener('click', function(e) {
                const ragWidget = document.getElementById('ragWidget');
                const ragToggle = document.getElementById('ragToggle');
                
                if (ragWidget && ragToggle) {
                    if (!ragWidget.contains(e.target) && !ragToggle.contains(e.target)) {
                        ragWidget.classList.remove('active');
                    }
                }
            });
            
            console.log("✅ Widget RAG inicializado correctamente");
            console.log("✅ Botón Get assistance listo para abrir el widget");
        });
    </script>
</body>
</html>