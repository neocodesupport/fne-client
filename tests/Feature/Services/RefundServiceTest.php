<?php

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Http\GuzzleHttpClient;
use Neocode\FNE\Services\InvoiceService;
use Neocode\FNE\Services\RefundService;

beforeEach(function () {
    $this->config = new FNEConfig([
        'api_key' => 'test-key-123',
        'base_url' => 'https://fne-api-mock.test/api',
        'mode' => 'test',
        'cache' => [
            'enabled' => false,
        ],
    ]);

    $this->httpClient = new GuzzleHttpClient($this->config);

    // Créer d'abord une facture pour avoir un invoiceId valide
    $invoiceService = new InvoiceService($this->httpClient, $this->config);
    $invoiceData = [
        'invoiceType' => 'sale',
        'paymentMethod' => 'cash',
        'template' => 'B2C',
        'isRne' => false,
        'clientCompanyName' => 'Test Customer',
        'clientPhone' => '0123456789',
        'clientEmail' => 'customer@example.com',
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Product 1',
                'quantity' => 2,
                'amount' => 100.0,
                'taxes' => ['TVA'],
            ],
        ],
    ];

    $invoiceResult = $invoiceService->sign($invoiceData);
    $this->invoiceId = $invoiceResult->invoice->id;

    $this->service = new RefundService(
        $this->httpClient,
        $this->config
    );
});

test('RefundService can issue a refund', function () {
    $items = [
        [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'quantity' => 1.0,
        ],
    ];

    $result = $this->service->issue($this->invoiceId, $items);

    expect($result)
        ->toBeInstanceOf(\Neocode\FNE\DTOs\ResponseDTO::class)
        ->and($result->ncc)->not->toBeEmpty()
        ->and($result->reference)->not->toBeEmpty()
        ->and($result->reference)->toStartWith('A') // Les avoirs commencent par "A"
        ->and($result->token)->not->toBeEmpty()
        ->and($result->invoice)->toBeNull(); // Les avoirs n'ont pas d'objet invoice
});

test('RefundService throws ValidationException for invalid UUID', function () {
    // Skip si l'API mock n'est pas disponible
    $context = stream_context_create([
        'http' => ['timeout' => 2, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    
    if (!@file_get_contents('https://fne-api-mock.test/api', false, $context)) {
        $this->markTestSkipped('API mock not available');
        return;
    }

    $items = [
        [
            'id' => 'invalid-uuid',
            'quantity' => 1.0,
        ],
    ];

    // Le mapper devrait lever une exception pour UUID invalide
    expect(fn() => $this->service->issue($this->invoiceId, $items))
        ->toThrow(\Neocode\FNE\Exceptions\MappingException::class);
});

test('RefundService throws ValidationException for invalid quantity', function () {
    // Skip si l'API mock n'est pas disponible
    $context = stream_context_create([
        'http' => ['timeout' => 2, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    
    if (!@file_get_contents('https://fne-api-mock.test/api', false, $context)) {
        $this->markTestSkipped('API mock not available');
        return;
    }

    $items = [
        [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'quantity' => 0, // Invalid
        ],
    ];

    // Le validator devrait lever une exception pour quantité invalide
    expect(fn() => $this->service->issue($this->invoiceId, $items))
        ->toThrow(\Neocode\FNE\Exceptions\ValidationException::class);
});

