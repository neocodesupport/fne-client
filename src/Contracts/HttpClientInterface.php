<?php

namespace Neocode\FNE\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface;
}

