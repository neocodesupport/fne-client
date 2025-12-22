<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de base pour toutes les exceptions FNE
 *
 * @package Neocode\FNE\Exceptions
 */
class FNEException extends \Exception
{
    /**
     * Code d'erreur
     */
    protected string $errorCode;

    /**
     * Code de statut HTTP
     */
    protected int $statusCode;

    /**
     * Erreurs de validation (si applicable)
     */
    protected array $errors = [];

    /**
     * Métadonnées supplémentaires
     */
    protected array $meta = [];

    /**
     * Create a new FNEException instance.
     *
     * @param  string  $message
     * @param  string  $errorCode
     * @param  int  $statusCode
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     * @param  \Throwable|null  $previous
     */
    public function __construct(
        string $message = '',
        string $errorCode = 'fne_error',
        int $statusCode = 500,
        array $errors = [],
        array $meta = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);

        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->errors = $errors;
        $this->meta = $meta;
    }

    /**
     * Obtenir le code d'erreur.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Obtenir le code de statut HTTP.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Obtenir les erreurs de validation.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtenir les métadonnées.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Convertir l'exception en array.
     *
     * @param  string|null  $locale
     * @return array<string, mixed>
     */
    public function toArray(?string $locale = null): array
    {
        $array = [
            'message' => $this->getMessage(),
            'error' => $this->errorCode,
            'statusCode' => $this->statusCode,
        ];

        if (!empty($this->errors)) {
            $array['errors'] = $this->errors;
        }

        if (!empty($this->meta)) {
            $array['meta'] = $this->meta;
        }

        return $array;
    }
}

