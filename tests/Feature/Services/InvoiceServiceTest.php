<?php

use Neocode\FNE\Cache\ArrayCache;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Http\GuzzleHttpClient;
use Neocode\FNE\Services\InvoiceService;

beforeEach(function () {
    // Configuration pour utiliser l'API mock locale
    $this->config = new FNEConfig([
        'api_key' => 'test-key-123',
        'base_url' => 'https://fne-api-mock.test',
        'mode' => 'test',
        'cache' => [
            'enabled' => false,
        ],
    ]);

    // Utiliser Guzzle pour les tests (plus simple que Laravel HTTP Client)
    $this->httpClient = new GuzzleHttpClient($this->config);

    $this->service = new InvoiceService(
        $this->httpClient,
        $this->config,
        null, // Mapper par défaut
        null, // Validator par défaut
        null, // Pas de cache pour les tests
        null  // Pas de logger pour les tests
    );
});

test('InvoiceService can sign a B2C sale invoice', function () {
    // Skip si l'API mock n'est pas disponible
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 2,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    
    if (!@file_get_contents('https://fne-api-mock.test/api/external/invoices/sign', false, $context)) {
        $this->markTestSkipped('API mock not available');
    }

    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Customer',
        'clientPhone' => '0123456789',
        'clientEmail' => 'customer@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    $result = $this->service->sign($data);

    expect($result)
        ->toBeInstanceOf(\Neocode\FNE\DTOs\ResponseDTO::class)
        ->and($result->ncc)->not->toBeEmpty()
        ->and($result->reference)->not->toBeEmpty()
        ->and($result->token)->not->toBeEmpty()
        ->and($result->invoice)->not->toBeNull()
        ->and($result->invoice->id)->not->toBeEmpty();
})->skip(fn() => !@file_get_contents('https://fne-api-mock.test', false, stream_context_create([
    'http' => ['timeout' => 2, 'ignore_errors' => true],
])));

test('InvoiceService can sign a B2B sale invoice with clientNcc', function () {
    // Skip si l'API mock n'est pas disponible
    $context = stream_context_create([
        'http' => ['timeout' => 2, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    
    if (!@file_get_contents('https://fne-api-mock.test', false, $context)) {
        $this->markTestSkipped('API mock not available');
    }
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::MOBILE_MONEY->value,
        'template' => InvoiceTemplate::B2B->value,
        'isRne' => false,
        'clientNcc' => '123456789',
        'clientCompanyName' => 'Business Customer',
        'clientPhone' => '0123456789',
        'clientEmail' => 'business@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Service 1',
                'quantity' => 2.0, // Float au lieu de int
                'amount' => 500.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    try {
        $result = $this->service->sign($data);
    } catch (\Neocode\FNE\Exceptions\ValidationException $e) {
        // Afficher les erreurs pour debug
        $errors = $e->getErrors();
        if (!empty($errors)) {
            // Si on a des erreurs, les afficher et re-throw
            fwrite(STDERR, "\nValidation errors: " . json_encode($errors, JSON_PRETTY_PRINT) . "\n");
        }
        throw $e;
    }

    expect($result)
        ->toBeInstanceOf(\Neocode\FNE\DTOs\ResponseDTO::class)
        ->and($result->invoice)->not->toBeNull()
        ->and($result->invoice->clientNcc)->not->toBeNull(); // L'API mock peut retourner null ou la valeur
});

test('InvoiceService throws ValidationException for invalid data', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        // Missing required fields
    ];

    expect(fn() => $this->service->sign($data))
        ->toThrow(\Neocode\FNE\Exceptions\ValidationException::class);
});

test('InvoiceService throws ValidationException for B2B without clientNcc', function () {
    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2B->value,
        'isRne' => false,
        'clientCompanyName' => 'Business Customer',
        'clientPhone' => '0123456789',
        'clientEmail' => 'business@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Service 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    expect(fn() => $this->service->sign($data))
        ->toThrow(\Neocode\FNE\Exceptions\ValidationException::class);
});

test('InvoiceService throws AuthenticationException for invalid API key', function () {
    $invalidConfig = new FNEConfig([
        'api_key' => 'invalid-key',
        'base_url' => 'https://fne-api-mock.test',
        'mode' => 'test',
    ]);

    $invalidHttpClient = new GuzzleHttpClient($invalidConfig);
    $invalidService = new InvoiceService(
        $invalidHttpClient,
        $invalidConfig
    );

    $data = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'clientCompanyName' => 'Test Customer',
        'clientPhone' => '0123456789',
        'clientEmail' => 'customer@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => 1,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    expect(fn() => $invalidService->sign($data))
        ->toThrow(\Neocode\FNE\Exceptions\AuthenticationException::class);
});

