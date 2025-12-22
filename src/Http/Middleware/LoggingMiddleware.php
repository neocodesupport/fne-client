<?php

namespace Neocode\FNE\Http\Middleware;

use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Helpers\StringHelper;

/**
 * Middleware pour le logging des requêtes HTTP
 *
 * Log toutes les requêtes et réponses HTTP pour le débogage et l'audit.
 *
 * @package Neocode\FNE\Http\Middleware
 */
class LoggingMiddleware
{
    /**
     * Logger
     */
    private ?LoggerInterface $logger;

    /**
     * Masquer les données sensibles dans les logs
     */
    private bool $maskSensitiveData;

    /**
     * Create a new LoggingMiddleware instance.
     *
     * @param  LoggerInterface|null  $logger
     * @param  bool  $maskSensitiveData
     */
    public function __construct(?LoggerInterface $logger = null, bool $maskSensitiveData = true)
    {
        $this->logger = $logger;
        $this->maskSensitiveData = $maskSensitiveData;
    }

    /**
     * Logger une requête avant envoi.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array<string, mixed>  $options
     * @return void
     */
    public function logRequest(string $method, string $url, array $options): void
    {
        if (!$this->logger) {
            return;
        }

        $data = [
            'method' => $method,
            'url' => $url,
            'headers' => $this->sanitizeHeaders($options['headers'] ?? []),
            'body' => $this->sanitizeBody($options['body'] ?? $options['json'] ?? null),
        ];

        $this->logger->debug('FNE API Request', $data);
    }

    /**
     * Logger une réponse après réception.
     *
     * @param  int  $statusCode
     * @param  array<string, mixed>  $body
     * @param  float  $duration
     * @return void
     */
    public function logResponse(int $statusCode, array $body, float $duration): void
    {
        if (!$this->logger) {
            return;
        }

        $data = [
            'status_code' => $statusCode,
            'body' => $this->sanitizeBody($body),
            'duration_ms' => round($duration * 1000, 2),
        ];

        $level = $statusCode >= 400 ? 'error' : 'info';
        $this->logger->{$level}('FNE API Response', $data);
    }

    /**
     * Logger une erreur.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function logError(\Throwable $exception): void
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->error('FNE API Error', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    /**
     * Nettoyer les en-têtes pour les logs (masquer les données sensibles).
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    private function sanitizeHeaders(array $headers): array
    {
        if (!$this->maskSensitiveData) {
            return $headers;
        }

        $sanitized = $headers;

        // Masquer l'API key dans Authorization
        if (isset($sanitized['Authorization'])) {
            $sanitized['Authorization'] = StringHelper::mask($sanitized['Authorization'], 0, 0);
        }

        return $sanitized;
    }

    /**
     * Nettoyer le corps de la requête pour les logs.
     *
     * @param  mixed  $body
     * @return mixed
     */
    private function sanitizeBody(mixed $body): mixed
    {
        if (!$this->maskSensitiveData || !is_array($body)) {
            return $body;
        }

        $sanitized = $body;

        // Masquer les champs sensibles
        $sensitiveFields = ['api_key', 'password', 'token', 'secret'];
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = StringHelper::mask((string) $sanitized[$field]);
            }
        }

        return $sanitized;
    }
}

