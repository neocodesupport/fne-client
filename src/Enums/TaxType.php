<?php

namespace Neocode\FNE\Enums;

/**
 * Type de taxe TVA
 */
enum TaxType: string
{
    case TVA = 'TVA';
    case TVAB = 'TVAB';
    case TVAC = 'TVAC';
    case TVAD = 'TVAD';

    /**
     * Obtenir le taux de TVA en pourcentage.
     */
    public function getRate(): float
    {
        return match ($this) {
            self::TVA => 18.0,
            self::TVAB => 9.0,
            self::TVAC => 0.0,
            self::TVAD => 0.0,
        };
    }

    /**
     * Obtenir la description de la taxe.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TVA => 'TVA normal - TVA sur HT 18,00%',
            self::TVAB => 'TVA réduit - TVA sur HT 9,00%',
            self::TVAC => 'TVA exo.conv - TVA sur HT 00,00%',
            self::TVAD => 'TVA exo leg - TVA sur HT 00,00% pour TEE et RME',
        };
    }

    /**
     * Vérifier si la taxe est exonérée.
     */
    public function isExempt(): bool
    {
        return $this === self::TVAC || $this === self::TVAD;
    }
}

