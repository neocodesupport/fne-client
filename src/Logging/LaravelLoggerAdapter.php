<?php

namespace Neocode\FNE\Logging;

use Neocode\FNE\Contracts\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Adapter pour wrapper un logger PSR-3 (comme celui de Laravel) vers notre LoggerInterface
 *
 * @package Neocode\FNE\Logging
 */
class LaravelLoggerAdapter implements LoggerInterface
{
    /**
     * Logger PSR-3 sous-jacent
     */
    protected PsrLoggerInterface $logger;

    /**
     * Create a new LaravelLoggerAdapter instance.
     *
     * @param  PsrLoggerInterface  $logger
     */
    public function __construct(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logger un message d'urgence.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Logger une alerte.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Logger une erreur critique.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Logger une erreur.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Logger un avertissement.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Logger une notice.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Logger une information.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Logger un debug.
     *
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Logger avec un niveau arbitraire.
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    public function log($level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
