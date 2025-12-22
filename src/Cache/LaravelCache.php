<?php

namespace Neocode\FNE\Cache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Neocode\FNE\Contracts\CacheInterface;

/**
 * Implémentation de cache utilisant Laravel Cache
 *
 * @package Neocode\FNE\Cache
 */
class LaravelCache implements CacheInterface
{
    /**
     * Repository de cache Laravel
     */
    protected CacheRepository $cache;

    /**
     * Create a new LaravelCache instance.
     *
     * @param  CacheRepository  $cache
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Vérifier si une clé existe dans le cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * Récupérer une valeur du cache.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    /**
     * Stocker une valeur dans le cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int|null  $ttl  Time to live en secondes (null = pas d'expiration)
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if ($ttl === null) {
            $this->cache->forever($key, $value);
        } else {
            $this->cache->put($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Supprimer une clé du cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->cache->forget($key);
    }

    /**
     * Vider tout le cache.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->cache->flush();
    }

    /**
     * Récupérer plusieurs valeurs du cache.
     *
     * @param  array<int, string>  $keys
     * @param  mixed  $default
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        return $this->cache->many($keys);
    }

    /**
     * Stocker plusieurs valeurs dans le cache.
     *
     * @param  array<string, mixed>  $values
     * @param  int|null  $ttl
     * @return bool
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Supprimer plusieurs clés du cache.
     *
     * @param  array<int, string>  $keys
     * @return bool
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }
}

