<?php

use Neocode\FNE\Enums\InvoiceType;

test('InvoiceType enum has correct cases', function () {
    expect(InvoiceType::SALE->value)->toBe('sale');
    expect(InvoiceType::PURCHASE->value)->toBe('purchase');
});

test('InvoiceType getDescription returns correct descriptions', function () {
    expect(InvoiceType::SALE->getDescription())->toBe('Facture de vente');
    expect(InvoiceType::PURCHASE->getDescription())->toBe('Bordereau d\'achat');
});

