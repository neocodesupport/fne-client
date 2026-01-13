# FNE Client

[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-67%20passed-success.svg)](tests)

SDK PHP framework-agnostic pour l'intégration de l'API FNE (Facture Normalisée Électronique). Compatible avec Laravel 11+, Symfony 7.4+ et PHP natif.

## 🚀 Caractéristiques

- ✅ **Framework-agnostic** : Compatible Laravel 11+, Symfony 7.4+ et PHP natif
- ✅ **Architecture SOLID** : Code propre et maintenable
- ✅ **Type-safe** : Utilisation d'enums PHP 8.2+ pour la sécurité de type
- ✅ **Validation robuste** : Validation des données avant envoi à l'API
- ✅ **Gestion d'erreurs** : Exceptions détaillées et typées
- ✅ **Cache intégré** : Support du cache PSR-16
- ✅ **Logging** : Support du logging PSR-3
- ✅ **Installation interactive** : Assistant d'installation avec prompts
- ✅ **Détection automatique** : Détection du framework lors de l'installation
- ✅ **Gestion modulaire** : Modules activables via Laravel Pennant (Laravel uniquement)
- ✅ **Mapping personnalisé** : Transformation flexible des données ERP vers le format FNE
- ✅ **Intégration modèles** : Traits pour intégrer directement la certification dans vos modèles
- ✅ **Support multi-framework** : Compatible Laravel Eloquent, Symfony Doctrine et PHP natif

## 📦 Installation

### Laravel 11+

```bash
composer require neocode/fne-client
php artisan fne:install
```

### Symfony 7.4+

```bash
composer require neocode/fne-client
php bin/console fne:install
```

### PHP Natif

```bash
composer require neocode/fne-client
php vendor/bin/fne-install
```

## ⚡ Quick Start

### Laravel

```php
use Neocode\FNE\Facades\FNE;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\TaxType;

$result = FNE::invoice()->sign([
    'invoiceType' => InvoiceType::SALE->value,
    'paymentMethod' => PaymentMethod::MOBILE_MONEY->value,
    'template' => InvoiceTemplate::B2B->value,
    'isRne' => false,
    'clientNcc' => '123456789',
    'clientCompanyName' => 'Entreprise Client',
    'clientPhone' => '0123456789',
    'clientEmail' => 'client@example.com',
    'pointOfSale' => 'POS-001',
    'establishment' => 'EST-001',
    'items' => [
        [
            'description' => 'Service 1',
            'quantity' => 2,
            'amount' => 500.0,
            'taxes' => [TaxType::TVA->value],
        ],
    ],
]);

// Accéder aux résultats
echo $result->ncc;              // "9606123E"
echo $result->reference;         // "9606123E25000000019"
echo $result->token;             // URL de vérification QR code
echo $result->invoice->id;       // UUID de la facture (important pour avoirs)
```

### Symfony

```php
use Neocode\FNE\FNEClient;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\TaxType;

class InvoiceController
{
    public function __construct(
        private FNEClient $fneClient
    ) {}

    public function certify(): JsonResponse
    {
        $result = $this->fneClient->invoice()->sign([
            'invoiceType' => InvoiceType::SALE->value,
            'paymentMethod' => PaymentMethod::CASH->value,
            'template' => InvoiceTemplate::B2C->value,
            'isRne' => false,
            'clientCompanyName' => 'Client Particulier',
            'clientPhone' => '0123456789',
            'clientEmail' => 'client@example.com',
            'pointOfSale' => 'POS-001',
            'establishment' => 'EST-001',
            'items' => [
                [
                    'description' => 'Produit 1',
                    'quantity' => 1,
                    'amount' => 100.0,
                    'taxes' => [TaxType::TVA->value],
                ],
            ],
        ]);

        return new JsonResponse($result->toArray());
    }
}
```

### PHP Natif

