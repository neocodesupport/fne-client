<?php

namespace Neocode\FNE\Http;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Http\Middleware\AuthMiddleware;
use Neocode\FNE\Http\Middleware\LoggingMiddleware;

/**
 * Builder pour construction de requêtes HTTP
 *
 * Permet de construire des requêtes HTTP de manière fluide et flexible.
 *
 * @package Neocode\FNE\Http
 */
class RequestBuilder
{
    /**
     * Configuration FNE
     */
    private FNEConfig $config;

    /**
     * URL de base
     */
    private string $baseUrl;

    /**
     * Méthode HTTP
     */
    private string $method = 'POST';

    /**
     * Endpoint
     */
    private string $endpoint = '';

    /**
     * En-têtes HTTP
     *
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * Corps de la requête
     *
     * @var array<string, mixed>|null
     */
    private ?array $body = null;

    /**
     * Timeout en secondes
     */
    private int $timeout = 30;

    /**
     * Middleware d'authentification
     */
    private ?AuthMiddleware $authMiddleware = null;

    /**
     * Middleware de logging
     */
    private ?LoggingMiddleware $loggingMiddleware = null;

    /**
     * Create a new RequestBuilder instance.
     *
     * @param  FNEConfig  $config
     */
    public function __construct(FNEConfig $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config->getBaseUrl(), '/');
        $this->authMiddleware = new AuthMiddleware($config);

        // En-têtes par défaut
        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'FNE-Client/1.0',
        ];
    }

    /**
     * Définir la méthode HTTP.
     *
     * @param  string  $method
     * @return self
     */
    public function method(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Définir l'endpoint.
     *
     * @param  string  $endpoint
     * @return self
     */
    public function endpoint(string $endpoint): self
    {
        $this->endpoint = ltrim($endpoint, '/');

        return $this;
    }

    /**
     * Ajouter un en-tête HTTP.
     *
     * @param  string  $key
     * @param  string  $value
     * @return self
     */
    public function withHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Ajouter plusieurs en-têtes HTTP.
     *
     * @param  array<string, string>  $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Définir le corps de la requête.
     *
     * @param  array<string, mixed>  $body
     * @return self
     */
    public function withBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Définir le timeout.
     *
     * @param  int  $timeout
     * @return self
     */
    public function withTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Définir le middleware de logging.
     *
     * @param  LoggingMiddleware|null  $middleware
     * @return self
     */
    public function withLogging(?LoggingMiddleware $middleware): self
    {
        $this->loggingMiddleware = $middleware;

        return $this;
    }

    /**
     * Construire les options de la requête.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $options = [
            'headers' => $this->headers,
            'timeout' => $this->timeout,
        ];

        // Ajouter le corps si présent
        if ($this->body !== null) {
            $options['json'] = $this->body;
        }

        // Appliquer le middleware d'authentification
        if ($this->authMiddleware) {
            $options = $this->authMiddleware->handle($options);
        }

        return $options;
    }

    /**
     * Obtenir l'URL complète.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->baseUrl . '/' . $this->endpoint;
    }

    /**
     * Obtenir la méthode HTTP.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Logger la requête si le middleware de logging est configuré.
     *
     * @return void
     */
    public function logRequest(): void
    {
        if ($this->loggingMiddleware) {
            $this->loggingMiddleware->logRequest(
                $this->method,
                $this->getUrl(),
                $this->build()
            );
        }
    }
}

