<?php

namespace Neocode\FNE\Commands\Symfony;

use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;
use Neocode\FNE\Install\FrameworkDetector;
use Neocode\FNE\Install\FrameworkType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Commande d'installation interactive pour Symfony
 *
 * @package Neocode\FNE\Commands\Symfony
 */
class InstallCommand extends Command
{
    /**
     * La signature de la commande.
     *
     * @var string
     */
    protected static $defaultName = 'fne:install';

    /**
     * La description de la commande.
     *
     * @var string
     */
    protected static $defaultDescription = 'Installe et configure le package FNE Client';

    /**
     * Exécuter la commande.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Vérifier que nous sommes bien dans un projet Symfony
        $detector = new FrameworkDetector();
        $framework = $detector->detect();

        if ($framework !== FrameworkType::SYMFONY) {
            error("⚠️  Cette commande est uniquement pour Symfony.");
            error("Framework détecté : {$framework->getDescription()}");
            error("Utilisez la commande appropriée : {$framework->getInstallCommand()}");
            return Command::FAILURE;
        }

        $output->writeln('');
        note('🚀 Installation du package FNE Client', 'Symfony');
        $output->writeln('');

        // 1. Configuration de l'API
        $output->writeln("Configuration de l'API FNE");
        $output->writeln(str_repeat('─', 50));

        $apiKey = text(
            label: 'Clé API FNE',
            placeholder: 'Entrez votre clé API',
            required: true,
            validate: fn($value) => empty(trim($value)) ? 'La clé API est requise' : null
        );

        $baseUrlChoice = select(
            label: 'URL de l\'API FNE',
            options: [
                'test' => 'Test : http://54.247.95.108/ws',
                'production' => 'Production : (à configurer après validation DGI)',
                'custom' => 'URL personnalisée',
            ],
            default: 'test'
        );

        $customUrl = null;
        if ($baseUrlChoice === 'custom') {
            $customUrl = text(
                label: 'URL personnalisée',
                placeholder: 'https://api.fne.example.com/ws',
                required: true,
                validate: fn($value) => empty(trim($value)) ? 'L\'URL est requise' : null
            );
        }

        $baseUrl = match ($baseUrlChoice) {
            'test' => 'http://54.247.95.108/ws',
            'production' => '',
            'custom' => $customUrl,
            default => 'http://54.247.95.108/ws',
        };

        $mode = $baseUrlChoice === 'production' ? 'production' : 'test';

        // 2. Configuration du cache
        $output->writeln('');
        $useCache = confirm(
            label: 'Activer le cache ?',
            default: true
        );

        // 3. Configuration des migrations
        $output->writeln('');
        $publishMigrations = confirm(
            label: 'Installer les migrations SQL pour la table fne_certifications ?',
            default: true
        );

        // 4. Génération de la configuration
        $output->writeln('');
        note('📝 Génération de la configuration...');

        // Configuration pour config/packages/fne.yaml
        $this->generateConfigYaml($apiKey, $baseUrl, $mode, $useCache);

        // Configuration pour .env
        $this->updateEnvFile($apiKey, $baseUrl, $mode, $useCache);

        // 5. Installation des migrations
        if ($publishMigrations) {
            $output->writeln('');
            note('📝 Installation des migrations SQL...');
            $this->installMigrations($output);
        }

        // 6. Résumé
        $output->writeln('');
        note('✅ Installation terminée avec succès !');
        $output->writeln('');
        $output->writeln('📚 Documentation : https://docs.neocode.com/fne-client');
        $output->writeln('💡 Exemple d\'utilisation :');
        $output->writeln('   use Neocode\\FNE\\Facades\\FNE;');
        $output->writeln('   $result = FNE::invoice()->sign($data);');
        $output->writeln('');

        return Command::SUCCESS;
    }

    /**
     * Générer le fichier de configuration YAML pour Symfony.
     *
     * @param  string  $apiKey
     * @param  string  $baseUrl
     * @param  string  $mode
     * @param  bool  $useCache
     * @return void
     */
    protected function generateConfigYaml(string $apiKey, string $baseUrl, string $mode, bool $useCache): void
    {
        $configDir = getcwd() . '/config/packages';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configPath = $configDir . '/fne.yaml';

        $config = [
            'fne' => [
                'api_key' => '%env(FNE_API_KEY)%',
                'base_url' => '%env(FNE_BASE_URL)%',
                'mode' => '%env(FNE_MODE)%',
                'timeout' => 30,
                'cache' => [
                    'enabled' => $useCache,
                    'ttl' => 3600,
                ],
                'locale' => 'fr',
                'features' => [
                    'enabled' => true,
                    'advanced_mapping' => true,
                    'batch_processing' => false,
                    'webhooks' => false,
                    'queue_jobs' => false,
                    'audit_logging' => true,
                    'auto_retry' => true,
                    'certification_table' => false,
                ],
            ],
        ];

        // Utiliser symfony/yaml si disponible, sinon générer du YAML manuellement
        if (class_exists(Yaml::class)) {
            $yamlContent = Yaml::dump($config, 4, 2);
        } else {
            // Génération manuelle de YAML simple
            $yamlContent = $this->generateYamlManually($config);
        }
        
        $yamlContent = "# Configuration FNE Client\n# Ce fichier a été généré automatiquement par le script d'installation.\n\n" . $yamlContent;

        file_put_contents($configPath, $yamlContent);
        note("✅ Configuration créée dans : {$configPath}");
    }

