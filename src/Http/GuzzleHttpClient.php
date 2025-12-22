<?php

namespace Neocode\FNE\Http;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Http\ResponseHandler;
use Psr\Http\Message\ResponseInterface;

/**
 * Client HTTP utilisant Guzzle
 *
 * @package Neocode\FNE\Http
 */
class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * Client Guzzle
     */
    protected \GuzzleHttp\Client $client;

    /**
     * Configuration
     */
    protected FNEConfig $config;

    /**
     * Create a new GuzzleHttpClient instance.
     */
    public function __construct(FNEConfig $config)
    {
        if (!class_exists(\GuzzleHttp\Client::class)) {
            throw new \RuntimeException('Guzzle HTTP Client is not available.');
        }

        $this->config = $config;

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config->getBaseUrl(),
            'timeout' => $config->getTimeout(),
            'http_errors' => false, // On gère les erreurs manuellement
            'verify' => $config->isTestMode() ? false : true, // Désactiver SSL verification en mode test
        ]);
    }

    /**
     * Envoyer une requête HTTP.
     *
     * @param  string  $method  Méthode HTTP
     * @param  string  $uri  URI de la requête
     * @param  array<string, mixed>  $options  Options (headers, body, timeout, etc.)
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): mixed
    {
        $guzzleOptions = [
            'headers' => $options['headers'] ?? [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            ],
            'timeout' => $options['timeout'] ?? $this->config->getTimeout(),
        ];

        // Ajouter le body si présent
        if (isset($options['body'])) {
            if (is_string($options['body'])) {
                $guzzleOptions['body'] = $options['body'];
            } else {
                $guzzleOptions['json'] = json_decode($options['body'], true) ?? $options['body'];
            }
        }

        try {
            $response = $this->client->request($method, $uri, $guzzleOptions);

            // Gérer les erreurs HTTP
            ResponseHandler::handleGuzzleResponse($response);

            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            ResponseHandler::handleGuzzleException($e);
            throw $e;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            ResponseHandler::handleGuzzleException($e);
            throw $e;
        }
    }
}

