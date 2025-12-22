<?php

namespace Neocode\FNE\Mappers;

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;

/**
 * Mapper pour les factures de vente
 *
 * @package Neocode\FNE\Mappers
 */
class InvoiceMapper extends BaseMapper
{
    /**
     * Effectuer le mapping spécifique pour les factures de vente.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function doMap(array $data): array
    {
        $mapped = [
            'invoiceType' => $data['invoiceType'] ?? InvoiceType::SALE->value,
            'paymentMethod' => $this->mapPaymentMethod($data['paymentMethod'] ?? ''),
            'template' => $this->mapTemplate($data['template'] ?? ''),
            'isRne' => $data['isRne'] ?? false,
            'clientCompanyName' => $data['clientCompanyName'] ?? '',
            'clientPhone' => $this->normalizePhone($data['clientPhone'] ?? ''),
            'clientEmail' => $data['clientEmail'] ?? '',
            'pointOfSale' => $data['pointOfSale'] ?? '',
            'establishment' => $data['establishment'] ?? '',
        ];

        // Champs conditionnels
        if (($mapped['template'] === InvoiceTemplate::B2B->value) && isset($data['clientNcc'])) {
            $mapped['clientNcc'] = $data['clientNcc'];
        }

        if (($mapped['isRne'] === true) && isset($data['rne'])) {
            $mapped['rne'] = $data['rne'];
        }

        if (isset($data['clientSellerName'])) {
            $mapped['clientSellerName'] = $data['clientSellerName'];
        }

        if (isset($data['commercialMessage'])) {
            $mapped['commercialMessage'] = $data['commercialMessage'];
        }

        if (isset($data['footer'])) {
            $mapped['footer'] = $data['footer'];
        }

        // Gestion des devises étrangères
        if (!empty($data['foreignCurrency'] ?? '')) {
            $mapped['foreignCurrency'] = $data['foreignCurrency'];
            $mapped['foreignCurrencyRate'] = $data['foreignCurrencyRate'] ?? 0;
        } else {
            $mapped['foreignCurrency'] = '';
            $mapped['foreignCurrencyRate'] = 0;
        }

        // Mapping des items
        $mapped['items'] = $this->mapItems($data['items'] ?? []);

        // Remise globale
        if (isset($data['discount'])) {
            $mapped['discount'] = $this->normalizeDiscount($data['discount']);
        }

        // CustomTaxes au niveau facture
        if (isset($data['customTaxes']) && is_array($data['customTaxes'])) {
            $mapped['customTaxes'] = $this->mapCustomTaxes($data['customTaxes']);
        }

        return $mapped;
    }

    /**
     * Mapper les items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mapItems(array $items): array
    {
        return array_map(function ($item) {
            $mappedItem = [
                'description' => $item['description'] ?? '',
                'quantity' => $this->normalizeQuantity($item['quantity'] ?? 1),
                'amount' => $this->normalizeAmount($item['amount'] ?? 0),
            ];

            // Taxes (obligatoires pour factures de vente)
            if (isset($item['taxes']) && is_array($item['taxes'])) {
                $mappedItem['taxes'] = $this->mapTaxes($item['taxes']);
            }

            // CustomTaxes
            if (isset($item['customTaxes']) && is_array($item['customTaxes'])) {
                $mappedItem['customTaxes'] = $this->mapCustomTaxes($item['customTaxes']);
            }

            // Champs optionnels
            if (isset($item['reference'])) {
                $mappedItem['reference'] = $item['reference'];
            }

            if (isset($item['discount'])) {
                $mappedItem['discount'] = $this->normalizeDiscount($item['discount']);
            }

            if (isset($item['measurementUnit'])) {
                $mappedItem['measurementUnit'] = $item['measurementUnit'];
            }

            return $mappedItem;
        }, $items);
    }

    /**
     * Mapper les taxes.
     *
     * @param  array<int, string>  $taxes
     * @return array<int, string>
     */
    protected function mapTaxes(array $taxes): array
    {
        return array_map(function ($tax) {
            // Si c'est un enum, convertir en string
            if (is_object($tax) && enum_exists(get_class($tax))) {
                return $tax->value;
            }

            // Normaliser le format (TVA, TVAB, TVAC, TVAD)
            return strtoupper((string) $tax);
        }, $taxes);
    }

    /**
     * Mapper les customTaxes.
     *
     * @param  array<int, array<string, mixed>>  $customTaxes
     * @return array<int, array<string, mixed>>
     */
    protected function mapCustomTaxes(array $customTaxes): array
    {
        return array_map(function ($customTax) {
            return [
                'name' => $customTax['name'] ?? '',
                'amount' => $this->normalizeAmount($customTax['amount'] ?? 0),
            ];
        }, $customTaxes);
    }

    /**
     * Mapper le paymentMethod.
     *
     * @param  mixed  $paymentMethod
     * @return string
     */
    protected function mapPaymentMethod(mixed $paymentMethod): string
    {
        if (is_object($paymentMethod) && enum_exists(get_class($paymentMethod))) {
            return $paymentMethod->value;
        }

        $value = strtolower((string) $paymentMethod);

        // Normaliser les valeurs
        return match ($value) {
            'cash', 'espece' => PaymentMethod::CASH->value,
            'card', 'carte' => PaymentMethod::CARD->value,
            'check', 'cheque' => PaymentMethod::CHECK->value,
            'mobile-money', 'mobilemoney', 'mobile_money' => PaymentMethod::MOBILE_MONEY->value,
            'transfer', 'virement' => PaymentMethod::TRANSFER->value,
            'deferred', 'terme', 'a_terme' => PaymentMethod::DEFERRED->value,
            default => $value,
        };
    }

    /**
     * Mapper le template.
     *
     * @param  mixed  $template
     * @return string
     */
    protected function mapTemplate(mixed $template): string
    {
        if (is_object($template) && enum_exists(get_class($template))) {
            return $template->value;
        }

        $value = strtoupper((string) $template);

        return match ($value) {
            'B2C' => InvoiceTemplate::B2C->value,
            'B2B' => InvoiceTemplate::B2B->value,
            'B2F' => InvoiceTemplate::B2F->value,
            'B2G' => InvoiceTemplate::B2G->value,
            default => $value,
        };
    }

    /**
     * Normaliser le numéro de téléphone.
     *
     * @param  mixed  $phone
     * @return string
     */
    protected function normalizePhone(mixed $phone): string
    {
        if (is_numeric($phone)) {
            return (string) $phone;
        }

        return (string) $phone;
    }

    /**
     * Normaliser la quantité.
     *
     * @param  mixed  $quantity
     * @return float
     */
    protected function normalizeQuantity(mixed $quantity): float
    {
        return (float) $quantity;
    }

    /**
     * Normaliser le montant.
     *
     * @param  mixed  $amount
     * @return float
     */
    protected function normalizeAmount(mixed $amount): float
    {
        return (float) $amount;
    }

    /**
     * Normaliser la remise (en pourcentage).
     *
     * @param  mixed  $discount
     * @return float
     */
    protected function normalizeDiscount(mixed $discount): float
    {
        $value = (float) $discount;

        // S'assurer que la remise est entre 0 et 100
        return max(0, min(100, $value));
    }
}

