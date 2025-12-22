<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour les réponses de l'API FNE
 *
 * @package Neocode\FNE\DTOs
 */
class ResponseDTO
{
    /**
     * NCC (Numéro Contribuable)
     */
    public string $ncc;

    /**
     * Référence de la facture/avoir
     */
    public string $reference;

    /**
     * Token de vérification
     */
    public string $token;

    /**
     * Warning (alerte sur le stock de sticker)
     */
    public bool $warning;

    /**
     * Balance sticker
     */
    public int $balanceSticker;

    /**
     * Invoice (objet facture, null pour les avoirs)
     */
    public ?InvoiceResponseDTO $invoice = null;

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
            $data['balance_sticker'] ?? 0,
            $invoice
        );
    }

    /**
     * Vérifier si c'est une facture (pas un avoir).
     *
     * @return bool
     */
    public function isInvoice(): bool
    {
        return $this->invoice !== null;
    }

    /**
     * Vérifier si c'est un avoir.
     *
     * @return bool
     */
    public function isRefund(): bool
    {
        return $this->invoice === null;
    }

    /**
     * Convertir en array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ncc' => $this->ncc,
            'reference' => $this->reference,
            'token' => $this->token,
            'warning' => $this->warning,
            'balance_sticker' => $this->balanceSticker,
            'invoice' => $this->invoice?->toArray(),
        ];
    }
}

