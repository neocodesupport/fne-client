<?php

use Neocode\FNE\Enums\InvoiceTemplate;

test('InvoiceTemplate enum has correct cases', function () {
    expect(InvoiceTemplate::B2C->value)->toBe('B2C');
    expect(InvoiceTemplate::B2B->value)->toBe('B2B');
    expect(InvoiceTemplate::B2F->value)->toBe('B2F');
    expect(InvoiceTemplate::B2G->value)->toBe('B2G');
});

test('InvoiceTemplate requiresNcc returns correct values', function () {
    expect(InvoiceTemplate::B2B->requiresNcc())->toBeTrue();
    expect(InvoiceTemplate::B2C->requiresNcc())->toBeFalse();
    expect(InvoiceTemplate::B2F->requiresNcc())->toBeFalse();
    expect(InvoiceTemplate::B2G->requiresNcc())->toBeFalse();
});

test('InvoiceTemplate getDescription returns correct descriptions', function () {
    expect(InvoiceTemplate::B2C->getDescription())->toBe('Business to Consumer - Le client est un particulier');
    expect(InvoiceTemplate::B2B->getDescription())->toBe('Business to Business - Le client est une entreprise ou professionnel possédant un NCC');
    expect(InvoiceTemplate::B2F->getDescription())->toBe('Business to Foreign - Le client est à l\'international');
    expect(InvoiceTemplate::B2G->getDescription())->toBe('Business to Government - Le client est une institution gouvernementale');
});

