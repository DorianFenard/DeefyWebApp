<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\audio\tracks\PodcastTrack;

/**
 * Action administrateur permettant d'ajouter ou de supprimer une piste à la base de données
 */
class AddTrackToDbAction extends Action {

    public function execute(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Test admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 100) {
            return "<p style='color:red;'>Accès refusé : seuls les administrateurs peuvent ajouter et supprimer des pistes.</p>";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_track'])) { 
                $this->handleDelete(); 
            } else { 
                return $this->handlePost(); 
            }
        }
        return $this->handleGet();
    }

    protected function handleGet(): string {
        $html = $this->renderForm();
    
        // Formulaire pour supprimer une piste
        $tracks = DeefyRepository::getAllTracks();  
        $html .= "<h2>Supprimer une piste</h2>
                  <form method='post' action='?action=add-track-to-db'>
                      <input type='hidden' name='action' value='add-track-to-db'>";

        foreach ($tracks as $track) {
            $html .= "<input type='checkbox' name='tracks_to_delete[]' value='" . htmlspecialchars((string)$track->getId()) . "'> " 
                   . htmlspecialchars($track->getTitre()) . "<br>";
        }
        $html .= "<button type='submit' name='delete_track'>Supprimer les pistes sélectionnées</button>
                  </form>";
    
        return $html;
    }

    /**
     * Créer un formulaire pour ajouter une piste à la base de données
     * @return string
     */
    protected function renderForm(): string {
        return <<<HTML
            <h2>Ajouter une nouvelle piste avec fichier MP3</h2>
            <form method="post" action="?action=add-track-to-db" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add-track-to-db"> 
                <label for="title">Titre :</label>
                <input type="text" id="title" name="title" required><br>
    
                <label for="author">Auteur :</label>
                <input type="text" id="author" name="author" required><br>
    
                <label for="date">Date (yyyy-mm-dd) :</label>
                <input type="date" id="date" name="date" required><br>
    
                <label for="genre">Genre :</label>
                <input type="text" id="genre" name="genre" required><br>
    
                <label for="duration">Durée (en secondes) :</label>
                <input type="number" id="duration" name="duration" required><br>
    
                <label for="file">Fichier MP3 :</label>
                <input type="file" id="file" name="file" accept=".mp3" required><br>
    
                <button type="submit">Ajouter la piste</button>
            </form>
        HTML;
    }

    /**
     * Supprimer les pistes sélectionnées de la base de données
     * @return void
     */
    protected function handleDelete(): void {
        // Récupérer les pistes à supprimer
        $tracksToDelete = $_POST['tracks_to_delete'] ?? [];
    
        if (empty($tracksToDelete)) {
            echo "<p style='color:red;'>Aucune piste sélectionnée pour suppression.</p>";
            return;
        }
    
        try {
            foreach ($tracksToDelete as $trackId) {
                $track = DeefyRepository::findTrackById((int)$trackId);
                if ($track) {
                    // Suppression du fichier correspondant
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/td11/audio/' . $track->getNomFichierAudio();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
    
                    // Supprimer la piste de la base de données
                    DeefyRepository::deleteTrackById((int)$trackId);
                }
            }
            echo "<p>Pistes sélectionnées supprimées avec succès !</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:red;'>Erreur lors de la suppression des pistes : " . $e->getMessage() . "</p>";
        }
    }

    protected function handlePost(): string {
        // Récupérer les données du formulaire
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_STRING);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
    
        if (!$title || !$author || !$date || !$genre || !$duration) {
            return "<p style='color:red;'>Erreur : veuillez remplir tous les champs correctement.</p>";
        }
    
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $originalFileName = basename($_FILES['file']['name']);
            $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    
            if ($fileExtension !== 'mp3') {
                return "<p style='color:red;'>Erreur : seul le format MP3 est accepté.</p>";
            }
    
            $newFileName = preg_replace('/[^A-Za-z0-9-]/', ' ', $title) . '.mp3'; 
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/td11/audio/' . $newFileName;
    
            // Déplacer le fichier téléchargé dans le dossier audio avec le nouveau nom
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                try {
                    // Créer la piste avec le nouveau nom de fichier
                    $track = new PodcastTrack(0, $title, $newFileName, $author, $date, $genre, $duration);
                    DeefyRepository::saveTrack($track);
                    return "<p>Piste ajoutée avec succès et fichier téléchargé sous le nom : " . htmlspecialchars($newFileName) . "!</p>";
                } catch (\PDOException $e) {
                    return "<p style='color:red;'>Erreur lors de l'ajout de la piste : " . $e->getMessage() . "</p>";
                }
            } else {
                return "<p style='color:red;'>Erreur : échec de téléchargement du fichier MP3.</p>";
            }
        } else {
            return "<p style='color:red;'>Erreur : veuillez fournir un fichier MP3 valide.</p>";
        }
    }
}
?>