<?php
declare(strict_types=1);

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\Renderer;

require_once 'Renderer.php';

/**
 * Classe permettant de générer le code HTML pour afficher un podcast.
 */
class PodcastRenderer implements Renderer
{
    private PodcastTrack $podcast;
    const COMPACT = 0;
    const LONG = 1;

    public function __construct(PodcastTrack $podcast)
    {
        $this->podcast = $podcast;
    }

    public function render(int $mode): string
    {
        switch ($mode) {
            case self::COMPACT:
                return $this->renderCompact();
            case self::LONG:
                return $this->renderLong();
            default:
                return "<p>Mode d'affichage inconnu.</p>";
        }
    }

    private function renderCompact(): string
    {
        $urlFichierAudio = "audio/{$this->podcast->titre}.mp3";

        return "
            <div class='podcast compact'>
                <p><strong>{$this->podcast->titre}</strong> - Auteur: {$this->podcast->auteur}</p>
                <audio controls>
                    <source src='{$urlFichierAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>
            </div>
        ";
    }

    private function renderLong(): string
    {
        $urlFichierAudio = "audio/{$this->podcast->titre}.mp3";

        return "
            <div class='podcast long'>
                <h1>{$this->podcast->titre}</h1>
                <p><strong>Auteur :</strong> {$this->podcast->auteur}</p>
                <p><strong>Date :</strong> {$this->podcast->date}</p>
                <p><strong>Genre :</strong> {$this->podcast->genre}</p>
                <p><strong>Durée :</strong> {$this->podcast->duree} secondes</p>
                <audio controls>
                    <source src='{$urlFichierAudio}' type='audio/mpeg'>
                    Votre navigateur ne supporte pas la balise audio.
                </audio>
            </div>
        ";
    }
}
