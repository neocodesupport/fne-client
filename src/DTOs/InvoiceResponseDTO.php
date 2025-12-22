<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour les réponses de facture
 *
 * @package Neocode\FNE\DTOs
 */
class InvoiceResponseDTO
{
    /**
     * ID de la facture
     */
    public string $id;

    /**
     * Référence FNE
     */
    public string $reference;

    /**
     * Token de vérification
     */
    public string $token;

    /**
     * Type de facture
     */
    public string $type;

    /**
     * Date de la facture
     */
    public string $date;

    /**
     * Montant total TTC (en centimes)
     */
    public int $amount;

    /**
     * Montant TVA (en centimes)
     */
    public int $vatAmount;

    /**
     * Client NCC (optionnel)
     */
    public ?string $clientNcc = null;

    /**
     * Create a new InvoiceResponseDTO instance.
     */
    public function __construct(
        string $id,
        string $reference,
        string $token,
        string $type,
        string $date,
        int $amount,
        int $vatAmount,
        ?string $clientNcc = null
    ) {
        $this->id = $id;
        $this->reference = $reference;
        $this->token = $token;
        $this->type = $type;
        $this->date = $date;
        $this->amount = $amount;
        $this->vatAmount = $vatAmount;
        $this->clientNcc = $clientNcc;
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
            $data['reference'] ?? '',
            $data['token'] ?? '',
            $data['type'] ?? '',
            $data['date'] ?? '',
            $data['amount'] ?? 0,
            $data['vatAmount'] ?? 0,
            $data['clientNcc'] ?? null
        );
    }

    /**
     * Obtenir le montant total en FCFA.
     *
     * @return float
     */
    public function getAmountInFCFA(): float
    {
        return $this->amount / 100;
    }

    /**
     * Obtenir le montant TVA en FCFA.
     *
     * @return float
     */
    public function getVatAmountInFCFA(): float
    {
        return $this->vatAmount / 100;
    }

    /**
     * Convertir en array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'token' => $this->token,
            'type' => $this->type,
            'date' => $this->date,
            'amount' => $this->amount,
            'vatAmount' => $this->vatAmount,
            'clientNcc' => $this->clientNcc,
        ];
    }
}

