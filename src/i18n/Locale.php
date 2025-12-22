<?php

namespace Neocode\FNE\i18n;

/**
 * Gestion des locales supportées
 *
 * @package Neocode\FNE\i18n
 */
class Locale
{
    /**
     * Locale par défaut
     */
    public const DEFAULT = 'fr';

    /**
     * Locale de fallback
     */
    public const FALLBACK = 'en';

    /**
     * Locales disponibles
     */
    public const AVAILABLE = ['fr', 'en', 'es'];

    /**
     * Vérifier si une locale est supportée.
     *
     * @param  string  $locale
     * @return bool
     */
    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::AVAILABLE, true);
    }

    /**
     * Obtenir la locale par défaut.
     *
     * @return string
     */
    public static function getDefault(): string
    {
        return self::DEFAULT;
    }

    /**
     * Obtenir la locale de fallback.
     *
     * @return string
     */
    public static function getFallback(): string
    {
        return self::FALLBACK;
    }

    /**
     * Obtenir toutes les locales disponibles.
     *
     * @return array<string>
     */
    public static function getAvailable(): array
    {
        return self::AVAILABLE;
    }
}

