<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour un article de facture dans la réponse API FNE
 *
 * @package Neocode\FNE\DTOs
 */
class InvoiceItemResponseDTO extends BaseDTO
{
    /**
     * UUID de l'item (⚠️ IMPORTANT pour avoirs futurs)
     */
    public readonly string $id;

    /**
     * Quantité
     */
    public readonly float $quantity;

    /**
     * Référence produit (optionnel)
     */
    public readonly ?string $reference;

    /**
     * Description de l'article
     */
    public readonly string $description;

    /**
     * Prix unitaire HT en centimes (⚠️ diviser par 100 pour obtenir FCFA)
     */
    public readonly int $amount;

    /**
     * Remise sur article en %
     */
    public readonly float $discount;

    /**
     * Unité de mesure (pcs, kg, etc.)
     */
    public readonly ?string $measurementUnit;

    /**
     * Taxes de l'item (TVA, TVAB, etc.)
     *
     * @var array<TaxResponseDTO>
     */
    public readonly array $taxes;

    /**
     * Taxes personnalisées de l'item (GRA, AIRSI, etc.)
     *
     * @var array<CustomTaxResponseDTO>
     */
    public readonly array $customTaxes;

    /**
     * ID de la facture associée
     */
    public readonly string $invoiceId;

    /**
     * ID de la facture parente (null si facture normale)
     */
    public readonly ?string $parentId;

    /**
     * Date de création (ISO 8601)
     */
    public readonly string $createdAt;

    /**
     * Date de mise à jour (ISO 8601)
     */
    public readonly string $updatedAt;

    /**
     * Create a new InvoiceItemResponseDTO instance.
     */
    public function __construct(
        string $id,
        float $quantity,
        ?string $reference,
        string $description,
        int $amount,
        float $discount,
        ?string $measurementUnit,
        array $taxes = [],
        array $customTaxes = [],
        string $invoiceId = '',
        ?string $parentId = null,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->reference = $reference;
        $this->description = $description;
        $this->amount = $amount;
        $this->discount = $discount;
        $this->measurementUnit = $measurementUnit;
        $this->taxes = $taxes;
        $this->customTaxes = $customTaxes;
        $this->invoiceId = $invoiceId;
        $this->parentId = $parentId;
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
        $taxes = [];
        if (isset($data['taxes']) && is_array($data['taxes'])) {
            foreach ($data['taxes'] as $tax) {
                $taxes[] = TaxResponseDTO::fromArray($tax);
            }
        }

        $customTaxes = [];
        if (isset($data['customTaxes']) && is_array($data['customTaxes'])) {
            foreach ($data['customTaxes'] as $customTax) {
                $customTaxes[] = CustomTaxResponseDTO::fromArray($customTax);
            }
        }

        return new self(
            $data['id'] ?? '',
            (float) ($data['quantity'] ?? 0),
            $data['reference'] ?? null,
            $data['description'] ?? '',
            (int) ($data['amount'] ?? 0),
            (float) ($data['discount'] ?? 0),
            $data['measurementUnit'] ?? $data['measurement_unit'] ?? null,
            $taxes,
            $customTaxes,
            $data['invoiceId'] ?? $data['invoice_id'] ?? '',
            $data['parentId'] ?? $data['parent_id'] ?? null,
            $data['createdAt'] ?? $data['created_at'] ?? '',
            $data['updatedAt'] ?? $data['updated_at'] ?? ''
        );
    }

    /**
     * Retourne le prix unitaire HT en FCFA (divise par 100).
     *
     * @return float
     */
    public function getAmountInFCFA(): float
    {
        return $this->amount / 100;
    }
}

