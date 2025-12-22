<?php

namespace Neocode\FNE\Helpers;

/**
 * Helper pour manipulation des chaînes de caractères
 *
 * @package Neocode\FNE\Helpers
 */
class StringHelper
{
    /**
     * Convertir une chaîne en camelCase.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return lcfirst(str_replace(' ', '', $value));
    }

    /**
     * Convertir une chaîne en snake_case.
     *
     * @param  string  $value
     * @return string
     */
    public static function snake(string $value): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);
        }

        return strtolower($value);
    }

    /**
     * Convertir une chaîne en kebab-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function kebab(string $value): string
    {
        return str_replace('_', '-', self::snake($value));
    }

    /**
     * Convertir une chaîne en StudlyCase.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Limiter le nombre de caractères d'une chaîne.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit)) . $end;
    }

    /**
     * Générer une chaîne aléatoire.
     *
     * @param  int  $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Vérifier si une chaîne commence par une sous-chaîne.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Vérifier si une chaîne se termine par une sous-chaîne.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Vérifier si une chaîne contient une sous-chaîne.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Masquer une partie d'une chaîne (utile pour les données sensibles).
     *
     * @param  string  $value
     * @param  int  $visibleStart
     * @param  int  $visibleEnd
     * @param  string  $mask
     * @return string
     */
    public static function mask(string $value, int $visibleStart = 4, int $visibleEnd = 4, string $mask = '*'): string
    {
        $length = strlen($value);
        
        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat($mask, $length);
        }

        $start = substr($value, 0, $visibleStart);
        $end = substr($value, -$visibleEnd);
        $middle = str_repeat($mask, $length - $visibleStart - $visibleEnd);

        return $start . $middle . $end;
    }
}

