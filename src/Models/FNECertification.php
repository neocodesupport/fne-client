<?php

namespace Neocode\FNE\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour la table fne_certifications
 *
 * @package Neocode\FNE\Models
 */
class FNECertification extends Model
{
    /**
     * Nom de la table
     *
     * @var string
     */
    protected $table = 'fne_certifications';

    /**
     * Indiquer que les IDs ne sont pas auto-incrémentés (utiliser UUID)
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Type de la clé primaire
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fne_invoice_id',
        'reference',
        'ncc',
        'token',
        'type',
        'subtype',
        'status',
        'template',
        'client_company_name',
        'client_ncc',
        'client_phone',
        'client_email',
        'amount',
        'vat_amount',
        'fiscal_stamp',
        'discount',
        'is_rne',
        'rne',
        'source',
        'warning',
        'balance_sticker',
        'fne_date',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'vat_amount' => 'integer',
        'fiscal_stamp' => 'integer',
        'discount' => 'decimal:2',
        'is_rne' => 'boolean',
        'warning' => 'boolean',
        'balance_sticker' => 'integer',
        'fne_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
