<?php

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Mappers\InvoiceMapper;

// Mock d'un modèle avec structure ERP personnalisée
class MockERPInvoiceModelForMapper
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}

beforeEach(function () {
    $this->config = new FNEConfig([
        'api_key' => 'test-key-123',
        'base_url' => 'https://fne-api-mock.test',
        'mode' => 'test',
        'mapping' => [
            'invoice' => [
                'clientCompanyName' => 'client.name',
                'clientPhone' => 'customer.phone_number',
                'clientEmail' => 'client.email',
                'pointOfSale' => 'pos.code',
                'establishment' => 'establishment.code',
            ],
        ],
    ]);
});

test('InvoiceMapper applies custom mapping to model data', function () {
    // Données au format ERP (structure personnalisée)
    $erpData = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'client' => [
            'name' => 'ERP Company Name',
            'email' => 'erp@example.com',
        ],
        'customer' => [
            'phone_number' => '0123456789',
        ],
        'pos' => [
            'code' => 'POS-ERP-001',
        ],
        'establishment' => [
            'code' => 'EST-ERP-001',
        ],
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1.0,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    // Créer le mapper avec le mapping personnalisé
    $customMapping = $this->config->getMapping('invoice');
    $mapper = new InvoiceMapper($customMapping);

    // Appliquer le mapping
    $mappedData = $mapper->map($erpData);

    // Vérifier que les champs ERP ont été mappés vers FNE
    expect($mappedData)
        ->toHaveKey('clientCompanyName')
        ->toHaveKey('clientPhone')
        ->toHaveKey('clientEmail')
        ->toHaveKey('pointOfSale')
        ->toHaveKey('establishment')
        ->and($mappedData['clientCompanyName'])->toBe('ERP Company Name')
        ->and($mappedData['clientPhone'])->toBe('123456789') // Le mapper normalise le téléphone
        ->and($mappedData['clientEmail'])->toBe('erp@example.com')
        ->and($mappedData['pointOfSale'])->toBe('POS-ERP-001')
        ->and($mappedData['establishment'])->toBe('EST-ERP-001');
});

test('InvoiceMapper preserves non-mapped fields', function () {
    $erpData = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'client' => [
            'name' => 'ERP Company',
        ],
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1.0,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ];

    $customMapping = $this->config->getMapping('invoice');
    $mapper = new InvoiceMapper($customMapping);

    $mappedData = $mapper->map($erpData);

    // Les champs non mappés doivent être préservés
    expect($mappedData)
        ->toHaveKey('invoiceType')
        ->toHaveKey('paymentMethod')
        ->toHaveKey('template')
        ->toHaveKey('items')
        ->and($mappedData['invoiceType'])->toBe(InvoiceType::SALE->value)
        ->and($mappedData['items'])->toBeArray();
});

test('InvoiceMapper handles missing mapped fields gracefully', function () {
    $erpData = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        // Pas de 'client' ni 'customer' dans les données
        'items' => [],
    ];

    $customMapping = $this->config->getMapping('invoice');
    $mapper = new InvoiceMapper($customMapping);

    // Le mapping ne doit pas lever d'exception si les champs sont manquants
    $mappedData = $mapper->map($erpData);

    expect($mappedData)
        ->toBeArray()
        ->toHaveKey('invoiceType');
    
    // Note: Le mapper peut créer des clés vides même si les données source sont manquantes
    // C'est un comportement acceptable car le mapper applique le mapping même si la valeur est null
    // La validation se chargera de vérifier que les champs requis sont présents
});

test('InvoiceMapper hasMapping() returns true when custom mapping is configured', function () {
    $customMapping = $this->config->getMapping('invoice');
    $mapper = new InvoiceMapper($customMapping);

    expect($mapper->hasMapping())->toBeTrue();
});

test('InvoiceMapper hasMapping() returns false when no custom mapping is configured', function () {
    $mapper = new InvoiceMapper([]);

    expect($mapper->hasMapping())->toBeFalse();
});

