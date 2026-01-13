<?php

namespace Neocode\FNE\Validation;

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;

/**
 * Validateur pour les factures de vente
 *
 * @package Neocode\FNE\Validation
 */
class InvoiceValidator extends BaseValidator
{
    /**
     * Obtenir les règles de validation de base.
     *
     * @return array<string, array<int, string>>
     */
    protected function getRules(): array
    {
        return [
            'invoiceType' => ['required', 'in:' . InvoiceType::SALE->value . ',' . InvoiceType::PURCHASE->value],
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
            'items.*.taxes' => ['required', 'array', 'min:1'],
            'items.*.taxes.*' => [
                'required',
                'in:' . implode(',', array_map(fn($e) => $e->value, TaxType::cases())),
            ],
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

        // Validation conditionnelle : clientNcc requis pour B2B
        $template = $data['template'] ?? '';
        if ($template === InvoiceTemplate::B2B->value && empty($data['clientNcc'] ?? '')) {
            $errors['clientNcc'] = ['The client ncc field is required when template is B2B.'];
        }

        // Validation conditionnelle : rne requis si isRne = true
        if (($data['isRne'] ?? false) === true && empty($data['rne'] ?? '')) {
            $errors['rne'] = ['The rne field is required when isRne is true.'];
        }

        // Validation conditionnelle : foreignCurrencyRate requis si foreignCurrency fourni
        $foreignCurrency = $data['foreignCurrency'] ?? '';
        if (!empty($foreignCurrency) && (!isset($data['foreignCurrencyRate']) || $data['foreignCurrencyRate'] === '')) {
            $errors['foreignCurrencyRate'] = ['The foreign currency rate field is required when foreign currency is provided.'];
        }

        // Validation conditionnelle : taxes requises pour factures de vente
        $invoiceType = $data['invoiceType'] ?? '';
        if ($invoiceType === InvoiceType::SALE->value) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                $errors['items'] = [
                    'Items are required for sale invoices.',
                    'Hint: Make sure to load the items relation before certifying: $invoice->load(\'items\');',
                ];
            } else {
                foreach ($items as $index => $item) {
                    if (empty($item['taxes'] ?? [])) {
                        $errors["items.{$index}.taxes"] = ['Taxes are required for sale invoice items.'];
                    }
                }
            }
        }

        // Validation conditionnelle : customTaxes
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (isset($item['customTaxes']) && is_array($item['customTaxes']) && count($item['customTaxes']) > 0) {
                    foreach ($item['customTaxes'] as $taxIndex => $customTax) {
                        if (empty($customTax['name'] ?? '')) {
                            $errors["items.{$index}.customTaxes.{$taxIndex}.name"] = ['The name field is required for custom taxes.'];
                        }
                        if (!isset($customTax['amount']) || !is_numeric($customTax['amount'])) {
                            $errors["items.{$index}.customTaxes.{$taxIndex}.amount"] = ['The amount field is required and must be numeric for custom taxes.'];
                        }
                    }
                }
            }
        }

        return $errors;
    }
}

