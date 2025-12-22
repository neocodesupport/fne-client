<?php

namespace Neocode\FNE\Mappers;

/**
 * Mapper pour les avoirs
 *
 * @package Neocode\FNE\Mappers
 */
class RefundMapper extends BaseMapper
{
    /**
     * Effectuer le mapping spécifique pour les avoirs.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function doMap(array $data): array
    {
        // Les avoirs ont une structure simplifiée
        // On retourne directement les items car c'est ce qui est envoyé à l'API
        return [
            'items' => $this->mapItems($data['items'] ?? []),
        ];
    }

    /**
     * Mapper les items de l'avoir.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mapItems(array $items): array
    {
        return array_map(function ($item) {
            $mappedItem = [
                'id' => $this->normalizeUuid($item['id'] ?? ''),
                'quantity' => $this->normalizeQuantity($item['quantity'] ?? 1),
            ];

            // Valider que l'ID est un UUID valide
            if (!$this->isValidUuid($mappedItem['id'])) {
                throw new \InvalidArgumentException('Invalid UUID for refund item: ' . $mappedItem['id']);
            }

            return $mappedItem;
        }, $items);
    }

    /**
     * Normaliser un UUID.
     *
     * @param  mixed  $uuid
     * @return string
     */
    protected function normalizeUuid(mixed $uuid): string
    {
        $uuid = (string) $uuid;

        // Supprimer les espaces
        $uuid = trim($uuid);

        return $uuid;
    }

    /**
     * Vérifier si une chaîne est un UUID valide.
     *
     * @param  string  $uuid
     * @return bool
     */
    protected function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Normaliser la quantité.
     *
     * @param  mixed  $quantity
     * @return float
     */
    protected function normalizeQuantity(mixed $quantity): float
    {
        // Le mapper ne valide pas la quantité, c'est le rôle du validator
        // On normalise juste le type
        return (float) $quantity;
    }
}

