<?php

use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\MapperInterface;
use Neocode\FNE\Contracts\ValidatorInterface;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\TaxType;
use Neocode\FNE\Http\GuzzleHttpClient;
use Neocode\FNE\Services\BaseService;
use Neocode\FNE\Services\InvoiceService;

// Mock d'un modèle Laravel Eloquent
class MockModel
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

    public function attributesToArray(): array
    {
        return $this->attributes;
    }
}

// Service concret pour tester BaseService
class ConcreteService extends BaseService
{
    protected function makeRequest(array $data): mixed
    {
        return ['success' => true, 'data' => $data];
    }

    protected function processResponse(mixed $response): mixed
    {
        return $response;
    }

    protected function getValidationRules(): array
    {
        return [];
    }

    protected function getCacheKey(array $data): string
    {
        return 'test-key';
    }

    protected function getCacheTtl(): ?int
    {
        return 3600;
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

    $this->httpClient = new GuzzleHttpClient($this->config);
});

test('BaseService can extract data from model using toArray()', function () {
    $model = new MockModel([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Test Company',
    ]);

    $service = new ConcreteService($this->httpClient, $this->config);
    $service->setModel($model);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('extractModelData');
    $method->setAccessible(true);

    $data = $method->invoke($service, $model);

    expect($data)
        ->toBeArray()
        ->toHaveKey('invoiceType')
        ->toHaveKey('clientCompanyName')
        ->and($data['invoiceType'])->toBe(InvoiceType::SALE->value)
        ->and($data['clientCompanyName'])->toBe('Test Company');
});

test('BaseService getData() retrieves data from model when setModel() is called', function () {
    $model = new MockModel([
        'invoiceType' => InvoiceType::SALE->value,
        'paymentMethod' => PaymentMethod::CASH->value,
        'template' => InvoiceTemplate::B2C->value,
        'clientCompanyName' => 'Test Company',
    ]);

    $service = new ConcreteService($this->httpClient, $this->config);
    $service->setModel($model);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getData');
    $method->setAccessible(true);

    $data = $method->invoke($service, null);

    expect($data)
        ->toBeArray()
        ->toHaveKey('invoiceType')
        ->toHaveKey('clientCompanyName')
        ->and($data['invoiceType'])->toBe(InvoiceType::SALE->value)
        ->and($data['clientCompanyName'])->toBe('Test Company');
});

test('BaseService getData() prioritizes explicit data over model data', function () {
    $model = new MockModel([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Model Company',
    ]);

    $explicitData = [
        'invoiceType' => InvoiceType::PURCHASE->value,
        'clientCompanyName' => 'Explicit Company',
    ];

    $service = new ConcreteService($this->httpClient, $this->config);
    $service->setModel($model);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getData');
    $method->setAccessible(true);

    $data = $method->invoke($service, $explicitData);

    expect($data)
        ->toBeArray()
        ->toHaveKey('invoiceType')
        ->toHaveKey('clientCompanyName')
        ->and($data['invoiceType'])->toBe(InvoiceType::PURCHASE->value)
        ->and($data['clientCompanyName'])->toBe('Explicit Company');
});

test('BaseService getData() uses context data when setData() is called', function () {
    $model = new MockModel([
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Model Company',
    ]);

    $contextData = [
        'invoiceType' => InvoiceType::SALE->value,
        'clientCompanyName' => 'Context Company',
    ];

    $service = new ConcreteService($this->httpClient, $this->config);
    $service->setModel($model);
    $service->setData($contextData);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getData');
    $method->setAccessible(true);

    $data = $method->invoke($service, null);

    expect($data)
        ->toBeArray()
        ->toHaveKey('clientCompanyName')
        ->and($data['clientCompanyName'])->toBe('Context Company');
});

test('BaseService getData() throws exception when no data is available', function () {
    $service = new ConcreteService($this->httpClient, $this->config);

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getData');
    $method->setAccessible(true);

    expect(fn() => $method->invoke($service, null))
        ->toThrow(\Neocode\FNE\Exceptions\BadRequestException::class);
});

test('BaseService extractModelData() supports different model methods', function () {
    // Test avec toArray()
    $model1 = new class {
        public function toArray(): array
        {
            return ['method' => 'toArray', 'data' => 'test'];
        }
    };

    $service = new ConcreteService($this->httpClient, $this->config);
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('extractModelData');
    $method->setAccessible(true);

    $data1 = $method->invoke($service, $model1);
    expect($data1)->toHaveKey('method')->and($data1['method'])->toBe('toArray');

    // Test avec attributesToArray()
    $model2 = new class {
        public function attributesToArray(): array
        {
            return ['method' => 'attributesToArray', 'data' => 'test'];
        }
    };

    $data2 = $method->invoke($service, $model2);
    expect($data2)->toHaveKey('method')->and($data2['method'])->toBe('attributesToArray');

    // Test avec getAttributes()
    $model3 = new class {
        public function getAttributes(): array
        {
            return ['method' => 'getAttributes', 'data' => 'test'];
        }
    };

    $data3 = $method->invoke($service, $model3);
    expect($data3)->toHaveKey('method')->and($data3['method'])->toBe('getAttributes');
});

