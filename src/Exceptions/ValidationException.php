<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de validation
 *
 * @package Neocode\FNE\Exceptions
 */
class ValidationException extends BadRequestException
{
    public function __construct(
        string $message = 'Validation failed',
        array $errors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errors, $previous);
        $this->errorCode = 'validation_exception';
    }
}

