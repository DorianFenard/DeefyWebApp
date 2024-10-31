<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;

/**
 * Action pour gérer les playlists d'un utilisateur spécifique par un administrateur.
 */
class ManagePlaylistsAction extends Action {
    public function execute(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie que l'utilisateur est administrateur
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 100) {
            return "<p style='color:red;'>Accès refusé : seuls les administrateurs peuvent gérer les playlists.</p>";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_playlist'])) {
                return $this->handleAddPlaylist();
            } elseif (isset($_POST['delete_playlist'])) {
                return $this->handleDeletePlaylist();
            } elseif (isset($_POST['add_track_to_playlist'])) {
                return $this->handleAddTrackToPlaylist();
            }
        }
        return $this->renderForm();
    }

    protected function renderForm(): string {
        // Formulaire pour ajouter une playlist
        $html = "<h2>Gérer les Playlists d'un Utilisateur</h2>
                 <form method='post' action='?action=manage-playlists'>
                     <input type='hidden' name='action' value='manage-playlists'>
                     <label for='user_id'>ID Utilisateur :</label>
                     <input type='text' name='user_id' required><br>
                     <label for='playlist_name'>Nom de la Playlist à Ajouter :</label>
                     <input type='text' name='playlist_name' required><br>
                     <button type='submit' name='add_playlist'>Ajouter Playlist</button>
                 </form>";
        
        // Formulaire pour supprimer une playlist
        $html .= "<h3>Supprimer une Playlist</h3>
                  <form method='post' action='?action=manage-playlists'>
                      <input type='hidden' name='action' value='manage-playlists'>
                      <label for='playlist_id'>ID de la Playlist à Supprimer :</label>
                      <input type='text' name='playlist_id' required><br>
                      <button type='submit' name='delete_playlist'>Supprimer Playlist</button>
                  </form>";
        
        // Formulaire pour ajouter une piste à une playlist existante
        $html .= "<h3>Ajouter une Piste à une Playlist Existante</h3>
                  <form method='post' action='?action=manage-playlists'>
                      <input type='hidden' name='action' value='manage-playlists'>
                      <label for='existing_playlist'>Choisir une Playlist :</label>
                      <select name='existing_playlist_id' id='existing_playlist' required>";
    
        $playlists = DeefyRepository::getAllPlaylists();
        foreach ($playlists as $playlist) {
            $html .= "<option value='" . htmlspecialchars((string) $playlist->getId()) . "'>" . htmlspecialchars($playlist->getNom()) . "</option>";
        }
        $html .= "</select><br>";
        
        $tracks = DeefyRepository::getAllTracks(); 
        $html .= "<label for='track'>Choisir une Piste :</label>
                  <select name='track_id' id='track' required>";
        foreach ($tracks as $track) {
            $html .= "<option value='" . htmlspecialchars((string) $track->getId()) . "'>" . htmlspecialchars($track->getTitre()) . "</option>";
        }
        $html .= "</select><br>";
        
        $html .= "<button type='submit' name='add_track_to_playlist'>Ajouter la Piste à la Playlist</button>
                  </form>";
        
        return $html;
    }
    private function handleAddPlaylist(): string {
        $userId = (int)$_POST['user_id'];
        $playlistName = filter_input(INPUT_POST, 'playlist_name', FILTER_SANITIZE_STRING);

        if (empty($playlistName)) {
            return "<p style='color:red;'>Nom de la playlist ne peut pas être vide.</p>";
        }

        DeefyRepository::addPlaylist($userId, $playlistName);
        return "<p>Playlist ajoutée avec succès.</p>";
    }

    private function handleDeletePlaylist(): string {
        $playlistId = (int)$_POST['playlist_id'];

        if ($playlistId <= 0) {
            return "<p style='color:red;'>ID de la playlist non valide.</p>";
        }

        DeefyRepository::deletePlaylistById($playlistId);
        return "<p>Playlist supprimée avec succès.</p>";
    }

    private function handleAddTrackToPlaylist(): string {
        $playlistId = (int)$_POST['existing_playlist_id'];
        $trackId = (int)$_POST['track_id'];

        if ($playlistId <= 0 || $trackId <= 0) {
            return "<p style='color:red;'>ID de la playlist ou de la piste non valide.</p>";
        }

        DeefyRepository::addTrackToPlaylist($playlistId, $trackId);

        return "<p>Piste ajoutée à la playlist avec succès !</p>";
    }
}
?>