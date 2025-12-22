<?php

namespace Neocode\FNE\Exceptions;

use Neocode\FNE\Contracts\TranslatorInterface;
use Neocode\FNE\i18n\Translator;
use Neocode\FNE\i18n\Locale;

/**
 * Formateur unifié pour les exceptions FNE
 *
 * @package Neocode\FNE\Exceptions
 */
class ExceptionFormatter
{
    /**
     * Translator pour les messages localisés
     */
    private TranslatorInterface $translator;

    /**
     * Create a new ExceptionFormatter instance.
     *
     * @param  TranslatorInterface|null  $translator
     */
    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator ?? new Translator(Locale::getDefault(), Locale::getFallback());
    }

    /**
     * Formater une exception en array.
     *
     * @param  FNEException  $exception
     * @param  string|null  $locale
     * @param  bool  $includeTrace
     * @return array<string, mixed>
     */
    public function format(FNEException $exception, ?string $locale = null, bool $includeTrace = false): array
    {
        $locale = $locale ?? $this->translator->getLocale();

        $formatted = [
            'message' => $this->getLocalizedMessage($exception, $locale),
            'error' => $exception->getErrorCode(),
            'status_code' => $exception->getStatusCode(),
            'errors' => $exception->getErrors(),
            'meta' => array_merge($exception->getMeta(), [
                'timestamp' => date('c'),
                'request_id' => $exception->getMeta()['request_id'] ?? uniqid('req_', true),
            ]),
        ];

        if ($includeTrace) {
            $formatted['trace'] = $exception->getTrace();
        }

        return $formatted;
    }

    /**
     * Formater une exception en JSON.
     *
     * @param  FNEException  $exception
     * @param  string|null  $locale
     * @param  bool  $includeTrace
     * @return string
     */
    public function formatJson(FNEException $exception, ?string $locale = null, bool $includeTrace = false): string
    {
        return json_encode($this->format($exception, $locale, $includeTrace), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtenir le message localisé d'une exception.
     *
     * @param  FNEException  $exception
     * @param  string  $locale
     * @return string
     */
    private function getLocalizedMessage(FNEException $exception, string $locale): string
    {
        $errorCode = $exception->getErrorCode();
        $key = "errors.api.{$errorCode}";

        // Essayer de traduire le message
        $translated = $this->translator->translate($key, [], $locale);

        // Si la traduction n'existe pas ou retourne la clé, utiliser le message original
        if ($translated === $key) {
            return $exception->getMessage();
        }

        // Remplacer les variables dans le message traduit
        $replace = [];
        if (preg_match_all('/:(\w+)/', $translated, $matches)) {
            foreach ($matches[1] as $var) {
                if (isset($exception->getMeta()[$var])) {
                    $replace[$var] = $exception->getMeta()[$var];
                }
            }
        }

        return $this->translator->translate($key, $replace, $locale);
    }

    /**
     * Formater plusieurs exceptions en array.
     *
     * @param  array<FNEException>  $exceptions
     * @param  string|null  $locale
     * @return array<string, mixed>
     */
    public function formatMultiple(array $exceptions, ?string $locale = null): array
    {
        return [
            'errors' => array_map(
                fn($exception) => $this->format($exception, $locale, false),
                $exceptions
            ),
            'count' => count($exceptions),
        ];
    }
}

