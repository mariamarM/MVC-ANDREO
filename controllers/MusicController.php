<?php
require_once __DIR__ . '/Controller.php';

class MusicController extends Controller {
    
    // Listar todas las canciones
    public function index() {
        // Datos de ejemplo (luego vendrán de la base de datos)
        $songs = [
            ['id' => 1, 'title' => 'Bohemian Rhapsody', 'artist' => 'Queen', 'year' => 1975],
            ['id' => 2, 'title' => 'Blinding Lights', 'artist' => 'The Weeknd', 'year' => 2020],
            ['id' => 3, 'title' => 'Stairway to Heaven', 'artist' => 'Led Zeppelin', 'year' => 1971],
            ['id' => 4, 'title' => 'Billie Jean', 'artist' => 'Michael Jackson', 'year' => 1982],
            ['id' => 5, 'title' => 'Hotel California', 'artist' => 'Eagles', 'year' => 1977],
        ];
        
        $this->render('music/index.php', [
            'songs' => $songs,
            'title' => 'Todas las canciones'
        ]);
    }
    
    // Ver una canción específica
    public function show($id) {
        // Buscar la canción por ID (ejemplo)
        $allSongs = [
            1 => ['id' => 1, 'title' => 'Bohemian Rhapsody', 'artist' => 'Queen', 'album' => 'A Night at the Opera', 'year' => 1975, 'genre' => 'Rock'],
            2 => ['id' => 2, 'title' => 'Blinding Lights', 'artist' => 'The Weeknd', 'album' => 'After Hours', 'year' => 2020, 'genre' => 'Pop'],
        ];
        
        $song = isset($allSongs[$id]) ? $allSongs[$id] : null;
        
        if (!$song) {
            // Canción no encontrada
            echo "Canción no encontrada";
            return;
        }
        
        $this->render('music/show.php', [
            'song' => $song
        ]);
    }
}