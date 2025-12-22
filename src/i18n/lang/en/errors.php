<?php

return [
    'validation' => [
        'required' => 'The :field field is required.',
        'email' => 'The :field field must be a valid email address.',
        'numeric' => 'The :field field must be a number.',
        'min' => 'The :field field must be at least :min.',
        'max' => 'The :field field must not exceed :max.',
        'in' => 'The :field field must be one of the following values: :values.',
        'array' => 'The :field field must be an array.',
        'string' => 'The :field field must be a string.',
        'boolean' => 'The :field field must be a boolean.',
        'uuid' => 'The :field field must be a valid UUID.',
    ],
    'api' => [
        'authentication_failed' => 'Authentication failed. Please check your API key.',
        'bad_request' => 'Bad request: :message',
        'server_error' => 'A server error occurred. Please try again later.',
        'not_found' => 'The requested resource was not found.',
        'timeout' => 'The request timed out. Please try again.',
        'network_error' => 'Network error. Please check your internet connection.',
    ],
    'mapping' => [
        'invalid_data' => 'The provided data cannot be mapped.',
        'missing_field' => 'The field :field is missing for mapping.',
        'invalid_type' => 'The data type for :field is invalid.',
    ],
    'invoice' => [
        'client_ncc_required' => 'Client NCC is required when template is B2B.',
        'rne_required' => 'RNE number is required when isRne is true.',
        'foreign_currency_rate_required' => 'Exchange rate is required when a foreign currency is provided.',
        'items_required' => 'At least one item is required for the invoice.',
        'taxes_required' => 'At least one tax is required for each item.',
    ],
    'refund' => [
        'invoice_not_found' => 'Invoice with ID :id was not found.',
        'items_required' => 'At least one item is required for the refund.',
        'invalid_item_id' => 'Item ID :id is invalid.',
    ],
];

