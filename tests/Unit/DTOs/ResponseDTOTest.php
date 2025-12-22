<?php

use Neocode\FNE\DTOs\ResponseDTO;

test('ResponseDTO can be created from array', function () {
    $data = [
        'ncc' => '123456789',
        'reference' => 'REF-123',
        'token' => 'token-123',
        'warning' => false,
        'balance_sticker' => 100,
        'invoice' => [
            'id' => 'invoice-id',
            'amount' => 1000,
        ],
    ];

    $dto = ResponseDTO::fromArray($data);

    expect($dto->ncc)->toBe('123456789');
    expect($dto->reference)->toBe('REF-123');
    expect($dto->token)->toBe('token-123');
    expect($dto->warning)->toBeFalse();
    expect($dto->balanceSticker)->toBe(100);
    expect($dto->invoice)->toBeInstanceOf(\Neocode\FNE\DTOs\InvoiceResponseDTO::class);
});

test('ResponseDTO handles missing optional fields', function () {
    $data = [
        'ncc' => '123456789',
        'reference' => 'REF-123',
    ];

    $dto = ResponseDTO::fromArray($data);

    expect($dto->ncc)->toBe('123456789');
    expect($dto->reference)->toBe('REF-123');
    expect($dto->token)->toBe('');
    expect($dto->warning)->toBeFalse();
    expect($dto->balanceSticker)->toBe(0);
    expect($dto->invoice)->toBeNull();
});

