<?php
namespace iutnc\deefy\audio\lists;

/**
 * Classe représentant une liste de lecture audio.
 */
class AudioList
{
    protected string $nom;
    protected ?array $tracks;

    public function __construct(string $nom, array $tracks = []) 
    {
        $this->nom = $nom;
        $this->tracks = $tracks;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new \Exception("Propriété non définie : $property");
    }
}