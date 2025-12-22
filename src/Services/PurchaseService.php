<?php

namespace Neocode\FNE\Services;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Contracts\MapperInterface;
use Neocode\FNE\Contracts\ValidatorInterface;
use Neocode\FNE\DTOs\ResponseDTO;
use Neocode\FNE\Mappers\MapperFactory;
use Neocode\FNE\Validation\ValidatorFactory;

/**
 * Service pour la gestion des bordereaux d'achat
 *
 * @package Neocode\FNE\Services
 */
class PurchaseService extends BaseService
{
    /**
     * Valider les données avant le mapping pour vérifier qu'il n'y a pas de taxes.
     *
     * @param  array<string, mixed>  $data
     * @return void
     */
    protected function validatePreMapping(array $data): void
    {
        // Vérifier qu'il n'y a pas de taxes dans les items (bordereaux d'achat)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (isset($item['taxes']) && !empty($item['taxes'])) {
                    throw new \Neocode\FNE\Exceptions\ValidationException(
                        'Taxes are not allowed for purchase invoices.',
                        ["items.{$index}.taxes" => ['Taxes are not allowed for purchase invoices.']]
                    );
                }
            }
        }
    }
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
        // Utiliser PurchaseMapper par défaut si aucun mapper n'est fourni
        if ($mapper === null) {
            // Récupérer le mapping personnalisé depuis la configuration
            $customMapping = $config->getMapping('purchase');
            $mapper = MapperFactory::createPurchaseMapper($customMapping);
        }

        // Utiliser PurchaseValidator par défaut si aucun validator n'est fourni
        if ($validator === null) {
            $validator = ValidatorFactory::createPurchaseValidator();
        }

        parent::__construct($httpClient, $config, $mapper, $validator, $cache, $logger);
    }

    /**
     * Soumettre un bordereau d'achat.
     *
     * @param  array<string, mixed>|null  $data  Données du bordereau (optionnel, peut être récupéré via getData())
     * @return ResponseDTO
     */
    public function submit(?array $data = null): ResponseDTO
    {
        $this->log('info', 'Submitting purchase invoice', ['data' => $data ?? 'from context']);

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
        // Le base_url inclut déjà /api pour l'API mock ou pas selon la configuration
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

