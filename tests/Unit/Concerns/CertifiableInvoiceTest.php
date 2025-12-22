<?php

use Neocode\FNE\Concerns\CertifiableInvoice;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\DTOs\ResponseDTO;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Services\InvoiceService;
use Neocode\FNE\Http\GuzzleHttpClient;

// Mock d'un modèle Laravel Eloquent
class MockInvoiceModel
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
    ]);
});

test('CertifiableInvoice trait has certify method', function () {
    $model = new MockInvoiceModel([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Test Company',
    ]);

    // Vérifier que le modèle a bien la méthode certify()
    expect($model)->toBeInstanceOf(MockInvoiceModel::class);
    
    $reflection = new ReflectionClass($model);
    expect($reflection->hasMethod('certify'))->toBeTrue();
    
    $method = $reflection->getMethod('certify');
    expect($method->isPublic())->toBeTrue();
    
    // Vérifier que la méthode accepte un paramètre optionnel
    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('data')
        ->and($parameters[0]->allowsNull())->toBeTrue();
});

test('CertifiableInvoice trait extracts model data correctly', function () {
    $model = new MockInvoiceModel([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Test Company',
    ]);

    $reflection = new ReflectionClass($model);
    $method = $reflection->getMethod('getFneData');
    $method->setAccessible(true);

    $data = $method->invoke($model);

    expect($data)
        ->toBeArray()
        ->toHaveKey('invoiceType')
        ->toHaveKey('clientCompanyName')
        ->and($data['invoiceType'])->toBe(InvoiceType::SALE->value)
        ->and($data['clientCompanyName'])->toBe('Test Company');
});

test('CertifiableInvoice trait certify method signature is correct', function () {
    $model = new MockInvoiceModel([
        'invoiceType' => InvoiceType::SALE->value,
    ]);

    // Vérifier la signature de la méthode certify()
    $reflection = new ReflectionClass($model);
    expect($reflection->hasMethod('certify'))->toBeTrue();
    
    $method = $reflection->getMethod('certify');
    $parameters = $method->getParameters();
    
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getName())->toBe('data')
        ->and($parameters[0]->allowsNull())->toBeTrue()
        ->and($method->getReturnType()->getName())->toBe(ResponseDTO::class);
});

