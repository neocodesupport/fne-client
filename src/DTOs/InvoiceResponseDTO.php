<?php

namespace Neocode\FNE\DTOs;

/**
 * DTO pour les informations de facture dans la réponse API FNE
 *
 * @property string $id UUID FNE de la facture (⚠️ IMPORTANT pour avoirs)
 * @property int $amount Montant total TTC en centimes (diviser par 100)
 * @property int $vatAmount Montant TVA en centimes (diviser par 100)
 * @property array<InvoiceItemResponseDTO> $items Liste des articles
 * @property array<CustomTaxResponseDTO> $customTaxes Taxes personnalisées au niveau facture
 *
 * @package Neocode\FNE\DTOs
 */
class InvoiceResponseDTO extends BaseDTO
{
    /**
     * UUID FNE de la facture (⚠️ IMPORTANT pour avoirs futurs)
     */
    public readonly string $id;

    /**
     * UUID facture parente (null si facture normale)
     */
    public readonly ?string $parentId;

    /**
     * Référence facture parente (null si facture normale)
     */
    public readonly ?string $parentReference;

    /**
     * Token de vérification (partie après /verification/)
     */
    public readonly string $token;

    /**
     * Référence de la facture
     */
    public readonly string $reference;

    /**
     * Type de document ("invoice")
     */
    public readonly string $type;

    /**
     * Sous-type ("normal", "refund")
     */
    public readonly string $subtype;

    /**
     * Date ISO 8601 (ex: "2025-01-14T16:59:11.016Z")
     */
    public readonly string $date;

    /**
     * Méthode de paiement ("mobile-money", "cash", "card", "check", "transfer", "deferred")
     */
    public readonly string $paymentMethod;

    /**
     * Statut paiement ("paid", "pending")
     */
    public readonly string $status;

    /**
     * Montant total TTC en centimes (⚠️ diviser par 100 pour obtenir FCFA)
     */
    public readonly int $amount;

    /**
     * Montant TVA en centimes (⚠️ diviser par 100 pour obtenir FCFA)
     */
    public readonly int $vatAmount;

    /**
     * Timbre fiscal en centimes
     */
    public readonly int $fiscalStamp;

    /**
     * Remise globale en pourcentage
     */
    public readonly float $discount;

    /**
     * NCC du client (obligatoire si template B2B)
     */
    public readonly ?string $clientNcc;

    /**
     * Nom entreprise client
     */
    public readonly string $clientCompanyName;

    /**
     * Téléphone client
     */
    public readonly string $clientPhone;

    /**
     * Email client
     */
    public readonly string $clientEmail;

    /**
     * Terminal client (optionnel)
     */
    public readonly ?string $clientTerminal;

    /**
     * Nom du marchand client (optionnel)
     */
    public readonly ?string $clientMerchantName;

    /**
     * RCCM client (optionnel)
     */
    public readonly ?string $clientRccm;

    /**
     * Nom du vendeur (optionnel)
     */
    public readonly ?string $clientSellerName;

    /**
     * Établissement
     */
    public readonly string $clientEstablishment;

    /**
     * Point de vente
     */
    public readonly string $clientPointOfSale;

    /**
     * Template utilisé ("B2C", "B2B", "B2F", "B2G")
     */
    public readonly string $template;

    /**
     * Description (optionnel)
     */
    public readonly ?string $description;

    /**
     * Footer (optionnel)
     */
    public readonly ?string $footer;

    /**
     * Message commercial (optionnel)
     */
    public readonly ?string $commercialMessage;

    /**
     * Devise étrangère (si B2F)
     */
    public readonly ?string $foreignCurrency;

    /**
     * Taux de change devise étrangère
     */
    public readonly float $foreignCurrencyRate;

    /**
     * Indique si c'est un RNE
     */
    public readonly bool $isRne;

    /**
     * Numéro RNE (obligatoire si isRne = true)
     */
    public readonly ?string $rne;

    /**
     * Source ("api", "mobile")
     */
    public readonly string $source;

    /**
     * Date de création (ISO 8601)
     */
    public readonly string $createdAt;

    /**
     * Date de mise à jour (ISO 8601)
     */
    public readonly string $updatedAt;

    /**
     * Liste des articles (⚠️ IMPORTANT : les UUIDs des items sont nécessaires pour avoirs)
     *
     * @var array<InvoiceItemResponseDTO>
     */
    public readonly array $items;

    /**
     * Taxes personnalisées au niveau facture
     *
     * @var array<CustomTaxResponseDTO>
     */
    public readonly array $customTaxes;

