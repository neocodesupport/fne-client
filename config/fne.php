<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Clé API FNE
    |--------------------------------------------------------------------------
    |
    | Votre clé API pour l'authentification auprès de l'API FNE.
    | Obtenez votre clé API depuis votre compte DGI.
    |
    */
    'api_key' => env('FNE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL de Base de l'API FNE
    |--------------------------------------------------------------------------
    |
    | URL de base de l'API FNE. En mode test, utilisez l'API mock locale.
    | En production, l'URL sera fournie par la DGI.
    |
    */
    'base_url' => env('FNE_BASE_URL', 'https://fne-api-mock.test'),

    /*
    |--------------------------------------------------------------------------
    | Mode d'Exécution
    |--------------------------------------------------------------------------
    |
    | Mode d'exécution du package : 'test' ou 'production'
    |
    */
    'mode' => env('FNE_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Timeout HTTP
    |--------------------------------------------------------------------------
    |
    | Timeout en secondes pour les requêtes HTTP vers l'API FNE.
    |
    */
    'timeout' => env('FNE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Configuration du Cache
    |--------------------------------------------------------------------------
    |
    | Configuration du système de cache pour les réponses de l'API.
    |
    */
    'cache' => [
        'enabled' => env('FNE_CACHE_ENABLED', true),
        'ttl' => env('FNE_CACHE_TTL', 3600), // 1 heure par défaut
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Locale pour les messages d'erreur et de validation.
    | Valeurs supportées : 'fr', 'en'
    |
    */
    'locale' => env('FNE_LOCALE', 'fr'),

    /*
    |--------------------------------------------------------------------------
    | Configuration des Features (Laravel Pennant)
    |--------------------------------------------------------------------------
    |
    | Configuration des modules et fonctionnalités du package.
    | Ces features peuvent être activées/désactivées via Laravel Pennant.
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Configuration du Mapping Personnalisé
    |--------------------------------------------------------------------------
    |
    | Configuration du mapping personnalisé pour transformer vos données ERP
    | vers le format FNE. Utilisez la notation pointée pour les chemins imbriqués.
    |
    | Format : ['fne_key' => 'erp.path.to.value']
    |
    | Exemple :
    | - 'clientCompanyName' => 'client.name' transforme $data['client']['name'] en $data['clientCompanyName']
    | - 'clientPhone' => 'customer.phone_number' transforme $data['customer']['phone_number'] en $data['clientPhone']
    |
    | Les mappings sont appliqués AVANT le mapping standard du package.
    |
    */
    'mapping' => [
        /*
        |--------------------------------------------------------------------------
        | Mapping pour les Factures de Vente
        |--------------------------------------------------------------------------
        |
        | Définissez ici le mapping personnalisé pour vos factures de vente.
        | La clé est le nom du champ FNE, la valeur est le chemin vers la valeur dans vos données ERP.
        |
        */
        'invoice' => [
            // Exemples (décommentez et adaptez selon vos besoins) :
            // 'clientCompanyName' => 'client.name',
            // 'clientPhone' => 'customer.phone_number',
            // 'clientEmail' => 'client.email',
            // 'pointOfSale' => 'pos.code',
            // 'establishment' => 'establishment.code',
            // 'items' => 'invoice_items',
            // 'items.0.description' => 'items.0.product_name',
            // 'items.0.amount' => 'items.0.price',
        ],

        /*
        |--------------------------------------------------------------------------
        | Mapping pour les Bordereaux d'Achat
        |--------------------------------------------------------------------------
        |
        | Définissez ici le mapping personnalisé pour vos bordereaux d'achat.
        |
        */
        'purchase' => [
            // Exemples (décommentez et adaptez selon vos besoins) :
            // 'clientCompanyName' => 'supplier.name',
            // 'clientPhone' => 'supplier.phone',
            // 'clientEmail' => 'supplier.email',
            // 'pointOfSale' => 'pos.code',
            // 'establishment' => 'establishment.code',
            // 'items' => 'purchase_items',
        ],

        /*
        |--------------------------------------------------------------------------
        | Mapping pour les Avoirs
        |--------------------------------------------------------------------------
        |
        | Définissez ici le mapping personnalisé pour vos avoirs.
        |
        */
        'refund' => [
            // Exemples (décommentez et adaptez selon vos besoins) :
            // 'items' => 'refund_items',
            // 'items.0.id' => 'items.0.fne_item_id',
            // 'items.0.quantity' => 'items.0.refund_quantity',
        ],
    ],
];

