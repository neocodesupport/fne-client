<?php

namespace Neocode\FNE\Enums;

/**
 * Type de facture
 */
enum InvoiceType: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';

    /**
     * Obtenir la description du type de facture.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::SALE => 'Facture de vente',
            self::PURCHASE => 'Bordereau d\'achat',
        };
    }

    /**
     * Vérifier si ce type nécessite des taxes.
     */
    public function requiresTaxes(): bool
    {
        return $this === self::SALE;
    }
}

