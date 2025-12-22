<?php

namespace Neocode\FNE\Providers;

use Illuminate\Support\ServiceProvider;
use Neocode\FNE\Cache\CacheFactory;
use Neocode\FNE\Config\FNEConfig;
use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Contracts\HttpClientInterface;
use Neocode\FNE\Contracts\LoggerInterface;
use Neocode\FNE\Features\FNEFeatures;
use Neocode\FNE\FNEClient;
use Neocode\FNE\Http\HttpClientFactory;

/**
 * Service Provider Laravel pour le package FNE Client
 *
 * @package Neocode\FNE\Providers
 */
class FNEServiceProvider extends ServiceProvider
{
    /**
     * Enregistrer les services.
     *
     * @return void
     */
    public function register(): void
    {
        // Publier la configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/fne.php',
            'fne'
        );

        // Enregistrer la configuration
        $this->app->singleton(FNEConfig::class, function ($app) {
            return new FNEConfig($app['config']['fne'] ?? []);
        });

        // Enregistrer le HTTP Client
        $this->app->singleton(HttpClientInterface::class, function ($app) {
            $config = $app->make(FNEConfig::class);
            $logger = $app->bound(LoggerInterface::class) ? $app->make(LoggerInterface::class) : null;

            return HttpClientFactory::create($config, $logger);
        });

        // Enregistrer le Cache (optionnel)
        $this->app->singleton(CacheInterface::class, function ($app) {
            $config = $app->make(FNEConfig::class);
            
            // Si le cache est désactivé, retourner un ArrayCache vide (ne sera pas utilisé)
            if (!$config->isCacheEnabled()) {
                return new \Neocode\FNE\Cache\ArrayCache();
            }

            // Essayer de créer un cache Laravel si disponible
            try {
                if (function_exists('app') && app()->bound(\Illuminate\Contracts\Cache\Repository::class)) {
                    $laravelCache = app()->make(\Illuminate\Contracts\Cache\Repository::class);
                    return \Neocode\FNE\Cache\CacheFactory::createLaravelCacheWithInstance($laravelCache);
                }
            } catch (\Throwable $e) {
                // Si on ne peut pas obtenir le cache Laravel, utiliser ArrayCache
            }

            // Fallback vers ArrayCache
            return CacheFactory::createArrayCache();
        });

        // Enregistrer le Logger (optionnel, utilise le logger Laravel si disponible)
        if ($this->app->bound(\Psr\Log\LoggerInterface::class)) {
            $this->app->singleton(LoggerInterface::class, function ($app) {
                return $app->make(\Psr\Log\LoggerInterface::class);
            });
        }

        // Enregistrer le client FNE principal
        $this->app->singleton(FNEClient::class, function ($app) {
            $httpClient = $app->make(HttpClientInterface::class);
            $config = $app->make(FNEConfig::class);
            $cache = $app->bound(CacheInterface::class) ? $app->make(CacheInterface::class) : null;
            $logger = $app->bound(LoggerInterface::class) ? $app->make(LoggerInterface::class) : null;

            return new FNEClient($httpClient, $config, $cache, $logger);
        });

        // Alias pour faciliter l'injection
        $this->app->alias(FNEClient::class, 'fne.client');
    }

    /**
     * Démarrer les services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publier la configuration
        $this->publishes([
            __DIR__ . '/../../config/fne.php' => config_path('fne.php'),
        ], 'fne-config');

        // Publier les migrations (optionnel)
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'fne-migrations');

        // Enregistrer les features Laravel Pennant
        if (class_exists(\Laravel\Pennant\Feature::class)) {
            FNEFeatures::define();
        }

        // Enregistrer les commandes
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Neocode\FNE\Commands\InstallCommand::class,
            ]);
        }
    }
}

