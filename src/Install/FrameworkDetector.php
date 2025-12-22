<?php

namespace Neocode\FNE\Install;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Détecteur de framework
 */
class FrameworkDetector
{
    /**
     * Détecter le framework utilisé.
     */
    public function detect(): FrameworkType
    {
        // Détecter Laravel
        if ($this->isLaravel()) {
            return FrameworkType::LARAVEL;
        }

        // Détecter Symfony
        if ($this->isSymfony()) {
            return FrameworkType::SYMFONY;
        }

        // Par défaut, PHP natif
        return FrameworkType::PHP;
    }

    /**
     * Vérifier si Laravel est présent.
     */
    public function isLaravel(): bool
    {
        return (class_exists(Application::class)
            || class_exists(Facade::class))
            && file_exists(getcwd() . '/artisan');
    }

    /**
     * Vérifier si Symfony est présent.
     */
    public function isSymfony(): bool
    {
        return class_exists(Kernel::class)
            && file_exists(getcwd() . '/bin/console');
    }

    /**
     * Obtenir la version de Laravel si disponible.
     */
    public function getLaravelVersion(): ?string
    {
        if (!$this->isLaravel()) {
            return null;
        }

        if (class_exists(Application::class)) {
            return Application::VERSION ?? null;
        }

        return null;
    }

    /**
     * Obtenir la version de Symfony si disponible.
     */
    public function getSymfonyVersion(): ?string
    {
        if (!$this->isSymfony()) {
            return null;
        }

        if (class_exists(Kernel::class)) {
            return Kernel::VERSION ?? null;
        }

        return null;
    }

    /**
     * Détecter et afficher le message d'installation (utilisé dans les scripts Composer).
     */
    public static function detectAndDisplay(): void
    {
        $detector = new self();
        $framework = $detector->detect();

        echo PHP_EOL;
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
        echo "  FNE Client - Framework détecté : " . $framework->getDescription() . PHP_EOL;
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . PHP_EOL;
        echo PHP_EOL;
        echo "  Pour installer et configurer le package, exécutez :" . PHP_EOL;
        echo "  " . $framework->getInstallCommand() . PHP_EOL;
        echo PHP_EOL;
    }
}

