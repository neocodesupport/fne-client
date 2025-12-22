<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de ressource non trouvée (404)
 *
 * @package Neocode\FNE\Exceptions
 */
class NotFoundException extends FNEException
{
    public function __construct(string $message = 'Not Found', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            'not_found',
            404,
            [],
            [],
            $previous
        );
    }
}

