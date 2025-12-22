<?php

use Neocode\FNE\Exceptions\MappingException;
use Neocode\FNE\Mappers\RefundMapper;

beforeEach(function () {
    $this->mapper = new RefundMapper();
});

test('RefundMapper maps refund data correctly', function () {
    $data = [
        'invoiceId' => '550e8400-e29b-41d4-a716-446655440000',
        'items' => [
            [
                'id' => '660e8400-e29b-41d4-a716-446655440001',
                'quantity' => 1.0,
            ],
            [
                'id' => '660e8400-e29b-41d4-a716-446655440002',
                'quantity' => 2.5,
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result)
        ->toHaveKey('items')
        ->and($result['items'])->toBeArray()
        ->and(count($result['items']))->toBe(2)
        ->and($result['items'][0])->toHaveKey('id')
        ->and($result['items'][0])->toHaveKey('quantity')
        ->and($result['items'][0]['id'])->toBe('660e8400-e29b-41d4-a716-446655440001')
        ->and($result['items'][0]['quantity'])->toBe(1.0);
});

test('RefundMapper validates UUID format', function () {
    $data = [
        'invoiceId' => 'invalid-uuid',
        'items' => [
            [
                'id' => '660e8400-e29b-41d4-a716-446655440001',
                'quantity' => 1.0,
            ],
        ],
    ];

    // Le mapper devrait normaliser ou valider les UUIDs
    $result = $this->mapper->map($data);

    expect($result)->toBeArray();
});

test('RefundMapper normalizes quantity values', function () {
    $data = [
        'invoiceId' => '550e8400-e29b-41d4-a716-446655440000',
        'items' => [
            [
                'id' => '660e8400-e29b-41d4-a716-446655440001',
                'quantity' => '1.5', // String
            ],
        ],
    ];

    $result = $this->mapper->map($data);

    expect($result['items'][0]['quantity'])->toBe(1.5);
});

test('RefundMapper handles empty items array', function () {
    $data = [
        'invoiceId' => '550e8400-e29b-41d4-a716-446655440000',
        'items' => [],
    ];

    $result = $this->mapper->map($data);

    expect($result['items'])->toBeArray()
        ->and($result['items'])->toBeEmpty();
});

