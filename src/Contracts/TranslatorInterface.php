<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour le système de traduction
 *
 * @package Neocode\FNE\Contracts
 */
interface TranslatorInterface
{
    /**
     * Traduire une clé.
     *
     * @param  string  $key  Clé de traduction
     * @param  array<string, mixed>  $replace  Variables de remplacement
     * @param  string|null  $locale  Locale à utiliser
     * @return string
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string;

    /**
     * Définir la locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale(string $locale): void;

    /**
     * Obtenir la locale actuelle.
     *
     * @return string
     */
    public function getLocale(): string;
}

