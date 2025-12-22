<?php

namespace Neocode\FNE\Http;

use Neocode\FNE\Exceptions\AuthenticationException;
use Neocode\FNE\Exceptions\BadRequestException;
use Neocode\FNE\Exceptions\NotFoundException;
use Neocode\FNE\Exceptions\ServerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Gestionnaire de réponses HTTP
 *
 * @package Neocode\FNE\Http
 */
class ResponseHandler
{
    /**
     * Gérer une réponse Laravel HTTP Client.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     * @throws \Neocode\FNE\Exceptions\FNEException
     */
    public static function handleLaravelResponse(\Illuminate\Http\Client\Response $response): void
    {
        $statusCode = $response->status();

        // Succès (200-299)
        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        // Erreurs
        $body = $response->json();
        $message = $body['message'] ?? self::getDefaultMessage($statusCode);
        $errors = $body['errors'] ?? [];

        match ($statusCode) {
            400 => throw new BadRequestException($message, $errors),
            401 => throw new AuthenticationException($message),
            404 => throw new NotFoundException($message),
            default => throw new ServerException($message),
        };
    }

    /**
     * Gérer une réponse Guzzle.
     *
     * @param  ResponseInterface  $response
     * @return void
     * @throws \Neocode\FNE\Exceptions\FNEException
     */
    public static function handleGuzzleResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        // Succès (200-299)
        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        // Erreurs
        $bodyContent = $response->getBody()->getContents();
        $body = json_decode($bodyContent, true) ?? [];
        $message = $body['message'] ?? self::getDefaultMessage($statusCode);
        $errors = $body['errors'] ?? [];

        match ($statusCode) {
            400, 422 => throw new BadRequestException($message, $errors),
            401, 403 => throw new AuthenticationException($message),
            404 => throw new NotFoundException($message),
            default => throw new ServerException($message),
        };
    }

    /**
     * Gérer une exception Guzzle.
     *
     * @param  \GuzzleHttp\Exception\RequestException  $exception
     * @return void
     * @throws \Neocode\FNE\Exceptions\FNEException
     */
    public static function handleGuzzleException(\GuzzleHttp\Exception\RequestException $exception): void
    {
        $response = $exception->getResponse();

        if ($response === null) {
            throw new ServerException('Network error: ' . $exception->getMessage(), $exception);
        }

        self::handleGuzzleResponse($response);
    }

    /**
     * Obtenir un message par défaut selon le code de statut.
     *
     * @param  int  $statusCode
     * @return string
     */
    protected static function getDefaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            default => "HTTP Error {$statusCode}",
        };
    }
}

