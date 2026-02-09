<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Asistente Musical'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #4cc9f0, #4361ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header p {
            color: #a0a0c0;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .user-info {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background: rgba(67, 97, 238, 0.2);
            border-radius: 50px;
            font-size: 0.9rem;
        }
        
        .rag-form {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #4cc9f0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .question-input {
            width: 100%;
            min-height: 150px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1.1rem;
            font-family: inherit;
            resize: vertical;
            transition: all 0.3s ease;
        }
        
        .question-input:focus {
            outline: none;
            border-color: #4361ee;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .question-input::placeholder {
            color: #8888aa;
        }
        
        .submit-btn {
            background: linear-gradient(45deg, #4361ee, #3a0ca3);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
            background: linear-gradient(45deg, #4cc9f0, #4361ee);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .examples {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .examples h3 {
            color: #4cc9f0;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .examples-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .example-card {
            background: rgba(255, 255, 255, 0.08);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #4361ee;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .example-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(5px);
        }
        
        .example-text {
            color: #c0c0e0;
            font-style: italic;
            line-height: 1.5;
        }
        
        .how-it-works {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .how-it-works h3 {
            color: #4cc9f0;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .step {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
        }
        
        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #4361ee;
            color: white;
            border-radius: 50%;
            line-height: 40px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .step-title {
            color: #fff;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .step-desc {
            color: #a0a0c0;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .error-message {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #4cc9f0;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #fff;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .rag-form, .examples, .how-it-works {
                padding: 20px;
            }
            
            .examples-grid {
                grid-template-columns: 1fr;
            }
            
            .steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-robot"></i> Asistente Musical RAG</h1>
            <p>Pregunta sobre canciones, artistas, géneros musicales o reviews de nuestra comunidad</p>
            <div class="user-info">
                <i class="fas fa-user"></i> Hola, <?php echo htmlspecialchars($username); ?>
            </div>
        </div>

        <!-- Mensajes de error -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="rag-form">
            <form method="POST" action="/rag/answer" id="ragForm">
                <div class="form-group">
                    <label for="question">
                        <i class="fas fa-question-circle"></i> Tu pregunta musical:
                    </label>
                    <textarea 
                        name="question" 
                        id="question" 
                        class="question-input" 
                        placeholder="Ej: ¿Qué canciones de pop tienen las mejores reviews? ¿Qué opinan los usuarios sobre Daddy Yankee? ¿Puedes recomendarme música para estudiar?"
                        required
                        autofocus
                    ><?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-search"></i> Consultar al Asistente
                </button>
            </form>
        </div>

        <!-- Ejemplos -->
        <div class="examples">
            <h3><i class="fas fa-lightbulb"></i> Ejemplos de preguntas:</h3>
            <div class="examples-grid">
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"¿Qué canciones de reggaeton tienen rating 5 estrellas?"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"¿Cuáles son las mejores canciones de Dua Lipa según los usuarios?"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"Recomiéndame música indie para relajarme"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"¿Qué opinan los usuarios sobre el álbum Future Nostalgia?"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"Estadísticas de canciones por género"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p class="example-text">"¿Hay canciones de rock de los años 80 en la plataforma?"</p>
                </div>
            </div>
        </div>

        <!-- Cómo funciona -->
        <div class="how-it-works">
            <h3><i class="fas fa-cogs"></i> ¿Cómo funciona el asistente?</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-title">Escribe tu pregunta</div>
                    <div class="step-desc">Pregunta en lenguaje natural sobre cualquier tema musical</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-title">Búsqueda inteligente</div>
                    <div class="step-desc">Buscamos en canciones, reviews y datos de la plataforma</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-title">Análisis y síntesis</div>
                    <div class="step-desc">Combinamos información relevante de múltiples fuentes</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-title">Respuesta personalizada</div>
                    <div class="step-desc">Generamos una respuesta útil basada en los datos encontrados</div>
                </div>
            </div>
        </div>

        <!-- Enlace de regreso -->
        <a href="/" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

    <script>
        // Función para establecer ejemplo en el textarea
        function setExample(card) {
            const text = card.querySelector('.example-text').textContent;
            document.getElementById('question').value = text;
            document.getElementById('question').focus();
            
            // Efecto visual
            card.style.background = 'rgba(67, 97, 238, 0.2)';
            card.style.borderLeft = '4px solid #4cc9f0';
            setTimeout(() => {
                card.style.background = '';
                card.style.borderLeft = '4px solid #4361ee';
            }, 1000);
        }

        // Validación del formulario
        document.getElementById('ragForm').addEventListener('submit', function(e) {
            const textarea = document.getElementById('question');
            const question = textarea.value.trim();
            
            if (question.length < 3) {
                e.preventDefault();
                alert('Por favor, escribe una pregunta más detallada (mínimo 3 caracteres).');
                textarea.focus();
                return false;
            }
            
            if (question.length > 500) {
                e.preventDefault();
                alert('La pregunta es demasiado larga. Por favor, acórtala a menos de 500 caracteres.');
                textarea.focus();
                return false;
            }
            
            // Mostrar indicador de carga
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds just in case
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Contador de caracteres
        const textarea = document.getElementById('question');
        const charCount = document.createElement('div');
        charCount.style.cssText = 'text-align: right; margin-top: 5px; color: #8888aa; font-size: 0.9rem;';
        textarea.parentNode.appendChild(charCount);
        
        function updateCharCount() {
            const length = textarea.value.length;
            charCount.textContent = `${length}/500 caracteres`;
            
            if (length > 450) {
                charCount.style.color = '#ff6b6b';
            } else if (length > 300) {
                charCount.style.color = '#feca57';
            } else {
                charCount.style.color = '#8888aa';
            }
        }
        
        textarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Inicial
    </script>
</body>
</html>