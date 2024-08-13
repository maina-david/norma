<?php

use App\Enums\Requirements\ConsequenceAmountPrefix;
use App\Enums\Requirements\ConsequenceAmountType;
use App\Enums\Requirements\ConsequencePeriod;
use App\Enums\Requirements\ConsequenceType;
use App\Enums\Requirements\PenaltyType;

return [
    'amendments' => 'Amendments',
    'consequence' => [
        'administrative_civil' => 'Administrative / Civil',
        'amount' => 'Amount',
        'amount_prefix' => [
            ConsequenceAmountPrefix::upTo()->value => 'Up to',
            ConsequenceAmountPrefix::exactly()->value => 'Exactly',
            ConsequenceAmountPrefix::atLeast()->value => 'At least',
        ],
        'amount_prefix_label' => 'Amount Prefix',
        'amount_prefix_for' => [
            ConsequenceAmountPrefix::upTo()->value => 'for up to',
            ConsequenceAmountPrefix::exactly()->value => 'for',
            ConsequenceAmountPrefix::atLeast()->value => 'for at least',
        ],
        'amount_prefix_of' => [
            ConsequenceAmountPrefix::upTo()->value => 'of up to',
            ConsequenceAmountPrefix::exactly()->value => 'of',
            ConsequenceAmountPrefix::atLeast()->value => 'of at least',
        ],
        'amount_type' => [
            ConsequenceAmountType::currency()->value => 'Currency',
            ConsequenceAmountType::percentageRevenue()->value => '% of revenue / turnover / profit',
            ConsequenceAmountType::penaltyUnits()->value => 'Penalty Units',
            ConsequenceAmountType::penaltyFines()->value => 'Penalty Fines',
            ConsequenceAmountType::currencyPoints()->value => 'Currency Points',
            ConsequenceAmountType::penaltyPoints()->value => 'Penalty Points',
            ConsequenceAmountType::standardScale()->value => 'on the standard scale',
        ],
        'amount_type_label' => 'Amount Type',
        'consequence_type' => 'Consequence Type',
        'consequences' => 'Consequences',
        'create_title' => 'Create Consequence',
        'currency' => 'Currency',
        'description' => 'Description',
        'details' => 'Details',
        'edit_title' => 'Edit Consequence',
        'fine_penalty' => 'Fine/Penalty',
        'for_up_to' => 'for up to',
        'imprisonment' => 'Imprisonment',
        'index_title' => 'Consequences',
        'level' => 'Level',
        'of_up_to' => 'of up to',
        'offence' => 'Offence',
        'penalty' => 'Penalty',
        'penalty_type' => [
            PenaltyType::fine()->value => 'Fine / Penalty',
            PenaltyType::prison()->value => 'Imprisonment',
            PenaltyType::costs()->value => 'Costs',
            PenaltyType::community()->value => 'Community service',
            PenaltyType::other()->value => 'Other',
        ],
        'period_label' => 'Period',
        'period' => [
            ConsequencePeriod::days()->value => [
                'plural' => ':amount days',
                'singular' => ':amount day',
            ],
            ConsequencePeriod::weeks()->value => [
                'plural' => ':amount weeks',
                'singular' => ':amount week',
            ],
            ConsequencePeriod::months()->value => [
                'plural' => ':amount months',
                'singular' => ':amount month',
            ],
            ConsequencePeriod::years()->value => [
                'plural' => ':amount years',
                'singular' => ':amount year',
            ],
            ConsequencePeriod::life()->value => [
                'plural' => ':amount life sentences',
                'singular' => ':amount life sentence',
            ],
        ],
        'period_plain' => [
            ConsequencePeriod::days()->value => 'Days',
            ConsequencePeriod::weeks()->value => 'Weeks',
            ConsequencePeriod::months()->value => 'Months',
            ConsequencePeriod::years()->value => 'Years',
            ConsequencePeriod::life()->value => 'Life sentences',
        ],
        'per_day' => 'per day',
        'personal_liability' => 'with personal liability',
        'sentence_period' => 'Sentence Period',
        'severity' => 'Severity',
        'show_title' => 'Consequence Details',
        'type' => [
            ConsequenceType::fine()->value => 'Fine/Penalty',
            ConsequenceType::prison()->value => 'Imprisonment',
            ConsequenceType::other()->value => 'Other',
        ],
        'units' => 'Units',
        'unspecified_imprisonment' => 'Unspecified imprisonment',
        'unspecified_fine' => 'Unspecified fine',
        'view_details' => 'View Details',
        'view_related_citations' => 'View Related Citations',
        'with_penalty' => 'with a penalty of',
    ],
    'consequences' => 'Consequences',
    'export' => 'Export',
    'general' => 'General',
    'no_notes' => 'There are no Notes to display for these Requirement Details.',
    'no_action_areas' => 'There are no Linked Action Areas to display for these Requirement Details.',
    'no_read_with' => 'There are no related Read Withs to display for these Requirement Details.',
    'read_withs' => 'Read Withs',
    'requirement_details' => 'Requirement Details',
    'requirements' => 'Requirements',
    'linked_action_areas' => 'Linked Action Areas',
    'applicability' => 'Applicability',
    'summary' => [
        'create_title' => 'Create Summary',
        'created_at' => 'Date Created',
        'draft' => [
            'draft' => 'Draft',
            'index_title' => 'Summaries: Draft',
        ],
        'edit_title' => 'Edit Summary',
        'index_title' => 'Summaries',
        'notes' => 'Notes',
        'show_title' => 'Summary Details',
        'translate_to' => 'Translate to',
    ],
];
