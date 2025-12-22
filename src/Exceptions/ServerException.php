<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception serveur (500+)
 *
 * @package Neocode\FNE\Exceptions
 */
class ServerException extends FNEException
{
    public function __construct(string $message = 'Internal Server Error', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            'internal_server_error',
            500,
            [],
            [],
            $previous
        );
    }
}

