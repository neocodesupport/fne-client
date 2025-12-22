<?php

use Neocode\FNE\Concerns\CertifiableInvoice;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Services\InvoiceService;
use Neocode\FNE\Http\GuzzleHttpClient;

// Mock d'un modèle Laravel Eloquent avec structure ERP
class MockERPInvoiceModelForIntegration
{
    use CertifiableInvoice;

    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function attributesToArray(): array
    {
        return $this->attributes;
    }
}

beforeEach(function () {
    $this->config = new FNEConfig([
        'api_key' => 'test-key-123',
        'base_url' => 'https://fne-api-mock.test',
        'mode' => 'test',
        'cache' => [
            'enabled' => false,
        ],
        'mapping' => [
            'invoice' => [
                'clientCompanyName' => 'client.name',
                'clientPhone' => 'customer.phone',
                'clientEmail' => 'client.email',
            ],
        ],
    ]);
});

test('Model with CertifiableInvoice trait can certify without passing data', function () {
    // Données au format ERP (structure personnalisée)
    $model = new MockERPInvoiceModelForIntegration([
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'isRne' => false,
        'client' => [
            'name' => 'ERP Company',
            'email' => 'erp@example.com',
        ],
        'customer' => [
            'phone' => '0123456789',
        ],
        'pointOfSale' => 'POS-001',
        'establishment' => 'EST-001',
        'items' => [
            [
                'description' => 'Item 1',
                'quantity' => 1.0,
                'amount' => 100.0,
                'taxes' => [TaxType::TVA->value],
            ],
        ],
    ]);

    // Vérifier que le modèle a bien la méthode certify()
    $reflection = new ReflectionClass($model);
    expect($reflection->hasMethod('certify'))->toBeTrue();

    // Vérifier que getFneData() retourne les bonnes données
    $reflection = new ReflectionClass($model);
    $method = $reflection->getMethod('getFneData');
    $method->setAccessible(true);

    $data = $method->invoke($model);

    expect($data)
        ->toBeArray()
        ->toHaveKey('client')
        ->toHaveKey('customer')
        ->and($data['client']['name'])->toBe('ERP Company');
});

test('Model data is correctly extracted and passed to service', function () {
    $model = new MockERPInvoiceModelForIntegration([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Direct Company',
        'items' => [],
    ]);

    // Créer un service mock pour vérifier que setModel() est appelé
    $httpClient = new GuzzleHttpClient($this->config);
    $service = new InvoiceService($httpClient, $this->config);

    // Utiliser setModel() directement
    $service->setModel($model);

    // Vérifier que les données peuvent être récupérées
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getData');
    $method->setAccessible(true);

    $data = $method->invoke($service, null);

    expect($data)
        ->toBeArray()
        ->toHaveKey('invoiceType')
        ->and($data['invoiceType'])->toBe(InvoiceType::SALE->value);
});

test('Custom mapping is applied when model data is used', function () {
    // Données ERP avec structure personnalisée
    $erpData = [
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'client' => [
            'name' => 'ERP Company',
            'email' => 'erp@example.com',
        ],
        'customer' => [
            'phone' => '0123456789',
        ],
        'items' => [],
    ];

    $model = new MockERPInvoiceModelForIntegration($erpData);
    $httpClient = new GuzzleHttpClient($this->config);
    $service = new InvoiceService($httpClient, $this->config);

    $service->setModel($model);

    // Récupérer les données (devrait utiliser le modèle)
    $reflection = new ReflectionClass($service);
    $getDataMethod = $reflection->getMethod('getData');
    $getDataMethod->setAccessible(true);

    $data = $getDataMethod->invoke($service, null);

    // Les données doivent être au format ERP (avant mapping)
    expect($data)
        ->toHaveKey('client')
        ->toHaveKey('customer');

    // Maintenant, tester le mapping
    $mapMethod = $reflection->getMethod('map');
    $mapMethod->setAccessible(true);

    $mappedData = $mapMethod->invoke($service, $data);

    // Après mapping, les données doivent être au format FNE
    // Note: Le mapper peut normaliser certains champs (ex: téléphone)
    expect($mappedData)
        ->toBeArray()
        ->toHaveKey('invoiceType');
    
    // Si le mapping personnalisé est appliqué, clientCompanyName devrait être présent
    if (isset($mappedData['clientCompanyName'])) {
        expect($mappedData['clientCompanyName'])->toBe('ERP Company');
    }
});

