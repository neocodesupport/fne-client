<?php

namespace Neocode\FNE\Concerns;

use Neocode\FNE\DTOs\ResponseDTO;

/**
 * Trait pour les modèles de factures certifiées (pour émettre des avoirs)
 *
 * @package Neocode\FNE\Concerns
 */
trait CertifiableRefund
{
    /**
     * Émettre un avoir pour cette facture certifiée.
     *
     * @param  array<int, array<string, mixed>>  $items  Items à rembourser [['id' => 'uuid', 'quantity' => float], ...]
     * @return ResponseDTO
     */
    public function issueRefund(array $items): ResponseDTO
    {
        // Récupérer l'UUID FNE de la facture
        $fneInvoiceId = $this->getFneInvoiceId();

        if (empty($fneInvoiceId)) {
            throw new \RuntimeException(
                'Le modèle doit avoir un attribut fne_id ou fne_invoice_id contenant l\'UUID FNE de la facture certifiée.'
            );
        }

        // Obtenir le client FNE
        $fneClient = $this->getFneClient();

        // Émettre l'avoir
        return $fneClient->refund()->issue($fneInvoiceId, $items);
    }

    /**
     * Obtenir l'UUID FNE de la facture.
     *
     * @return string
     */
    protected function getFneInvoiceId(): string
    {
        // Essayer différents attributs possibles
        if (isset($this->fne_id)) {
            return (string) $this->fne_id;
        }

        if (isset($this->fne_invoice_id)) {
            return (string) $this->fne_invoice_id;
        }

        // Essayer via méthode getter
        if (method_exists($this, 'getFneId')) {
            return (string) $this->getFneId();
        }

        if (method_exists($this, 'getFneInvoiceId')) {
            return (string) $this->getFneInvoiceId();
        }

        // Essayer via array access
        if (is_array($this) || $this instanceof \ArrayAccess) {
            if (isset($this['fne_id'])) {
                return (string) $this['fne_id'];
            }
            if (isset($this['fne_invoice_id'])) {
                return (string) $this['fne_invoice_id'];
            }
        }

        return '';
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

