<?php

return [
    'validation' => [
        'required' => 'Le champ :field est requis.',
        'email' => 'Le champ :field doit être une adresse email valide.',
        'numeric' => 'Le champ :field doit être un nombre.',
        'min' => 'Le champ :field doit être au moins :min.',
        'max' => 'Le champ :field ne doit pas dépasser :max.',
        'in' => 'Le champ :field doit être l\'une des valeurs suivantes : :values.',
        'array' => 'Le champ :field doit être un tableau.',
        'string' => 'Le champ :field doit être une chaîne de caractères.',
        'boolean' => 'Le champ :field doit être un booléen.',
        'uuid' => 'Le champ :field doit être un UUID valide.',
    ],
    'api' => [
        'authentication_failed' => 'Échec de l\'authentification. Vérifiez votre clé API.',
        'bad_request' => 'La requête est mal formée: :message',
        'server_error' => 'Une erreur serveur est survenue. Veuillez réessayer plus tard.',
        'not_found' => 'La ressource demandée n\'a pas été trouvée.',
        'timeout' => 'La requête a expiré. Veuillez réessayer.',
        'network_error' => 'Erreur réseau. Vérifiez votre connexion internet.',
    ],
    'mapping' => [
        'invalid_data' => 'Les données fournies ne peuvent pas être mappées.',
        'missing_field' => 'Le champ :field est manquant pour le mapping.',
        'invalid_type' => 'Le type de données pour :field est invalide.',
    ],
    'invoice' => [
        'client_ncc_required' => 'Le NCC client est requis lorsque le template est B2B.',
        'rne_required' => 'Le numéro RNE est requis lorsque isRne est true.',
        'foreign_currency_rate_required' => 'Le taux de change est requis lorsque une devise étrangère est fournie.',
        'items_required' => 'Au moins un article est requis pour la facture.',
        'taxes_required' => 'Au moins une taxe est requise pour chaque article.',
    ],
    'refund' => [
        'invoice_not_found' => 'La facture avec l\'ID :id n\'a pas été trouvée.',
        'items_required' => 'Au moins un article est requis pour l\'avoir.',
        'invalid_item_id' => 'L\'ID de l\'article :id est invalide.',
    ],
];

