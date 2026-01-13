<?php

namespace Neocode\FNE\Validation;

use Neocode\FNE\Contracts\ValidatorInterface;
use Neocode\FNE\Exceptions\ValidationException;

/**
 * Validateur de base abstrait
 *
 * @package Neocode\FNE\Validation
 */
abstract class BaseValidator implements ValidatorInterface
{
    /**
     * Valider les données.
     *
     * @param  array<string, mixed>  $data  Données à valider
     * @param  array<string, mixed>  $rules  Règles de validation
     * @return void
     * @throws \Neocode\FNE\Exceptions\ValidationException
     */
    public function validate(array $data, array $rules): void
    {
        // Obtenir les règles de base
        $baseRules = $this->getRules();

        // Fusionner avec les règles fournies
        $allRules = array_merge($baseRules, $rules);

        // Valider avec les règles
        $validationResult = $this->validateRules($data, $allRules);
        $errors = $validationResult['errors'] ?? [];
        $failedRules = $validationResult['failed_rules'] ?? [];

        // Validation conditionnelle
        $conditionalErrors = $this->validateConditional($data);

        // Fusionner les erreurs
        $allErrors = array_merge($errors, $conditionalErrors);

        // Si des erreurs existent, lever une exception avec contexte détaillé
        if (!empty($allErrors)) {
            throw new ValidationException(
                'Validation failed',
                $allErrors,
                $data,
                $failedRules
            );
        }
    }

    /**
     * Obtenir les règles de validation de base.
     *
     * @return array<string, mixed>
     */
    abstract protected function getRules(): array;

    /**
     * Valider les règles conditionnelles.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, array<int, string>>  Erreurs de validation
     */
    abstract protected function validateConditional(array $data): array;

    /**
     * Valider les données selon les règles.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>  Résultat avec 'errors' et 'failed_rules'
     */
    protected function validateRules(array $data, array $rules): array
    {
        $errors = [];
        $failedRules = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            // Si le champ est un tableau imbriqué (ex: items.*.taxes.*)
            // On vérifie si le champ se termine par '.*' après avoir un '.*.' au milieu
            if (str_contains($field, '.*.') && str_ends_with($field, '.*')) {
                // Gérer les tableaux imbriqués (ex: items.*.taxes.*)
                $fieldParts = explode('.*.', $field);
                $nestedResult = $this->validateNestedArrayField($data, $fieldParts, $fieldRules);
                $errors = array_merge($errors, $nestedResult['errors'] ?? []);
                if (!empty($nestedResult['failed_rules'] ?? [])) {
                    $failedRules[$field] = $nestedResult['failed_rules'];
                }
                continue;
            }

            // Si le champ est un tableau simple (ex: items.*.description)
            if (str_contains($field, '.*.')) {
                [$parentField, $childField] = explode('.*.', $field, 2);
                $arrayResult = $this->validateArrayField($data, $parentField, $childField, $fieldRules);
                $errors = array_merge($errors, $arrayResult['errors'] ?? []);
                if (!empty($arrayResult['failed_rules'] ?? [])) {
                    $failedRules[$field] = $arrayResult['failed_rules'];
                }
                continue;
            }

            // Valider le champ
            $fieldResult = $this->validateField($field, $value, $fieldRules);

            if (!empty($fieldResult['errors'])) {
                $errors[$field] = $fieldResult['errors'];
                if (!empty($fieldResult['failed_rules'])) {
                    $failedRules[$field] = $fieldResult['failed_rules'];
                }
            }
        }

