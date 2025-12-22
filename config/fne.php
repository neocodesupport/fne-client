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
];

