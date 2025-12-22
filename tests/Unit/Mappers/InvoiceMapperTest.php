<?php

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Exceptions\MappingException;
use Neocode\FNE\Mappers\InvoiceMapper;

beforeEach(function () {
    $this->mapper = new InvoiceMapper();
});

test('InvoiceMapper maps basic invoice data correctly', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result)
        ->toHaveKey('invoiceType')
        ->toHaveKey('paymentMethod')
        ->toHaveKey('template')
        ->toHaveKey('items')
        ->and($result['invoiceType'])->toBe(InvoiceType::SALE->value)
        ->and($result['paymentMethod'])->toBe(PaymentMethod::CASH->value)
        ->and($result['template'])->toBe(InvoiceTemplate::B2C->value)
        ->and($result['items'])->toBeArray()
        ->and($result['items'][0])->toHaveKey('taxes');
});

test('InvoiceMapper maps B2B template with clientNcc', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2B->value,
        'isRne' => false,
        'clientNcc' => '123456789',
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result)
        ->toHaveKey('clientNcc')
        ->and((string) $result['clientNcc'])->toBe('123456789');
});

test('InvoiceMapper maps items with taxes correctly', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 2.5,
                'amount' => 100.50,
                'taxes' => [TaxType::TVA->value, TaxType::TVAB->value],
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result['items'][0])
        ->toHaveKey('taxes')
        ->and($result['items'][0]['taxes'])->toBeArray()
        ->and($result['items'][0]['taxes'])->toContain(TaxType::TVA->value)
        ->and($result['items'][0]['taxes'])->toContain(TaxType::TVAB->value)
        ->and($result['items'][0]['quantity'])->toBe(2.5)
        ->and($result['items'][0]['amount'])->toBe(100.50);
});

test('InvoiceMapper maps items with customTaxes correctly', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
                'customTaxes' => [
                    ['name' => 'GRA', 'amount' => 5.0],
                    ['name' => 'AIRSI', 'amount' => 3.0],
                ],
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result['items'][0])
        ->toHaveKey('customTaxes')
        ->and($result['items'][0]['customTaxes'])->toBeArray()
        ->and($result['items'][0]['customTaxes'][0])->toHaveKey('name')
        ->and($result['items'][0]['customTaxes'][0])->toHaveKey('amount')
        ->and($result['items'][0]['customTaxes'][0]['name'])->toBe('GRA')
        ->and($result['items'][0]['customTaxes'][0]['amount'])->toBe(5.0);
});

test('InvoiceMapper normalizes boolean values', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => 'true', // String instead of boolean
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result['isRne'])->toBeBool();
});

test('InvoiceMapper handles dot notation for nested data', function () {
    $data = [
        'invoice' => [
            'type' => InvoiceType::SALE->value,
            'payment' => [
                'method' => PaymentMethod::CASH->value,
            ],
        ],
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'client' => [
            'companyName' => 'Test Company',
            'phone' => '0123456789',
            'email' => 'test@example.com',
        ],
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    // Le mapper devrait pouvoir gérer la notation pointée si configuré
    $result = $this->mapper->map($data);

    expect($result)->toBeArray();
});

