<?php

namespace Neocode\FNE\Features;

/**
 * Définition des features Laravel Pennant pour le package FNE Client
 *
 * @package Neocode\FNE\Features
 */
class FNEFeatures
{
    /**
     * Définir toutes les features du package.
     *
     * @return void
     */
    public static function define(): void
    {
        if (!class_exists(\Laravel\Pennant\Feature::class)) {
            return;
        }

        // Module : Mapping avancé (v1.0.0 - activé par défaut)
        \Laravel\Pennant\Feature::define('fne:advanced-mapping', function ($user) {
            return config('fne.features.advanced_mapping', true);
        });

        // Module : Traitement par lots (v1.1.0 - désactivé par défaut)
        \Laravel\Pennant\Feature::define('fne:batch-processing', function ($user) {
            return config('fne.features.batch_processing', false);
        });

        // Module : Webhooks (v1.1.0 - désactivé par défaut)
        \Laravel\Pennant\Feature::define('fne:webhooks', function ($user) {
            return config('fne.features.webhooks', false);
        });

        // Module : Queue Jobs (v1.1.0 - désactivé par défaut)
        \Laravel\Pennant\Feature::define('fne:queue-jobs', function ($user) {
            return config('fne.features.queue_jobs', false);
        });

        // Module : Audit Logging (v1.0.0 - activé par défaut)
        \Laravel\Pennant\Feature::define('fne:audit-logging', function ($user) {
            return config('fne.features.audit_logging', true);
        });

        // Module : Auto Retry (v1.0.0 - activé par défaut)
        \Laravel\Pennant\Feature::define('fne:auto-retry', function ($user) {
            return config('fne.features.auto_retry', true);
        });

        // Module : Table de certification (v1.0.0 - désactivé par défaut)
        \Laravel\Pennant\Feature::define('fne:certification-table', function ($user) {
            return config('fne.features.certification_table', false);
        });
    }

    /**
     * Obtenir la liste de tous les modules disponibles avec leurs versions.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAvailableModules(): array
    {
        return [
            'fne:advanced-mapping' => [
                'version' => '1.0.0',
                'description' => 'Mapping avancé ERP → FNE avec notation pointée',
                'default' => true,
            ],
            'fne:batch-processing' => [
                'version' => '1.1.0',
                'description' => 'Traitement par lots de factures',
                'default' => false,
            ],
            'fne:webhooks' => [
                'version' => '1.1.0',
                'description' => 'Support des webhooks FNE',
                'default' => false,
            ],
            'fne:queue-jobs' => [
                'version' => '1.1.0',
                'description' => 'Traitement asynchrone via queues',
                'default' => false,
            ],
            'fne:audit-logging' => [
                'version' => '1.0.0',
                'description' => 'Logging détaillé pour audit',
                'default' => true,
            ],
            'fne:auto-retry' => [
                'version' => '1.0.0',
                'description' => 'Retry automatique en cas d\'erreur',
                'default' => true,
            ],
            'fne:certification-table' => [
                'version' => '1.0.0',
                'description' => 'Utilisation de la table fne_certifications',
                'default' => false,
            ],
        ];
    }
}

