<?php

namespace Neocode\FNE\Http\Middleware;

use Neocode\FNE\Config\FNEConfig;

/**
 * Middleware pour l'authentification API FNE
 *
 * Ajoute automatiquement les en-têtes d'authentification aux requêtes.
 *
 * @package Neocode\FNE\Http\Middleware
 */
class AuthMiddleware
{
    /**
     * Configuration FNE
     */
    private FNEConfig $config;

    /**
     * Create a new AuthMiddleware instance.
     *
     * @param  FNEConfig  $config
     */
    public function __construct(FNEConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Appliquer le middleware à une requête.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function handle(array $options): array
    {
        $headers = $options['headers'] ?? [];

        // Ajouter l'en-tête d'authentification
        $headers['Authorization'] = 'Bearer ' . $this->config->getApiKey();

        $options['headers'] = $headers;

        return $options;
    }
}

