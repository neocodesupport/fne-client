<?php

namespace Neocode\FNE\Concerns;

use Neocode\FNE\DTOs\ResponseDTO;

/**
 * Trait pour les modèles de factures de vente certifiables
 *
 * @package Neocode\FNE\Concerns
 */
trait CertifiableInvoice
{
    /**
     * Certifier la facture via l'API FNE.
     *
     * @param  array<string, mixed>|null  $data  Données optionnelles (si null, utilise les données du modèle)
     * @param  bool  $saveToCertificationsTable  Enregistrer dans la table fne_certifications (défaut: selon config)
     * @return ResponseDTO
     */
    public function certify(?array $data = null, ?bool $saveToCertificationsTable = null): ResponseDTO
    {
        // Obtenir le client FNE
        $fneClient = $this->getFneClient();
        $invoiceService = $fneClient->invoice();

        // Si aucune donnée n'est fournie, utiliser le modèle directement
        if ($data === null) {
            // Passer le modèle au service pour qu'il puisse extraire les données
            $invoiceService->setModel($this);
            // Appeler sign() sans paramètre pour utiliser les données du modèle
            $response = $invoiceService->sign();
        } else {
            // Si des données sont fournies, les utiliser directement
            $response = $invoiceService->sign($data);
        }

        // Enregistrer dans la table fne_certifications si activé
        if ($this->shouldSaveToCertificationsTable($saveToCertificationsTable)) {
            $this->saveToCertificationsTable($response, $data ?? $this->getFneData());
        }

        return $response;
    }

    /**
     * Déterminer si on doit enregistrer dans la table fne_certifications.
     *
     * @param  bool|null  $explicitValue  Valeur explicite fournie
     * @return bool
     */
    protected function shouldSaveToCertificationsTable(?bool $explicitValue): bool
    {
        // Si une valeur explicite est fournie, l'utiliser
        if ($explicitValue !== null) {
            return $explicitValue;
        }

        // Vérifier la configuration Laravel Pennant si disponible
        if (function_exists('app') && class_exists(\Laravel\Pennant\Feature::class)) {
            try {
                return \Laravel\Pennant\Feature::active('fne:certification-table');
            } catch (\Throwable $e) {
                // Si Pennant n'est pas configuré, utiliser la config
            }
        }

        // Fallback : utiliser la configuration
        if (function_exists('config')) {
            return config('fne.features.certification_table', false);
        }

        return false;
    }