```php
require_once 'vendor/autoload.php';

use Neocode\FNE\FNEClient;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Http\HttpClientFactory;
use Neocode\FNE\Enums\InvoiceType;
use Neocode\FNE\Enums\PaymentMethod;
use Neocode\FNE\Enums\InvoiceTemplate;
use Neocode\FNE\Enums\TaxType;

$config = new FNEConfig([
    'api_key' => 'your-api-key',
    'base_url' => 'https://fne-api-mock.test',
    'mode' => 'test',
]);

$httpClient = HttpClientFactory::create($config);
$fne = new FNEClient($httpClient, $config);

$result = $fne->invoice()->sign([
    'invoiceType' => InvoiceType::SALE->value,
    'paymentMethod' => PaymentMethod::MOBILE_MONEY->value,
    'template' => InvoiceTemplate::B2B->value,
    'isRne' => false,
    'clientNcc' => '123456789',
    'clientCompanyName' => 'Entreprise Client',
    'clientPhone' => '0123456789',
    'clientEmail' => 'client@example.com',
    'pointOfSale' => 'POS-001',
    'establishment' => 'EST-001',
    'items' => [
        [
            'description' => 'Service 1',
            'quantity' => 2,
            'amount' => 500.0,
            'taxes' => [TaxType::TVA->value],
        ],
    ],
]);
```

## 📖 Documentation

### Configuration

#### Variables d'environnement

```env
FNE_API_KEY=your-api-key
FNE_BASE_URL=https://fne-api-mock.test
FNE_MODE=test
FNE_TIMEOUT=30
FNE_CACHE_ENABLED=true
FNE_CACHE_TTL=3600
FNE_LOCALE=fr
```

#### Fichier de configuration (Laravel)

Le fichier `config/fne.php` est publié lors de l'installation :

```php
return [
    'api_key' => env('FNE_API_KEY'),
    'base_url' => env('FNE_BASE_URL', 'https://fne-api-mock.test'),
    'mode' => env('FNE_MODE', 'test'),
    'timeout' => env('FNE_TIMEOUT', 30),

    'cache_enabled' => env('FNE_CACHE_ENABLED', true),
    'cache_ttl' => env('FNE_CACHE_TTL', 3600),

    'locale' => env('FNE_LOCALE', 'fr'),

    'features' => [
        'enabled' => env('FNE_FEATURES_ENABLED', true),
        'advanced_mapping' => env('FNE_FEATURE_ADVANCED_MAPPING', true),
        'batch_processing' => env('FNE_FEATURE_BATCH_PROCESSING', false),
        'webhooks' => env('FNE_FEATURE_WEBHOOKS', false),
        'queue_jobs' => env('FNE_FEATURE_QUEUE_JOBS', false),
        'audit_logging' => env('FNE_FEATURE_AUDIT_LOGGING', true),
        'auto_retry' => env('FNE_FEATURE_AUTO_RETRY', true),
        'certification_table' => env('FNE_FEATURE_CERTIFICATION_TABLE', false),
    ],
];
```

### Mapping Personnalisé

Le package supporte le mapping personnalisé pour transformer vos données ERP vers le format FNE. Configurez le mapping dans `config/fne.php` :

```php
'mapping' => [
    'invoice' => [
        // Mapping des factures de vente
        'clientCompanyName' => 'client.name',
        'clientPhone' => 'customer.phone_number',
        'clientEmail' => 'client.email',
        'pointOfSale' => 'pos.code',
        'establishment' => 'establishment.code',
    ],
    'purchase' => [
        // Mapping des bordereaux d'achat
        'clientCompanyName' => 'supplier.name',
        'clientPhone' => 'supplier.phone',
    ],
    'refund' => [
        // Mapping des avoirs
        'items' => 'refund_items',
    ],
],
```

**Utilisation avec notation pointée :**
- `'clientCompanyName' => 'client.name'` transforme `$data['client']['name']` en `$data['clientCompanyName']`
- `'clientPhone' => 'customer.phone_number'` transforme `$data['customer']['phone_number']` en `$data['clientPhone']`

Le mapping personnalisé est appliqué **avant** le mapping standard du package, permettant une transformation flexible de vos structures de données.

**Exemple d'utilisation :**

```php
// Vos données ERP avec structure personnalisée
$erpData = [
    'invoiceType' => InvoiceType::SALE->value,
    'client' => [
        'name' => 'Entreprise Client',
        'email' => 'client@example.com',
    ],
    'customer' => [
        'phone_number' => '0123456789',
    ],
    'pos' => [
        'code' => 'POS-001',
    ],
    'items' => [...],
];

// Le mapper applique automatiquement le mapping personnalisé
$result = FNE::invoice()->sign($erpData);
```

