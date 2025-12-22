<?php

namespace Neocode\FNE\Http\Middleware;

use Neocode\FNE\Exceptions\ServerException;
use Neocode\FNE\Exceptions\FNEException;

/**
 * Middleware pour retry avec backoff exponentiel
 *
 * Réessaie automatiquement les requêtes en cas d'erreur serveur.
 *
 * @package Neocode\FNE\Http\Middleware
 */
class RetryMiddleware
{
    /**
     * Nombre maximum de tentatives
     */
    private int $maxAttempts;

    /**
     * Délai initial en millisecondes
     */
    private int $initialDelay;

    /**
     * Multiplicateur pour le backoff exponentiel
     */
    private float $multiplier;

    /**
     * Create a new RetryMiddleware instance.
     *
     * @param  int  $maxAttempts
     * @param  int  $initialDelay
     * @param  float  $multiplier
     */
    public function __construct(int $maxAttempts = 3, int $initialDelay = 1000, float $multiplier = 2.0)
    {
        $this->maxAttempts = $maxAttempts;
        $this->initialDelay = $initialDelay;
        $this->multiplier = $multiplier;
    }

    /**
     * Exécuter une requête avec retry.
     *
     * @param  callable  $request
     * @return mixed
     * @throws FNEException
     */
    public function handle(callable $request): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxAttempts) {
            try {
                return $request();
            } catch (ServerException $e) {
                $lastException = $e;
                $attempt++;

                // Ne pas réessayer si c'est la dernière tentative
                if ($attempt >= $this->maxAttempts) {
                    throw $e;
                }

                // Attendre avant de réessayer (backoff exponentiel)
                $delay = $this->initialDelay * ($this->multiplier ** ($attempt - 1));
                usleep($delay * 1000); // Convertir en microsecondes
            } catch (FNEException $e) {
                // Ne pas réessayer pour les autres types d'erreurs
                throw $e;
            }
        }

        throw $lastException ?? new ServerException('Request failed after ' . $this->maxAttempts . ' attempts');
    }

    /**
     * Obtenir le nombre maximum de tentatives.
     *
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }
}

