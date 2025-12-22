<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de requête invalide (400)
 *
 * @package Neocode\FNE\Exceptions
 */
class BadRequestException extends FNEException
{
    public function __construct(
        string $message = 'Bad Request',
        array $errors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'bad_request',
            400,
            $errors,
            [],
            $previous
        );
    }
}

