<?php

namespace Neocode\FNE\Cache;

use Neocode\FNE\Contracts\CacheInterface;

/**
 * Implémentation de cache en mémoire (array) pour PHP natif
 *
 * @package Neocode\FNE\Cache
 */
class ArrayCache implements CacheInterface
{
    /**
     * Stockage en mémoire
     *
     * @var array<string, array{value: mixed, expires: int|null}>
     */
    protected array $storage = [];

    /**
     * Vérifier si une clé existe dans le cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!isset($this->storage[$key])) {
            return false;
        }

        $item = $this->storage[$key];

        // Vérifier l'expiration
        if ($item['expires'] !== null && $item['expires'] < time()) {
            unset($this->storage[$key]);

            return false;
        }

        return true;
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
        if (!$this->has($key)) {
            return $default;
        }

        return $this->storage[$key]['value'];
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
        $expires = $ttl !== null ? time() + $ttl : null;

        $this->storage[$key] = [
            'value' => $value,
            'expires' => $expires,
        ];

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
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    /**
     * Vider tout le cache.
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->storage = [];

        return true;
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
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
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

