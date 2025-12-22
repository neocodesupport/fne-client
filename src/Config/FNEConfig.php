<?php

namespace Neocode\FNE\Config;

/**
 * Configuration centralisée du package FNE
 */
class FNEConfig
{
    /**
     * Clé API FNE
     */
    protected string $apiKey;

    /**
     * URL de base de l'API FNE
     */
    protected string $baseUrl;

    /**
     * Mode (test ou production)
     */
    protected string $mode;

    /**
     * Timeout pour les requêtes HTTP (en secondes)
     */
    protected int $timeout;

    /**
     * Activer le cache
     */
    protected bool $cacheEnabled;

    /**
     * TTL par défaut du cache (en secondes)
     */
    protected int $cacheTtl;

    /**
     * Locale pour les messages (fr, en)
     */
    protected string $locale;

    /**
     * Create a new FNE Config instance.
     *
     * @param  array<string, mixed>  $config  Configuration
     */
    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? $this->getDefaultBaseUrl();
        $this->mode = $config['mode'] ?? 'test';
        $this->timeout = $config['timeout'] ?? 30;
        $this->cacheEnabled = $config['cache_enabled'] ?? true;
        $this->cacheTtl = $config['cache_ttl'] ?? 3600;
        $this->locale = $config['locale'] ?? 'fr';
    }

    /**
     * Obtenir l'URL de base par défaut selon le mode.
     */
    protected function getDefaultBaseUrl(): string
    {
        // En mode test, utiliser l'API locale
        if (($this->mode ?? 'test') === 'test') {
            return 'https://fne-api-mock.test';
        }

        // En production, URL de production (sera fournie par la DGI)
        return 'https://fne.dgi.gouv.ci/ws';
    }

    /**
     * Obtenir la clé API.
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Obtenir l'URL de base.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Obtenir le mode.
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Vérifier si on est en mode test.
     */
    public function isTestMode(): bool
    {
        return $this->mode === 'test';
    }

    /**
     * Vérifier si on est en mode production.
     */
    public function isProduction(): bool
    {
        return $this->mode === 'production';
    }

    /**
     * Obtenir le timeout.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Vérifier si le cache est activé.
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    /**
     * Obtenir le TTL du cache.
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Obtenir la locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Valider la configuration.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }

        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Base URL is required');
        }

        if (!in_array($this->mode, ['test', 'production'])) {
            throw new \InvalidArgumentException('Mode must be "test" or "production"');
        }
    }
}

