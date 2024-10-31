<?php
declare(strict_types=1);

namespace iutnc\deefy\exception;

/**
 * Exception levée lorsqu'un nom de propriété invalide est utilisé
 */

class InvalidPropertyNameException extends \Exception {
    public function __construct($property) {
        parent::__construct("Invalid property name: $property");
    }
}
?>