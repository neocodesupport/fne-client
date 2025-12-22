<?php

use Neocode\FNE\Exceptions\ValidationException;
use Neocode\FNE\Validation\RefundValidator;

beforeEach(function () {
    $this->validator = new RefundValidator();
});

test('RefundValidator validates required fields', function () {
    $data = [];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

test('RefundValidator validates correct refund data', function () {
    $data = [
        'items' => [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'quantity' => 1,
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))->not->toThrow(ValidationException::class);
});

test('RefundValidator validates UUID format', function () {
    $data = [
        'items' => [
            [
                'id' => 'invalid-uuid',
                'quantity' => 1,
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

test('RefundValidator validates quantity is positive', function () {
    $data = [
        'items' => [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'quantity' => 0,
            ],
        ],
    ];

    expect(fn() => $this->validator->validate($data, []))
        ->toThrow(ValidationException::class);
});

