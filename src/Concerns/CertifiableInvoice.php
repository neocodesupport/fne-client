<?php

namespace Neocode\FNE\Concerns;

use Neocode\FNE\DTOs\ResponseDTO;
use Neocode\FNE\Storage\CertificationStorage;

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
     * Compatible Laravel (Eloquent) et Symfony (Doctrine/SQL natif).
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData  Données de la facture originale
     * @return void
     */
    protected function saveToCertificationsTable(ResponseDTO $response, array $invoiceData): void
    {
        // Utiliser la classe Storage qui gère la compatibilité multi-framework
        CertificationStorage::save($response, $invoiceData);
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

