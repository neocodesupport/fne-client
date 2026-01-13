<?php

namespace Neocode\FNE\Mappers;

use Neocode\FNE\Contracts\MapperInterface;
use Neocode\FNE\Helpers\StringHelper;

/**
 * Mapper de base pour la transformation ERP → FNE
 *
 * @package Neocode\FNE\Mappers
 */
abstract class BaseMapper implements MapperInterface
{
    /**
     * Configuration de mapping personnalisé
     */
    protected array $customMapping = [];

    /**
     * Create a new BaseMapper instance.
     *
     * @param  array<string, mixed>  $customMapping  Configuration de mapping personnalisé
     */
    public function __construct(array $customMapping = [])
    {
        $this->customMapping = $customMapping;
    }

    /**
     * Vérifier si un mapping personnalisé est configuré.
     *
     * @return bool
     */
    public function hasMapping(): bool
    {
        return !empty($this->customMapping);
    }

    /**
     * Transformer les données ERP vers le format FNE.
     *
     * @param  array<string, mixed>  $data  Données ERP
     * @return array<string, mixed>  Données au format FNE
     */
    public function map(array $data): array
    {
        // Convertir les clés snake_case en camelCase (pour compatibilité avec les modèles Laravel)
        $data = $this->normalizeKeys($data);

        // Appliquer le mapping personnalisé si configuré
        if (!empty($this->customMapping)) {
            $data = $this->applyCustomMapping($data);
        }

        // Normaliser les données
        $data = $this->normalize($data);

        // Effectuer le mapping spécifique
        return $this->doMap($data);
    }

    /**
     * Effectuer le mapping spécifique (à implémenter dans les classes enfants).
     *
     * @param  array<string, mixed>  $data  Données normalisées
     * @return array<string, mixed>  Données au format FNE
     */
    abstract protected function doMap(array $data): array;

    /**
     * Appliquer le mapping personnalisé.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyCustomMapping(array $data): array
    {
        $mapped = [];

        foreach ($this->customMapping as $fneKey => $erpKey) {
            $value = $this->getValueByPath($data, $erpKey);

            if ($value !== null) {
                $this->setValueByPath($mapped, $fneKey, $value);
            }
        }

        // Fusionner avec les données originales (les clés non mappées restent)
        return array_merge($data, $mapped);
    }

    /**
     * Obtenir une valeur par notation pointée (ex: "client.name").
     *
     * @param  array<string, mixed>  $data
     * @param  string  $path
     * @return mixed
     */
    protected function getValueByPath(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Définir une valeur par notation pointée.
     *
     * @param  array<string, mixed>  $data
     * @param  string  $path
     * @param  mixed  $value
     * @return void
     */
    protected function setValueByPath(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$data;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }

            $current = &$current[$key];
        }

        $current = $value;
    }

    /**
     * Normaliser les clés (convertir snake_case en camelCase).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeKeys(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            // Convertir la clé en camelCase si elle est en snake_case
            $camelKey = $this->convertKeyToCamelCase($key);

            // Si la valeur est un tableau, normaliser récursivement
            if (is_array($value) && !$this->isListArray($value)) {
                $value = $this->normalizeKeys($value);
            } elseif (is_array($value) && $this->isListArray($value)) {
                // Pour les tableaux indexés numériquement (comme items), normaliser chaque élément
                $value = array_map(function ($item) {
                    if (is_array($item) && !$this->isListArray($item)) {
                        return $this->normalizeKeys($item);
                    }
                    return $item;
                }, $value);
            }

            $normalized[$camelKey] = $value;
        }

        return $normalized;
    }

    /**
     * Convertir une clé en camelCase si elle est en snake_case.
     *
     * @param  string  $key
     * @return string
     */
    protected function convertKeyToCamelCase(string $key): string
    {
        // Si la clé contient des underscores, la convertir en camelCase
        if (str_contains($key, '_')) {
            return StringHelper::camel($key);
        }

        // Sinon, retourner la clé telle quelle
        return $key;
    }

    /**
     * Vérifier si un tableau est une liste (indexée numériquement) ou un tableau associatif.
     *
     * @param  array  $array
     * @return bool
     */
    protected function isListArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Normaliser les données (conversion des types, valeurs par défaut, etc.).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalize(array $data): array
    {
        // Convertir les enums en valeurs string si nécessaire
        $data = $this->normalizeEnums($data);

        // Normaliser les valeurs booléennes
        $data = $this->normalizeBooleans($data);

        // Normaliser les valeurs numériques
        $data = $this->normalizeNumbers($data);

        return $data;
    }

    /**
     * Normaliser les enums (convertir en string si c'est un enum).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeEnums(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value) && enum_exists(get_class($value))) {
                $data[$key] = $value->value;
            } elseif (is_array($value)) {
                $data[$key] = $this->normalizeEnums($value);
            }
        }

        return $data;
    }

    /**
     * Normaliser les booléens.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeBooleans(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeBooleans($value);
            } elseif (is_bool($value)) {
                $data[$key] = $value;
            } elseif (is_scalar($value) && in_array(strtolower((string) $value), ['true', '1', 'yes', 'on'], true)) {
                $data[$key] = true;
            } elseif (is_scalar($value) && in_array(strtolower((string) $value), ['false', '0', 'no', 'off', ''], true)) {
                $data[$key] = false;
            }
        }

        return $data;
    }

    /**
     * Normaliser les nombres.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeNumbers(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeNumbers($value);
            } elseif (is_numeric($value) && !is_string($value)) {
                $data[$key] = $value;
            } elseif (is_string($value) && is_numeric($value)) {
                // Convertir les strings numériques en float/int
                $data[$key] = str_contains($value, '.') ? (float) $value : (int) $value;
            }
        }

        return $data;
    }
}

