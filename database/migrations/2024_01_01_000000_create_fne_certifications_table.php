<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Laravel pour créer la table fne_certifications
 *
 * Cette table permet de stocker les informations des factures certifiées
 * par l'API FNE, notamment les UUIDs nécessaires pour créer des avoirs futurs.
 *
 * @package Neocode\FNE\Database\Migrations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('fne_certifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('fne_invoice_id')->unique()->comment('UUID FNE de la facture (⚠️ IMPORTANT pour avoirs)');
            $table->string('reference')->unique()->comment('Référence FNE (ex: 9606123E25000000019)');
            $table->string('ncc')->index()->comment('Numéro Contribuable');
            $table->string('token')->comment('Token de vérification QR code');
            $table->enum('type', ['invoice', 'refund'])->default('invoice')->comment('Type de document');
            $table->enum('subtype', ['normal', 'refund'])->default('normal')->comment('Sous-type');
            $table->enum('status', ['paid', 'pending'])->default('pending')->comment('Statut paiement');
            $table->string('template')->comment('Template utilisé (B2C, B2B, B2F, B2G)');
            
            // Informations client
            $table->string('client_company_name')->nullable();
            $table->string('client_ncc')->nullable()->index();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            
            // Montants (en centimes)
            $table->bigInteger('amount')->default(0)->comment('Montant total TTC en centimes');
            $table->bigInteger('vat_amount')->default(0)->comment('Montant TVA en centimes');
            $table->bigInteger('fiscal_stamp')->default(0)->comment('Timbre fiscal en centimes');
            $table->decimal('discount', 5, 2)->default(0)->comment('Remise globale en %');
            
            // Métadonnées
            $table->boolean('is_rne')->default(false);
            $table->string('rne')->nullable();
            $table->string('source')->default('api')->comment('Source (api, mobile)');
            $table->boolean('warning')->default(false)->comment('Alerte stock stickers');
            $table->integer('balance_sticker')->default(0)->comment('Nombre de stickers restants');
            
            // Dates
            $table->timestamp('fne_date')->nullable()->comment('Date de la facture FNE');
            $table->timestamps();
            
            // Index pour performances
            $table->index('created_at');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('fne_certifications');
    }
};

