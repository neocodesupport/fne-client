<?php

namespace Neocode\FNE\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Neocode\FNE\Install\FrameworkDetector;
use Neocode\FNE\Install\FrameworkType;

/**
 * Commande d'installation interactive pour Laravel
 *
 * @package Neocode\FNE\Commands
 */
class InstallCommand extends Command
{
    /**
     * La signature de la commande.
     *
     * @var string
     */
    protected $signature = 'fne:install';

    /**
     * La description de la commande.
     *
     * @var string
     */
    protected $description = 'Installe et configure le package FNE Client';

    /**
     * Exécuter la commande.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🚀 Installation du package FNE Client');
        $this->newLine();

        // Détecter le framework (devrait être Laravel)
        $detector = new FrameworkDetector();
        $framework = $detector->detect();
        if ($framework !== FrameworkType::LARAVEL) {
            $this->error('Cette commande est uniquement disponible pour Laravel.');
            return Command::FAILURE;
        }

        $this->info("Framework détecté : {$framework->value}");
        $this->newLine();

        // 1. Configuration de l'API
        $apiKey = $this->ask('Clé API FNE', env('FNE_API_KEY', ''));
        if (empty($apiKey)) {
            $this->error('La clé API est requise.');
            return Command::FAILURE;
        }

        $baseUrlChoice = $this->choice(
            'URL de l\'API FNE',
            [
                'test' => 'Test : https://fne-api-mock.test',
                'production' => 'Production : (à configurer après validation DGI)',
                'custom' => 'URL personnalisée',
            ],
            'test'
        );

        $baseUrl = match ($baseUrlChoice) {
            'test' => 'https://fne-api-mock.test',
            'production' => '',
            'custom' => $this->ask('URL personnalisée', ''),
            default => 'https://fne-api-mock.test',
        };

        $mode = $baseUrlChoice === 'production' ? 'production' : 'test';

        // 2. Configuration du cache
        $useCache = $this->confirm('Activer le cache ?', true);

        // 3. Configuration des migrations
        $publishMigrations = $this->confirm('Installer les migrations pour la table fne_certifications ?', true);

        // 4. Vérifier Laravel Pennant
        if (!class_exists(\Laravel\Pennant\Feature::class)) {
            $this->warn('Laravel Pennant n\'est pas installé. Installation recommandée pour la gestion modulaire.');
            $installPennant = $this->confirm('Installer Laravel Pennant maintenant ?', false);

            if ($installPennant) {
                $this->info('Installation de Laravel Pennant...');
                $this->call('composer', ['require', 'laravel/pennant']);
                $this->call('pennant:install');
                $this->call('migrate');
            }
        }

        // 5. Publier la configuration
        $this->info('📝 Publication de la configuration...');
        $this->call('vendor:publish', ['--tag' => 'fne-config']);

        // 6. Mettre à jour le fichier .env
        $this->updateEnvFile([
            'FNE_API_KEY' => $apiKey,
            'FNE_BASE_URL' => $baseUrl,
            'FNE_MODE' => $mode,
            'FNE_CACHE_ENABLED' => $useCache ? 'true' : 'false',
        ]);

        // 7. Publier les migrations si demandé
        if ($publishMigrations) {
            $this->info('📝 Publication des migrations...');
            $this->call('vendor:publish', ['--tag' => 'fne-migrations']);
            $this->info('Migrations publiées. Exécutez "php artisan migrate" pour les appliquer.');
        }

        $this->newLine();
        $this->info('✅ Installation terminée avec succès !');
        $this->newLine();
        $this->info('📚 Documentation : https://docs.neocode.com/fne-client');
        $this->info('💡 Exemple d\'utilisation :');
        $this->line('   use Neocode\\FNE\\Facades\\FNE;');
        $this->line('   $result = FNE::invoice()->sign($data);');

        return Command::SUCCESS;
    }

    /**
     * Mettre à jour le fichier .env avec les nouvelles valeurs.
     *
     * @param  array<string, string>  $values
     * @return void
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            // Chercher si la clé existe déjà
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                // Remplacer la valeur existante
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                // Ajouter la nouvelle clé
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }
}

