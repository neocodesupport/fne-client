<?php

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Mappers\PurchaseMapper;

beforeEach(function () {
    $this->mapper = new PurchaseMapper();
});

test('PurchaseMapper maps purchase invoice data correctly', function () {
    $data = [
        'invoiceType' => InvoiceType::PURCHASE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Supplier Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'supplier@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => 10,
                'amount' => 50.0,
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result)
        ->toHaveKey('invoiceType')
        ->toHaveKey('paymentMethod')
        ->toHaveKey('template')
        ->toHaveKey('items')
        ->and($result['invoiceType'])->toBe(InvoiceType::PURCHASE->value)
        ->and($result['items'])->toBeArray()
        ->and($result['items'][0])->toHaveKey('description')
        ->and($result['items'][0])->toHaveKey('quantity')
        ->and($result['items'][0])->toHaveKey('amount');
});

test('PurchaseMapper does not include taxes in items', function () {
    $data = [
        'invoiceType' => InvoiceType::PURCHASE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Supplier Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'supplier@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => 10,
                'amount' => 50.0,
                'taxes' => ['TVA'], // Should be removed
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    // Les taxes ne doivent pas être présentes dans les items pour les bordereaux d'achat
    expect($result['items'][0])->not->toHaveKey('taxes');
});

test('PurchaseMapper normalizes numeric values', function () {
    $data = [
        'invoiceType' => InvoiceType::PURCHASE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Supplier Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'supplier@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => '10.5', // String
                'amount' => '50.25', // String
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result['items'][0]['quantity'])->toBe(10.5)
        ->and($result['items'][0]['amount'])->toBe(50.25);
});

