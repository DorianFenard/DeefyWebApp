<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;

/**
 * Action permettant d'ajouter une piste audio à une playlist
 */

class AddPodcastTrackAction extends Action {
    protected function handleGet(): string {
        // Récupérer les playlists de l'utilisateur en s'assurant que l'ID est un entier
        $userId = (int) $_SESSION['user_id'];
        $playlists = DeefyRepository::findPlaylistsByUserId($userId);
    
        // Récupérer toutes les pistes audio existantes
        $tracks = DeefyRepository::getAllTracks(); 
    
        // Créer un formulaire pour choisir quelle piste ajouter à quelle playlist
        $html = "<form method='post' action='?action=add-podcast-track'>";
        $html .= "<input type='hidden' name='action' value='add-podcast-track'>"; 
    
        $html .= "<label for='playlist'>Choisir une Playlist :</label>";
        $html .= "<select name='playlist_id' id='playlist' required>";
        foreach ($playlists as $playlist) {
            $html .= "<option value='" . htmlspecialchars((string) $playlist->getId()) . "'>" . htmlspecialchars($playlist->getNom()) . "</option>";
        }
        $html .= "</select><br>";
    
        $html .= "<label for='track'>Choisir une piste :</label>";
        $html .= "<select name='track_id' id='track' required>";
        foreach ($tracks as $track) {
            $html .= "<option value='" . htmlspecialchars((string) $track->getId()) . "'>" . htmlspecialchars($track->getTitre()) . "</option>";
        }
        $html .= "</select><br>";
    
        $html .= "<button type='submit'>Ajouter la piste à la playlist</button>";
        $html .= "</form>";
    
        return $html;
    }

    protected function handlePost(): string {
    
        // Récupérer les données du formulaire
        $playlistId = (int) filter_input(INPUT_POST, 'playlist_id', FILTER_SANITIZE_NUMBER_INT);
        $trackId = (int) filter_input(INPUT_POST, 'track_id', FILTER_SANITIZE_NUMBER_INT);
    
        if (!$playlistId || !$trackId) {
            return "<p style='color:red;'>Playlist ou piste non valide.</p>";
        }
    
        echo "Playlist ID: " . $playlistId; 
        echo "Track ID: " . $trackId;
    
        // Ajouter la piste à la playlist dans la db
        DeefyRepository::addTrackToPlaylist($playlistId, $trackId);
    
        return "<p>Piste ajoutée à la playlist avec succès !</p>";
    }
}