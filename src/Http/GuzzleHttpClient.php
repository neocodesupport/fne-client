<?php

namespace Neocode\FNE\Http;

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
     * Create a new GuzzleHttpClient instance.
     */
    public function __construct()
    {
        if (!class_exists(\GuzzleHttp\Client::class)) {
            throw new \RuntimeException('Guzzle HTTP Client is not available.');
        }

        $this->client = new \GuzzleHttp\Client();
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
            'headers' => $options['headers'] ?? [],
            'timeout' => $options['timeout'] ?? 30,
        ];

        // Ajouter le body si présent
        if (isset($options['body'])) {
            if (is_string($options['body'])) {
                $guzzleOptions['body'] = $options['body'];
            } else {
                $guzzleOptions['json'] = $options['body'];
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

