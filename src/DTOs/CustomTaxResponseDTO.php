<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour une taxe personnalisée dans la réponse API FNE
 *
 * Les taxes personnalisées peuvent être au niveau facture ou au niveau item.
 * Exemples : GRA, AIRSI, DTD
 *
 * @package Neocode\FNE\DTOs
 */
class CustomTaxResponseDTO extends BaseDTO
{
    /**
     * UUID de la taxe personnalisée
     */
    public readonly string $id;

    /**
     * Nom de la taxe personnalisée (GRA, AIRSI, DTD)
     */
    public readonly string $name;

    /**
     * Montant en % (ex: 5.0 = 5%)
     */
    public readonly float $amount;

    /**
     * ID de l'item de facture associé (null si taxe au niveau facture)
     */
    public readonly ?string $invoiceItemId;

    /**
     * ID de la facture associée (null si taxe au niveau item)
     */
    public readonly ?string $invoiceId;

    /**
     * Date de création (ISO 8601)
     */
    public readonly string $createdAt;

    /**
     * Date de mise à jour (ISO 8601)
     */
    public readonly string $updatedAt;

    /**
     * Create a new CustomTaxResponseDTO instance.
     */
    public function __construct(
        string $id,
        string $name,
        float $amount,
        ?string $invoiceItemId = null,
        ?string $invoiceId = null,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->invoiceItemId = $invoiceItemId;
        $this->invoiceId = $invoiceId;
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
            $data['id'] ?? '',
            $data['name'] ?? '',
            (float) ($data['amount'] ?? 0),
            $data['invoiceItemId'] ?? $data['invoice_item_id'] ?? null,
            $data['invoiceId'] ?? $data['invoice_id'] ?? null,
            $data['createdAt'] ?? $data['created_at'] ?? '',
            $data['updatedAt'] ?? $data['updated_at'] ?? ''
        );
    }
}

