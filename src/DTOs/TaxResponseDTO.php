<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour une taxe dans la réponse API FNE
 *
 * @package Neocode\FNE\DTOs
 */
class TaxResponseDTO extends BaseDTO
{
    /**
     * Nom court de la taxe (TVA, TVAB, TVAC, TVAD)
     */
    public readonly string $shortName;

    /**
     * Taux de TVA en % (ex: 18.0 = 18%)
     */
    public readonly float $amount;

    /**
     * Nom complet de la taxe
     */
    public readonly string $name;

    /**
     * ID de l'item de facture associé
     */
    public readonly string $invoiceItemId;

    /**
     * ID du taux de TVA
     */
    public readonly string $vatRateId;

    /**
     * Date de création (ISO 8601)
     */
    public readonly string $createdAt;

    /**
     * Date de mise à jour (ISO 8601)
     */
    public readonly string $updatedAt;

    /**
     * Create a new TaxResponseDTO instance.
     */
    public function __construct(
        string $shortName,
        float $amount,
        string $name,
        string $invoiceItemId,
        string $vatRateId,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->shortName = $shortName;
        $this->amount = $amount;
        $this->name = $name;
        $this->invoiceItemId = $invoiceItemId;
        $this->vatRateId = $vatRateId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Créer une instance depuis un array.
     *
     * @param  array<string, mixed>  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['shortName'] ?? $data['short_name'] ?? '',
            (float) ($data['amount'] ?? 0),
            $data['name'] ?? '',
            $data['invoiceItemId'] ?? $data['invoice_item_id'] ?? '',
            $data['vatRateId'] ?? $data['vat_rate_id'] ?? '',
            $data['createdAt'] ?? $data['created_at'] ?? '',
            $data['updatedAt'] ?? $data['updated_at'] ?? ''
        );
    }
}

