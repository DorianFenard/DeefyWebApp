<?php
declare(strict_types=1);
require_once 'vendor/autoload.php';
session_start();
require_once 'src/classes/auth/AuthnProvider.php';
require_once 'src/classes/dispatch/Dispatcher.php';

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\dispatch\Dispatcher;

$dsn = 'mysql:host=localhost;dbname=deefy_db';
$username = 'root';
$password = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

$action = $_GET['action'] ?? 'signin';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $authProvider = new AuthnProvider($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($action === 'add-user') {
            try {
                $authProvider->register($email, $password, $passwordConfirm);
                $_SESSION['user'] = $email;
                echo "<p>Inscription réussie pour l'utilisateur : $email</p>";
            } catch (Exception $e) {
                echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }
        } elseif ($action === 'signin') {
            try {
                $authProvider->signin($email, $password);
                $_SESSION['user'] = $email;


                $_SESSION['user_role'] = $authProvider->getUserRoleById((int)$_SESSION['user_id']);
                
                echo "<p>Connexion réussie ! Bienvenue, " . htmlspecialchars((string)$_SESSION['user']) . ".</p>";
            } catch (Exception $e) {
                echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }
        }
    }


    if ($action === 'logout') {
        session_unset();
        session_destroy();
        header("Location: ?action=signin");
        exit;
    }


    if (isset($_SESSION['user'])) {
        echo "<h2>Bienvenue, {$_SESSION['user']}!</h2>";
        echo "<p><a href=\"?action=logout\">Se déconnecter</a></p>";
        //Actions disponibles par le dispatcher
        echo "<form method=\"get\">
            <h2>Actions disponibles :</h2>
            <button type=\"submit\" name=\"action\" value=\"playlist\">Afficher Playlist</button>
            <button type=\"submit\" name=\"action\" value=\"add-playlist\">Ajouter Playlist</button>
            <button type=\"submit\" name=\"action\" value=\"add-podcast-track\">Ajouter Podcast à la Playlist</button>";
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 100) {
            echo "<button type=\"submit\" name=\"action\" value=\"add-track-to-db\">Gestion des Pistes</button>";
        }

        if ($_SESSION['user_role'] === 100): ?>
            <form method="get">
                <button type="submit" name="action" value="manage-playlists">Gérer les Playlists d'un Utilisateur</button>
            </form>
        <?php endif;

        echo "</form>";

        $dispatcher = new Dispatcher();
        $dispatcher->run();
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur de base de données : " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Application de gestion de playlists</title>
</head>
<style>
/* Style de base */
body {
    font-family: Arial, sans-serif;
    background-color: #121212;
    color: #FFFFFF;
    margin: 0;
    padding: 0;
}

/* Conteneur principal */
main {
    margin: 0 auto;
    padding: 20px;
    max-width: 500px;
    text-align: center;
}

/* Titres */
h2, h1,h3 {
    color: #1DB954;
}

/* Style des formulaires */
form {
    background-color: #1C1C1C;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
    margin-top: 20px;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    color: #b3b3b3;
}

/* Champs de saisie */
input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
input[type="number"],
select,
textarea {
    width: 100%;
    height: 30px;
    padding: 10px;
    margin-bottom: 20px; 
    border: 1px solid #333333;
    border-radius: 4px;
    background-color: #333333;
    color: #FFFFFF;
}
/* Boutons */
input[type="submit"],
button {
    width: 100%;
    padding: 10px;
    background-color: #333333;
    color: #1DB954;
    margin-top : 10px;
    border: 1px solid #1DB954;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}

input[type="submit"]:hover,
button:hover {
    background-color: #1DB954;
    color: #FFFFFF;
}

/* Liens */
a {
    color: #1DB954;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Groupes de boutons radio */
.radio-group {
    display: flex;
    justify-content: space-around;
    margin-top: 15px;
}
input[type="checkbox"] {
    accent-color: #1DB954; 
    transform: scale(1.2);
    margin-right: 10px;
    
}


.radio-group label {
    font-weight: normal;
}

/* Style des listes */
li {
    background-color: #333333;
    padding: 10px;
    margin: 8px 0;
    border-radius: 5px;
    list-style-type: none;
}

/* Style pour l'audio */
audio {
    width: 100%;
    margin-top: 15px;
}

</style>
<body>
    <main>
        <?php if (!isset($_SESSION['user']) && $action === 'signin'): ?>
            <h2>Connexion</h2>
            <form method="POST" action="?action=signin">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required><br>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required><br>

                <input type="submit" value="Se connecter">
            </form>
            <p><a href="?action=add-user">Pas encore inscrit ? Inscrivez-vous ici.</a></p>
        <?php elseif (!isset($_SESSION['user']) && $action === 'add-user'): ?>
            <h2>Inscription</h2>
            <form method="POST" action="?action=add-user">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required><br>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required><br>

                <label for="password_confirm">Confirmer le mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required><br>

                <input type="submit" value="S'inscrire">
            </form>
            <p><a href="?action=signin">Déjà inscrit ? Connectez-vous ici.</a></p>
        <?php endif; ?>
    </main>

</body>
</html>