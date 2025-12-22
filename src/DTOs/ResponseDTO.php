<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour la réponse complète de l'API FNE
 *
 * @property string $ncc Identifiant contribuable
 * @property string $reference Numéro de référence FNE
 * @property string $token URL complète de vérification QR code
 * @property bool $warning Alerte si stock de stickers faible
 * @property int $balanceSticker Nombre de stickers restants
 * @property ?InvoiceResponseDTO $invoice Informations facture (null pour refund)
 *
 * @package Neocode\FNE\DTOs
 */
class ResponseDTO extends BaseDTO
{
    /**
     * Identifiant contribuable (ex: "9606123E")
     */
    public readonly string $ncc;

    /**
     * Numéro de référence FNE (ex: "9606123E25000000019")
     */
    public readonly string $reference;

    /**
     * URL complète de vérification QR code
     */
    public readonly string $token;

    /**
     * Alerte si stock de stickers faible (false = OK)
     */
    public readonly bool $warning;

    /**
     * Nombre de stickers restants
     */
    public readonly int $balanceSticker;

    /**
     * Informations facture (null pour refund)
     */
    public readonly ?InvoiceResponseDTO $invoice;

    /**
     * Create a new ResponseDTO instance.
     */
    public function __construct(
        string $ncc,
        string $reference,
        string $token,
        bool $warning,
        int $balanceSticker,
        ?InvoiceResponseDTO $invoice = null
    ) {
        $this->ncc = $ncc;
        $this->reference = $reference;
        $this->token = $token;
        $this->warning = $warning;
        $this->balanceSticker = $balanceSticker;
        $this->invoice = $invoice;
    }

    /**
     * Créer une instance depuis un array.
     *
     * @param  array<string, mixed>  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $invoice = null;
        if (isset($data['invoice']) && is_array($data['invoice'])) {
            $invoice = InvoiceResponseDTO::fromArray($data['invoice']);
        }

        return new self(
            $data['ncc'] ?? '',
            $data['reference'] ?? '',
            $data['token'] ?? '',
            $data['warning'] ?? false,
            $data['balance_sticker'] ?? $data['balanceSticker'] ?? 0,
            $invoice
        );
    }

    /**
     * Vérifie si c'est une facture (pas un avoir).
     *
     * @return bool
     */
    public function isInvoice(): bool
    {
        return $this->invoice !== null;
    }

    /**
     * Vérifie si c'est un avoir.
     *
     * @return bool
     */
    public function isRefund(): bool
    {
        return $this->invoice === null || str_starts_with($this->reference, 'A');
    }
}

