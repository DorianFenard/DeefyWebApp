<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\tracks;
use iutnc\deefy\exception;

/**
 * Classe représentant une piste d'album.
 */
class AlbumTrack {
    private string $titre;
    private string $fichierAudio;
    private int $duree;

    public function __construct(string $titre, string $fichierAudio, int $duree) {
        $this->titre = $titre;
        $this->fichierAudio = $fichierAudio;
        $this->duree = $duree;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new \InvalidArgumentException("Propriété non définie : $property");
    }

    /**
     * @return string
     */
    public function getTitre(): string
    {
        return $this->titre;
    }

    /**
     * @return int
     */
    public function getDuree(): int
    {
        return $this->duree;
    }

    /**
     * @return string
     */
    public function getFichier(): string
    {
        return $this->fichierAudio;
    }
}
?>