<?php

namespace Neocode\FNE\Exceptions;

/**
 * Exception de validation avec contexte détaillé
 *
 * @package Neocode\FNE\Exceptions
 */
class ValidationException extends BadRequestException
{
    /**
     * Données qui ont causé l'erreur
     */
    protected array $contextData = [];

    /**
     * Règles qui ont échoué
     */
    protected array $failedRules = [];

    /**
     * Create a new ValidationException instance.
     *
     * @param  string  $message
     * @param  array  $errors
     * @param  array  $contextData  Données qui ont causé l'erreur
     * @param  array  $failedRules  Règles qui ont échoué
     * @param  \Throwable|null  $previous
     */
    public function __construct(
        string $message = 'Validation failed',
        array $errors = [],
        array $contextData = [],
        array $failedRules = [],
        ?\Throwable $previous = null
    ) {
        // Construire un message détaillé avec contexte
        $detailedMessage = $this->buildDetailedMessage($message, $errors, $contextData);

        parent::__construct($detailedMessage, $errors, $previous);

        $this->errorCode = 'validation_exception';
        $this->contextData = $contextData;
        $this->failedRules = $failedRules;

        // Ajouter des métadonnées détaillées
        $this->meta = array_merge($this->meta, [
            'validation_context' => $this->getValidationContext(),
            'failed_fields' => array_keys($errors),
            'failed_rules' => $failedRules,
            'data_sample' => $this->getDataSample($contextData),
        ]);
    }

    /**
     * Construire un message détaillé avec contexte.
     *
     * @param  string  $message
     * @param  array  $errors
     * @param  array  $contextData
     * @return string
     */
    protected function buildDetailedMessage(string $message, array $errors, array $contextData): string
    {
        $details = [];
        $details[] = $message;

        // Ajouter le nombre d'erreurs
        $errorCount = count($errors);
        $details[] = "{$errorCount} champ(s) invalide(s)";

        // Ajouter les champs en erreur
        $failedFields = array_keys($errors);
        if (!empty($failedFields)) {
            $details[] = 'Champs en erreur: ' . implode(', ', $failedFields);
        }

        // Ajouter un échantillon des données problématiques
        $sample = $this->getDataSample($contextData, 3);
        if (!empty($sample)) {
            $details[] = 'Données reçues (échantillon): ' . json_encode($sample, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return implode("\n", $details);
    }

    /**
     * Obtenir le contexte de validation.
     *
     * @return array<string, mixed>
     */
    protected function getValidationContext(): array
    {
        $trace = $this->getTrace();
        $caller = $trace[0] ?? null;

        $context = [
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];

        // Trouver le fichier appelant dans la stack trace
        foreach ($trace as $frame) {
            // Ignorer les fichiers internes du package
            if (isset($frame['file']) && !str_contains($frame['file'], 'vendor')) {
                $context['called_from'] = [
                    'file' => $frame['file'],
                    'line' => $frame['line'] ?? null,
                    'function' => $frame['function'] ?? null,
                    'class' => $frame['class'] ?? null,
                ];
                break;
            }
        }

        return $context;
    }

    /**
     * Obtenir un échantillon des données (masqué pour les données sensibles).
     *
     * @param  array  $data
     * @param  int  $maxDepth
     * @return array
     */
    protected function getDataSample(array $data, int $maxDepth = 2): array
    {
        $sensitiveKeys = ['api_key', 'apiKey', 'password', 'token', 'secret', 'authorization'];
        $sample = [];

        foreach ($data as $key => $value) {
            // Masquer les données sensibles
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $sample[$key] = '***MASKED***';
                continue;
            }

            if (is_array($value)) {
                if ($maxDepth > 0) {
                    $sample[$key] = $this->getDataSample($value, $maxDepth - 1);
                } else {
                    $sample[$key] = '[Array(' . count($value) . ' items)]';
                }
            } elseif (is_object($value)) {
                $sample[$key] = '[' . get_class($value) . ']';
            } elseif (is_string($value) && strlen($value) > 100) {
                $sample[$key] = substr($value, 0, 100) . '...';
            } else {
                $sample[$key] = $value;
            }
        }

        return $sample;
    }

    /**
     * Obtenir les données qui ont causé l'erreur.
     *
     * @return array
     */
    public function getContextData(): array
    {
        return $this->contextData;
    }

    /**
     * Obtenir les règles qui ont échoué.
     *
     * @return array
     */
    public function getFailedRules(): array
    {
        return $this->failedRules;
    }

    /**
     * Obtenir un rapport détaillé de l'erreur.
     *
     * @return array<string, mixed>
     */
    public function getDetailedReport(): array
    {
        return [
            'message' => $this->getMessage(),
            'error_code' => $this->getErrorCode(),
            'status_code' => $this->getStatusCode(),
            'errors' => $this->getErrors(),
            'context' => [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'validation_context' => $this->getValidationContext(),
            ],
            'failed_fields' => array_keys($this->getErrors()),
            'failed_rules' => $this->failedRules,
            'data_sample' => $this->getDataSample($this->contextData),
            'stack_trace' => array_slice($this->getTrace(), 0, 5), // Limiter à 5 frames
        ];
    }

    /**
     * Obtenir une représentation textuelle détaillée de l'erreur.
     *
     * @return string
     */
    public function getDetailedMessage(): string
    {
        $lines = [];
        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';
        $lines[] = '  ERREUR DE VALIDATION FNE';
        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';
        $lines[] = '';

        // Message principal
        $lines[] = 'Message: ' . $this->getMessage();
        $lines[] = '';

        // Contexte
        $context = $this->getValidationContext();
        $lines[] = '📍 Contexte:';
        $lines[] = '  Fichier: ' . $this->getFile();
        $lines[] = '  Ligne: ' . $this->getLine();
        if (isset($context['called_from'])) {
            $lines[] = '  Appelé depuis:';
            $lines[] = '    - Fichier: ' . ($context['called_from']['file'] ?? 'N/A');
            $lines[] = '    - Ligne: ' . ($context['called_from']['line'] ?? 'N/A');
            $lines[] = '    - Fonction: ' . ($context['called_from']['function'] ?? 'N/A');
            if (isset($context['called_from']['class'])) {
                $lines[] = '    - Classe: ' . $context['called_from']['class'];
            }
        }
        $lines[] = '';

        // Champs en erreur
        $failedFields = array_keys($this->getErrors());
        $lines[] = '❌ Champs invalides (' . count($failedFields) . '):';
        foreach ($failedFields as $field) {
            $lines[] = '  - ' . $field;
            if (isset($this->getErrors()[$field])) {
                foreach ($this->getErrors()[$field] as $error) {
                    $lines[] = '    → ' . $error;
                }
            }
            if (isset($this->failedRules[$field])) {
                $lines[] = '    Règles échouées: ' . implode(', ', $this->failedRules[$field]);
            }
        }
        $lines[] = '';

        // Échantillon des données
        $sample = $this->getDataSample($this->contextData, 2);
        if (!empty($sample)) {
            $lines[] = '📋 Données reçues (échantillon):';
            $lines[] = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $lines[] = '';
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';

        return implode("\n", $lines);
    }

    /**
     * Afficher le message détaillé (pour débogage).
     *
     * @return void
     */
    public function dump(): void
    {
        echo $this->getDetailedMessage();
    }
}

