<?php

namespace Neocode\FNE\Mappers;

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;

/**
 * Mapper pour les bordereaux d'achat
 *
 * @package Neocode\FNE\Mappers
 */
class PurchaseMapper extends BaseMapper
{
    /**
     * Effectuer le mapping spécifique pour les bordereaux d'achat.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function doMap(array $data): array
    {
        $mapped = [
            'invoiceType' => InvoiceType::PURCHASE->value,
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

        // Mapping des items (sans taxes pour les bordereaux d'achat)
        $mapped['items'] = $this->mapItems($data['items'] ?? []);

        // Remise globale
        if (isset($data['discount'])) {
            $mapped['discount'] = $this->normalizeDiscount($data['discount']);
        }

        return $mapped;
    }

    /**
     * Mapper les items (sans taxes pour les bordereaux d'achat).
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
     * Mapper le paymentMethod.
     *
     * @param  mixed  $paymentMethod
     * @return string
     */
    protected function mapPaymentMethod(mixed $paymentMethod): string
    {
        if (is_array($paymentMethod)) {
            return PaymentMethod::CASH->value;
        }

        if (is_object($paymentMethod) && enum_exists(get_class($paymentMethod))) {
            return $paymentMethod->value;
        }

        $value = strtolower((string) $paymentMethod);

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
        if (is_array($template)) {
            return InvoiceTemplate::B2C->value;
        }

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
        if (is_array($phone)) {
            return '';
        }

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

        return max(0, min(100, $value));
    }
}