### Services Disponibles

#### InvoiceService - Factures de Vente

```php
use Neocode\FNE\Facades\FNE;

$result = FNE::invoice()->sign([
    'invoiceType' => InvoiceType::SALE->value,
    'paymentMethod' => PaymentMethod::MOBILE_MONEY->value,
    'template' => InvoiceTemplate::B2B->value,
    'isRne' => false,
    'clientNcc' => '123456789', // Obligatoire pour B2B
    'clientCompanyName' => 'Entreprise Client',
    'clientPhone' => '0123456789',
    'clientEmail' => 'client@example.com',
    'pointOfSale' => 'POS-001',
    'establishment' => 'EST-001',
    'items' => [
        [
            'description' => 'Service 1',
            'quantity' => 2,
            'amount' => 500.0,
            'taxes' => [TaxType::TVA->value],
        ],
    ],
]);
```

#### PurchaseService - Bordereaux d'Achat

```php
use Neocode\FNE\Facades\FNE;

$result = FNE::purchase()->submit([
    'invoiceType' => InvoiceType::PURCHASE->value,
    'paymentMethod' => PaymentMethod::CASH->value,
    'template' => InvoiceTemplate::B2C->value,
    'isRne' => false,
    'clientCompanyName' => 'Fournisseur',
    'clientPhone' => '0987654321',
    'clientEmail' => 'fournisseur@example.com',
    'pointOfSale' => 'POS-002',
    'establishment' => 'EST-002',
    'items' => [
        [
            'description' => 'Matière première',
            'quantity' => 10,
            'amount' => 50.0,
            // Note : Pas de taxes pour les bordereaux d'achat
        ],
    ],
]);
```

#### RefundService - Avoirs

```php
use Neocode\FNE\Facades\FNE;

// Créer d'abord une facture
$invoice = FNE::invoice()->sign([...]);
$invoiceId = $invoice->invoice->id; // UUID de la facture

// Créer un avoir pour certains items
$refund = FNE::refund()->issue($invoiceId, [
    [
        'id' => $invoice->invoice->items[0]->id, // UUID de l'item
        'quantity' => 1.0,
    ],
]);
```

### Traits pour Modèles

Le package fournit des traits pour intégrer facilement la certification FNE dans vos modèles. Les traits détectent automatiquement le framework (Laravel, Symfony, PHP natif) et utilisent le service container approprié.

#### CertifiableInvoice - Factures de Vente

```php
use Neocode\FNE\Concerns\CertifiableInvoice;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use CertifiableInvoice;

    protected $fillable = [
        'invoice_type',
        'payment_method',
        'template',
        'client_name',
        'client_phone',
        'client_email',
        'point_of_sale',
        'establishment',
        // ... autres champs
    ];
}

// Utilisation - Le trait extrait automatiquement les données du modèle
$invoice = Invoice::find(1);
$response = $invoice->certify(); // Certifie avec les données du modèle (toArray())

// Ou avec des données personnalisées (priorité sur les données du modèle)
$response = $invoice->certify([
    'invoiceType' => InvoiceType::SALE->value,
    'items' => [...],
]);

// Le mapping personnalisé est automatiquement appliqué si configuré
// Les données du modèle sont transformées selon le mapping défini dans config/fne.php
```

**Méthodes supportées pour l'extraction des données :**
- `toArray()` (Laravel Eloquent)
- `attributesToArray()` (Laravel Eloquent)
- `getAttributes()` (Laravel Eloquent)
- `__toArray()` (Symfony/Doctrine)
- Cast en array (PHP natif)

#### CertifiablePurchase - Bordereaux d'Achat

```php
use Neocode\FNE\Concerns\CertifiablePurchase;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use CertifiablePurchase;

    protected $fillable = [
        'invoice_type',
        'payment_method',
        'template',
        'client_name',
        'client_phone',
        'client_email',
        'point_of_sale',
        'establishment',
        // ... autres champs
    ];
}

// Utilisation - Le trait extrait automatiquement les données du modèle
$purchase = Purchase::find(1);
$response = $purchase->submit(); // Soumet avec les données du modèle

// Ou avec des données personnalisées
$response = $purchase->submit([
    'invoiceType' => InvoiceType::PURCHASE->value,
    'items' => [...],
]);
```

