<?php

use App\Enums\Lookups\CannedResponseField;

return [
    'canned_response' => [
        'create_title' => 'Create Canned Response',
        'created_at' => 'Date Created',
        'edit_title' => 'Edit Canned Response',
        'fields' => [
            CannedResponseField::changesToRegister()->value => 'Changes to Register',
            CannedResponseField::issuingAuthority()->value => 'Issuing Authority',
            CannedResponseField::justification()->value => 'Justification',
            CannedResponseField::checkedComment()->value => 'Check Sources Review Comment ',
            CannedResponseField::notificationStatusReason()->value => 'Notification Status Reason',
            CannedResponseField::sourceRights()->value => 'Rights for Sources',
        ],
        'for_field' => 'For Field',
        'index_title' => 'Canned Responses',
        'populate_with_canned_response' => '↑ Populate with canned response',
        'response' => 'Response',
        'show_title' => 'Canned Response Details',
    ],
];
