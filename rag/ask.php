<?php
// views/rag/ask.php (widget flotante)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Asistente Musical RAG'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Estilos para el widget flotante */
        .rag-widget-container {
            position: fixed;
            bottom: 20px;
            left: -400px; /* Inicialmente fuera de la pantalla */
            z-index: 1000;
            width: 380px;
            transition: left 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        
        .rag-widget-container.active {
            left: 20px; /* Posición final */
        }
        
        .rag-widget-toggle {
            position: absolute;
            bottom: 0;
            left: 380px; /* Al lado derecho del widget */
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transform-origin: left center;
            transform: rotate(-90deg) translateX(100%);
            transform-origin: left bottom;
            white-space: nowrap;
        }
        
        .rag-widget-toggle:hover {
            background: linear-gradient(135deg, #3ab8df, #3250e8);
        }
        
        .rag-widget-content {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px 15px 0 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        
        .rag-widget-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .rag-widget-header h3 {
            font-size: 1.5rem;
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
            min-height: 120px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-family: inherit;
            resize: none;
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
            padding: 12px 30px;
            font-size: 1rem;
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
        
        .rag-widget-examples {
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }
        
        .rag-widget-examples h4 {
            color: #4cc9f0;
            margin-bottom: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .example-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .example-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid #4361ee;
        }
        
        .example-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(3px);
            border-left-color: #f72585;
        }
        
        .rag-widget-response {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            margin-top: 10px;
            display: none;
        }
        
        .rag-widget-response.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .response-content {
            color: #e0e0ff;
            line-height: 1.5;
        }
        
        .rag-widget-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .rag-widget-loading.active {
            display: block;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(76, 201, 240, 0.3);
            border-top: 4px solid #4cc9f0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .rag-widget-container {
                width: 320px;
                bottom: 10px;
            }
            
            .rag-widget-container.active {
                left: 10px;
            }
            
            .rag-widget-toggle {
                left: 320px;
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            
            .rag-widget-content {
                height: 450px;
            }
        }
        
        @media (max-width: 480px) {
            .rag-widget-container {
                width: calc(100vw - 20px);
                left: calc(-100vw + 20px);
            }
            
            .rag-widget-container.active {
                left: 10px;
            }
            
            .rag-widget-toggle {
                left: calc(100vw - 20px);
            }
        }
    </style>
</head>
<body>
    <!-- Widget flotante RAG -->
    <div class="rag-widget-container" id="ragWidget">
        <button class="rag-widget-toggle" id="ragToggle">
            <i class="fas fa-robot"></i> Get Assistant
        </button>
        
        <div class="rag-widget-content">
            <div class="rag-widget-header">
                <h3><i class="fas fa-robot"></i> Asistente Musical RAG</h3>
                <p>Pregunta sobre canciones, artistas, géneros musicales</p>
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
                    <i class="fas fa-search"></i> Consultar al Asistente
                </button>
            </form>
            
            <div class="rag-widget-examples">
                <h4><i class="fas fa-lightbulb"></i> Ejemplos:</h4>
                <div class="example-list">
                    <div class="example-item" onclick="setExample('¿Qué canciones de reggaeton tienen rating 5 estrellas?')">
                        "¿Qué canciones de reggaeton tienen rating 5 estrellas?"
                    </div>
                    <div class="example-item" onclick="setExample('Recomiéndame música indie para relajarme')">
                        "Recomiéndame música indie para relajarme"
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle del widget
        const ragWidget = document.getElementById('ragWidget');
        const ragToggle = document.getElementById('ragToggle');
        const ragForm = document.getElementById('ragForm');
        const ragResponse = document.getElementById('ragResponse');
        const responseContent = document.getElementById('responseContent');
        const ragLoading = document.getElementById('ragLoading');
        const ragQuestion = document.getElementById('ragQuestion');

        // Inicialmente, mostrar el widget después de un breve retraso
        setTimeout(() => {
            ragWidget.classList.add('active');
        }, 500);

        ragToggle.addEventListener('click', () => {
            ragWidget.classList.toggle('active');
        });

        // Función para establecer ejemplos
        function setExample(text) {
            ragQuestion.value = text;
            ragQuestion.focus();
        }

        // Manejo del formulario con AJAX
        ragForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const question = formData.get('question');
            
            if (!question.trim()) return;
            
            // Mostrar loading
            ragLoading.classList.add('active');
            ragResponse.classList.remove('active');
            
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
                ragLoading.classList.remove('active');
                
                // Mostrar respuesta
                responseContent.innerHTML = data.answer || 
                    `<p>${data.error || 'No se pudo obtener una respuesta.'}</p>`;
                ragResponse.classList.add('active');
                
                // Auto-scroll a la respuesta
                ragResponse.scrollTop = 0;
                
            } catch (error) {
                console.error('Error:', error);
                ragLoading.classList.remove('active');
                responseContent.innerHTML = '<p>Error al conectar con el servidor. Intenta nuevamente.</p>';
                ragResponse.classList.add('active');
            }
        });

        // Cerrar widget al hacer clic fuera (opcional)
        document.addEventListener('click', (e) => {
            if (!ragWidget.contains(e.target) && !ragToggle.contains(e.target)) {
                ragWidget.classList.remove('active');
            }
        });
    </script>
</body>
</html>