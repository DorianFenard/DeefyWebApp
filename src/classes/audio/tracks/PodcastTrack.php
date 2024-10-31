<?php

namespace iutnc\deefy\audio\tracks;

/**
 * Classe représentant une piste de podcast.
 */
class PodcastTrack extends AudioTrack {
    protected int $id; 
    protected string $auteur;
    protected string $date;
    protected string $genre;

    public function __construct(
        int $id, 
        string $titre,
        string $nomFichierAudio,
        string $auteur,
        string $date,
        string $genre,
        ?int $duree = null
    ) {
        parent::__construct($titre, $nomFichierAudio, $duree);
        $this->id = $id; 
        $this->auteur = $auteur;
        $this->date = $date;
        $this->genre = $genre;
    }

    public function getAuteur(): string {
        return $this->auteur;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getGenre(): string {
        return $this->genre;
    }

    public function getId(): int {
        return $this->id;
    }
    
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
}