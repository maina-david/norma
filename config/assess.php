<?php

use App\Enums\Assess\RiskRating;

return [
    /*
    |--------------------------------------------------------------------------
    | Start due offset
    |--------------------------------------------------------------------------
    | How many months from start should SAI responses be due by default (can be set on start_due_offset per SAI as well)
    */
    'default_start_due_offset' => [
        RiskRating::high()->value => 3,
        RiskRating::medium()->value => 6,
        RiskRating::low()->value => 12,
        RiskRating::notRated()->value => 9,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default frequency for new assessment item
    |--------------------------------------------------------------------------
    | Frequency is measured in months. Once a response is added, the next due date will by in this many months
    */
    'default_assessment_item_frequency' => 12,

    'auto_apply_from_action_areas' => [
        'locations' => [
            'exclusive' => [
                384,
            ],
            'inherit' => [
                1, 3, 4, 5, 6, 7, 8, 9, 14, 15, 19, 22, 26, 36, 37, 38, 388, 392, 2334, 2336, 2338, 2342, 2344, 2345,
                2348, 2349, 2350, 2351, 2354, 2355, 2357, 2358, 2367, 2369, 2371, 2376, 2378, 2380, 2383, 42274, 42311,
                42312, 42319, 42320, 42382, 42387, 42441, 42442, 42452, 42453, 42473, 77419, 77838,
                83501, 86635, 111809, 111914, 163810,
            ],
        ],
    ],
];
