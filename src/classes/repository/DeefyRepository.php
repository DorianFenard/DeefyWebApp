<?php

namespace iutnc\deefy\repository;

use PDO;
use PDOException;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository {
    private static $instance = null;
    private static $config;

    public static function setConfig($file) {
        self::$config = parse_ini_file($file);
        if (self::$config === false) {
            throw new PDOException("Erreur lors du chargement de la configuration.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            if (self::$config === null) {
                throw new PDOException("La configuration de la base de données n'est pas définie.");
            }

            $dsn = self::$config['driver'] . ':host=' . self::$config['host'] . ';dbname=' . self::$config['dbname'] . ';charset=utf8';
            self::$instance = new PDO($dsn, self::$config['username'], self::$config['password'], [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false
            ]);
        }
        return self::$instance;
    }

    public static function getAllTracks(): array {
        try {
            $db = self::getInstance();
            $stmt = $db->prepare('SELECT * FROM track');
            $stmt->execute();

            $tracksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tracks = [];

            foreach ($tracksData as $data) {
                $tracks[] = new PodcastTrack(
                    (int)$data['id'],
                    $data['titre'],
                    $data['filename'],
                    $data['auteur_podcast'] ?? 'Inconnu',
                    $data['date_podcast'] ?? '1970-01-01',
                    $data['genre'] ?? 'Genre Inconnu',
                    (int)$data['duree'] ?? 0
                );
            }

            return $tracks;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des pistes : " . $e->getMessage());
        }
    }

    public static function findTrackById(int $id): ?PodcastTrack {
        try {
            $db = self::getInstance();
            $stmt = $db->prepare('SELECT * FROM track WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $trackData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($trackData) {
                return new PodcastTrack(
                    (int)$trackData['id'],
                    $trackData['titre'],
                    $trackData['filename'],
                    $trackData['auteur_podcast'] ?? 'Inconnu',
                    $trackData['date_posdcast'] ?? '1970-01-01',
                    $trackData['genre'] ?? 'Genre Inconnu',
                    (int)$trackData['duree'] ?? 0
                );
            }
            return null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la piste : " . $e->getMessage());
        }
    }

    public static function findPlaylistById(int $id): ?Playlist {
        try {
            $db = self::getInstance();
    

            $stmt = $db->prepare('SELECT * FROM playlist WHERE id = :id');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $playlistData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$playlistData) {
                return null; 
            }
    
            $stmt = $db->prepare('
                SELECT t.* FROM track t
                JOIN playlist2track p2t ON p2t.id_track = t.id
                WHERE p2t.id_pl = :id
                ORDER BY p2t.no_piste_dans_liste
            ');
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $tracksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $tracks = [];
            foreach ($tracksData as $trackData) {
                $track = new PodcastTrack(
                    (int)$trackData['id'],
                    $trackData['titre'],
                    $trackData['filename'],
                    $trackData['auteur_podcast'] ?? 'Inconnu',
                    $trackData['date_posdcast'] ?? '1970-01-01',
                    $trackData['genre'] ?? 'Genre Inconnu',
                    (int)$trackData['duree'] ?? 0
                );
                $tracks[] = $track;
            }
    
           
            $playlist = new Playlist($playlistData['nom'], $tracks, (int)$playlistData['id']);
    
            return $playlist;
    
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la playlist : " . $e->getMessage());
        }
    }

    public static function saveTrack(PodcastTrack $track): int {
        try {
            $db = self::getInstance();
    
            $stmt = $db->prepare('INSERT INTO track (titre, filename, auteur_podcast, date_podcast, genre, duree) VALUES (:titre, :filename, :auteur_podcast, :date_podcast, :genre, :duree)');
    

            $titre = $track->getTitre();
            $filename = $track->getNomFichierAudio();
            $auteur = $track->getAuteur();
            $date = $track->getDate();
            $genre = $track->getGenre();
            $duree = $track->getDuree();
    
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':auteur_podcast', $auteur);
            $stmt->bindParam(':date_podcast', $date);
            $stmt->bindParam(':genre', $genre);
            $stmt->bindParam(':duree', $duree, PDO::PARAM_INT);
    
            $stmt->execute();
            return $db->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la sauvegarde de la piste : " . $e->getMessage());
        }
    }
    
    public static function savePlaylistWithTracks(Playlist $playlist, int $userId): int {
        try {
            $db = self::getInstance();
            

            $stmt = $db->prepare('INSERT INTO playlist (nom) VALUES (:nom)');
            $nomPlaylist = $playlist->getNom(); 
            $stmt->bindParam(':nom', $nomPlaylist);
            $stmt->execute();
            
            $playlistId = $db->lastInsertId();
    
            $stmt = $db->prepare('INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :playlist_id)');
            $stmt->bindParam(':id_user', $userId);
            $stmt->bindParam(':playlist_id', $playlistId);
            $stmt->execute();
    
            foreach ($playlist->getTracks() as $track) {
                $stmt = $db->prepare('INSERT INTO playlist2track (id_pl, id_track) VALUES (:playlist_id, :track_id)');
                $playlistIdVar = $playlistId; 
                $trackIdVar = $track->getId();
                $stmt->bindParam(':playlist_id', $playlistIdVar);
                $stmt->bindParam(':track_id', $trackIdVar);
                $stmt->execute();
            }
    
            return $playlistId;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la sauvegarde de la playlist et des pistes : " . $e->getMessage());
        }
    }

    public static function findPlaylistsByUserId(int $userId): array {
        $stmt = self::getInstance()->prepare('
            SELECT id, nom FROM playlist p
            JOIN user2playlist up ON p.id = up.id_pl
            WHERE up.id_user = :user_id
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    
        $playlistsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $playlists = [];
    
        foreach ($playlistsData as $data) {
            $playlists[] = new Playlist($data['nom'], [], (int)$data['id']);
        }
    
        return $playlists;
    }
    
    public static function getTracksForPlaylist(int $playlistId): array {
        try {
            $db = self::getInstance();
            $stmt = $db->prepare('
                SELECT t.* FROM track t
                JOIN playlist2track p2t ON p2t.id_track = t.id
                WHERE p2t.id_pl = :playlist_id
            ');
            $stmt->bindParam(':playlist_id', $playlistId, PDO::PARAM_INT);
            $stmt->execute();
    
            $tracksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tracks = [];
    
            foreach ($tracksData as $data) {
                $tracks[] = new PodcastTrack(
                    (int) $data['id'],
                    $data['titre'],
                    $data['filename'],
                    $data['auteur_podcast'] ?? 'Inconnu',
                    $data['date_posdcast'] ?? '1970-01-01',
                    $data['genre'] ?? 'Genre Inconnu',
                    (int) $data['duree'] ?? 0
                );
            }
    
            return $tracks;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des pistes pour la playlist : " . $e->getMessage());
        }
    }

    public static function findUserIdByEmail(string $email): ?int {
        try {
            $db = self::getInstance();
            $stmt = $db->prepare('SELECT id FROM user WHERE email = :email');
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
    
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $userData ? (int) $userData['id'] : null; 

        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de l'ID utilisateur : " . $e->getMessage());
        }
    }
    public static function getTracksByPlaylistId(int $playlistId): array {
        try {
            $db = self::getInstance();
            $stmt = $db->prepare('
                SELECT t.* FROM track t
                JOIN playlist2track p2t ON t.id = p2t.id_track
                WHERE p2t.id_pl = :playlist_id
            ');
            $stmt->bindParam(':playlist_id', $playlistId, PDO::PARAM_INT);
            $stmt->execute();
    
            $tracksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tracks = [];
    
            foreach ($tracksData as $data) {
                $tracks[] = new PodcastTrack(
                    (int) $data['id'],
                    $data['titre'],
                    $data['filename'],
                    $data['auteur_podcast'] ?? 'Inconnu',
                    $data['date_posdcast'] ?? '1970-01-01',
                    $data['genre'] ?? 'Genre Inconnu',
                    (int) $data['duree'] ?? 0
                );
            }
    
            return $tracks;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des pistes de la playlist : " . $e->getMessage());
        }
    }
    public static function addTrackToPlaylist(int $playlistId, int $trackId): void {
        try {
            $db = self::getInstance();
            
            $stmt = $db->prepare('SELECT COUNT(*) as count FROM playlist2track WHERE id_pl = :playlist_id');
            $stmt->bindParam(':playlist_id', $playlistId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $noPisteDansListe = isset($result['count']) ? (int)$result['count'] + 1 : 1; // Incrémente pour la nouvelle piste
            
            $stmt = $db->prepare('INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste) VALUES (:playlist_id, :track_id, :no_piste_dans_liste)');
            $stmt->bindParam(':playlist_id', $playlistId, PDO::PARAM_INT);
            $stmt->bindParam(':track_id', $trackId, PDO::PARAM_INT);
            $stmt->bindParam(':no_piste_dans_liste', $noPisteDansListe, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return;
            } else {
                throw new \Exception("Échec de l'ajout de la piste à la playlist.");
            }
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de l'ajout de la piste à la playlist : " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception("Erreur: " . $e->getMessage());
        }
    }

    public static function deletePlaylistById(int $playlistId): void {
        $db = self::getInstance();
    
        try {
            $stmt = $db->prepare('DELETE FROM user2playlist WHERE id_pl = :id');
            $stmt->execute([':id' => $playlistId]);
    
            $stmt = $db->prepare('DELETE FROM playlist2track WHERE id_pl = :id');
            $stmt->execute([':id' => $playlistId]);
    
            $stmt = $db->prepare('DELETE FROM playlist WHERE id = :id');
            $stmt->execute([':id' => $playlistId]);
    
        } catch (\PDOException $e) {
            throw new \PDOException("Erreur lors de la suppression de la playlist : " . $e->getMessage());
        }
    }
    public static function deleteTrackById(int $trackId): void {
        $db = self::getInstance();
    
        try {
            $db->beginTransaction();
    
            $stmt = $db->prepare('DELETE FROM playlist2track WHERE id_track = :trackId');
            $stmt->bindParam(':trackId', $trackId, PDO::PARAM_INT);
            $stmt->execute();
    
            $stmt = $db->prepare('DELETE FROM track WHERE id = :trackId');
            $stmt->bindParam(':trackId', $trackId, PDO::PARAM_INT);
            $stmt->execute();
    
            $db->commit();
        } catch (\PDOException $e) {
            $db->rollBack();
            throw new \PDOException("Erreur lors de la suppression de la piste : " . $e->getMessage());
        }
    }

    public static function addPlaylist(int $userId, string $playlistName): void {
        $db = self::getInstance();
        

        $stmt = $db->prepare("INSERT INTO playlist (nom) VALUES (:playlist_name)");
        $stmt->bindParam(':playlist_name', $playlistName, PDO::PARAM_STR);
        $stmt->execute();
    

        $playlistId = $db->lastInsertId();
        
        $stmt = $db->prepare("INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :id_pl)");
        $stmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':id_pl', $playlistId, PDO::PARAM_INT);
        $stmt->execute();
    }
    public static function getAllPlaylists(): array {
        $db = self::getInstance();
        $stmt = $db->prepare("SELECT * FROM playlist");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $playlists = [];
        foreach ($results as $row) {
            $playlists[] = new Playlist($row['nom'], [], (int)$row['id']);
        }
        return $playlists;
    }
}