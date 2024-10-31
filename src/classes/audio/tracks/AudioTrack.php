<?php

namespace iutnc\deefy\audio\tracks;

/**
 * Classe abstraite représentant une piste audio.
 */
abstract class AudioTrack {
    protected string $titre;
    protected string $nomFichierAudio;
    protected ?int $duree;

    public function __construct(string $titre, string $nomFichierAudio, ?int $duree = null) {
        $this->titre = $titre;
        $this->nomFichierAudio = $nomFichierAudio;
        $this->duree = $duree ?? 0; 

    }


    public function getTitre(): string {
        return $this->titre;
    }

    public function getNomFichierAudio(): string {
        return $this->nomFichierAudio;
    }

    public function getDuree(): ?int {
        return $this->duree;
    }
}