    /**
     * Mettre à jour le fichier .env.
     *
     * @param  string  $apiKey
     * @param  string  $baseUrl
     * @param  string  $mode
     * @param  bool  $useCache
     * @return void
     */
    protected function updateEnvFile(string $apiKey, string $baseUrl, string $mode, bool $useCache): void
    {
        $envPath = getcwd() . '/.env';

        if (!file_exists($envPath)) {
            note("⚠️  Fichier .env introuvable. Créez-le manuellement avec les variables suivantes :");
            echo "FNE_API_KEY={$apiKey}\n";
            echo "FNE_BASE_URL={$baseUrl}\n";
            echo "FNE_MODE={$mode}\n";
            echo "FNE_CACHE_ENABLED=" . ($useCache ? 'true' : 'false') . "\n";
            return;
        }

        $envContent = file_get_contents($envPath);
        $values = [
            'FNE_API_KEY' => $apiKey,
            'FNE_BASE_URL' => $baseUrl,
            'FNE_MODE' => $mode,
            'FNE_CACHE_ENABLED' => $useCache ? 'true' : 'false',
        ];

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
        note("✅ Fichier .env mis à jour");
    }

    /**
     * Installer les migrations SQL.
     *
     * @param  OutputInterface  $output
     * @return void
     */
    protected function installMigrations(OutputInterface $output): void
    {
        $sqlFile = __DIR__ . '/../../../database/migrations/fne_certifications.sql';
        $targetDir = getcwd() . '/database/migrations';
        $targetPath = $targetDir . '/fne_certifications.sql';

        // Créer le dossier database/migrations s'il n'existe pas
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error("❌ Impossible de créer le dossier : {$targetDir}");
                return;
            }
        }

        // Copier le fichier SQL
        if (!file_exists($sqlFile)) {
            error("❌ Fichier de migration introuvable : {$sqlFile}");
            return;
        }

        if (copy($sqlFile, $targetPath) === false) {
                error("❌ Impossible de copier le fichier de migration vers : {$targetPath}");
            return;
        }

        note("✅ Migration SQL copiée dans : {$targetPath}");
        note("💡 Exécutez cette migration dans votre base de données pour créer la table fne_certifications.");
    }

    /**
     * Générer du YAML manuellement si symfony/yaml n'est pas disponible.
     *
     * @param  array<string, mixed>  $config
     * @return string
     */
    protected function generateYamlManually(array $config): string
    {
        $yaml = '';
        foreach ($config as $key => $value) {
            $yaml .= $this->yamlEncode($key, $value, 0);
        }
        return $yaml;
    }

    /**
     * Encoder une valeur en YAML.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $indent
     * @return string
     */
    protected function yamlEncode(string $key, mixed $value, int $indent): string
    {
        $prefix = str_repeat('  ', $indent);
        $yaml = '';

        if (is_array($value)) {
            $yaml .= "{$prefix}{$key}:\n";
            foreach ($value as $k => $v) {
                $yaml .= $this->yamlEncode($k, $v, $indent + 1);
            }
        } else {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $yaml .= "{$prefix}{$key}: {$formattedValue}\n";
        }

        return $yaml;
    }
}

