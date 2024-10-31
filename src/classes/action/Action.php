<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

/**
 * Classe abstraite Action
 */
abstract class Action {

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __construct() {
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Fonction execute qui sépare le traitement selon la méthode HTTP demandée
     * @return string
     */
    public function execute(): string {
        //echo "<div>La méthode execute() est appelée</div>";
        if ($this->http_method === 'GET') {
            return $this->handleGet();
        } elseif ($this->http_method === 'POST') {
            return $this->handlePost();
        } else {
            return "<div>Méthode HTTP non supportée</div>";
        }
    }


    /**
     * Fonction handleGet qui sera redéfinie dans les classes héritées
     * @return string
     */
    protected function handleGet(): string {
      return "a";
    }
    /**
     * Fonction handlePost qui sera redéfinie dans les classes héritées
     * @return string
     */

    protected function handlePost(): string {
        echo "<div>handlePost() est bien appelé</div>";
        return "<div>Test : handlePost() a été appelé avec succès.</div>";
    }
}