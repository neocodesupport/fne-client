<?php

namespace Neocode\FNE\Helpers;

/**
 * Helper pour manipulation des tableaux
 *
 * @package Neocode\FNE\Helpers
 */
class ArrayHelper
{
    /**
     * Obtenir une valeur d'un tableau avec valeur par défaut.
     *
     * @param  array<string, mixed>  $array
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        return $array[$key] ?? $default;
    }

    /**
     * Vérifier si une clé existe dans un tableau.
     *
     * @param  array<string, mixed>  $array
     * @param  string  $key
     * @return bool
     */
    public static function has(array $array, string $key): bool
    {
        return isset($array[$key]);
    }

    /**
     * Obtenir une valeur d'un tableau avec notation pointée (ex: "user.profile.name").
     *
     * @param  array<string, mixed>  $array
     * @param  string  $path
     * @param  mixed  $default
     * @return mixed
     */
    public static function dotGet(array $array, string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Définir une valeur dans un tableau avec notation pointée.
     *
     * @param  array<string, mixed>  $array
     * @param  string  $path
     * @param  mixed  $value
     * @return void
     */
    public static function dotSet(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current = $value;
    }

    /**
     * Vérifier si un chemin existe dans un tableau avec notation pointée.
     *
     * @param  array<string, mixed>  $array
     * @param  string  $path
     * @return bool
     */
    public static function dotHas(array $array, string $path): bool
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return false;
            }
            $value = $value[$key];
        }

        return true;
    }

    /**
     * Fusionner deux tableaux de manière récursive.
     *
     * @param  array<string, mixed>  $array1
     * @param  array<string, mixed>  $array2
     * @return array<string, mixed>
     */
    public static function merge(array $array1, array $array2): array
    {
        return array_merge_recursive($array1, $array2);
    }

    /**
     * Obtenir uniquement les clés spécifiées d'un tableau.
     *
     * @param  array<string, mixed>  $array
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Exclure les clés spécifiées d'un tableau.
     *
     * @param  array<string, mixed>  $array
     * @param  array<string>  $keys
     * @return array<string, mixed>
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }
}

