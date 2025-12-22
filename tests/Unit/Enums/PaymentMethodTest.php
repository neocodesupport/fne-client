<?php

use Neocode\FNE\Enums\PaymentMethod;

test('PaymentMethod enum has correct cases', function () {
    expect(PaymentMethod::CASH->value)->toBe('cash');
    expect(PaymentMethod::CARD->value)->toBe('card');
    expect(PaymentMethod::MOBILE_MONEY->value)->toBe('mobile-money');
    expect(PaymentMethod::TRANSFER->value)->toBe('transfer');
    expect(PaymentMethod::CHECK->value)->toBe('check');
    expect(PaymentMethod::DEFERRED->value)->toBe('deferred');
});

test('PaymentMethod getDescription returns correct descriptions', function () {
    expect(PaymentMethod::CASH->getDescription())->toBe('Espèce');
    expect(PaymentMethod::CARD->getDescription())->toBe('Carte bancaire');
    expect(PaymentMethod::MOBILE_MONEY->getDescription())->toBe('Mobile money');
    expect(PaymentMethod::TRANSFER->getDescription())->toBe('Virement bancaire');
    expect(PaymentMethod::CHECK->getDescription())->toBe('Chèque');
    expect(PaymentMethod::DEFERRED->getDescription())->toBe('À terme');
});

