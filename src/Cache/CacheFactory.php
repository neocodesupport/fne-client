<?php

namespace Neocode\FNE\Cache;

use Neocode\FNE\Contracts\CacheInterface;
use Neocode\FNE\Install\FrameworkDetector;

/**
 * Factory pour créer les implémentations de cache appropriées
 *
 * @package Neocode\FNE\Cache
 */
class CacheFactory
{
    /**
     * Créer une instance de cache basée sur le framework détecté.
     *
     * @return CacheInterface|null  null si aucun cache n'est disponible
     */
    public static function create(): ?CacheInterface
    {
        $detector = new FrameworkDetector();
        $framework = $detector->detect();

        return match ($framework) {
            \Neocode\FNE\Install\FrameworkType::LARAVEL => self::createLaravelCache(),
            default => self::createArrayCache(),
        };
    }

    /**
     * Créer un cache Laravel.
     *
     * @return CacheInterface|null
     */
    protected static function createLaravelCache(): ?CacheInterface
    {
        // Vérifier si Laravel Cache est disponible
        if (!class_exists(\Illuminate\Contracts\Cache\Repository::class)) {
            return self::createArrayCache();
        }

        try {
            // Essayer d'obtenir le cache Laravel via le service container
            if (function_exists('app')) {
                $cache = app(\Illuminate\Contracts\Cache\Repository::class);

                return new LaravelCache($cache);
            }
        } catch (\Throwable $e) {
            // Si on ne peut pas obtenir le cache Laravel, utiliser ArrayCache
        }

        return self::createArrayCache();
    }

    /**
     * Créer un cache en mémoire (array).
     *
     * @return CacheInterface
     */
    public static function createArrayCache(): CacheInterface
    {
        return new ArrayCache();
    }

    /**
     * Créer un cache Laravel avec une instance de cache fournie.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return CacheInterface
     */
    public static function createLaravelCacheWithInstance(\Illuminate\Contracts\Cache\Repository $cache): CacheInterface
    {
        return new LaravelCache($cache);
    }
}