#### CertifiableRefund - Avoirs

```php
use Neocode\FNE\Concerns\CertifiableRefund;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use CertifiableRefund;

    // Le modèle doit avoir un attribut fne_id, fne_invoice_id ou une méthode getFneInvoiceId()
    protected $fillable = ['fne_id', 'fne_invoice_id', ...];
    
    // Ou définir une méthode personnalisée
    public function getFneInvoiceId(): ?string
    {
        return $this->fne_id ?? $this->fne_invoice_id;
    }
}

// Utilisation - Le trait trouve automatiquement l'ID FNE de la facture
$invoice = Invoice::find(1); // Facture déjà certifiée avec fne_id
$response = $invoice->issueRefund([
    [
        'id' => 'uuid-de-l-item', // UUID de l'item à rembourser (depuis la facture certifiée)
        'quantity' => 1.0,
    ],
]);
```

**Détection automatique de l'ID FNE :**
Le trait cherche l'ID FNE dans l'ordre suivant :
1. Attribut `fne_id`
2. Attribut `fne_invoice_id`
3. Méthode `getFneInvoiceId()`

#### Certifiable - Trait Combiné

Pour les modèles qui peuvent être factures ET bordereaux :

```php
use Neocode\FNE\Concerns\Certifiable;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use Certifiable;

    protected $fillable = [
        'document_type', // 'invoice' ou 'purchase'
        'invoice_type',
        'payment_method',
        'template',
        'client_name',
        'fne_id', // Pour les avoirs
        // ... autres champs
    ];
}

// Utilisation
$document = Document::find(1);
$response = $document->certify(); // Certifier comme facture
$response = $document->submitPurchase(); // Soumettre comme bordereau
$response = $document->issueRefund([...]); // Émettre un avoir
```

**Intégration avec le Mapping Personnalisé :**

Lorsque vous utilisez les traits avec des modèles, le mapping personnalisé configuré dans `config/fne.php` est automatiquement appliqué. Cela permet de transformer vos structures de données ERP directement depuis vos modèles :

```php
// config/fne.php
'mapping' => [
    'invoice' => [
        'clientCompanyName' => 'client_name', // Colonne de votre table
        'clientPhone' => 'phone',
        'pointOfSale' => 'pos_code',
    ],
],

// Votre modèle
class Invoice extends Model
{
    use CertifiableInvoice;
    
    // Colonnes de votre table : client_name, phone, pos_code
}

// Le mapping est appliqué automatiquement lors de la certification
$invoice->certify(); // Les données sont transformées selon le mapping
```

**Note** : Les traits détectent automatiquement le framework (Laravel, Symfony, PHP natif) et utilisent le service container approprié. Ils supportent également l'extraction de données depuis différents types de modèles (Eloquent, Doctrine, objets PHP natifs).

### Enums Disponibles

#### InvoiceTemplate

```php
use Neocode\FNE\Enums\InvoiceTemplate;

InvoiceTemplate::B2C->value;  // 'B2C' - Business to Consumer
InvoiceTemplate::B2B->value;  // 'B2B' - Business to Business
InvoiceTemplate::B2F->value;  // 'B2F' - Business to Foreign
InvoiceTemplate::B2G->value;  // 'B2G' - Business to Government
```

#### PaymentMethod

```php
use Neocode\FNE\Enums\PaymentMethod;

PaymentMethod::CASH->value;           // 'cash'
PaymentMethod::CARD->value;            // 'card'
PaymentMethod::CHECK->value;           // 'check'
PaymentMethod::MOBILE_MONEY->value;   // 'mobile-money'
PaymentMethod::TRANSFER->value;        // 'transfer'
PaymentMethod::DEFERRED->value;        // 'deferred'
```

#### TaxType

```php
use Neocode\FNE\Enums\TaxType;

TaxType::TVA->value;   // 'TVA' - TVA normal 18%
TaxType::TVAB->value;  // 'TVAB' - TVA réduit 9%
TaxType::TVAC->value;  // 'TVAC' - TVA exo.conv 0%
TaxType::TVAD->value;  // 'TVAD' - TVA exo.leg 0%
```

### Gestion des Erreurs

