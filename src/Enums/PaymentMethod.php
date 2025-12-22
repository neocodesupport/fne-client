<?php

namespace Neocode\FNE\Enums;

/**
 * Méthode de paiement
 */
enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case CHECK = 'check';
    case MOBILE_MONEY = 'mobile-money';
    case TRANSFER = 'transfer';
    case DEFERRED = 'deferred';

    /**
     * Obtenir la description de la méthode de paiement.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CASH => 'Espèce',
            self::CARD => 'Carte bancaire',
            self::CHECK => 'Chèque',
            self::MOBILE_MONEY => 'Mobile money',
            self::TRANSFER => 'Virement bancaire',
            self::DEFERRED => 'À terme',
        };
    }

    /**
     * Vérifier si le paiement est immédiat.
     */
    public function isImmediate(): bool
    {
        return $this !== self::DEFERRED;
    }
}

