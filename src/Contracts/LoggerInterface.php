<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour le logger (compatible PSR-3)
 */
interface LoggerInterface
{
    /**
     * Logger un message d'urgence.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Logger une alerte.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Logger une erreur critique.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Logger une erreur.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function error(string $message, array $context = []): void;

    /**
     * Logger un avertissement.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Logger une notice.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function notice(string $message, array $context = []): void;

    /**
     * Logger une information.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function info(string $message, array $context = []): void;

    /**
     * Logger un debug.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Logger avec un niveau arbitraire.
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function log($level, string $message, array $context = []): void;
}

