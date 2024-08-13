<?php

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskProject;

return [
    'avatar_url' => [
        'passphrase' => '4KZyBDpJnXzSWpg6sGKCtIIQVmMJeZRHuCFy8jMv',
    ],
    'hash_id' => [
        AssessmentItem::class => [
            'passphrase' => '6u2v2XyG67rL4a4moFkEOZKaoqZO8zGyQDObKsgT',
            'padding' => 8,
        ],
        AssessmentItemResponse::class => [
            'passphrase' => '6u2v2XyG67rL4a4moFkEOZKaoqZO8zGyQDObKsgT',
            'padding' => 8,
        ],
        Collaborator::class => [
            'passphrase' => 'zv6ACbRZPDyC0gLvBDcbKTiYcElkbyk5Ql9aKcft',
            'padding' => 8,
        ],
        ContextQuestion::class => [
            'passphrase' => '6u2v2XyG67rL4a4moFkEOZKaoqZO8zGyQDObKsgT',
            'padding' => 8,
        ],
        Libryo::class => [
            'passphrase' => '6u2v2XyG67rL4a4moFkEOZKaoqZO8zGyQDObKsgT',
            'padding' => 8,
        ],
        LegalUpdate::class => [
            'passphrase' => '6u2v2XyG67rL4a4moFkEOZKaoqZO8zGyQDObKsgT',
            'padding' => 40,
        ],
        Task::class => [
            'passphrase' => '8NNPGdDdXOdJo10VRXHVpHearyN1a23PeE0jr4OF',
            'padding' => 8,
        ],
        TaskProject::class => [
            'passphrase' => 'ijqr3cWS2Xkbur5bt6lQxlnR2S4qIDUkkM9Vw6Sw',
            'padding' => 8,
        ],
        User::class => [
            'passphrase' => 'zv6ACbRZPDyC0gLvBDcbKTiYcElkbyk5Ql9aKcft',
            'padding' => 8,
        ],
    ],
];
