<?php

namespace Neocode\FNE\Concerns;

/**
 * Trait combiné pour les modèles qui peuvent être factures ET bordereaux
 *
 * Ce trait combine CertifiableInvoice, CertifiablePurchase et CertifiableRefund
 * pour fournir toutes les méthodes de certification.
 *
 * @package Neocode\FNE\Concerns
 */
trait Certifiable
{
    use CertifiableInvoice, CertifiablePurchase, CertifiableRefund {
        // Renommer les méthodes pour éviter les conflits
        CertifiablePurchase::submit as submitPurchase;
    }

    /**
     * Certifier la facture via l'API FNE (alias pour certify()).
     *
     * @param  array<string, mixed>|null  $data  Données optionnelles (si null, utilise les données du modèle)
     * @return ResponseDTO
     */
    public function certifyInvoice(?array $data = null): ResponseDTO
    {
        return $this->certify($data);
    }

    /**
     * Soumettre le bordereau d'achat via l'API FNE (alias pour submitPurchase()).
     *
     * @param  array<string, mixed>|null  $data  Données optionnelles (si null, utilise les données du modèle)
     * @return ResponseDTO
     */
    public function certifyPurchase(?array $data = null): ResponseDTO
    {
        return $this->submitPurchase($data);
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