        return [
            'errors' => $errors,
            'failed_rules' => $failedRules,
        ];
    }

    /**
     * Valider un champ de tableau (ex: items.*.description).
     *
     * @param  array<string, mixed>  $data
     * @param  string  $parentField
     * @param  string  $childField
     * @param  array<int, string>  $rules
     * @return array<string, mixed>  Résultat avec 'errors' et 'failed_rules'
     */
    protected function validateArrayField(array $data, string $parentField, string $childField, array $rules): array
    {
        $errors = [];
        $failedRules = [];

        if (!isset($data[$parentField]) || !is_array($data[$parentField])) {
            return ['errors' => $errors, 'failed_rules' => $failedRules];
        }

        foreach ($data[$parentField] as $index => $item) {
            $value = $item[$childField] ?? null;
            $fieldKey = "{$parentField}.{$index}.{$childField}";
            $fieldResult = $this->validateField($fieldKey, $value, $rules);

            if (!empty($fieldResult['errors'])) {
                $errors[$fieldKey] = $fieldResult['errors'];
                if (!empty($fieldResult['failed_rules'])) {
                    $failedRules[$fieldKey] = $fieldResult['failed_rules'];
                }
            }
        }

        return [
            'errors' => $errors,
            'failed_rules' => $failedRules,
        ];
    }

    /**
     * Valider un champ de tableau imbriqué (ex: items.*.taxes.*).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $parts  Parties du chemin (ex: ['items', 'taxes', ''])
     * @param  array<int, string>  $rules
     * @return array<string, mixed>  Résultat avec 'errors' et 'failed_rules'
     */
    protected function validateNestedArrayField(array $data, array $parts, array $rules): array
    {
        $errors = [];
        $failedRules = [];

        // Exemple: items.*.taxes.* -> ['items', 'taxes', '']
        // Le dernier élément peut être vide car c'est pour valider chaque élément du tableau taxes
        $parentField = $parts[0] ?? ''; // 'items'
        $nestedField = $parts[1] ?? ''; // 'taxes'

        if (empty($parentField) || empty($nestedField)) {
            return ['errors' => $errors, 'failed_rules' => $failedRules];
        }

        if (!isset($data[$parentField]) || !is_array($data[$parentField])) {
            return ['errors' => $errors, 'failed_rules' => $failedRules];
        }

        foreach ($data[$parentField] as $index => $item) {
            if (!isset($item[$nestedField]) || !is_array($item[$nestedField])) {
                continue;
            }

            foreach ($item[$nestedField] as $taxIndex => $taxValue) {
                $fieldKey = "{$parentField}.{$index}.{$nestedField}.{$taxIndex}";
                $fieldResult = $this->validateField($fieldKey, $taxValue, $rules);

                if (!empty($fieldResult['errors'])) {
                    $errors[$fieldKey] = $fieldResult['errors'];
                    if (!empty($fieldResult['failed_rules'])) {
                        $failedRules[$fieldKey] = $fieldResult['failed_rules'];
                    }
                }
            }
        }

        return [
            'errors' => $errors,
            'failed_rules' => $failedRules,
        ];
    }

    /**
     * Valider un champ selon ses règles.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  array<int, string>  $rules
     * @return array<string, mixed>  Résultat avec 'errors' et 'failed_rules'
     */
    protected function validateField(string $field, mixed $value, array $rules): array
    {
        $errors = [];
        $failedRules = [];

        foreach ($rules as $rule) {
            $error = $this->applyRule($field, $value, $rule);

            if ($error !== null) {
                $errors[] = $error;
                $failedRules[] = $rule;
            }
        }

        return [
            'errors' => $errors,
            'failed_rules' => $failedRules,
        ];
    }

    /**
     * Appliquer une règle de validation.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  string  $rule
     * @return string|null  Message d'erreur ou null si valide
     */
    protected function applyRule(string $field, mixed $value, string $rule): ?string
    {
        // Parser la règle (ex: "required", "in:value1,value2", "min:5")
        [$ruleName, $ruleValue] = str_contains($rule, ':') ? explode(':', $rule, 2) : [$rule, null];

        return match ($ruleName) {
            'required' => $this->validateRequired($field, $value),
            'string' => $this->validateString($field, $value),
            'numeric' => $this->validateNumeric($field, $value),
            'email' => $this->validateEmail($field, $value),
            'boolean' => $this->validateBoolean($field, $value),
            'array' => $this->validateArray($field, $value),
            'min' => $this->validateMin($field, $value, (float) $ruleValue),
            'max' => $this->validateMax($field, $value, (float) $ruleValue),
            'in' => $this->validateIn($field, $value, $ruleValue),
            'uuid' => $this->validateUuid($field, $value),
            default => null,
        };
    }

    /**
     * Valider que le champ est requis.
     */
    protected function validateRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return "The {$field} field is required.";
        }

        return null;
    }

    /**
     * Valider que le champ est une chaîne.
     */
    protected function validateString(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_string($value)) {
            return "The {$field} field must be a string.";
        }

        return null;
    }

    /**
     * Valider que le champ est numérique.
     */
    protected function validateNumeric(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_numeric($value)) {
            return "The {$field} field must be numeric.";
        }

        return null;
    }

    /**
     * Valider que le champ est un email.
     */
    protected function validateEmail(string $field, mixed $value): ?string
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "The {$field} field must be a valid email address.";
        }

        return null;
    }

    /**
     * Valider que le champ est un booléen.
     */
    protected function validateBoolean(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_bool($value)) {
            return "The {$field} field must be a boolean.";
        }

        return null;
    }

    /**
     * Valider que le champ est un tableau.
     */
    protected function validateArray(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_array($value)) {
            return "The {$field} field must be an array.";
        }

        return null;
    }

    /**
     * Valider la valeur minimale.
     */
    protected function validateMin(string $field, mixed $value, float $min): ?string
    {
        if ($value !== null && is_numeric($value) && (float) $value < $min) {
            return "The {$field} field must be at least {$min}.";
        }

        return null;
    }

    /**
     * Valider la valeur maximale.
     */
    protected function validateMax(string $field, mixed $value, float $max): ?string
    {
        if ($value !== null && is_numeric($value) && (float) $value > $max) {
            return "The {$field} field must not exceed {$max}.";
        }

        return null;
    }

    /**
     * Valider que la valeur est dans une liste.
     */
    protected function validateIn(string $field, mixed $value, ?string $allowedValues): ?string
    {
        if ($value === null || $allowedValues === null) {
            return null;
        }

        $allowed = explode(',', $allowedValues);

        if (!in_array((string) $value, $allowed, true)) {
            return "The {$field} field must be one of: " . implode(', ', $allowed) . '.';
        }

        return null;
    }

    /**
     * Valider que le champ est un UUID valide.
     */
    protected function validateUuid(string $field, mixed $value): ?string
    {
        if ($value !== null && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $value)) {
            return "The {$field} field must be a valid UUID.";
        }

        return null;
    }
}

