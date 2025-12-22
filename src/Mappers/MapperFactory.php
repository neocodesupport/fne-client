<?php

namespace Neocode\FNE\Mappers;

use Neocode\FNE\Contracts\MapperInterface;

/**
 * Factory pour créer les mappers appropriés
 *
 * @package Neocode\FNE\Mappers
 */
class MapperFactory
{
    /**
     * Créer un mapper pour les factures de vente.
     *
     * @param  array<string, mixed>  $customMapping  Configuration de mapping personnalisé
     * @return InvoiceMapper
     */
    public static function createInvoiceMapper(array $customMapping = []): InvoiceMapper
    {
        return new InvoiceMapper($customMapping);
    }

    /**
     * Créer un mapper pour les bordereaux d'achat.
     *
     * @param  array<string, mixed>  $customMapping  Configuration de mapping personnalisé
     * @return PurchaseMapper
     */
    public static function createPurchaseMapper(array $customMapping = []): PurchaseMapper
    {
        return new PurchaseMapper($customMapping);
    }

    /**
     * Créer un mapper pour les avoirs.
     *
     * @param  array<string, mixed>  $customMapping  Configuration de mapping personnalisé
     * @return RefundMapper
     */
    public static function createRefundMapper(array $customMapping = []): RefundMapper
    {
        return new RefundMapper($customMapping);
    }

    /**
     * Créer un mapper selon le type.
     *
     * @param  string  $type  Type de mapper (invoice, purchase, refund)
     * @param  array<string, mixed>  $customMapping  Configuration de mapping personnalisé
     * @return MapperInterface
     */
    public static function create(string $type, array $customMapping = []): MapperInterface
    {
        return match ($type) {
            'invoice' => self::createInvoiceMapper($customMapping),
            'purchase' => self::createPurchaseMapper($customMapping),
            'refund' => self::createRefundMapper($customMapping),
            default => throw new \InvalidArgumentException("Unknown mapper type: {$type}"),
        };
    }
}

