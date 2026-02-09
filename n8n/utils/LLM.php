<?php
// utils/LLM.php

class LLM {
    
    /**
     * Genera respuesta basada en la pregunta y resultados
     */
    public function generate($question, $results, $stats = []) {
        $questionLower = strtolower($question);
        
        // Análisis de la pregunta
        $analysis = $this->analyzeQuestion($questionLower);
        
        // Construir contexto
        $context = $this->buildContext($results, $stats);
        
        // Generar respuesta según tipo de pregunta
        switch($analysis['tipo']) {
            case 'recomendacion':
                return $this->generateRecomendacion($question, $context, $results);
            case 'busqueda':
                return $this->generateBusqueda($question, $context, $results);
            case 'opinion':
                return $this->generateOpinion($question, $context, $results);
            case 'estadisticas':
                return $this->generateEstadisticas($question, $context, $stats);
            case 'genero':
                return $this->generatePorGenero($question, $context, $results);
            default:
                return $this->generateGeneral($question, $context, $results);
        }
    }
    
    /**
     * Analiza el tipo de pregunta
     */
    private function analyzeQuestion($question) {
        $keywords = [
            'recomendacion' => ['recomendar', 'recomiendas', 'recomienda', 'recomendación', 'sugerir', 'sugiere'],
            'busqueda' => ['buscar', 'encuentra', 'dónde', 'dónde está', 'qué', 'cuál', 'cuáles'],
            'opinion' => ['opinión', 'opinan', 'piensan', 'crítica', 'review', 'reseña'],
            'estadisticas' => ['estadísticas', 'estadistica', 'total', 'cuántos', 'promedio', 'media'],
            'genero' => ['género', 'rock', 'pop', 'reggaeton', 'bolero', 'indie', 'jazz', 'electrónica']
        ];
        
        foreach ($keywords as $tipo => $palabras) {
            foreach ($palabras as $palabra) {
                if (strpos($question, $palabra) !== false) {
                    return ['tipo' => $tipo, 'palabra' => $palabra];
                }
            }
        }
        
        return ['tipo' => 'general', 'palabra' => ''];
    }
    
    /**
     * Construye contexto a partir de resultados
     */
    private function buildContext($results, $stats) {
        $context = "INFORMACIÓN ENCONTRADA:\n\n";
        
        $canciones = [];
        $reviews = [];
        
        foreach ($results as $item) {
            if ($item['tipo'] === 'cancion') {
                $canciones[] = $item;
            } else {
                $reviews[] = $item;
            }
        }
        
        if (!empty($canciones)) {
            $context .= "CANCIONES RELEVANTES:\n";
            foreach ($canciones as $cancion) {
                $context .= "- {$cancion['titulo']} de {$cancion['artista']}";
                if ($cancion['album']) $context .= " (Álbum: {$cancion['album']})";
                $context .= ". Género: {$cancion['genero']}. Año: {$cancion['ano']}\n";
            }
            $context .= "\n";
        }
        
        if (!empty($reviews)) {
            $context .= "REVIEWS DE USUARIOS:\n";
            foreach ($reviews as $review) {
                $estrellas = str_repeat('★', $review['puntuacion']) . str_repeat('☆', 5 - $review['puntuacion']);
                $context .= "- {$review['usuario']} sobre \"{$review['cancion_titulo']}\": ";
                $context .= "{$review['comentario']} {$estrellas}\n";
            }
            $context .= "\n";
        }
        
        if (!empty($stats)) {
            $context .= "ESTADÍSTICAS GENERALES:\n";
            $context .= "- Canciones en el sistema: {$stats['total_canciones']}\n";
            $context .= "- Reviews realizadas: {$stats['total_reviews']}\n";
            $context .= "- Usuarios registrados: {$stats['total_usuarios']}\n";
            if ($stats['rating_promedio']) {
                $context .= "- Rating promedio: {$stats['rating_promedio']}/5\n";
            }
        }
        
        return $context;
    }
    
    /**
     * Genera recomendaciones
     */
    private function generateRecomendacion($question, $context, $results) {
        $response = "🎵 **BASÁNDOME EN LAS REVIEWS DE NUESTRA COMUNIDAD, TE RECOMIENDO:**\n\n";
        
        $topCanciones = [];
        foreach ($results as $item) {
            if ($item['tipo'] === 'cancion') {
                $topCanciones[] = $item;
            }
        }
        
        if (empty($topCanciones)) {
            $response .= "No encontré canciones específicas relacionadas con tu búsqueda.\n";
            $response .= "Sin embargo, en nuestra plataforma tenemos una gran variedad de géneros musicales.\n";
            $response .= "Te sugiero explorar canciones de Pop, Rock, Reggaeton o Indie que suelen ser muy populares.\n\n";
        } else {
            $response .= "Estas son algunas canciones que podrían interesarte:\n\n";
            
            foreach (array_slice($topCanciones, 0, 5) as $index => $cancion) {
                $num = $index + 1;
                $response .= "{$num}. **{$cancion['titulo']}** - *{$cancion['artista']}*\n";
                $response .= "   🎶 Género: {$cancion['genero']}\n";
                $response .= "   📅 Año: {$cancion['ano']}\n";
                if ($cancion['album']) {
                    $response .= "   💿 Álbum: {$cancion['album']}\n";
                }
                $response .= "\n";
            }
            
            $response .= "🎧 Estas canciones han sido mencionadas positivamente por nuestros usuarios.\n";
        }
        
        $response .= "\n💡 **CONSEJO:** También puedes consultar las reviews específicas de cada canción para conocer opiniones detalladas.";
        
        return $response;
    }
    
