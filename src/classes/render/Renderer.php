<?php
declare(strict_types=1);

namespace iutnc\deefy\render;

interface Renderer {

    /**
     * Génère le code HTML pour afficher un élément.
     * @param int $mode
     * @return string
     */
    public function render(int $mode): string;
}
?>