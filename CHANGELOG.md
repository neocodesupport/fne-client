# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Améliorations futures et corrections de bugs

## [1.0.0] - 2025-01-XX

### Changed
- **Version stable** : Passage en version stable 1.0.0
- Toutes les fonctionnalités de la version beta sont maintenant stables et prêtes pour la production

## [1.0.0-beta] - 2025-01-XX

### Added
- **Mapping personnalisé** : Configuration flexible ERP → FNE avec notation pointée
  - Support de la notation pointée pour chemins imbriqués (ex: `'client.name'`)
  - Configuration via `config/fne.php` avec sections `invoice`, `purchase`, `refund`
  - Application automatique du mapping avant le mapping standard
- **Traits pour intégration modèles** :
  - `CertifiableInvoice` : Trait pour factures de vente avec méthode `certify()`
  - `CertifiablePurchase` : Trait pour bordereaux d'achat avec méthode `submit()`
  - `CertifiableRefund` : Trait pour avoirs avec méthode `issueRefund()`
  - `Certifiable` : Trait combiné pour tous types de documents
  - Support multi-framework (Laravel Eloquent, Symfony Doctrine, PHP natif)
  - Détection automatique du framework et extraction des données
- **Intégration modèles dans BaseService** :
  - Méthodes `setModel()` et `setData()` pour contexte flexible
  - Méthode `getData()` avec ordre de priorité (explicite > contexte > modèle)
  - Méthode `extractModelData()` supportant différentes méthodes d'extraction
- **Protection contre conversions de tableaux** :
  - Correction des avertissements "Array to string conversion" dans les mappers
  - Vérification des types avant conversion dans `normalizeBooleans()`
  - Protection dans toutes les méthodes de normalisation

### Changed
- **BaseService** : Support de `?array $data = null` dans `execute()` et `sign()/submit()`
- **Services** : Les méthodes `sign()` et `submit()` acceptent maintenant `null` et utilisent le contexte
- **Mappers** : Application automatique du mapping personnalisé si configuré
- **Tests** : Suite de tests étendue à 67 tests (222 assertions)

### Fixed
- Correction des avertissements "Array to string conversion" dans tous les mappers
- Amélioration de la vérification SSL dans les tests API
- Correction de la gestion des tableaux dans les méthodes de normalisation

### Security
- Validation stricte des types avant conversion pour éviter les conversions non sécurisées

## [1.0.0-alpha] - 2025-01-XX

### Added
- Architecture SOLID complète avec séparation des responsabilités
- Support framework-agnostic (Laravel 11+, Symfony 7.4+, PHP natif)
- Détection automatique du framework lors de l'installation
- Installation interactive avec Laravel Prompts
- Service Provider Laravel avec intégration complète
- Facade Laravel pour accès simplifié
- Trait `InteractsWithFNE` pour utilisation dans les classes Laravel
- Gestion modulaire avec Laravel Pennant (Laravel uniquement)
- Enums PHP 8.2+ pour sécurité de type :
  - `InvoiceTemplate` (B2C, B2B, B2F, B2G)
  - `PaymentMethod` (CASH, CARD, CHECK, MOBILE_MONEY, TRANSFER, DEFERRED)
  - `InvoiceType` (SALE, PURCHASE)
  - `TaxType` (TVA, TVAB, TVAC, TVAD)
  - `ForeignCurrency` (XOF, USD, EUR, JPY, CAD, GBP, AUD, CNH, CHF, HKD, NZD)
- Services complets :
  - `InvoiceService` pour factures de vente
  - `PurchaseService` pour bordereaux d'achat
  - `RefundService` pour avoirs
- Mappers pour transformation ERP → FNE :
  - `InvoiceMapper` avec normalisation des données
  - `PurchaseMapper` pour bordereaux d'achat
  - `RefundMapper` pour avoirs
  - `BaseMapper` avec méthodes utilitaires communes
- Validators avec validation stricte :
  - `InvoiceValidator` avec règles conditionnelles
  - `PurchaseValidator` avec validation des taxes absentes
  - `RefundValidator` avec validation UUID
  - `BaseValidator` avec système de règles extensible
- HTTP Clients avec sélection automatique :
  - `LaravelHttpClient` (priorité si `illuminate/http` disponible)
  - `GuzzleHttpClient` (fallback)
  - `HttpClientFactory` pour sélection automatique
- Cache PSR-16 compatible :
  - `LaravelCache` pour intégration Laravel
  - `ArrayCache` pour PHP natif
  - `CacheFactory` pour sélection automatique
- DTOs typés pour réponses API :
  - `ResponseDTO` pour réponses complètes
  - `InvoiceResponseDTO` pour informations facture
  - `InvoiceItemResponseDTO` pour articles
  - `TaxResponseDTO` pour taxes
  - `CustomTaxResponseDTO` pour taxes personnalisées
- Hiérarchie d'exceptions complète :
  - `FNEException` (classe de base)
  - `ValidationException` (422)
  - `MappingException` (500)
  - `AuthenticationException` (401)
  - `BadRequestException` (400)
  - `NotFoundException` (404)
  - `ServerException` (500+)
- Configuration centralisée avec `FNEConfig`
- Support du logging PSR-3
- Tests unitaires complets
- Tests d'intégration avec API mock locale
- Documentation complète (README.md, ConceptionDirective.md)
- Commandes d'installation :
  - `php artisan fne:install` (Laravel)
  - `php bin/console fne:install` (Symfony)
  - `php vendor/bin/fne-install` (PHP natif)

### Changed
- Architecture modulaire avec séparation claire des couches
- Validation pré-mapping pour certaines règles (ex: taxes dans achats)
- Gestion améliorée des erreurs avec messages détaillés

### Fixed
- Correction de la gestion du cache dans le Service Provider Laravel
- Correction de la construction d'URL pour les avoirs (double slash)
- Correction de la validation des taxes dans les bordereaux d'achat
- Correction du mapping de `clientNcc` pour assurer le type string
- Correction de la gestion des réponses HTTP avec décodage JSON sécurisé
- Correction de la détection de framework pour meilleure compatibilité
- Correction des endpoints API (suppression du préfixe `/api` incorrect)

### Security
- Validation stricte de toutes les entrées utilisateur
- Protection de l'API key dans les logs
- Désactivation SSL verification uniquement en mode test

### Added
- Version alpha initiale
- Architecture de base complète
- Support des trois frameworks (Laravel, Symfony, PHP natif)
- Tests de base

---

## Types de changements

- **Added** : Nouvelles fonctionnalités
- **Changed** : Changements dans les fonctionnalités existantes
- **Deprecated** : Fonctionnalités qui seront supprimées
- **Removed** : Fonctionnalités supprimées
- **Fixed** : Corrections de bugs
- **Security** : Corrections de sécurité
