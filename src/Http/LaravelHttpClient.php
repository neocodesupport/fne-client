<?php

namespace Neocode\FNE\Http;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Http\ResponseHandler;

/**
 * Client HTTP utilisant Laravel HTTP Client
 *
 * @package Neocode\FNE\Http
 */
class LaravelHttpClient implements HttpClientInterface
{
    /**
     * Factory Laravel HTTP Client
     */
    protected \Illuminate\Http\Client\Factory $client;

    /**
     * Configuration
     */
    protected FNEConfig $config;

    /**
     * Logger
     */
    protected ?LoggerInterface $logger;

    /**
     * Create a new LaravelHttpClient instance.
     */
    public function __construct(FNEConfig $config, ?LoggerInterface $logger = null)
    {
        if (!class_exists(\Illuminate\Http\Client\Factory::class)) {
            throw new \RuntimeException('Laravel HTTP Client is not available.');
        }

        $this->config = $config;
        $this->logger = $logger;
        $this->client = new \Illuminate\Http\Client\Factory();
    }

    /**
     * Envoyer une requête HTTP.
     *
     * @param  string  $method  Méthode HTTP
     * @param  string  $uri  URI de la requête
     * @param  array<string, mixed>  $options  Options (headers, body, timeout, etc.)
     * @return \Illuminate\Http\Client\Response
     */
    public function request(string $method, string $uri, array $options = []): mixed
    {
        $headers = $options['headers'] ?? [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->config->getApiKey(),
        ];
        $body = $options['body'] ?? null;
        $timeout = $options['timeout'] ?? $this->config->getTimeout();

        $request = $this->client
            ->baseUrl($this->config->getBaseUrl())
            ->timeout($timeout);

        // Ajouter les headers
        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        // Faire la requête
        $response = match (strtoupper($method)) {
            'GET' => $request->get($uri),
            'POST' => $request->post($uri, is_string($body) ? json_decode($body, true) : $body),
            'PUT' => $request->put($uri, is_string($body) ? json_decode($body, true) : $body),
            'PATCH' => $request->patch($uri, is_string($body) ? json_decode($body, true) : $body),
            'DELETE' => $request->delete($uri),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        // Gérer les erreurs HTTP
        ResponseHandler::handleLaravelResponse($response);

        return $response;
    }
}

