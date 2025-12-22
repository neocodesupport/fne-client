<?php

namespace Neocode\FNE\Contracts;

/**
 * Interface pour le cache (compatible PSR-16)
 */
interface CacheInterface
{
    /**
     * Récupérer une valeur du cache.
     *
     * @param  string  $key  Clé du cache
     * @param  mixed  $default  Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stocker une valeur dans le cache.
     *
     * @param  string  $key  Clé du cache
     * @param  mixed  $value  Valeur à stocker
     * @param  int|null  $ttl  Time to live en secondes (null = pas d'expiration)
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Supprimer une valeur du cache.
     *
     * @param  string  $key  Clé du cache
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Vider tout le cache.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Vérifier si une clé existe dans le cache.
     *
     * @param  string  $key  Clé du cache
     * @return bool
     */
    public function has(string $key): bool;
}

