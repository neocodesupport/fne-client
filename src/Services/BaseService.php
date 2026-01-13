<?php

namespace Neocode\FNE\Services;

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Contracts\MapperInterface;
use Neocode\FNE\Contracts\ValidatorInterface;

/**
 * Service de base abstrait pour tous les services FNE
 *
 * @package Neocode\FNE\Services
 */
abstract class BaseService
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
     * Cache (optionnel)
     */
    protected ?CacheInterface $cache;

    /**
     * Logger (optionnel)
     */
    protected ?LoggerInterface $logger;

    /**
     * Mapper
     */
    protected ?MapperInterface $mapper;

    /**
     * Validator
     */
    protected ?ValidatorInterface $validator;

    /**
     * Données du contexte (pour récupération via getData())
     */
    protected ?array $contextData = null;

    /**
     * Modèle associé (pour récupération via getData())
     */
    protected mixed $contextModel = null;

    /**
     * Create a new BaseService instance.
     *
     * @param  HttpClientInterface  $httpClient
     * @param  FNEConfig  $config
     * @param  MapperInterface|null  $mapper
     * @param  ValidatorInterface|null  $validator
     * @param  CacheInterface|null  $cache
     * @param  LoggerInterface|null  $logger
     */
    public function __construct(
        HttpClientInterface $httpClient,
        FNEConfig $config,
        ?MapperInterface $mapper = null,
        ?ValidatorInterface $validator = null,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->mapper = $mapper;
        $this->validator = $validator;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Définir les données du contexte (pour utilisation avec getData()).
     *
     * @param  array<string, mixed>  $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->contextData = $data;
        return $this;
    }

    /**
     * Définir le modèle associé (pour utilisation avec getData()).
     *
     * @param  mixed  $model  Modèle (doit avoir toArray() ou attributesToArray())
     * @return $this
     */
    public function setModel(mixed $model): self
    {
        $this->contextModel = $model;
        return $this;
    }

    /**
     * Obtenir les données depuis différentes sources (priorité).
     *
     * Priorité :
     * 1. Données fournies explicitement (paramètre)
     * 2. Données du contexte (setData())
     * 3. Données depuis le modèle (setModel()) - TOUJOURS utilisé si disponible
     * 4. Données depuis le mapper (getData()) - si le mapper supporte
     *
     * @param  array<string, mixed>|null  $explicitData  Données fournies explicitement
     * @return array<string, mixed>
     * @throws \Neocode\FNE\Exceptions\BadRequestException Si aucune donnée disponible
     */
    protected function getData(?array $explicitData = null): array
    {
        // 1. Données fournies explicitement
        if ($explicitData !== null && !empty($explicitData)) {
            return $explicitData;
        }

        // 2. Données du contexte
        if ($this->contextData !== null && !empty($this->contextData)) {
            return $this->contextData;
        }

        // 3. Données depuis le modèle (TOUJOURS utilisé si disponible)
        // Le mapper appliquera le mapping personnalisé si configuré
        if ($this->contextModel !== null) {
            return $this->extractModelData($this->contextModel);
        }

        // 4. Données depuis le mapper (si supporte getData())
        if ($this->mapper && method_exists($this->mapper, 'getData')) {
            $mapperData = $this->mapper->getData();
            if ($mapperData !== null && !empty($mapperData)) {
                return $mapperData;
            }
        }

        // Aucune donnée disponible
        throw new \Neocode\FNE\Exceptions\BadRequestException(
            'Aucune donnée disponible. Fournissez des données via le paramètre, setData() ou setModel().'
        );
    }

    /**
     * Extraire les données d'un modèle.
     *
     * @param  mixed  $model
     * @return array<string, mixed>
     */
    protected function extractModelData(mixed $model): array
    {
        // Essayer toArray() si disponible (Laravel Eloquent)
        // Cette méthode inclut automatiquement les relations chargées
        if (method_exists($model, 'toArray')) {
            $data = $model->toArray();
            
            // Convertir les relations Laravel (Collections) en arrays si nécessaire
            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    // Si c'est une Collection Laravel, convertir en array
                    if (method_exists($value, 'toArray')) {
                        $data[$key] = $value->toArray();
                    } elseif (method_exists($value, 'all')) {
                        // Pour les relations non chargées qui retournent une relation
                        $data[$key] = $value->all();
                    }
                }
            }
            
            return $data;
        }

        // Essayer attributesToArray() si disponible (Laravel Eloquent)
        // Cette méthode n'inclut PAS les relations, seulement les attributs
        if (method_exists($model, 'attributesToArray')) {
            return $model->attributesToArray();
        }

        // Essayer getAttributes() si disponible
        if (method_exists($model, 'getAttributes')) {
            return $model->getAttributes();
        }

        // Essayer __toArray() si disponible
        if (method_exists($model, '__toArray')) {
            return $model->__toArray();
        }

        // Fallback : convertir en array
        return (array) $model;
    }

    /**
     * Exécuter le service (orchestration complète).
     *
     * @param  array<string, mixed>|null  $data  Données d'entrée (optionnel, peut être récupéré via getData())
     * @return mixed  Résultat du service
     */
    public function execute(?array $data = null): mixed
    {
        // Récupérer les données (avec priorité)
        $data = $this->getData($data);

        // 1. Validation pré-mapping (sur les données d'entrée)
        // Note: Certaines validations doivent se faire sur les données d'entrée
        // (ex: vérifier qu'il n'y a pas de taxes dans les achats)
        $this->validatePreMapping($data);

        // 2. Mapping des données (ERP → FNE)
        $mappedData = $this->map($data);

        // 3. Validation post-mapping (sur les données mappées)
        $this->validate($mappedData);

        // 4. Vérifier le cache
        $cacheKey = $this->getCacheKey($mappedData);
        if ($this->shouldUseCache() && $this->cache && $this->cache->has($cacheKey)) {
            $this->log('debug', 'Cache hit', ['key' => $cacheKey]);
            return $this->cache->get($cacheKey);
        }

        // 5. Exécuter la requête HTTP
        $response = $this->makeRequest($mappedData);

        // 6. Traiter la réponse
        $result = $this->processResponse($response);

        // 7. Mettre en cache si nécessaire
        if ($this->shouldUseCache() && $this->cache && $result !== null) {
            $ttl = $this->getCacheTtl();
            $this->cache->set($cacheKey, $result, $ttl);
            $this->log('debug', 'Cached response', ['key' => $cacheKey, 'ttl' => $ttl]);
        }

        return $result;
    }

    /**
     * Mapper les données (ERP → FNE).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     * @throws \Neocode\FNE\Exceptions\MappingException
     */
    protected function map(array $data): array
    {
        if ($this->mapper) {
            try {
                return $this->mapper->map($data);
            } catch (\InvalidArgumentException $e) {
                // Convertir InvalidArgumentException en MappingException
                throw new \Neocode\FNE\Exceptions\MappingException(
                    'Mapping failed: ' . $e->getMessage(),
                    ['original_error' => $e->getMessage()],
                    $e
                );
            } catch (\Exception $e) {
                throw new \Neocode\FNE\Exceptions\MappingException(
                    'Mapping failed: ' . $e->getMessage(),
                    ['original_error' => $e->getMessage()],
                    $e
                );
            }
        }

        return $data;
    }

    /**
     * Valider les données avant le mapping (sur les données d'entrée).
     *
     * @param  array<string, mixed>  $data
     * @return void
     */
    protected function validatePreMapping(array $data): void
    {
        // Par défaut, pas de validation pré-mapping
        // Les sous-classes peuvent surcharger cette méthode
    }

    /**
     * Valider les données après le mapping (sur les données mappées).
     *
     * @param  array<string, mixed>  $data
     * @return void
     */
    protected function validate(array $data): void
    {
        if ($this->validator) {
            // Les validateurs ont leurs propres règles intégrées
            // On peut passer des règles supplémentaires via getValidationRules() si nécessaire
            $additionalRules = $this->getValidationRules();
            $this->validator->validate($data, $additionalRules);
        }
    }

    /**
     * Faire la requête HTTP.
     *
     * @param  array<string, mixed>  $data
     * @return mixed
     */
    abstract protected function makeRequest(array $data): mixed;

    /**
     * Traiter la réponse HTTP.
     *
     * @param  mixed  $response
     * @return mixed
     */
    abstract protected function processResponse(mixed $response): mixed;

    /**
     * Obtenir les règles de validation.
     *
     * @return array<string, mixed>
     */
    abstract protected function getValidationRules(): array;

    /**
     * Obtenir la clé de cache.
     *
     * @param  array<string, mixed>  $data
     * @return string
     */
    abstract protected function getCacheKey(array $data): string;

    /**
     * Obtenir le TTL du cache en secondes.
     *
     * @return int|null  null = pas de cache
     */
    abstract protected function getCacheTtl(): ?int;

    /**
     * Vérifier si le cache doit être utilisé.
     *
     * @return bool
     */
    protected function shouldUseCache(): bool
    {
        return $this->config->isCacheEnabled() && $this->cache !== null;
    }

    /**
     * Logger un message.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array<string, mixed>  $context
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}

