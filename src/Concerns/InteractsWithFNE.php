<?php

namespace Neocode\FNE\Concerns;

use Neocode\FNE\FNEClient;

/**
 * Trait pour faciliter l'utilisation du client FNE dans les controllers
 *
 * @package Neocode\FNE\Concerns
 */
trait InteractsWithFNE
{
    /**
     * Obtenir une instance du client FNE.
     *
     * @return FNEClient
     */
    protected function fne(): FNEClient
    {
        return app(FNEClient::class);
    }
}

