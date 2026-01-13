-- Migration SQL pour créer la table fne_certifications
-- Compatible avec MySQL, PostgreSQL, SQLite
--
-- Cette table permet de stocker les informations des factures certifiées
-- par l'API FNE, notamment les UUIDs nécessaires pour créer des avoirs futurs.
--
-- Usage:
--   MySQL: mysql -u user -p database < fne_certifications.sql
--   PostgreSQL: psql -U user -d database -f fne_certifications.sql
--   SQLite: sqlite3 database.db < fne_certifications.sql

-- Table principale pour les certifications FNE
CREATE TABLE IF NOT EXISTS fne_certifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'ID interne',
    fne_invoice_id VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID FNE de la facture (⚠️ IMPORTANT pour avoirs)',
    reference VARCHAR(255) NOT NULL UNIQUE COMMENT 'Référence FNE (ex: 9606123E25000000019)',
    ncc VARCHAR(50) NOT NULL COMMENT 'Numéro Contribuable',
    token TEXT NOT NULL COMMENT 'Token de vérification QR code',
    type ENUM('invoice', 'refund') NOT NULL DEFAULT 'invoice' COMMENT 'Type de document',
    subtype ENUM('normal', 'refund') NOT NULL DEFAULT 'normal' COMMENT 'Sous-type',
    status ENUM('paid', 'pending') NOT NULL DEFAULT 'pending' COMMENT 'Statut paiement',
    template VARCHAR(10) NOT NULL COMMENT 'Template utilisé (B2C, B2B, B2F, B2G)',
    
    -- Informations client
    client_company_name VARCHAR(255) NULL,
    client_ncc VARCHAR(50) NULL,
    client_phone VARCHAR(50) NULL,
    client_email VARCHAR(255) NULL,
    
    -- Montants (en centimes)
    amount BIGINT NOT NULL DEFAULT 0 COMMENT 'Montant total TTC en centimes',
    vat_amount BIGINT NOT NULL DEFAULT 0 COMMENT 'Montant TVA en centimes',
    fiscal_stamp BIGINT NOT NULL DEFAULT 0 COMMENT 'Timbre fiscal en centimes',
    discount DECIMAL(5, 2) NOT NULL DEFAULT 0 COMMENT 'Remise globale en %',
    
    -- Métadonnées
    is_rne BOOLEAN NOT NULL DEFAULT FALSE,
    rne VARCHAR(50) NULL,
    source VARCHAR(20) NOT NULL DEFAULT 'api' COMMENT 'Source (api, mobile)',
    warning BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Alerte stock stickers',
    balance_sticker INT NOT NULL DEFAULT 0 COMMENT 'Nombre de stickers restants',
    
    -- Dates
    fne_date TIMESTAMP NULL COMMENT 'Date de la facture FNE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index pour performances
    INDEX idx_ncc (ncc),
    INDEX idx_client_ncc (client_ncc),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_fne_invoice_id (fne_invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table de stockage des certifications FNE';

-- Note: Pour PostgreSQL, remplacer:
--   - AUTO_INCREMENT par SERIAL
--   - ENUM par VARCHAR avec CHECK constraint
--   - ENGINE=InnoDB par rien
--   - ON UPDATE CURRENT_TIMESTAMP par trigger

-- Exemple pour PostgreSQL:
/*
CREATE TABLE IF NOT EXISTS fne_certifications (
    id BIGSERIAL PRIMARY KEY,
    fne_invoice_id VARCHAR(36) NOT NULL UNIQUE,
    reference VARCHAR(255) NOT NULL UNIQUE,
    ncc VARCHAR(50) NOT NULL,
    token TEXT NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'invoice' CHECK (type IN ('invoice', 'refund')),
    subtype VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (subtype IN ('normal', 'refund')),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('paid', 'pending')),
    template VARCHAR(10) NOT NULL,
    client_company_name VARCHAR(255),
    client_ncc VARCHAR(50),
    client_phone VARCHAR(50),
    client_email VARCHAR(255),
    amount BIGINT NOT NULL DEFAULT 0,
    vat_amount BIGINT NOT NULL DEFAULT 0,
    fiscal_stamp BIGINT NOT NULL DEFAULT 0,
    discount DECIMAL(5, 2) NOT NULL DEFAULT 0,
    is_rne BOOLEAN NOT NULL DEFAULT FALSE,
    rne VARCHAR(50),
    source VARCHAR(20) NOT NULL DEFAULT 'api',
    warning BOOLEAN NOT NULL DEFAULT FALSE,
    balance_sticker INTEGER NOT NULL DEFAULT 0,
    fne_date TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_ncc ON fne_certifications(ncc);
CREATE INDEX idx_client_ncc ON fne_certifications(client_ncc);
CREATE INDEX idx_type ON fne_certifications(type);
CREATE INDEX idx_status ON fne_certifications(status);
CREATE INDEX idx_created_at ON fne_certifications(created_at);
CREATE INDEX idx_fne_invoice_id ON fne_certifications(fne_invoice_id);
*/

