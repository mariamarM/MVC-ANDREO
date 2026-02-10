<?php

?>
    <div class="rag-container">
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