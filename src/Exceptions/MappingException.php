<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de mapping
 *
 * @package Neocode\FNE\Exceptions
 */
class MappingException extends FNEException
{
    public function __construct(
        string $message = 'Mapping error',
        array $meta = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'mapping_exception',
            400,
            [],
            $meta,
            $previous
        );
    }
}

