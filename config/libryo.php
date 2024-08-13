<?php

use App\Enums\Auth\LifecycleStage;
use App\Enums\System\LibryoModule;
use App\Enums\Tasks\TaskActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;

return [
    'website_address' => 'https://libryo.com',
    'emails' => [
        'libryo_deactivated' => [
            'mariette.steenkamp@libryo.com',
            'michelle.mandy@libryo.com',
            'emily@libryo.com',
            'astrid.coombes@libryo.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Settings
    |--------------------------------------------------------------------------
    |
    | The settings that will be added to the model when creating
    */

    'model_settings' => [
        User::class => [
            'defaults' => [
                'accepted_terms_of_use' => false,
                'all_legal_domains' => true,
                'notification_legal_domains' => [],
                'dismissed_notifications' => [],
                'active_libryo' => null,
                'email_comment_mention' => true,
                'email_comment_reply' => true,
                'email_reminder' => true,
                'email_task_assigned' => true,
                'email_task_assignee_changed_assignee' => true,
                'email_task_assignee_changed_author' => true,
                'email_task_assignee_changed_watcher' => true,
                'email_task_due_date_changed_assignee' => true,
                'email_task_due_date_changed_author' => true,
                'email_task_due_date_changed_watcher' => true,
                'email_task_due_assignee' => true,
                'email_task_due_author' => true,
                'email_task_due_watcher' => true,
                'email_task_status_changed_assignee' => false,
                'email_task_status_changed_author' => false,
                'email_task_status_changed_watcher' => false,
                'email_task_priority_changed_assignee' => true,
                'email_task_priority_changed_author' => true,
                'email_task_priority_changed_watcher' => true,
                'email_task_completed_assignee' => true,
                'email_task_completed_author' => true,
                'email_task_completed_watcher' => true,
                'email_task_title_changed_assignee' => true,
                'email_task_title_changed_author' => true,
                'email_task_title_changed_watcher' => true,
                '2fa' => [],
                'app_filters' => [],
                'last_invite_sent' => null,
            ],
            'allowed_to_update' => [
                'accepted_terms_of_use',
                'all_legal_domains',
                'notification_legal_domains',
                'dismissed_notifications',
                'email_comment_mention',
                'email_comment_reply',
                'email_reminder',
                'email_task_assigned',
                'email_task_assignee_changed_assignee',
                'email_task_assignee_changed_author',
                'email_task_assignee_changed_watcher',
                'email_task_due_date_changed_assignee',
                'email_task_due_date_changed_author',
                'email_task_due_date_changed_watcher',
                'email_task_due_assignee',
                'email_task_due_author',
                'email_task_due_watcher',
                'email_task_status_changed_assignee',
                'email_task_status_changed_author',
                'email_task_status_changed_watcher',
                'email_task_priority_changed_assignee',
                'email_task_priority_changed_author',
                'email_task_priority_changed_watcher',
                'email_task_title_changed_assignee',
                'email_task_title_changed_author',
                'email_task_title_changed_watcher',
                'email_task_completed_assignee',
                'email_task_completed_author',
                'email_task_completed_watcher',
                'last_invite_sent',
            ],
            'notification_settings' => [
                'all_legal_domains',
                'notification_legal_domains',
                'email_comment_mention',
                'email_comment_reply',
                'email_reminder',
                'email_task_assigned',
                'email_task_assignee_changed_assignee',
                'email_task_assignee_changed_author',
                'email_task_assignee_changed_watcher',
                'email_task_due_date_changed_assignee',
                'email_task_due_date_changed_author',
                'email_task_due_date_changed_watcher',
                'email_task_due_assignee',
                'email_task_due_author',
                'email_task_due_watcher',
                'email_task_status_changed_assignee',
                'email_task_status_changed_author',
                'email_task_status_changed_watcher',
                'email_task_priority_changed_assignee',
                'email_task_priority_changed_author',
                'email_task_priority_changed_watcher',
                'email_task_title_changed_assignee',
                'email_task_title_changed_author',
                'email_task_title_changed_watcher',
                'email_task_completed_assignee',
                'email_task_completed_author',
                'email_task_completed_watcher',
            ],
            // maps task activities to setting keys
            'notification_activity_mappings' => [
                TaskActivityType::statusChange()->value => 'email_task_status_changed_',
                TaskActivityType::assigneeChange()->value => 'email_task_assignee_changed_',
                TaskActivityType::dueDateChanged()->value => 'email_task_due_date_changed_',
                TaskActivityType::priorityChanged()->value => 'email_task_priority_changed_',
                TaskActivityType::titleChanged()->value => 'email_task_title_changed_',
            ],
            'guarded' => [],
        ],
        Libryo::class => [
            'defaults' => [
                'modules' => [
                    LibryoModule::actions()->value => false,
                    LibryoModule::comply()->value => false,
                    LibryoModule::tasks()->value => true,
                    LibryoModule::update_emails()->value => true,
                    LibryoModule::search_requirements_and_drives()->value => true,
                    LibryoModule::keyword_search()->value => false,
                    LibryoModule::drives()->value => true,
                    'hide_applicability' => false,
                ],
            ],
            'guarded' => [],
        ],
        Organisation::class => [
            'defaults' => [
                'folders' => [],
                'storage_allocation' => 5,
                'authorized_domains' => [],
                'modules' => [
                    LibryoModule::actions()->value => false,
                    'comply' => false,
                    'tasks' => true,
                    'live_chat' => true,
                    'comments' => true,
                    'update_emails' => true,
                    'search_requirements_and_drives' => true,
                    'keyword_search' => false,
                    'drives' => true,
                ],
                'policies' => [
                    'password_reset' => [
                        'enabled' => false,
                        'frequency' => 0,
                        'last_implemented' => null,
                    ],
                    '2fa' => [
                        'enabled' => false,
                        'type' => 'email',
                    ],
                ],
            ],
            'guarded' => [],
        ],
    ],

    'user' => [
        'lifecycle' => [
            'deactivated_stages' => [
                LifecycleStage::deactivated()->value,
                LifecycleStage::deactivatedInactivity()->value,
            ],
            'notify_deactivation_when' => [
                'inactivity_months_reach' => 11,
            ],
            'deactivate_when' => [
                'invitations_days_reach' => 179,
                'inactivity_months_reach' => 12,
            ],
            'resend_invitation_after_days' => [30, 60, 90, 120, 150],
        ],
    ],
];
