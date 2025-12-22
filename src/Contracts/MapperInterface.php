<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour les mappers (transformation ERP → FNE)
 */
interface MapperInterface
{
    /**
     * Transformer les données ERP vers le format FNE.
     *
     * @param  array<string, mixed>  $data  Données ERP
     * @return array<string, mixed>  Données au format FNE
     */
    public function map(array $data): array;
}

