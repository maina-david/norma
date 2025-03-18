<?php

return [
    'api' => [
        'auth' => [
            'user_not_exists' => 'There is no user account with this email address on the system. Please try your alternative company email address or contact support.',
        ],
        'invalid_uri' => [
            'filter_operand' => 'Please make sure the you use the correct operand for the given filters',
            'filter' => 'Please make sure the filter value is in the correct format.',
            'sort_field' => 'You cannot sort by that field.',
        ],
    ],
    'customer' => [
        'duplicate_norma' => 'A Norma Stream already exists with that integration ID for the given organisation',
    ],
    'requirements' => [
        'consequence' => [
            'duplicate_consequence' => 'A similar consequence already exists.',
        ],
    ],
    'storage' => [
        'unsupported_media_type' => 'This file type is not supported',
        'organisation_no_storage' => 'There is no storage available for this organisation.',
    ],
    'works' => [
        'no_preview_available' => 'There is no preview available for this document.',
    ],
];
