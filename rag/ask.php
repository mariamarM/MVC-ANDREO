<?php
// views/rag/ask.php (página completa)
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
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }
        
        .rag-full-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .rag-full-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.8s ease;
        }
        
        .rag-full-header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #4cc9f0, #4361ee, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .rag-full-header p {
            color: #a0a0c0;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .rag-full-form {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
            animation: slideUp 0.6s ease 0.2s both;
        }
        
        .rag-full-textarea {
            width: 100%;
            min-height: 150px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            font-size: 1.1rem;
            font-family: inherit;
            resize: vertical;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .rag-full-textarea:focus {
            outline: none;
            border-color: #4cc9f0;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.2);
        }
        
        .rag-full-submit {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 auto;
            transition: all 0.3s;
        }
        
        .rag-full-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(76, 201, 240, 0.3);
        }
        
        .rag-full-examples {
            background: rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: slideUp 0.6s ease 0.4s both;
        }
        
        .rag-full-examples h3 {
            color: #4cc9f0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .example-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .example-card {
            background: rgba(255, 255, 255, 0.08);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #4361ee;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .example-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(5px);
        }
        
        .rag-full-back {
            margin-top: 30px;
            text-align: center;
        }
        
        .rag-full-back a {
            color: #4cc9f0;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .rag-full-container {
                padding: 20px 10px;
            }
            
            .rag-full-header h1 {
                font-size: 2.2rem;
            }
            
            .example-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="rag-full-container">
        <div class="rag-full-header">
            <h1><i class="fas fa-robot"></i> Asistente Musical RAG</h1>
            <p>Pregunta sobre canciones, artistas, géneros musicales o reviews de nuestra comunidad</p>
            <div style="
                display: inline-block;
                margin-top: 15px;
                padding: 8px 20px;
                background: rgba(67, 97, 238, 0.2);
                border-radius: 50px;
                font-size: 0.9rem;
                color: #4cc9f0;
            ">
                <i class="fas fa-user"></i> Hola, <?php echo htmlspecialchars($username ?? 'Usuario'); ?>
            </div>
        </div>
        
        <form method="POST" action="/rag/answer" class="rag-full-form">
            <textarea 
                name="question" 
                class="rag-full-textarea" 
                placeholder="Ej: ¿Qué canciones de pop tienen las mejores reviews? ¿Qué opinan los usuarios sobre Bad Bunny? ¿Puedes recomendarme música para estudiar?"
                required
                autofocus
            ><?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?></textarea>
            
            <button type="submit" class="rag-full-submit">
                <i class="fas fa-search"></i> Consultar al Asistente
            </button>
        </form>
        
        <div class="rag-full-examples">
            <h3><i class="fas fa-lightbulb"></i> Ejemplos de preguntas:</h3>
            <div class="example-grid">
                <div class="example-card" onclick="setExample(this)">
                    <p>"¿Qué canciones de reggaeton tienen rating 5 estrellas?"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p>"¿Cuáles son las mejores canciones de Dua Lipa según los usuarios?"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p>"Recomiéndame música indie para relajarme"</p>
                </div>
                <div class="example-card" onclick="setExample(this)">
                    <p>"¿Qué opinan los usuarios sobre el álbum Future Nostalgia?"</p>
                </div>
            </div>
        </div>
        
        <div class="rag-full-back">
            <a href="/">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
    
    <script>
        function setExample(card) {
            const text = card.querySelector('p').textContent;
            const textarea = document.querySelector('.rag-full-textarea');
            textarea.value = text;
            textarea.focus();
            
            // Efecto visual
            card.style.background = 'rgba(76, 201, 240, 0.2)';
            card.style.borderLeft = '4px solid #f72585';
            setTimeout(() => {
                card.style.background = '';
                card.style.borderLeft = '4px solid #4361ee';
            }, 500);
        }
    </script>
</body>
</html>