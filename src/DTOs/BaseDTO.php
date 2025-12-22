<?php

namespace Neocode\FNE\DTOs;

/**
 * Classe de base pour tous les DTOs
 *
 * Fournit des méthodes communes pour la sérialisation et la conversion en array.
 *
 * @package Neocode\FNE\DTOs
 */
abstract class BaseDTO
{
    /**
     * Convertir le DTO en array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $data = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            if ($value instanceof BaseDTO) {
                $data[$property->getName()] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$property->getName()] = array_map(
                    fn($item) => $item instanceof BaseDTO ? $item->toArray() : $item,
                    $value
                );
            } elseif ($value !== null) {
                $data[$property->getName()] = $value;
            }
        }

        return $data;
    }

    /**
     * Sérialiser le DTO en JSON.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

