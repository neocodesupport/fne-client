#!/usr/bin/env php
<?php

/**
 * Script exécuté après l'installation/mise à jour du package via Composer
 * Détecte et affiche le framework utilisé
 */

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require $autoloadPath;
    
    try {
        \Neocode\FNE\Install\FrameworkDetector::detectAndDisplay();
    } catch (\Throwable $e) {
        // Ignorer les erreurs pendant l'installation/mise à jour
        // pour ne pas bloquer le processus Composer
    }
}
