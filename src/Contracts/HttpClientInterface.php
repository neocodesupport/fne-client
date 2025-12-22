<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour le client HTTP
 */
interface HttpClientInterface
{
    /**
     * Envoyer une requête HTTP.
     *
     * @param  string  $method  Méthode HTTP (GET, POST, etc.)
     * @param  string  $uri  URI de la requête
     * @param  array<string, mixed>  $options  Options de la requête (headers, body, etc.)
     * @return mixed  Réponse HTTP (peut être Laravel Response, PSR-7 ResponseInterface, ou autre)
     */
    public function request(string $method, string $uri, array $options = []): mixed;
}

