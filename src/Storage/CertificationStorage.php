<?php

namespace Neocode\FNE\Storage;

use Neocode\FNE\DTOs\ResponseDTO;

/**
 * Classe pour gérer l'enregistrement des certifications dans la base de données
 * Compatible Laravel (Eloquent) et Symfony (Doctrine/SQL natif)
 *
 * @package Neocode\FNE\Storage
 */
class CertificationStorage
{
    /**
     * Enregistrer une certification dans la table fne_certifications.
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool  true si enregistré avec succès, false sinon
     */
    public static function save(ResponseDTO $response, array $invoiceData): bool
    {
        // Détecter le framework et utiliser la méthode appropriée
        if (self::isLaravel()) {
            return self::saveLaravel($response, $invoiceData);
        }

        if (self::isSymfony()) {
            return self::saveSymfony($response, $invoiceData);
        }

        // PHP natif - utiliser PDO directement
        return self::saveNativePHP($response, $invoiceData);
    }

    /**
     * Vérifier si on est dans Laravel.
     *
     * @return bool
     */
    protected static function isLaravel(): bool
    {
        return function_exists('app') && class_exists(\Illuminate\Database\Eloquent\Model::class);
    }

    /**
     * Vérifier si on est dans Symfony.
     *
     * @return bool
     */
    protected static function isSymfony(): bool
    {
        return class_exists(\Symfony\Component\HttpKernel\Kernel::class)
            || class_exists(\Doctrine\ORM\EntityManagerInterface::class);
    }

