<?php

namespace Neocode\FNE\Facades;

use Illuminate\Support\Facades\Facade;
use Neocode\FNE\FNEClient;
use Neocode\FNE\Services\InvoiceService;
use Neocode\FNE\Services\PurchaseService;
use Neocode\FNE\Services\RefundService;

/**
 * Façade Laravel pour le package FNE Client
 *
 * @method static InvoiceService invoice() Obtenir le service de factures de vente
 * @method static PurchaseService purchase() Obtenir le service de bordereaux d'achat
 * @method static RefundService refund() Obtenir le service d'avoirs
 * @method static \Neocode\FNE\Config\FNEConfig getConfig() Obtenir la configuration
 *
 * @package Neocode\FNE\Facades
 */
class FNE extends Facade
{
    /**
     * Obtenir le nom de l'accesseur de la façade.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return FNEClient::class;
    }
}

