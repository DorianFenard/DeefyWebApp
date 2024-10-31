<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

/**
 * Action par défaut
 */

class DefaultAction extends Action {

    public function execute(): string {
        return "<div>Bienvenue !</div>";
    }
}