    /**
     * Enregistrer avec Laravel Eloquent.
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool
     */
    protected static function saveLaravel(ResponseDTO $response, array $invoiceData): bool
    {
        // Vérifier si le modèle existe
        if (!class_exists(\Neocode\FNE\Models\FNECertification::class)) {
            return false;
        }

        // Vérifier si on peut accéder à la base de données
        if (!function_exists('app') || !app()->bound('db')) {
            return false;
        }

        try {
            // Vérifier si la table existe
            if (!\Illuminate\Support\Facades\Schema::hasTable('fne_certifications')) {
                self::logInfo('FNE certifications table does not exist. Run migration: php artisan migrate', $response);
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        try {
            $invoice = $response->invoice;

            // Utiliser les montants directement depuis la réponse (déjà en centimes)
            $amount = $invoice ? $invoice->amount : 0;
            $vatAmount = $invoice ? $invoice->vatAmount : 0;
            $fiscalStamp = $invoice ? $invoice->fiscalStamp : 0;

            // Créer l'enregistrement avec Eloquent
            \Neocode\FNE\Models\FNECertification::create([
                'fne_invoice_id' => $invoice->id ?? null,
                'reference' => $response->reference,
                'ncc' => $response->ncc,
                'token' => $response->token,
                'type' => 'invoice',
                'subtype' => 'normal',
                'status' => $invoice->status ?? 'pending',
                'template' => $invoiceData['template'] ?? 'B2C',
                'client_company_name' => $invoiceData['clientCompanyName'] ?? null,
                'client_ncc' => $invoiceData['clientNcc'] ?? null,
                'client_phone' => $invoiceData['clientPhone'] ?? null,
                'client_email' => $invoiceData['clientEmail'] ?? null,
                'amount' => $amount,
                'vat_amount' => $vatAmount,
                'fiscal_stamp' => $fiscalStamp,
                'discount' => $invoiceData['discount'] ?? 0,
                'is_rne' => $invoiceData['isRne'] ?? false,
                'rne' => $invoiceData['rne'] ?? null,
                'source' => 'api',
                'warning' => $response->warning,
                'balance_sticker' => $response->balanceSticker,
                'fne_date' => $invoice->date ?? now(),
            ]);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            self::logWarning('Failed to save FNE certification to table (database error)', $response, $e);
            return false;
        } catch (\Throwable $e) {
            self::logWarning('Failed to save FNE certification to table', $response, $e);
            return false;
        }
    }

    /**
     * Enregistrer avec Symfony (Doctrine ou SQL natif).
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool
     */
    protected static function saveSymfony(ResponseDTO $response, array $invoiceData): bool
    {
        try {
            // Essayer d'utiliser Doctrine EntityManager si disponible
            if (class_exists(\Doctrine\ORM\EntityManagerInterface::class)) {
                return self::saveSymfonyDoctrine($response, $invoiceData);
            }

            // Fallback : utiliser PDO/SQL natif
            return self::saveSymfonyPDO($response, $invoiceData);
        } catch (\Throwable $e) {
            self::logWarning('Failed to save FNE certification to table (Symfony)', $response, $e);
            return false;
        }
    }

    /**
     * Enregistrer avec Doctrine EntityManager (Symfony).
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool
     */
    protected static function saveSymfonyDoctrine(ResponseDTO $response, array $invoiceData): bool
    {
        // Vérifier si on peut obtenir le container Symfony
        if (!class_exists(\Symfony\Component\DependencyInjection\ContainerInterface::class)) {
            return false;
        }

        global $kernel;
        if (!isset($kernel) || !method_exists($kernel, 'getContainer')) {
            return false;
        }

        $container = $kernel->getContainer();
        if (!$container->has(\Doctrine\ORM\EntityManagerInterface::class)) {
            return false;
        }

        try {
            $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
            $connection = $em->getConnection();

            // Vérifier si la table existe (méthode compatible Doctrine DBAL 2.x et 3.x)
            try {
                $schemaManager = method_exists($connection, 'createSchemaManager')
                    ? $connection->createSchemaManager()
                    : $connection->getSchemaManager();
                
                if (!$schemaManager->tablesExist(['fne_certifications'])) {
                    self::logInfo('FNE certifications table does not exist. Run migration: php bin/console doctrine:migrations:migrate', $response);
                    return false;
                }
            } catch (\Throwable $e) {
                // Si on ne peut pas vérifier, on essaie quand même d'insérer
                // L'erreur sera capturée plus bas si la table n'existe pas
            }

            $invoice = $response->invoice;
            $amount = $invoice ? $invoice->amount : 0;
            $vatAmount = $invoice ? $invoice->vatAmount : 0;
            $fiscalStamp = $invoice ? $invoice->fiscalStamp : 0;

            // Préparer les données pour l'insertion
            $data = [
                'fne_invoice_id' => $invoice->id ?? null,
                'reference' => $response->reference,
                'ncc' => $response->ncc,
                'token' => $response->token,
                'type' => 'invoice',
                'subtype' => 'normal',
                'status' => $invoice->status ?? 'pending',
                'template' => $invoiceData['template'] ?? 'B2C',
                'client_company_name' => $invoiceData['clientCompanyName'] ?? null,
                'client_ncc' => $invoiceData['clientNcc'] ?? null,
                'client_phone' => $invoiceData['clientPhone'] ?? null,
                'client_email' => $invoiceData['clientEmail'] ?? null,
                'amount' => $amount,
                'vat_amount' => $vatAmount,
                'fiscal_stamp' => $fiscalStamp,
                'discount' => $invoiceData['discount'] ?? 0,
                'is_rne' => $invoiceData['isRne'] ?? false,
                'rne' => $invoiceData['rne'] ?? null,
                'source' => 'api',
                'warning' => $response->warning,
                'balance_sticker' => $response->balanceSticker,
                'fne_date' => $invoice->date ?? (new \DateTime())->format('Y-m-d H:i:s'),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];

            // Construire la requête SQL
            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);
            $sql = 'INSERT INTO fne_certifications (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

            $stmt = $connection->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->executeStatement();

            return true;
        } catch (\Doctrine\DBAL\Exception $e) {
            self::logWarning('Failed to save FNE certification to table (Doctrine error)', $response, $e);
            return false;
        } catch (\Throwable $e) {
            self::logWarning('Failed to save FNE certification to table (Symfony Doctrine)', $response, $e);
            return false;
        }
    }

    /**
     * Enregistrer avec PDO (Symfony fallback).
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool
     */
    protected static function saveSymfonyPDO(ResponseDTO $response, array $invoiceData): bool
    {
        // Pour Symfony sans Doctrine, on ne peut pas facilement vérifier l'existence de la table
        // On essaie d'insérer et on gère l'erreur si la table n'existe pas
        try {
            global $kernel;
            if (!isset($kernel) || !method_exists($kernel, 'getContainer')) {
                return false;
            }

            $container = $kernel->getContainer();
            if (!$container->has('doctrine.dbal.default_connection')) {
                return false;
            }

            $connection = $container->get('doctrine.dbal.default_connection');
            $invoice = $response->invoice;
            $amount = $invoice ? $invoice->amount : 0;
            $vatAmount = $invoice ? $invoice->vatAmount : 0;
            $fiscalStamp = $invoice ? $invoice->fiscalStamp : 0;

            $data = [
                'fne_invoice_id' => $invoice->id ?? null,
                'reference' => $response->reference,
                'ncc' => $response->ncc,
                'token' => $response->token,
                'type' => 'invoice',
                'subtype' => 'normal',
                'status' => $invoice->status ?? 'pending',
                'template' => $invoiceData['template'] ?? 'B2C',
                'client_company_name' => $invoiceData['clientCompanyName'] ?? null,
                'client_ncc' => $invoiceData['clientNcc'] ?? null,
                'client_phone' => $invoiceData['clientPhone'] ?? null,
                'client_email' => $invoiceData['clientEmail'] ?? null,
                'amount' => $amount,
                'vat_amount' => $vatAmount,
                'fiscal_stamp' => $fiscalStamp,
                'discount' => $invoiceData['discount'] ?? 0,
                'is_rne' => $invoiceData['isRne'] ? 1 : 0,
                'rne' => $invoiceData['rne'] ?? null,
                'source' => 'api',
                'warning' => $response->warning ? 1 : 0,
                'balance_sticker' => $response->balanceSticker,
                'fne_date' => $invoice->date ?? (new \DateTime())->format('Y-m-d H:i:s'),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];

            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);
            $sql = 'INSERT INTO fne_certifications (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

            $stmt = $connection->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->executeStatement();

            return true;
        } catch (\Throwable $e) {
            // Si c'est une erreur de table inexistante, logger un message informatif
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'does not exist')) {
                self::logInfo('FNE certifications table does not exist. Create it using the SQL migration file.', $response);
            } else {
                self::logWarning('Failed to save FNE certification to table (PDO error)', $response, $e);
            }
            return false;
        }
    }

    /**
     * Enregistrer avec PHP natif (PDO).
     *
     * @param  ResponseDTO  $response
     * @param  array<string, mixed>  $invoiceData
     * @return bool
     */
    protected static function saveNativePHP(ResponseDTO $response, array $invoiceData): bool
    {
        try {
            // Essayer d'obtenir une connexion PDO depuis les variables d'environnement
            $pdo = self::getNativePDOConnection();
            if (!$pdo) {
                return false;
            }

            $invoice = $response->invoice;
            $amount = $invoice ? $invoice->amount : 0;
            $vatAmount = $invoice ? $invoice->vatAmount : 0;
            $fiscalStamp = $invoice ? $invoice->fiscalStamp : 0;

            // Vérifier si la table existe (pour MySQL, PostgreSQL, SQLite)
            if (!self::tableExistsNative($pdo, 'fne_certifications')) {
                self::logInfo('FNE certifications table does not exist. Create it using the SQL migration file.', $response);
                return false;
            }

            $data = [
                'fne_invoice_id' => $invoice->id ?? null,
                'reference' => $response->reference,
                'ncc' => $response->ncc,
                'token' => $response->token,
                'type' => 'invoice',
                'subtype' => 'normal',
                'status' => $invoice->status ?? 'pending',
                'template' => $invoiceData['template'] ?? 'B2C',
                'client_company_name' => $invoiceData['clientCompanyName'] ?? null,
                'client_ncc' => $invoiceData['clientNcc'] ?? null,
                'client_phone' => $invoiceData['clientPhone'] ?? null,
                'client_email' => $invoiceData['clientEmail'] ?? null,
                'amount' => $amount,
                'vat_amount' => $vatAmount,
                'fiscal_stamp' => $fiscalStamp,
                'discount' => $invoiceData['discount'] ?? 0,
                'is_rne' => $invoiceData['isRne'] ? 1 : 0,
                'rne' => $invoiceData['rne'] ?? null,
                'source' => 'api',
                'warning' => $response->warning ? 1 : 0,
                'balance_sticker' => $response->balanceSticker,
                'fne_date' => $invoice->date ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);
            $sql = 'INSERT INTO fne_certifications (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

            $stmt = $pdo->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            // Si c'est une erreur de table inexistante, logger un message informatif
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'does not exist')) {
                self::logInfo('FNE certifications table does not exist. Create it using the SQL migration file.', $response);
            } else {
                self::logWarning('Failed to save FNE certification to table (PDO error)', $response, $e);
            }
            return false;
        } catch (\Throwable $e) {
            self::logWarning('Failed to save FNE certification to table (PHP native)', $response, $e);
            return false;
        }
    }

    /**
     * Obtenir une connexion PDO pour PHP natif.
     *
     * @return \PDO|null
     */
    protected static function getNativePDOConnection(): ?\PDO
    {
        // Essayer d'obtenir les informations de connexion depuis les variables d'environnement
        $driver = $_ENV['DB_DRIVER'] ?? $_ENV['DB_CONNECTION'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? null;
        $database = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? null;
        $username = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

        if (!$database) {
            return null;
        }

        try {
            // Construire le DSN selon le driver
            $dsn = match ($driver) {
                'mysql' => sprintf('mysql:host=%s%s;dbname=%s;charset=utf8mb4', $host, $port ? ';port=' . $port : '', $database),
                'pgsql', 'postgresql' => sprintf('pgsql:host=%s%s;dbname=%s', $host, $port ? ';port=' . $port : '', $database),
                'sqlite', 'sqlite3' => sprintf('sqlite:%s', $database),
                default => null,
            };

            if (!$dsn) {
                return null;
            }

            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            return $pdo;
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Vérifier si une table existe (PHP natif avec PDO).
     *
     * @param  \PDO  $pdo
     * @param  string  $tableName
     * @return bool
     */
    protected static function tableExistsNative(\PDO $pdo, string $tableName): bool
    {
        try {
            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

            return match ($driver) {
                'mysql' => self::tableExistsMySQL($pdo, $tableName),
                'pgsql' => self::tableExistsPostgreSQL($pdo, $tableName),
                'sqlite' => self::tableExistsSQLite($pdo, $tableName),
                default => false,
            };
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Vérifier si une table existe (MySQL).
     *
     * @param  \PDO  $pdo
     * @param  string  $tableName
     * @return bool
     */
    protected static function tableExistsMySQL(\PDO $pdo, string $tableName): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
            $stmt->execute([$tableName]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Vérifier si une table existe (PostgreSQL).
     *
     * @param  \PDO  $pdo
     * @param  string  $tableName
     * @return bool
     */
    protected static function tableExistsPostgreSQL(\PDO $pdo, string $tableName): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?)");
            $stmt->execute([$tableName]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Vérifier si une table existe (SQLite).
     *
     * @param  \PDO  $pdo
     * @param  string  $tableName
     * @return bool
     */
    protected static function tableExistsSQLite(\PDO $pdo, string $tableName): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$tableName]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Logger un message d'information.
     *
     * @param  string  $message
     * @param  ResponseDTO  $response
     * @return void
     */
    protected static function logInfo(string $message, ResponseDTO $response): void
    {
        if (function_exists('app') && app()->bound(\Psr\Log\LoggerInterface::class)) {
            app(\Psr\Log\LoggerInterface::class)->info($message, [
                'reference' => $response->reference ?? null,
            ]);
        } elseif (class_exists(\Psr\Log\LoggerInterface::class)) {
            // Symfony logger
            global $kernel;
            if (isset($kernel) && method_exists($kernel, 'getContainer')) {
                $container = $kernel->getContainer();
                if ($container->has('logger')) {
                    $logger = $container->get('logger');
                    $logger->info($message, ['reference' => $response->reference ?? null]);
                }
            }
        }
    }

    /**
     * Logger un message d'avertissement.
     *
     * @param  string  $message
     * @param  ResponseDTO  $response
     * @param  \Throwable|null  $exception
     * @return void
     */
    protected static function logWarning(string $message, ResponseDTO $response, ?\Throwable $exception = null): void
    {
        $context = [
            'error' => $exception ? $exception->getMessage() : null,
            'reference' => $response->reference ?? null,
        ];

        if ($exception instanceof \Illuminate\Database\QueryException) {
            $context['sql_state'] = $exception->getCode();
            $context['hint'] = 'Make sure the fne_certifications table exists. Run: php artisan migrate';
        } elseif ($exception && (str_contains($exception->getMessage(), "doesn't exist") || str_contains($exception->getMessage(), 'does not exist'))) {
            $context['hint'] = 'Make sure the fne_certifications table exists. Run migration or create table manually.';
        }

        if (function_exists('app') && app()->bound(\Psr\Log\LoggerInterface::class)) {
            app(\Psr\Log\LoggerInterface::class)->warning($message, $context);
        } elseif (class_exists(\Psr\Log\LoggerInterface::class)) {
            // Symfony logger
            global $kernel;
            if (isset($kernel) && method_exists($kernel, 'getContainer')) {
                $container = $kernel->getContainer();
                if ($container->has('logger')) {
                    $logger = $container->get('logger');
                    $logger->warning($message, $context);
                }
            }
        }
    }
}
