<?php

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Http\GuzzleHttpClient;
use Neocode\FNE\Services\PurchaseService;

beforeEach(function () {
    $this->config = new FNEConfig([
        'api_key' => 'test-key-123',
        'base_url' => 'https://fne-api-mock.test',
        'mode' => 'test',
        'cache' => [
            'enabled' => false,
        ],
    ]);

    $this->httpClient = new GuzzleHttpClient($this->config);

    $this->service = new PurchaseService(
        $this->httpClient,
        $this->config
    );
});

test('PurchaseService can submit a purchase invoice', function () {
    // Skip si l'API mock n'est pas disponible
    $context = stream_context_create([
        'http' => ['timeout' => 2, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    
    if (!@file_get_contents('https://fne-api-mock.test', false, $context)) {
        $this->markTestSkipped('API mock not available');
    }
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

    $result = $this->service->submit($data);

    expect($result)
        ->toBeInstanceOf(\Neocode\FNE\DTOs\ResponseDTO::class)
        ->and($result->ncc)->not->toBeEmpty()
        ->and($result->reference)->not->toBeEmpty()
        ->and($result->invoice)->not->toBeNull()
        ->and($result->invoice->type)->toBe('invoice'); // L'API retourne toujours "invoice" pour les factures
});

test('PurchaseService throws ValidationException if taxes are provided', function () {
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
                'taxes' => ['TVA'], // Should not be allowed
            ],
        ],
    ];

    expect(fn() => $this->service->submit($data))
        ->toThrow(\Neocode\FNE\Exceptions\ValidationException::class);
});

