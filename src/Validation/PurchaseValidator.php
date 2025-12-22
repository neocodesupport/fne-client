<?php

namespace Neocode\FNE\Validation;

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;

/**
 * Validateur pour les bordereaux d'achat
 *
 * @package Neocode\FNE\Validation
 */
class PurchaseValidator extends BaseValidator
{
    /**
     * Obtenir les règles de validation de base.
     *
     * @return array<string, array<int, string>>
     */
    protected function getRules(): array
    {
        return [
            'invoiceType' => ['required', 'in:' . InvoiceType::PURCHASE->value],
            'paymentMethod' => [
                'required',
                'in:' . implode(',', array_map(fn($e) => $e->value, PaymentMethod::cases())),
            ],
            'template' => [
                'required',
                'in:' . implode(',', array_map(fn($e) => $e->value, InvoiceTemplate::cases())),
            ],
            'isRne' => ['required', 'boolean'],
            'clientCompanyName' => ['required', 'string'],
            'clientPhone' => ['required'],
            'clientEmail' => ['required', 'email'],
            'pointOfSale' => ['required', 'string'],
            'establishment' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            // Note : Pas de taxes pour les bordereaux d'achat
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

        // Validation conditionnelle : rne requis si isRne = true
        if (($data['isRne'] ?? false) === true && empty($data['rne'] ?? '')) {
            $errors['rne'] = ['The rne field is required when isRne is true.'];
        }

        // Validation conditionnelle : foreignCurrencyRate requis si foreignCurrency fourni
        $foreignCurrency = $data['foreignCurrency'] ?? '';
        if (!empty($foreignCurrency) && (!isset($data['foreignCurrencyRate']) || $data['foreignCurrencyRate'] === '')) {
            $errors['foreignCurrencyRate'] = ['The foreign currency rate field is required when foreign currency is provided.'];
        }

        // Vérifier qu'il n'y a pas de taxes dans les items (bordereaux d'achat)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (isset($item['taxes']) && !empty($item['taxes'])) {
                    $errors["items.{$index}.taxes"] = ['Taxes are not allowed for purchase invoices.'];
                }
            }
        }

        return $errors;
    }
}

