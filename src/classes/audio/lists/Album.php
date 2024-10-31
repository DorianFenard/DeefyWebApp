<?php
declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

require_once 'AudioList.php';

/**
    * Classe représentant un album musical.
    */
class Album extends AudioList {
    private string $artiste;
    private string $dateSortie;

    public function __construct(string $nom, array $tracks, string $artiste, string $dateSortie) {
        parent::__construct($nom, $tracks);
        $this->artiste = $artiste;
        $this->dateSortie = $dateSortie;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new \Exception("Propriété non définie : $property");
    }
}
?>