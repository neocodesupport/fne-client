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
 * Service pour la gestion des avoirs
 *
 * @package Neocode\FNE\Services
 */
class RefundService extends BaseService
{
    /**
     * Create a new RefundService instance.
     */
    public function __construct(
        HttpClientInterface $httpClient,
        FNEConfig $config,
        ?MapperInterface $mapper = null,
        ?ValidatorInterface $validator = null,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        // Utiliser RefundMapper par défaut si aucun mapper n'est fourni
        if ($mapper === null) {
            $mapper = MapperFactory::createRefundMapper();
        }

        // Utiliser RefundValidator par défaut si aucun validator n'est fourni
        if ($validator === null) {
            $validator = ValidatorFactory::createRefundValidator();
        }

        parent::__construct($httpClient, $config, $mapper, $validator, $cache, $logger);
    }

    /**
     * Émettre un avoir.
     *
     * @param  string  $invoiceId  ID de la facture parente
     * @param  array<string, mixed>  $items  Items à rembourser
     * @return ResponseDTO
     */
    public function issue(string $invoiceId, array $items): ResponseDTO
    {
        $data = [
            'invoiceId' => $invoiceId,
            'items' => $items,
        ];

        $this->log('info', 'Issuing refund', ['invoiceId' => $invoiceId, 'items' => $items]);

        $result = $this->execute($data);

        return ResponseDTO::fromArray($result);
    }

    /**
     * Faire la requête HTTP pour émettre un avoir.
     *
     * @param  array<string, mixed>  $data
     * @return mixed
     */
    protected function makeRequest(array $data): mixed
    {
        $invoiceId = $data['invoiceId'];
        $url = rtrim($this->config->getBaseUrl(), '/') . "/external/invoices/{$invoiceId}/refund";

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            ],
            'body' => json_encode(['items' => $data['items']]),
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
        // Les avoirs ne sont pas mis en cache
        return '';
    }

    /**
     * Obtenir le TTL du cache en secondes.
     *
     * @return int|null  null = pas de cache pour les avoirs
     */
    protected function getCacheTtl(): ?int
    {
        // Les avoirs ne sont pas mis en cache
        return null;
    }

    /**
     * Vérifier si le cache doit être utilisé.
     *
     * @return bool  false pour les avoirs
     */
    protected function shouldUseCache(): bool
    {
        return false;
    }
}

