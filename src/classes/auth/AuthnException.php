<?php
namespace iutnc\deefy\auth;

/**
 * Exception qui est levée lorsqu'une erreur d'authentification survient.
 */
class AuthnException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}