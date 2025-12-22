<?php

use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Exceptions\ValidationException;
use Neocode\FNE\Validation\InvoiceValidator;

beforeEach(function () {
    $this->validator = new InvoiceValidator();
});

test('InvoiceValidator validates required fields', function () {
    $data = [];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

test('InvoiceValidator validates correct invoice data', function () {
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
                'quantity' => 1.0,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    // La validation ne doit pas lever d'exception
    $this->validator->validate($data, []);
    expect(true)->toBeTrue();
});

test('InvoiceValidator requires clientNcc for B2B template', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2B->value,
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
                'amount' => 100,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

test('InvoiceValidator requires rne when isRne is true', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => true,
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'test@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

test('InvoiceValidator validates email format', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Company',
        'clientPhone' => '0123456789',
        'clientEmail' => 'invalid-email',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1,
                'amount' => 100,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

