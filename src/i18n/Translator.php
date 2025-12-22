<?php

namespace Neocode\FNE\i18n;

use Neocode\FNE\Contracts\TranslatorInterface;

/**
 * Gestionnaire de traduction pour le package FNE Client
 *
 * @package Neocode\FNE\i18n
 */
class Translator implements TranslatorInterface
{
    /**
     * Locale actuelle
     */
    private string $locale;

    /**
     * Locale de fallback
     */
    private string $fallbackLocale;

    /**
     * Traductions chargées
     *
     * @var array<string, array<string, mixed>>
     */
    private array $translations = [];

    /**
     * Create a new Translator instance.
     *
     * @param  string  $locale  Locale par défaut
     * @param  string  $fallbackLocale  Locale de fallback
     */
    public function __construct(string $locale = 'fr', string $fallbackLocale = 'en')
    {
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * Traduire une clé.
     *
     * @param  string  $key  Clé de traduction (ex: "errors.validation.required")
     * @param  array<string, mixed>  $replace  Variables de remplacement
     * @param  string|null  $locale  Locale à utiliser (null = locale actuelle)
     * @return string
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;
        $translation = $this->getTranslation($key, $locale);

        // Si pas de traduction trouvée, essayer le fallback
        if ($translation === $key && $locale !== $this->fallbackLocale) {
            $translation = $this->getTranslation($key, $this->fallbackLocale);
        }

        // Remplacer les variables
        foreach ($replace as $search => $value) {
            $translation = str_replace(":{$search}", (string) $value, $translation);
        }

        return $translation;
    }

    /**
     * Obtenir une traduction.
     *
     * @param  string  $key  Clé de traduction
     * @param  string  $locale  Locale
     * @return string
     */
    private function getTranslation(string $key, string $locale): string
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);

        if (!isset($this->translations[$locale][$file])) {
            $this->loadTranslations($locale, $file);
        }

        $value = $this->translations[$locale][$file] ?? [];

        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return $key; // Fallback to key
            }
            $value = $value[$k];
        }

        return is_string($value) ? $value : $key;
    }

    /**
     * Charger les traductions d'un fichier.
     *
     * @param  string  $locale  Locale
     * @param  string  $file  Nom du fichier (sans extension)
     * @return void
     */
    private function loadTranslations(string $locale, string $file): void
    {
        $path = __DIR__ . "/lang/{$locale}/{$file}.php";

        if (file_exists($path)) {
            $this->translations[$locale][$file] = require $path;
        } else {
            $this->translations[$locale][$file] = [];
        }
    }

    /**
     * Définir la locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale(string $locale): void
    {
        if (Locale::isSupported($locale)) {
            $this->locale = $locale;
        }
    }

    /**
     * Obtenir la locale actuelle.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Obtenir la locale de fallback.
     *
     * @return string
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }
}

