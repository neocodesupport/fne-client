<?php

use Neocode\FNE\Enums\TaxType;

test('TaxType enum has correct cases', function () {
    expect(TaxType::TVA->value)->toBe('TVA');
    expect(TaxType::TVAB->value)->toBe('TVAB');
    expect(TaxType::TVAC->value)->toBe('TVAC');
    expect(TaxType::TVAD->value)->toBe('TVAD');
});

test('TaxType getDescription returns correct descriptions', function () {
    expect(TaxType::TVA->getDescription())->toBe('TVA normal - TVA sur HT 18,00%');
    expect(TaxType::TVAB->getDescription())->toBe('TVA réduit - TVA sur HT 9,00%');
    expect(TaxType::TVAC->getDescription())->toBe('TVA exo.conv - TVA sur HT 00,00%');
    expect(TaxType::TVAD->getDescription())->toBe('TVA exo leg - TVA sur HT 00,00% pour TEE et RME');
});