Le package utilise une hiérarchie d'exceptions typées :

```php
use Neocode\FNE\Exceptions\ValidationException;
use Neocode\FNE\Exceptions\AuthenticationException;
use Neocode\FNE\Exceptions\BadRequestException;
use Neocode\FNE\Exceptions\NotFoundException;
use Neocode\FNE\Exceptions\ServerException;

try {
    $result = FNE::invoice()->sign($data);
} catch (ValidationException $e) {
    // Erreurs de validation (422)
    $errors = $e->getErrors();
    foreach ($errors as $field => $messages) {
        echo "$field: " . implode(', ', $messages);
    }
} catch (AuthenticationException $e) {
    // Erreur d'authentification (401)
    echo "Clé API invalide";
} catch (BadRequestException $e) {
    // Requête mal formée (400)
    echo $e->getMessage();
} catch (NotFoundException $e) {
    // Ressource non trouvée (404)
    echo "Facture non trouvée";
} catch (ServerException $e) {
    // Erreur serveur (500+)
    echo "Erreur serveur: " . $e->getMessage();
}
```

### Trait InteractsWithFNE (Laravel)

Pour un accès simplifié au client FNE dans vos classes Laravel :

```php
use Neocode\FNE\Concerns\InteractsWithFNE;

class InvoiceController extends Controller
{
    use InteractsWithFNE;

    public function store(Request $request)
    {
        $result = $this->fne()->invoice()->sign($request->all());
        return response()->json($result);
    }
}
```

### Utilisation Avancée avec Modèles et Mapping

#### Exemple Complet : Intégration ERP avec Mapping Personnalisé

```php
// config/fne.php
'mapping' => [
    'invoice' => [
        'clientCompanyName' => 'customer.company_name',
        'clientPhone' => 'customer.phone',
        'clientEmail' => 'customer.email',
        'pointOfSale' => 'location.pos_code',
        'establishment' => 'location.est_code',
        'items' => 'line_items',
    ],
],

// Modèle Invoice
class Invoice extends Model
{
    use CertifiableInvoice;
    
    protected $fillable = [
        'customer_id',
        'location_id',
        'invoice_type',
        'payment_method',
        'template',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    public function lineItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    // Méthode personnalisée pour extraire les données avec relations
    public function getFneData(): array
    {
        return [
            'invoiceType' => $this->invoice_type,
            'paymentMethod' => $this->payment_method,
            'template' => $this->template,
            'customer' => [
                'company_name' => $this->customer->company_name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
            ],
            'location' => [
                'pos_code' => $this->location->pos_code,
                'est_code' => $this->location->est_code,
            ],
            'line_items' => $this->lineItems->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                    'taxes' => [$item->tax_type],
                ];
            })->toArray(),
        ];
    }
}

// Utilisation
$invoice = Invoice::with(['customer', 'location', 'lineItems'])->find(1);
$response = $invoice->certify(); // Mapping automatique appliqué
```

#### Utilisation Programmatique du Mapping

```php
use Neocode\FNE\Services\InvoiceService;
use Neocode\FNE\Mappers\InvoiceMapper;

// Créer un mapper avec mapping personnalisé
$customMapping = [
    'clientCompanyName' => 'client.name',
    'clientPhone' => 'customer.phone_number',
];

$mapper = new InvoiceMapper($customMapping);

// Utiliser avec le service
$service = new InvoiceService($httpClient, $config, $mapper);
$result = $service->sign($erpData);
```

## 🧪 Tests

Le package inclut une suite de tests complète avec **67 tests** (222 assertions) couvrant :

- ✅ Tests unitaires (Cache, DTOs, Enums, Mappers, Validators)
- ✅ Tests d'intégration (Services avec API mock)
- ✅ Tests de traits (CertifiableInvoice, CertifiablePurchase, CertifiableRefund)
- ✅ Tests de mapping personnalisé
- ✅ Tests d'intégration modèles

```bash
# Exécuter tous les tests
composer test

# Tests avec couverture
composer test-coverage

# Tests spécifiques
./vendor/bin/pest --filter="InvoiceService"

# Tests d'intégration API
./vendor/bin/pest tests/Feature/Services

# Tests de mapping
./vendor/bin/pest tests/Unit/Mappers
```

## 📚 API Reference

