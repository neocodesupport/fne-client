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
 * Service pour la gestion des bordereaux d'achat
 *
 * @package Neocode\FNE\Services
 */
class PurchaseService extends BaseService
{
    /**
     * Create a new PurchaseService instance.
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
     * Soumettre un bordereau d'achat.
     *
     * @param  array<string, mixed>  $data  Données du bordereau
     * @return ResponseDTO
     */
    public function submit(array $data): ResponseDTO
    {
        $this->log('info', 'Submitting purchase invoice', ['data' => $data]);

        $result = $this->execute($data);

        return ResponseDTO::fromArray($result);
    }

    /**
     * Faire la requête HTTP pour certifier un bordereau.
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
        if (is_array($response)) {
            return $response;
        }

        if (method_exists($response, 'getBody')) {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        }

        throw new \RuntimeException('Unsupported response type');
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
        $keyData = [
            'invoiceType' => 'purchase',
            'template' => $data['template'] ?? '',
            'items' => $data['items'] ?? [],
        ];

        return 'fne:purchase:' . md5(json_encode($keyData));
    }

    /**
     * Obtenir le TTL du cache en secondes.
     *
     * @return int|null
     */
    protected function getCacheTtl(): ?int
    {
        return $this->config->getCacheTtl();
    }
}

