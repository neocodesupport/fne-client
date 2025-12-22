# FNE Client

SDK PHP framework-agnostic pour l'intégration de l'API FNE (Facture Normalisée Électronique).

## Installation

```bash
composer require neocode/fne-client
```

## Quick Start

```php
use Neocode\FNE\Facades\FNE;

$result = FNE::invoice()->sign([
    'invoiceType' => 'sale',
    'paymentMethod' => 'mobile-money',
    // ...
]);
```

## Documentation

La documentation complète sera disponible prochainement.

## License

MIT

