<?php

namespace Neocode\FNE\Install;

/**
 * Type de framework détecté
 */
enum FrameworkType: string
{
    case LARAVEL = 'laravel';
    case SYMFONY = 'symfony';
    case PHP = 'php';

    /**
     * Get the installation command for this framework type.
     */
    public function getInstallCommand(): string
    {
        return match ($this) {
            self::LARAVEL => 'php artisan fne:install',
            self::SYMFONY => 'php bin/console fne:install',
            self::PHP => 'php vendor/bin/fne-install',
        };
    }

    /**
     * Get the configuration file path for this framework type.
     */
    public function getConfigPath(): string
    {
        return match ($this) {
            self::LARAVEL => 'config/fne.php',
            self::SYMFONY => '.env',
            self::PHP => 'fne.php',
        };
    }

    /**
     * Get a human-readable description of the framework.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::LARAVEL => 'Laravel',
            self::SYMFONY => 'Symfony',
            self::PHP => 'PHP natif',
        };
    }
}

