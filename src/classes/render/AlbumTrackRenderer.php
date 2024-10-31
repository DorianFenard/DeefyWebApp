<?php
declare(strict_types=1);

namespace render;

use iutnc\deefy\render\AudioTrackRenderer;

// Classe permettant de générer le code HTML pour afficher un fichier audio d'un album.
class AlbumTrackRenderer extends AudioTrackRenderer
{
    protected function renderCompact(): string
    {
        return "
            <div class='album-track compact'>
                <p><strong>{$this->track->titre}</strong> - {$this->track->artiste}</p>
                <audio controls>
                    <source src='{$this->track->nomFichierAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>
            </div>
        ";
    }

    protected function renderLong(): string
    {
        return "
            <div class='album-track long'>
                <h1>{$this->track->titre}</h1>
                <p><strong>Artiste :</strong> {$this->track->artiste}</p>
                <p><strong>Album :</strong> {$this->track->album}</p>
                <p><strong>Année :</strong> {$this->track->annee}</p>
                <p><strong>Genre :</strong> {$this->track->genre}</p>
                <p><strong>Durée :</strong> {$this->track->duree} secondes</p>
                <audio controls>
                    <source src='{$this->track->nomFichierAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>
            </div>
        ";
    }
}

?>