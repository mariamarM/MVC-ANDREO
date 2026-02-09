<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Respuesta RAG'; ?></title>
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
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #4cc9f0;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #4cc9f0;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .question-box {
            background: rgba(67, 97, 238, 0.1);
            border: 1px solid #4361ee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .question-box h3 {
            color: #4cc9f0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .question-text {
            font-size: 1.2rem;
            font-style: italic;
            color: #fff;
            line-height: 1.5;
        }
        
        .answer-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .answer-box h3 {
            color: #4cc9f0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .answer-content {
            white-space: pre-line;
            line-height: 1.6;
            font-size: 1.1rem;
            color: #e0e0ff;
        }
        
        .results-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #4cc9f0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .result-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            border-color: #4361ee;
        }
        
        .result-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .type-cancion {
            background: rgba(76, 201, 240, 0.2);
            color: #4cc9f0;
        }
        
        .type-review {
            background: rgba(247, 37, 133, 0.2);
            color: #f72585;
        }
        
        .result-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .result-info {
            color: #a0a0c0;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .result-content {
            color: #c0c0e0;
            line-height: 1.5;
            margin-top: 10px;
        }
        
        .rating {
            color: #ffd700;
            font-size: 1.1rem;
        }
        
        .stats-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #4cc9f0;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #a0a0c0;
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .action-btn {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #4361ee;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3a0ca3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .relevance {
            display: inline-block;
            margin-left: auto;
            padding: 3px 10px;
            background: rgba(76, 201, 240, 0.2);
            border-radius: 12px;
            font-size: 0.8rem;
            color: #4cc9f0;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #a0a0c0;
        }
        
        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-robot"></i> Asistente Musical - Respuesta</h1>
            <p>Información basada en canciones y reviews de la comunidad</p>
            <a href="/rag/ask" class="back-link">
                <i class="fas fa-arrow-left"></i> Hacer otra pregunta
            </a>
        </div>

        <!-- Pregunta Original -->
        <div class="question-box">
            <h3><i class="fas fa-question-circle"></i> Tu pregunta:</h3>
            <p class="question-text">"<?php echo htmlspecialchars($question); ?>"</p>
        </div>

        <!-- Respuesta Generada -->
        <div class="answer-box">
            <h3><i class="fas fa-comment-dots"></i> Respuesta del asistente:</h3>
            <div class="answer-content">
                <?php echo nl2br(htmlspecialchars($answer)); ?>
            </div>
        </div>

        <!-- Estadísticas -->
        <?php if (!empty($stats)): ?>
        <div class="stats-box">
            <h3 class="section-title"><i class="fas fa-chart-bar"></i> Estadísticas de la plataforma</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_canciones']; ?></div>
                    <div class="stat-label">Canciones</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_reviews']; ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_usuarios']; ?></div>
                    <div class="stat-label">Usuarios</div>
                </div>
                <?php if ($stats['rating_promedio']): ?>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['rating_promedio']; ?>/5</div>
                    <div class="stat-label">Rating promedio</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resultados Encontrados -->
        <?php if (!empty($results)): ?>
        <div class="results-section">
            <h3 class="section-title"><i class="fas fa-music"></i> Información encontrada (<?php echo $total_results; ?> resultados)</h3>
            
            <!-- Canciones -->
            <?php if ($has_canciones): ?>
            <h4 style="color: #4cc9f0; margin: 20px 0 15px 0;">
                <i class="fas fa-compact-disc"></i> Canciones relacionadas:
            </h4>
            <div class="results-grid">
                <?php foreach ($results as $item): ?>
                    <?php if ($item['tipo'] === 'cancion'): ?>
                    <div class="result-card">
                        <span class="result-type type-cancion">
                            <i class="fas fa-music"></i> Canción
                        </span>
                        <h4 class="result-title"><?php echo htmlspecialchars($item['titulo']); ?></h4>
                        <div class="result-info">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($item['artista']); ?>
                        </div>
                        <div class="result-info">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($item['genero']); ?>
                        </div>
                        <div class="result-info">
                            <i class="fas fa-calendar"></i>
                            Año: <?php echo $item['ano']; ?>
                        </div>
                        <?php if ($item['album']): ?>
                        <div class="result-info">
                            <i class="fas fa-compact-disc"></i>
                            Álbum: <?php echo htmlspecialchars($item['album']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($item['score'])): ?>
                        <div class="relevance">
                            Relevancia: <?php echo number_format($item['score'], 2); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Reviews -->
            <?php if ($has_reviews): ?>
            <h4 style="color: #f72585; margin: 30px 0 15px 0;">
                <i class="fas fa-comment"></i> Reviews de usuarios:
            </h4>
            <div class="results-grid">
                <?php foreach ($results as $item): ?>
                    <?php if ($item['tipo'] === 'review'): ?>
                    <div class="result-card">
                        <span class="result-type type-review">
                            <i class="fas fa-star"></i> Review
                        </span>
                        <h4 class="result-title"><?php echo htmlspecialchars($item['cancion_titulo']); ?></h4>
                        <div class="result-info">
                            <i class="fas fa-user"></i>
                            Por: <?php echo htmlspecialchars($item['usuario']); ?>
                        </div>
                        <div class="result-info">
                            <i class="fas fa-star"></i>
                            Puntuación: 
                            <span class="rating">
                                <?php echo str_repeat('★', $item['puntuacion']) . str_repeat('☆', 5 - $item['puntuacion']); ?>
                                (<?php echo $item['puntuacion']; ?>/5)
                            </span>
                        </div>
                        <div class="result-content">
                            "<?php echo htmlspecialchars($item['comentario']); ?>"
                        </div>
                        <div class="result-info" style="margin-top: 10px;">
                            <i class="fas fa-clock"></i>
                            <?php echo date('d/m/Y', strtotime($item['fecha'])); ?>
                        </div>
                        <?php if (isset($item['score'])): ?>
                        <div class="relevance">
                            Relevancia: <?php echo number_format($item['score'], 2); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="no-results">
            <h3><i class="fas fa-search"></i> No se encontraron resultados específicos</h3>
            <p>Intenta con otros términos de búsqueda o pregunta de forma diferente.</p>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="actions">
            <a href="/rag/ask" class="action-btn btn-primary">
                <i class="fas fa-question-circle"></i> Nueva pregunta
            </a>
            <a href="/" class="action-btn btn-secondary">
                <i class="fas fa-home"></i> Volver al inicio
            </a>
            <a href="/songs" class="action-btn btn-secondary">
                <i class="fas fa-music"></i> Ver canciones
            </a>
            <a href="/reviews" class="action-btn btn-secondary">
                <i class="fas fa-star"></i> Ver reviews
            </a>
        </div>
    </div>

    <script>
        // Efecto para mostrar la respuesta gradualmente
        document.addEventListener('DOMContentLoaded', function() {
            const answerContent = document.querySelector('.answer-content');
            const originalText = answerContent.textContent;
            answerContent.textContent = '';
            
            let index = 0;
            const speed = 10; // Velocidad de escritura (ms por caracter)
            
            function typeWriter() {
                if (index < originalText.length) {
                    answerContent.textContent += originalText.charAt(index);
                    index++;
                    setTimeout(typeWriter, speed);
                }
            }
            
            // Iniciar efecto después de 500ms
            setTimeout(typeWriter, 500);
            
            // Resaltar términos importantes en la respuesta
            const importantTerms = ['recomiendo', 'recomendación', 'mejor', 'top', 'rating', 'puntuación', 'estadísticas'];
            setTimeout(() => {
                let text = answerContent.textContent;
                importantTerms.forEach(term => {
                    const regex = new RegExp(`(${term})`, 'gi');
                    text = text.replace(regex, '<strong>$1</strong>');
                });
                answerContent.innerHTML = text;
            }, 3000);
        });
    </script>
</body>
</html>