    /**
     * Genera respuestas de búsqueda
     */
    private function generateBusqueda($question, $context, $results) {
        $response = "🔍 **HE ENCONTRADO ESTA INFORMACIÓN RELACIONADA CON TU BÚSQUEDA:**\n\n";
        
        if (empty($results)) {
            $response .= "No encontré resultados específicos para \"{$question}\".\n";
            $response .= "Prueba con otros términos o consulta por géneros musicales específicos.\n";
        } else {
            $response .= $context;
            $response .= "\n📊 **RESUMEN:** Encontré " . count($results) . " elementos relevantes.\n";
        }
        
        return $response;
    }
    
    /**
     * Genera resumen de opiniones
     */
    private function generateOpinion($question, $context, $results) {
        $response = "💬 **OPINIONES DE LA COMUNIDAD:**\n\n";
        
        $reviews = array_filter($results, function($item) {
            return $item['tipo'] === 'review';
        });
        
        if (empty($reviews)) {
            $response .= "No hay reviews específicas sobre este tema.\n";
            $response .= "Los usuarios aún no han compartido sus opiniones sobre esta consulta.\n";
        } else {
            $response .= "Los usuarios han compartido estas opiniones:\n\n";
            
            foreach (array_slice($reviews, 0, 5) as $review) {
                $estrellas = str_repeat('★', $review['puntuacion']) . str_repeat('☆', 5 - $review['puntuacion']);
                $response .= "⭐ **{$review['usuario']}** sobre *{$review['cancion_titulo']}*:\n";
                $response .= "   \"{$review['comentario']}\"\n";
                $response .= "   Puntuación: {$estrellas} ({$review['puntuacion']}/5)\n\n";
            }
            
            // Calcular promedio si hay suficientes reviews
            $puntuaciones = array_column($reviews, 'puntuacion');
            $promedio = array_sum($puntuaciones) / count($puntuaciones);
            $response .= "📈 **Promedio de puntuación:** " . round($promedio, 1) . "/5 estrellas\n";
        }
        
        return $response;
    }
    
    /**
     * Genera estadísticas
     */
    private function generateEstadisticas($question, $context, $stats) {
        $response = "📊 **ESTADÍSTICAS DE LA PLATAFORMA:**\n\n";
        
        $response .= "Actualmente en nuestra plataforma tenemos:\n";
        $response .= "• 🎶 **{$stats['total_canciones']} canciones** en el catálogo\n";
        $response .= "• 💬 **{$stats['total_reviews']} reviews** realizadas por usuarios\n";
        $response .= "• 👥 **{$stats['total_usuarios']} usuarios** registrados\n";
        
        if ($stats['rating_promedio']) {
            $estrellas = str_repeat('★', round($stats['rating_promedio'])) . str_repeat('☆', 5 - round($stats['rating_promedio']));
            $response .= "• ⭐ **Rating promedio:** {$estrellas} ({$stats['rating_promedio']}/5)\n";
        }
        
        $response .= "\n📈 **ANÁLISIS:** La comunidad está activa compartiendo sus opiniones musicales.\n";
        $response .= "Cada canción tiene en promedio " . round($stats['total_reviews'] / max(1, $stats['total_canciones']), 1) . " reviews.\n";
        
        return $response;
    }
    
    /**
     * Genera información por género
     */
    private function generatePorGenero($question, $context, $results) {
        $response = "🎸 **INFORMACIÓN POR GÉNERO MUSICAL:**\n\n";
        
        $generos = [];
        foreach ($results as $item) {
            if ($item['tipo'] === 'cancion' && !empty($item['genero'])) {
                if (!isset($generos[$item['genero']])) {
                    $generos[$item['genero']] = 0;
                }
                $generos[$item['genero']]++;
            }
        }
        
        if (empty($generos)) {
            $response .= "No encontré canciones del género específico que buscas.\n";
            $response .= "Tenemos una variedad de géneros disponibles: Pop, Rock, Reggaeton, Indie, etc.\n";
        } else {
            arsort($generos);
            
            $response .= "Géneros encontrados en tu búsqueda:\n\n";
            foreach ($generos as $genero => $cantidad) {
                $response .= "• **{$genero}**: {$cantidad} canción(es)\n";
            }
            
            $generoPrincipal = array_key_first($generos);
            $response .= "\n🎵 El género más común en los resultados es **{$generoPrincipal}**.\n";
        }
        
        return $response;
    }
    
    /**
     * Genera respuesta general
     */
    private function generateGeneral($question, $context, $results) {
        $response = "🎧 **INFORMACIÓN MUSICAL ENCONTRADA:**\n\n";
        
        if (empty($results)) {
            $response .= "No encontré información específica sobre \"{$question}\".\n";
            $response .= "Prueba con:\n";
            $response .= "• Nombres de canciones o artistas\n";
            $response .= "• Géneros musicales (rock, pop, reggaeton)\n";
            $response .= "• Términos como \"mejores canciones\" o \"reviews populares\"\n";
        } else {
            $response .= $context;
            $response .= "\n💡 **Puedes preguntarme cosas como:**\n";
            $response .= "• \"Recomiéndame canciones de pop\"\n";
            $response .= "• \"¿Qué opinan de Daddy Yankee?\"\n";
            $response .= "• \"Estadísticas de la plataforma\"\n";
        }
        
        return $response;
    }
}