<?php
// Retriever.php en /models/ o /helpers/
class Retriever {
    
    public function generateEmbedding($text) {
        // Implementación simple - puedes usar una librería como:
        // - sentence-transformers (Python)
        // - BERT embeddings
        // - O simplemente devolver un array vacío por ahora
        
        // Por ahora, devolvemos null ya que no tenemos embeddings reales
        return null;
    }
    
    public function searchSimilar($embedding, $limit = 10) {
        // Buscar documentos similares basados en embeddings
        // Por ahora devolvemos array vacío
        return [];
    }
    
    public function simpleSearch($query, $documents) {
        // Búsqueda simple por palabras clave
        $results = [];
        $query = strtolower($query);
        
        foreach ($documents as $doc) {
            $score = 0;
            $text = strtolower(implode(' ', array_values($doc)));
            
            $queryWords = explode(' ', $query);
            foreach ($queryWords as $word) {
                if (strlen($word) > 2 && strpos($text, $word) !== false) {
                    $score += 1;
                }
            }
            
            if ($score > 0) {
                $results[] = [
                    'document' => $doc,
                    'score' => $score
                ];
            }
        }
        
        // Ordenar por score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        $limit = 0;
        return array_slice($results, 0, $limit);
    }
}