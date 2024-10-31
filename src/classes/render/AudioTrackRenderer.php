<?php
declare(strict_types=1);

namespace iutnc\deefy\render;
use iutnc\deefy\render\Renderer;

require_once 'Renderer.php';
/**
 * Classe abstraite permettant de générer le code HTML pour afficher un fichier audio.
 */

abstract class AudioTrackRenderer implements Renderer
{

    protected $track;

    public function __construct($track)
    {
        $this->track = $track;
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

    protected abstract function renderCompact(): string;

    protected abstract function renderLong(): string;
}

?>