    /**
     * Create a new InvoiceResponseDTO instance.
     */
    public function __construct(
        string $id,
        ?string $parentId,
        ?string $parentReference,
        string $token,
        string $reference,
        string $type,
        string $subtype,
        string $date,
        string $paymentMethod,
        string $status,
        int $amount,
        int $vatAmount,
        int $fiscalStamp,
        float $discount,
        ?string $clientNcc,
        string $clientCompanyName,
        string $clientPhone,
        string $clientEmail,
        ?string $clientTerminal,
        ?string $clientMerchantName,
        ?string $clientRccm,
        ?string $clientSellerName,
        string $clientEstablishment,
        string $clientPointOfSale,
        string $template,
        ?string $description,
        ?string $footer,
        ?string $commercialMessage,
        ?string $foreignCurrency,
        float $foreignCurrencyRate,
        bool $isRne,
        ?string $rne,
        string $source,
        string $createdAt,
        string $updatedAt,
        array $items = [],
        array $customTaxes = []
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->parentReference = $parentReference;
        $this->token = $token;
        $this->reference = $reference;
        $this->type = $type;
        $this->subtype = $subtype;
        $this->date = $date;
        $this->paymentMethod = $paymentMethod;
        $this->status = $status;
        $this->amount = $amount;
        $this->vatAmount = $vatAmount;
        $this->fiscalStamp = $fiscalStamp;
        $this->discount = $discount;
        $this->clientNcc = $clientNcc;
        $this->clientCompanyName = $clientCompanyName;
        $this->clientPhone = $clientPhone;
        $this->clientEmail = $clientEmail;
        $this->clientTerminal = $clientTerminal;
        $this->clientMerchantName = $clientMerchantName;
        $this->clientRccm = $clientRccm;
        $this->clientSellerName = $clientSellerName;
        $this->clientEstablishment = $clientEstablishment;
        $this->clientPointOfSale = $clientPointOfSale;
        $this->template = $template;
        $this->description = $description;
        $this->footer = $footer;
        $this->commercialMessage = $commercialMessage;
        $this->foreignCurrency = $foreignCurrency;
        $this->foreignCurrencyRate = $foreignCurrencyRate;
        $this->isRne = $isRne;
        $this->rne = $rne;
        $this->source = $source;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->items = $items;
        $this->customTaxes = $customTaxes;
    }

    /**
     * Créer une instance depuis un array.
     *
     * @param  array<string, mixed>  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = InvoiceItemResponseDTO::fromArray($item);
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
            $data['parentId'] ?? $data['parent_id'] ?? null,
            $data['parentReference'] ?? $data['parent_reference'] ?? null,
            $data['token'] ?? '',
            $data['reference'] ?? '',
            $data['type'] ?? '',
            $data['subtype'] ?? '',
            $data['date'] ?? '',
            $data['paymentMethod'] ?? $data['payment_method'] ?? '',
            $data['status'] ?? '',
            (int) ($data['amount'] ?? 0),
            (int) ($data['vatAmount'] ?? $data['vat_amount'] ?? 0),
            (int) ($data['fiscalStamp'] ?? $data['fiscal_stamp'] ?? 0),
            (float) ($data['discount'] ?? 0),
            $data['clientNcc'] ?? $data['client_ncc'] ?? null,
            $data['clientCompanyName'] ?? $data['client_company_name'] ?? '',
            $data['clientPhone'] ?? $data['client_phone'] ?? '',
            $data['clientEmail'] ?? $data['client_email'] ?? '',
            $data['clientTerminal'] ?? $data['client_terminal'] ?? null,
            $data['clientMerchantName'] ?? $data['client_merchant_name'] ?? null,
            $data['clientRccm'] ?? $data['client_rccm'] ?? null,
            $data['clientSellerName'] ?? $data['client_seller_name'] ?? null,
            $data['clientEstablishment'] ?? $data['client_establishment'] ?? '',
            $data['clientPointOfSale'] ?? $data['client_point_of_sale'] ?? '',
            $data['template'] ?? '',
            $data['description'] ?? null,
            $data['footer'] ?? null,
            $data['commercialMessage'] ?? $data['commercial_message'] ?? null,
            $data['foreignCurrency'] ?? $data['foreign_currency'] ?? null,
            (float) ($data['foreignCurrencyRate'] ?? $data['foreign_currency_rate'] ?? 0),
            (bool) ($data['isRne'] ?? $data['is_rne'] ?? false),
            $data['rne'] ?? null,
            $data['source'] ?? '',
            $data['createdAt'] ?? $data['created_at'] ?? '',
            $data['updatedAt'] ?? $data['updated_at'] ?? '',
            $items,
            $customTaxes
        );
    }

    /**
     * Retourne le montant total TTC en FCFA (divise par 100).
     *
     * @return float
     */
    public function getAmountInFCFA(): float
    {
        return $this->amount / 100;
    }

    /**
     * Retourne le montant TVA en FCFA (divise par 100).
     *
     * @return float
     */
    public function getVatAmountInFCFA(): float
    {
        return $this->vatAmount / 100;
    }
}

