<?php

namespace Neocode\FNE\Enums;

/**
 * Devises étrangères supportées
 */
enum ForeignCurrency: string
{
    case XOF = 'XOF';
    case USD = 'USD';
    case EUR = 'EUR';
    case JPY = 'JPY';
    case CAD = 'CAD';
    case GBP = 'GBP';
    case AUD = 'AUD';
    case CNH = 'CNH';
    case CHF = 'CHF';
    case HKD = 'HKD';
    case NZD = 'NZD';

    /**
     * Obtenir le symbole de la devise.
     */
    public function getSymbol(): string
    {
        return match ($this) {
            self::XOF => 'FCFA',
            self::USD => '$',
            self::EUR => '€',
            self::JPY => '¥',
            self::CAD => 'C$',
            self::GBP => '£',
            self::AUD => 'A$',
            self::CNH => '¥',
            self::CHF => 'CHF',
            self::HKD => 'HK$',
            self::NZD => 'NZ$',
        };
    }

    /**
     * Obtenir le nom complet de la devise.
     */
    public function getName(): string
    {
        return match ($this) {
            self::XOF => 'Franc CFA',
            self::USD => 'Dollar Américain',
            self::EUR => 'Euro',
            self::JPY => 'Yen Japonais',
            self::CAD => 'Dollar Canadien',
            self::GBP => 'Livre Sterling Britannique',
            self::AUD => 'Dollar Australien',
            self::CNH => 'Yuan Chinois',
            self::CHF => 'Franc Suisse',
            self::HKD => 'Dollar Hong Kong',
            self::NZD => 'Dollar Néo-Zélandais',
        };
    }
}

