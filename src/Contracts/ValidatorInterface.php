<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour les validateurs
 */
interface ValidatorInterface
{
    /**
     * Valider les données.
     *
     * @param  array<string, mixed>  $data  Données à valider
     * @param  array<string, mixed>  $rules  Règles de validation
     * @return void
     * @throws \Neocode\FNE\Exceptions\ValidationException
     */
    public function validate(array $data, array $rules): void;
}

