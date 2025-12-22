<?php

namespace Neocode\FNE\Http;

use Neocode\FNE\Contracts\HttpClientInterface;

/**
 * Factory pour créer le client HTTP approprié
 *
 * @package Neocode\FNE\Http
 */
class HttpClientFactory
{
    /**
     * Créer le client HTTP approprié selon les dépendances disponibles.
     *
     * @return HttpClientInterface
     */
    public static function create(): HttpClientInterface
    {
        // Priorité 1 : Laravel HTTP Client
        if (self::isLaravelHttpAvailable()) {
            return new LaravelHttpClient();
        }

        // Priorité 2 : Guzzle HTTP Client
        if (self::isGuzzleAvailable()) {
            return new GuzzleHttpClient();
        }

        throw new \RuntimeException(
            'No HTTP client available. Please install either "illuminate/http" or "guzzlehttp/guzzle".'
        );
    }

    /**
     * Vérifier si Laravel HTTP Client est disponible.
     *
     * @return bool
     */
    protected static function isLaravelHttpAvailable(): bool
    {
        return class_exists(\Illuminate\Http\Client\Factory::class);
    }

    /**
     * Vérifier si Guzzle est disponible.
     *
     * @return bool
     */
    protected static function isGuzzleAvailable(): bool
    {
        return class_exists(\GuzzleHttp\Client::class);
    }
}

