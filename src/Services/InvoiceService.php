<?php

namespace Neocode\FNE\Services;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Contracts\MapperInterface;
use Neocode\FNE\Contracts\ValidatorInterface;
use Neocode\FNE\DTOs\ResponseDTO;

/**
 * Service pour la gestion des factures de vente
 *
 * @package Neocode\FNE\Services
 */
class InvoiceService extends BaseService
{
    /**
     * Create a new InvoiceService instance.
     */
    public function __construct(
        HttpClientInterface $httpClient,
        FNEConfig $config,
        ?MapperInterface $mapper = null,
        ?ValidatorInterface $validator = null,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($httpClient, $config, $mapper, $validator, $cache, $logger);
    }

    /**
     * Certifier une facture de vente.
     *
     * @param  array<string, mixed>  $data  Données de la facture
     * @return ResponseDTO
     */
    public function sign(array $data): ResponseDTO
    {
        $this->log('info', 'Signing invoice', ['data' => $data]);

        $result = $this->execute($data);

        return ResponseDTO::fromArray($result);
    }

    /**
     * Faire la requête HTTP pour certifier une facture.
     *
     * @param  array<string, mixed>  $data
     * @return mixed
     */
    protected function makeRequest(array $data): mixed
    {
        $url = rtrim($this->config->getBaseUrl(), '/') . '/external/invoices/sign';

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            ],
            'body' => json_encode($data),
            'timeout' => $this->config->getTimeout(),
        ];

        $this->log('debug', 'Making HTTP request', ['url' => $url, 'method' => 'POST']);

        $response = $this->httpClient->request('POST', $url, $options);

        return $response;
    }

    /**
     * Traiter la réponse HTTP.
     *
     * @param  mixed  $response
     * @return array<string, mixed>
     */
    protected function processResponse(mixed $response): array
    {
        // Laravel HTTP Client Response
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $data = $response->json();

            if ($data === null) {
                throw new \RuntimeException('Invalid JSON response from API');
            }

            return $data;
        }

        // PSR-7 ResponseInterface (Guzzle)
        if ($response instanceof \Psr\Http\Message\ResponseInterface) {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        }

        // Array direct (pour les tests)
        if (is_array($response)) {
            return $response;
        }

        throw new \RuntimeException('Unsupported response type: ' . get_class($response));
    }

    /**
     * Obtenir les règles de validation.
     *
     * @return array<string, mixed>
     */
    protected function getValidationRules(): array
    {
        // Les règles seront définies dans le validator spécifique
        return [];
    }

    /**
     * Obtenir la clé de cache.
     *
     * @param  array<string, mixed>  $data
     * @return string
     */
    protected function getCacheKey(array $data): string
    {
        // Générer une clé basée sur les données de la facture
        $keyData = [
            'invoiceType' => $data['invoiceType'] ?? '',
            'template' => $data['template'] ?? '',
            'items' => $data['items'] ?? [],
        ];

        return 'fne:invoice:' . md5(json_encode($keyData));
    }

    /**
     * Obtenir le TTL du cache en secondes.
     *
     * @return int|null
     */
    protected function getCacheTtl(): ?int
    {
        // Les factures peuvent être mises en cache pendant 1 heure
        return $this->config->getCacheTtl();
    }
}

