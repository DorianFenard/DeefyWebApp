<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;

/**
 * Action pour afficher et gérer les playlists de l'utilisateur
 */
class DisplayPlaylistAction extends Action {
    public function execute(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            return "Utilisateur non connecté.";
        }

        $userEmail = $_SESSION['user'];
        $userId = DeefyRepository::findUserIdByEmail($userEmail);

        if (isset($_GET['active_playlist_id'])) {
            $_SESSION['active_playlist_id'] = (int) $_GET['active_playlist_id'];
        }

        if (isset($_GET['delete_playlist_id'])) {
            $this->deletePlaylist((int)$_GET['delete_playlist_id']);
        }

        // Récupère les playlists de l'utilisateur
        $playlistsData = DeefyRepository::findPlaylistsByUserId($userId);
        if (empty($playlistsData)) {
            return "<p>Aucune playlist trouvée.</p>";
        }

        $html = "<h2>Playlists de l'utilisateur</h2>";

        // Affiche la playlist active si elle est définie dans la session
        if (isset($_SESSION['active_playlist_id'])) {
            $activePlaylistData = DeefyRepository::findPlaylistById($_SESSION['active_playlist_id']);
            if ($activePlaylistData) {
                $activePlaylist = new Playlist(
                    $activePlaylistData->getNom(),
                    $activePlaylistData->getTracks(),
                    $activePlaylistData->getId()
                );

                $html .= "<h3>Playlist Active</h3>";
                $audioListRenderer = new AudioListRenderer($activePlaylist);
                $html .= $audioListRenderer->render(0);
            }
        }

        // Afficher la liste de toutes les playlists avec un lien pour les mettre en active ou les supprimer
        $html .= "<h2>Toutes les Playlists</h2><br><h3>Cliquez sur le nom de la playlist de votre choix pour la choisir en tant que playlist principale</h3>";
        
        foreach ($playlistsData as $playlistData) {
            $playlistName = htmlspecialchars($playlistData->getNom());
            $playlistId = $playlistData->getId();
            $html .= "<p>
                <a href=\"?action=playlist&active_playlist_id=$playlistId\">$playlistName</a> | 
                <a href=\"?action=playlist&delete_playlist_id=$playlistId\" onclick=\"return confirm('Voulez-vous vraiment supprimer cette playlist ?');\">Supprimer</a>
            </p>";
        }

        return $html;
    }

    /**
     * Supprime une playlist de la base de données
     */
    private function deletePlaylist(int $playlistId): void {
        try {
            // Vérifiez si la playlist à supprimer est la playlist active
            if (isset($_SESSION['active_playlist_id']) && $_SESSION['active_playlist_id'] === $playlistId) {
                $_SESSION['active_playlist_id'] = null; 
            }
            DeefyRepository::deletePlaylistById($playlistId);
            echo "<p>Playlist supprimée avec succès !</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:red;'>Erreur lors de la suppression de la playlist : " . $e->getMessage() . "</p>";
        }
    }
}
?>