<?php

namespace Neocode\FNE;

use Neocode\FNE\Cache\CacheFactory;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;

/**
 * FNE Client - Point d'entrée principal du package
 *
 * @package Neocode\FNE
 */
class FNEClient
{
    /**
     * Configuration
     */
    protected FNEConfig $config;

    /**
     * Client HTTP
     */
    protected HttpClientInterface $httpClient;

    /**
     * Cache
     */
    protected ?CacheInterface $cache;

    /**
     * Logger
     */
    protected ?LoggerInterface $logger;

    /**
     * Services lazy-loaded
     */
    protected ?Services\InvoiceService $invoiceService = null;
    protected ?Services\PurchaseService $purchaseService = null;
    protected ?Services\RefundService $refundService = null;

    /**
     * Create a new FNE Client instance.
     *
     * @param  HttpClientInterface  $httpClient  Client HTTP
     * @param  FNEConfig  $config  Configuration
     * @param  CacheInterface|null  $cache  Cache (optionnel)
     * @param  LoggerInterface|null  $logger  Logger (optionnel)
     */
    public function __construct(
        HttpClientInterface $httpClient,
        FNEConfig $config,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;

        // Utiliser CacheFactory si aucun cache n'est fourni et que le cache est activé
        if ($cache === null && $config->isCacheEnabled()) {
            $this->cache = CacheFactory::create();
        } else {
            $this->cache = $cache;
        }

        $this->logger = $logger;

        // Valider la configuration
        $this->config->validate();
    }

    /**
     * Get the invoice service (lazy loading).
     *
     * @return Services\InvoiceService
     */
    public function invoice(): Services\InvoiceService
    {
        if ($this->invoiceService === null) {
            $this->invoiceService = new Services\InvoiceService(
                $this->httpClient,
                $this->config,
                null, // Mapper sera injecté plus tard
                null, // Validator sera injecté plus tard
                $this->cache,
                $this->logger
            );
        }

        return $this->invoiceService;
    }

    /**
     * Get the purchase service (lazy loading).
     *
     * @return Services\PurchaseService
     */
    public function purchase(): Services\PurchaseService
    {
        if ($this->purchaseService === null) {
            $this->purchaseService = new Services\PurchaseService(
                $this->httpClient,
                $this->config,
                null, // Mapper sera injecté plus tard
                null, // Validator sera injecté plus tard
                $this->cache,
                $this->logger
            );
        }

        return $this->purchaseService;
    }

    /**
     * Get the refund service (lazy loading).
     *
     * @return Services\RefundService
     */
    public function refund(): Services\RefundService
    {
        if ($this->refundService === null) {
            $this->refundService = new Services\RefundService(
                $this->httpClient,
                $this->config,
                null, // Mapper sera injecté plus tard
                null, // Validator sera injecté plus tard
                $this->cache,
                $this->logger
            );
        }

        return $this->refundService;
    }

    /**
     * Get the configuration.
     *
     * @return FNEConfig
     */
    public function getConfig(): FNEConfig
    {
        return $this->config;
    }
}

