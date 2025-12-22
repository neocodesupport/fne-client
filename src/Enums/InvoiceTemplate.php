<?php

namespace Neocode\FNE\Enums;

/**
 * Template de facture FNE
 */
enum InvoiceTemplate: string
{
    case B2C = 'B2C';
    case B2B = 'B2B';
    case B2F = 'B2F';
    case B2G = 'B2G';

    /**
     * Vérifier si ce template nécessite un NCC client.
     */
    public function requiresNcc(): bool
    {
        return $this === self::B2B;
    }

    /**
     * Vérifier si ce template nécessite une devise étrangère.
     */
    public function requiresForeignCurrency(): bool
    {
        return $this === self::B2F;
    }

    /**
     * Obtenir la description du template.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::B2C => 'Business to Consumer - Le client est un particulier',
            self::B2B => 'Business to Business - Le client est une entreprise ou professionnel possédant un NCC',
            self::B2F => 'Business to Foreign - Le client est à l\'international',
            self::B2G => 'Business to Government - Le client est une institution gouvernementale',
        };
    }
}

