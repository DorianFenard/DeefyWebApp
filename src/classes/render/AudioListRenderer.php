<?php
declare(strict_types=1);

namespace iutnc\deefy\render;

require_once 'Renderer.php';

use \iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    private AudioList $audioList;

    public function __construct(AudioList $audioList)
    {
        $this->audioList = $audioList;
    }

    public function render(int $mode): string
    {
        $html = "<h1>{$this->audioList->nom}</h1>";
        $html .= "<ul>";


        foreach ($this->audioList->__get('tracks') as $track) {

            $urlFichierAudio = "audio/{$track->__get('titre')}.mp3";

            $html .= "<li>
                      {$track->__get('titre')} ({$track->__get('duree')} s)
                        <audio controls>
                          <source src='{$urlFichierAudio}' type='audio/mpeg'>
                            Votre navigateur ne supporte pas la balise audio.
                        </audio>
                      </li>";
        }

        $nbPistes = count($this->audioList->__get('tracks'));
        $dureeTotale = array_sum(array_map(function ($track) {
            return $track->__get('duree');
        }, $this->audioList->__get('tracks')));

        $html .= "</ul>";
        $html .= "<p><strong>Nombre de pistes :</strong> $nbPistes</p>";
        $html .= "<p><strong>Durée totale :</strong> $dureeTotale secondes</p>";

        return $html;
    }
}
