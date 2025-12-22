<?php

namespace Neocode\FNE\Validation;

use Neocode\FNE\Contracts\ValidatorInterface;

/**
 * Factory pour créer les validateurs appropriés
 *
 * @package Neocode\FNE\Validation
 */
class ValidatorFactory
{
    /**
     * Créer un validateur pour les factures de vente.
     *
     * @return ValidatorInterface
     */
    public static function createInvoiceValidator(): ValidatorInterface
    {
        return new InvoiceValidator();
    }

    /**
     * Créer un validateur pour les bordereaux d'achat.
     *
     * @return ValidatorInterface
     */
    public static function createPurchaseValidator(): ValidatorInterface
    {
        return new PurchaseValidator();
    }

    /**
     * Créer un validateur pour les avoirs.
     *
     * @return ValidatorInterface
     */
    public static function createRefundValidator(): ValidatorInterface
    {
        return new RefundValidator();
    }
}

