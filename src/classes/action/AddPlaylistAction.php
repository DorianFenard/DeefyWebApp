<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Action permettant d'ajouter une playlist.
 */
class AddPlaylistAction extends Action {

    protected function handleGet(): string {

        /**
         * Récupérer toutes les pistes existantes pour les afficher dans le formulaire.
         */

        $tracks = DeefyRepository::getAllTracks(); 
        
        $html = <<<HTML
        <form method="post" action="?action=add-playlist">
            <input type="hidden" name="action" value="add-playlist"> 
            <label for="playlist-name">Nom de la playlist :</label>
            <input type="text" id="playlist-name" name="playlist_name" required>
            <h3>Ajouter des pistes existantes :</h3>
    HTML;
    
        foreach ($tracks as $track) {

            $html .= "<input type='checkbox' name='existing_tracks[]' value='" . htmlspecialchars((string) $track->getId()) . "'> " . htmlspecialchars((string) $track->getTitre()) . "<br>";
        }
    
        $html .= <<<HTML
            <button type="submit">Créer la playlist</button>
        </form>
    HTML;
    
        return $html;
    }
    
    protected function handlePost(): string {
    


        $nomPlaylist = filter_input(INPUT_POST, 'playlist_name', FILTER_SANITIZE_SPECIAL_CHARS);
        
        if (empty($nomPlaylist)) {
            return "<p style='color:red;'>Nom de playlist invalide !</p>";
        }


        $playlist = new Playlist($nomPlaylist, [], 0);

// Récupérer les pistes sélectionnées dans le formulaire pour les ajouter à la playlist.

        $selectedTracks = $_POST['existing_tracks'] ?? [];
        
        foreach ($selectedTracks as $trackId) {

            $track = DeefyRepository::findTrackById((int)$trackId);
            if ($track) {
                $playlist->ajouterPiste($track);
            }
        }

        if (!isset($_SESSION['user_id'])) {
            return "<p style='color:red;'>Utilisateur non connecté !</p>";
        }
        
        $userId = (int) $_SESSION['user_id'];

        try {

            // Save dans la db la playlist avec les pistes sélectionnées et la met en session comme playlist active.
            $playlistId = DeefyRepository::savePlaylistWithTracks($playlist, $userId);

            $_SESSION['active_playlist_id'] = $playlistId;

            return "<p>Playlist créée avec succès et définie comme active !</p>";
        } catch (\PDOException $e) {
            return "<p style='color:red;'>Erreur lors de la création de la playlist : " . $e->getMessage() . "</p>";
        }
    }
}
?>