<?php
declare(strict_types=1);

namespace iutnc\deefy\exception;

/**
 * Exception levée lorsqu'une valeur invalide est affectée à une propriété.
 */

class InvalidPropertyValueException extends \Exception {
    public function __construct($property, $value) {
        parent::__construct("Invalid value for property $property: $value");
    }
}
?>