    /**
     * Enregistrer la certification dans la table fne_certifications.
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData  Données de la facture originale
     * @return void
     */
    protected function saveToCertificationsTable(ResponseDTO $response, array $invoiceData): void
    {
        // Vérifier si le modèle existe
        if (!class_exists(\Neocode\FNE\Models\FNECertification::class)) {
            return;
        }

        // Vérifier si on est dans Laravel et si la table existe
        if (!function_exists('app') || !app()->bound('db')) {
            return;
        }

        try {
            // Vérifier si la table existe
            if (!\Illuminate\Support\Facades\Schema::hasTable('fne_certifications')) {
                // Logger un message informatif mais ne pas faire échouer
                if (app()->bound(\Psr\Log\LoggerInterface::class)) {
                    app(\Psr\Log\LoggerInterface::class)->info('FNE certifications table does not exist. Run migration: php artisan migrate', [
                        'reference' => $response->reference ?? null,
                    ]);
                }
                return;
            }
        } catch (\Throwable $e) {
            // Si on ne peut pas vérifier l'existence de la table, on abandonne silencieusement
            return;
        }

        try {
            $invoice = $response->invoice;

            // Utiliser les montants directement depuis la réponse (déjà en centimes)
            $amount = $invoice ? $invoice->amount : 0;
            $vatAmount = $invoice ? $invoice->vatAmount : 0;
            $fiscalStamp = $invoice ? $invoice->fiscalStamp : 0;

            // Créer l'enregistrement
            \Neocode\FNE\Models\FNECertification::create([
                'fne_invoice_id' => $invoice->id ?? null,
                'reference' => $response->reference,
                'ncc' => $response->ncc,
                'token' => $response->token,
                'type' => 'invoice',
                'subtype' => 'normal',
                'status' => $invoice->status ?? 'pending',
                'template' => $invoiceData['template'] ?? 'B2C',
                'client_company_name' => $invoiceData['clientCompanyName'] ?? null,
                'client_ncc' => $invoiceData['clientNcc'] ?? null,
                'client_phone' => $invoiceData['clientPhone'] ?? null,
                'client_email' => $invoiceData['clientEmail'] ?? null,
                'amount' => $amount, // Déjà en centimes depuis la réponse API
                'vat_amount' => $vatAmount, // Déjà en centimes depuis la réponse API
                'fiscal_stamp' => $fiscalStamp, // Déjà en centimes depuis la réponse API
                'discount' => $invoiceData['discount'] ?? 0,
                'is_rne' => $invoiceData['isRne'] ?? false,
                'rne' => $invoiceData['rne'] ?? null,
                'source' => 'api',
                'warning' => $response->warning,
                'balance_sticker' => $response->balanceSticker,
                'fne_date' => $invoice->date ?? now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Erreur SQL (table n'existe pas, colonne manquante, etc.)
            // Logger l'erreur mais ne pas faire échouer la certification
            if (app()->bound(\Psr\Log\LoggerInterface::class)) {
                app(\Psr\Log\LoggerInterface::class)->warning('Failed to save FNE certification to table (database error)', [
                    'error' => $e->getMessage(),
                    'sql_state' => $e->getCode(),
                    'reference' => $response->reference ?? null,
                    'hint' => 'Make sure the fne_certifications table exists. Run: php artisan migrate',
                ]);
            }
        } catch (\Throwable $e) {
            // Autres erreurs (validation, etc.)
            // Logger l'erreur mais ne pas faire échouer la certification
            if (app()->bound(\Psr\Log\LoggerInterface::class)) {
                app(\Psr\Log\LoggerInterface::class)->warning('Failed to save FNE certification to table', [
                    'error' => $e->getMessage(),
                    'reference' => $response->reference ?? null,
                ]);
            }
        }
    }

    /**
     * Obtenir les données du modèle au format FNE.
     *
     * @return array<string, mixed>
     */
    protected function getFneData(): array
    {
        // Essayer toArray() si disponible (Laravel Eloquent)
        if (method_exists($this, 'toArray')) {
            return $this->toArray();
        }

        // Essayer attributesToArray() si disponible (Laravel Eloquent)
        if (method_exists($this, 'attributesToArray')) {
            return $this->attributesToArray();
        }

        // Essayer getAttributes() si disponible
        if (method_exists($this, 'getAttributes')) {
            return $this->getAttributes();
        }

        // Fallback : convertir en array
        return (array) $this;
    }

    /**
     * Obtenir une instance du client FNE.
     *
     * @return \Neocode\FNE\FNEClient
     */
    protected function getFneClient(): \Neocode\FNE\FNEClient
    {
        // Si on est dans Laravel, utiliser le service container
        if (function_exists('app')) {
            return app(\Neocode\FNE\FNEClient::class);
        }

        // Si on est dans Symfony, utiliser le service container
        if (class_exists(\Symfony\Component\DependencyInjection\ContainerInterface::class)) {
            global $kernel;
            if (isset($kernel) && method_exists($kernel, 'getContainer')) {
                $container = $kernel->getContainer();
                if ($container->has(\Neocode\FNE\FNEClient::class)) {
                    return $container->get(\Neocode\FNE\FNEClient::class);
                }
            }
        }

        // Sinon, créer une instance avec la configuration par défaut
        $config = new \Neocode\FNE\Config\FNEConfig(
            require __DIR__ . '/../../config/fne.php'
        );

        $httpClient = \Neocode\FNE\Http\HttpClientFactory::create($config);

        return new \Neocode\FNE\FNEClient($httpClient, $config);
    }
}