### FNEClient

Point d'entrée principal du SDK.

```php
class FNEClient
{
    public function invoice(): InvoiceService
    public function purchase(): PurchaseService
    public function refund(): RefundService
    public function getConfig(): FNEConfig
}
```

### InvoiceService

Service pour la gestion des factures de vente.

```php
class InvoiceService extends BaseService
{
    /**
     * Certifie une facture de vente
     * 
     * @param array<string, mixed>|null $data Données de la facture (optionnel si setModel() ou setData() utilisé)
     * @return ResponseDTO Réponse de l'API avec la facture certifiée
     * @throws ValidationException Si les données sont invalides
     * @throws AuthenticationException Si l'API key est invalide
     * @throws BadRequestException Si la requête est mal formée
     * @throws ServerException Si une erreur serveur survient
     */
    public function sign(?array $data = null): ResponseDTO
    
    /**
     * Définir un modèle pour extraction automatique des données
     * 
     * @param mixed $model Modèle avec toArray() ou attributesToArray()
     * @return $this
     */
    public function setModel(mixed $model): self
    
    /**
     * Définir des données de contexte
     * 
     * @param array<string, mixed> $data
     * @return $this
     */
    public function setData(array $data): self
}
```

**Ordre de priorité pour la récupération des données :**
1. Données explicites passées à `sign($data)`
2. Données de contexte via `setData()`
3. Données du modèle via `setModel()`
4. Exception si aucune donnée disponible

### PurchaseService

Service pour la gestion des bordereaux d'achat.

```php
class PurchaseService extends BaseService
{
    /**
     * Soumet un bordereau d'achat
     * 
     * @param array<string, mixed>|null $data Données du bordereau (optionnel si setModel() ou setData() utilisé)
     * @return ResponseDTO Réponse de l'API avec le bordereau certifié
     * @throws ValidationException Si les données sont invalides
     */
    public function submit(?array $data = null): ResponseDTO
    
    /**
     * Définir un modèle pour extraction automatique des données
     * 
     * @param mixed $model Modèle avec toArray() ou attributesToArray()
     * @return $this
     */
    public function setModel(mixed $model): self
    
    /**
     * Définir des données de contexte
     * 
     * @param array<string, mixed> $data
     * @return $this
     */
    public function setData(array $data): self
}
```

### RefundService

Service pour la gestion des avoirs.

```php
class RefundService extends BaseService
{
    /**
     * Émet un avoir pour une facture
     * 
     * @param string $invoiceId UUID de la facture parente
     * @param array<string, mixed> $items Items à rembourser
     * @return ResponseDTO Réponse de l'API avec l'avoir généré
     * @throws ValidationException Si les données sont invalides
     * @throws NotFoundException Si la facture n'existe pas
     */
    public function issue(string $invoiceId, array $items): ResponseDTO
}
```

### BaseMapper

Classe de base pour les mappers avec support du mapping personnalisé.

```php
abstract class BaseMapper implements MapperInterface
{
    /**
     * Vérifier si un mapping personnalisé est configuré
     * 
     * @return bool
     */
    public function hasMapping(): bool
    
    /**
     * Transformer les données ERP vers le format FNE
     * 
     * @param array<string, mixed> $data Données ERP
     * @return array<string, mixed> Données au format FNE
     */
    public function map(array $data): array
}
```

## 🔧 Développement

### Prérequis

- PHP 8.2+
- Composer 2.0+

### Installation des dépendances de développement

```bash
composer install
```

### Formatage du code

```bash
composer format
```

### Analyse statique

```bash
composer analyse
```

## 📝 Licence

MIT License - Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez lire le guide de contribution avant de soumettre une pull request.

## 📞 Support

- **Documentation** : [https://fne-client.neocode.ci/](https://fne-client.neocode.ci/)
- **Repository GitHub** : [https://github.com/neocodesupport/fne-client](https://github.com/neocodesupport/fne-client)
- **Issues** : [https://github.com/neocodesupport/fne-client/issues](https://github.com/neocodesupport/fne-client/issues)
- **Email** : support@neocode.ci

## 🙏 Remerciements

Ce package a été développé pour faciliter l'intégration de l'API FNE dans les applications PHP.

---

**Développé avec ❤️ par Neocode**
