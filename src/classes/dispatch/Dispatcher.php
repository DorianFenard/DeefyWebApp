<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\action\DisplayPlaylistAction;
use iutnc\deefy\action\AddTrackToDbAction;
use iutnc\deefy\action\ManagePlaylistsAction;


DeefyRepository::setConfig('config/db.config.ini');

class Dispatcher {

    private string $action;

    public function __construct() {

        $this->action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? 'default') : ($_GET['action'] ?? 'default');
    }

    /**
     * 
     * Exécute l'action demandée
     * @return void
     */
    public function run(): void {
        $action = null;
     
        //Vérifiez si la méthode HTTP est POST ou GET
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->action = $_POST['action'] ?? 'default';
        } else {
            $this->action = $_GET['action'] ?? 'default';
        }
    
        //debug
        //echo "<div>Action demandée : " . htmlspecialchars($this->action) . "</div>";
        //echo "<div>Méthode HTTP : " . $_SERVER['REQUEST_METHOD'] . "</div>";
        
        //Instanciez la classe d'action correspondante
        switch ($this->action) {
            case 'add-track-to-db':
                $action = new AddTrackToDbAction();
                break;
            case 'add-playlist':
                $action = new AddPlaylistAction();
                break;
            case 'add-podcast-track':
                $action = new AddPodcastTrackAction();
                break;
            case 'playlist':
                $action = new DisplayPlaylistAction();
                break;
                case 'manage-playlists':
                $action = new ManagePlaylistsAction();
                break;
            default:
                $action = new DefaultAction();
                break;
        }
        // Si une action est trouvée, elle est exécutée
        if ($action !== null) {
            $html = $action->execute();
            $this->renderPage($html);
        }
    }

    //Méthode privée pour exécuter l'action
    private function executeAction($action): void {
        echo "<div>Classe d'action : " . get_class($action) . "</div>";
        echo "<div>Méthode HTTP : " . $_SERVER['REQUEST_METHOD'] . "</div>";
        $html = $action->execute();
        $this->renderPage($html);
    }

    // Méthode privée qui génère une page HTML
    private function renderPage(string $html): void {
        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TD11</title>
</head>
<body>
    $html
</body>
</html>
HTML;
    }
}