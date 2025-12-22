<?php

namespace Neocode\FNE\Validation;

/**
 * Validateur pour les avoirs
 *
 * @package Neocode\FNE\Validation
 */
class RefundValidator extends BaseValidator
{
    /**
     * Obtenir les règles de validation de base.
     *
     * @return array<string, array<int, string>>
     */
    protected function getRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Valider les règles conditionnelles.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, array<int, string>>
     */
    protected function validateConditional(array $data): array
    {
        $errors = [];

        // Vérifier que les quantités sont valides (positives)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $quantity = $item['quantity'] ?? 0;

                if (!is_numeric($quantity) || (float) $quantity <= 0) {
                    $errors["items.{$index}.quantity"] = ['The quantity must be greater than 0.'];
                }
            }
        }

        return $errors;
    }
}

