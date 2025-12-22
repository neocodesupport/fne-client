<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception d'authentification (401)
 *
 * @package Neocode\FNE\Exceptions
 */
class AuthenticationException extends FNEException
{
    public function __construct(string $message = 'Unauthorized', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            'unauthorized_exception',
            401,
            [],
            [],
            $previous
        );
    }
}

