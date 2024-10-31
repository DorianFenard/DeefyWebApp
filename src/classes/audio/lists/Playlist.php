<?php
declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\PodcastTrack;

/**
 * Classe représentant une liste de lecture audio.
 */
class Playlist extends AudioList
{
    protected int $id;


    public function __construct(string $nom, array $tracks = [], int $id) {
        $this->nom = $nom;
        $this->tracks = $tracks;
        $this->id = $id;
    }

    public function ajouterPiste(PodcastTrack $track): void 
    {
        $this->tracks[] = $track;
        $this->nbPistes = count($this->tracks);
        $this->dureeTotale = array_sum(array_map(fn($track) => $track->__get('duree'), $this->tracks));
    }

    public function supprimerPiste(int $index): void
    {
        unset($this->tracks[$index]);
        $this->tracks = array_values($this->tracks);
        $this->nbPistes = count($this->tracks);
        $this->dureeTotale = array_sum(array_map(fn($track) => $track->__get('duree'), $this->tracks));
    }

    public function ajouterListePistes(array $tracks): void
    {
        foreach ($tracks as $track) {
            if (!in_array($track, $this->tracks)) {
                $this->ajouterPiste($track);
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getTracks(): array
    {
        return $this->tracks;
    }

    public function __get($property)
    {
        return parent::__get($property);
    }
}
?>