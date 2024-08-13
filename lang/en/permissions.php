<?php

$common = [
    'create' => 'Create',
    'delete' => 'Delete',
    'search' => 'Search',
    'update' => 'Update',
    'view' => 'View One',
    'viewAny' => 'View All',
    'restore' => 'Restore Deleted',
];

return [
    'actions' => [
        'action_area' => [
            ...$common,
            'archive' => 'Archive',
        ],
    ],
    'arachno' => [
        'change_alert' => $common,
        'crawler' => [
            ...$common,
            'start_crawl' => 'Start new crawls',
        ],
        'source' => [
            ...$common,
        ],
        'sources' => [
            ...$common,
            'jurisdictions' => $common,
        ],
        'update_email' => $common,
        'url_frontier_link' => [
            ...$common,
        ],
    ],
    'assess' => [
        'assessment_item' => array_merge($common, [
            'archive' => 'Archive',
            'attach_context_questions' => 'Attach Context Questions',
            'detach_context_questions' => 'Detach Context Questions',
        ]),
        'assessment_item_description' => [
            ...$common,
            'index_title' => 'Assessment Item Explanations',
        ],
        'guidance_note' => $common,
    ],
    'auth' => [
        'role' => $common,
        'user' => $common,
    ],
    'collaborators' => [
        'collaborator' => array_merge($common, [
            'activate' => 'Activate',
            'deactivate' => 'Deactivate',
            'impersonate' => 'Impersonate',
            'manage_access' => 'Manage Authorisation',
            'update_documents' => 'Upload Documents',
            'update_own_documents' => 'Upload Documents Using Own Profile',
            'validate_documents' => 'Validate Uploaded Documents',
        ]),
        'collaborator_application' => [
            ...$common,
        ],
        'group' => $common,
        'team_rate' => $common,
        'team' => [
            ...$common,
            'payment_request' => [
                'outstanding' => [
                    'viewAny' => 'View all teams with outstanding payment requests',
                ],
            ],
        ],
    ],
    'comments' => [
        'collaborate' => [
            'comment' => $common,
        ],
    ],
    'compilation' => [
        'context_question' => [
            ...$common,
            'archive' => 'Archive',
            'category' => [
                'create' => 'Attach to context question',
                'delete' => 'Detach from context question',
                'viewAny' => 'View All for context question',
            ],
            'requirements_collection' => [
                'create' => 'Attach to context question',
                'delete' => 'Detach from context question',
                'viewAny' => 'View All for context question',
            ],
        ],
        'context_question_description' => $common,
    ],
    'corpus' => [
        'catalogue_doc' => [
            ...$common,
            'fetch' => 'Fetch docs from Source',
        ],
        'catalogue_work' => [
            'delete' => 'Delete',
            'sync' => 'Sync to work',
            'view' => 'View One',
            'viewAny' => 'View All',
        ],
        'content_resource' => [
            'upload' => 'Upload Images and Files for content',
        ],
        'doc' => [
            ...$common,
            'generate_work' => 'Generate Work',
            'for_updates' => [
                'createUpdate' => 'Generate legal update from doc',
                'viewAny' => 'View all for updates',
            ],
            'update_candidate' => [
                ...$common,
                'manage_without_task' => 'Manage Without Task',
                'override_edit' => 'Edit All Documents',
                'override_delete' => 'Delete All Documents',
                'set_additional_actions' => 'Set additional actions',
                'set_affected_legislation' => 'Set Affected Legislation',
                'set_checked_by' => 'Set Checked By',
                'set_handover_updates' => 'Set handover updates',
                'set_justification' => 'Set Justification',
                'set_notification_status' => 'Set Notification Status',
                'set_notification_task' => 'Set Notification Task',
                'view_additional_actions' => 'View additional actions',
                'view_affected_legislation' => 'View Affected Legislation',
                'view_checked_by' => 'View Checked By',
                'view_handover_updates' => 'View handover updates',
                'view_justification' => 'View Justification',
                'view_notification_status' => 'View Notification Status',
                'view_notification_task' => 'View Notification Task',
            ],
        ],
        'reference' => [
            ...$common,
            'consequence' => [
                'apply' => 'Apply Consequence Group',
                'create' => 'Create Consequence Group',
                'delete' => 'Delete Consequence Group',
            ],
            'apply_content_drafts' => 'Apply identified content drafts',
            'apply' => 'Apply Draft',
            'delete_non_requirements' => 'Delete non-requirements',
            'generate_content_drafts' => 'Generate content updates',
            'legal_domain' => [
                'viewAny' => 'View all Legal Domains for Citation',
            ],
            'link' => [
                'delete' => 'Delete citation links',
                'link_type_' . App\Enums\Corpus\ReferenceLinkType::READ_WITH->value => 'Read-withs',
                'link_type_' . App\Enums\Corpus\ReferenceLinkType::CONSEQUENCE->value => 'Consequences',
                'link_type_' . App\Enums\Corpus\ReferenceLinkType::AMENDMENT->value => 'Amendments',
            ],
            'requirement' => [
                'apply' => 'Apply Requirement',
                'create' => 'Create Requirement',
                'delete' => 'Delete Requirement',
                'delete_applied' => 'Delete Applied Requirement',
            ],
            'request_update' => 'Request update',
            'toc' => [
                'apply_all_pending' => 'Apply all pending',
                'delete_citations' => 'Delete Citations',
                'generate_citations' => 'Generate Citations',
            ],
            'versions' => [
                'activate' => 'Activate Version',
                'index' => 'View',
            ],
        ],
        'reference_content_extract' => [
            ...$common,
        ],
        'summary' => [
            ...$common,
            'create' => 'Create New Summary',
            'draft' => [
                'apply' => 'Apply Draft Summary',
                'delete' => 'Delete Draft Summary',
                'request' => 'Request Draft Summary',
                'update' => 'Update Draft Summary',
            ],
        ],
        'work' => [
            ...$common,
            'attach' => 'Attach To Work',
            'detach' => 'Detach From Work',
            'link_to_doc' => 'Link to Catalogue Doc',
            'set_organisation' => 'Attach to Organisation',
            'view_children' => 'View Attached Children',
            'view_parents' => 'View Attached Parents',
            'preview' => 'Preview Requirements',
        ],
        'work_expression' => [
            ...$common,
            'activate' => 'Activate Point-In-Time Version',
            'annotate' => 'Access Annotation Page',
            'annotate_without_task' => 'Annotate Without Task',
            'apply_action_areas' => 'Apply/Remove Action Area Changes',
            'apply_assessment_items' => 'Apply/Remove Assessment Item Changes',
            'apply_categories' => 'Apply/Remove Topic Changes',
            'apply_context_questions' => 'Apply/Remove Context Question Changes',
            'apply_legal_domains' => 'Apply/Remove Legal Domain Changes',
            'apply_locations' => 'Apply/Remove Jurisdiction Changes',
            'apply_tags' => 'Apply/Remove Tag Changes',
            'attach_action_areas' => 'Attach Action Areas',
            'attach_assessment_items' => 'Attach Assessment Items',
            'attach_categories' => 'Attach Categories',
            'attach_context_questions' => 'Attach Context Questions',
            'attach_legal_domains' => 'Attach Legal Domains',
            'attach_locations' => 'Attach Locations',
            'attach_tags' => 'Attach Tags',
            'bespoke' => 'Access Bespoke Citations Page',
            'bespoke_without_task' => 'Edit Bespoke Citations Without Task',
            'detach_action_areas' => 'Detach Action Areas',
            'detach_assessment_items' => 'Detach Assessment Items',
            'detach_categories' => 'Detach Categories',
            'detach_context_questions' => 'Detach Context Questions',
            'detach_legal_domains' => 'Detach Legal Domains',
            'detach_locations' => 'Detach Locations',
            'detach_tags' => 'Detach Tags',
            'edit_document' => 'Access Edit Document Page',
            'edit_document_without_task' => 'Edit Document Without Task',
            'edit_toc' => 'Access Edit ToC Page',
            'edit_toc_without_task' => 'Edit ToC Without Task',
            'identify_without_task' => 'Identify Requirements Without Task',
            'use_bulk_actions' => 'Use Bulk Actions',
        ],
    ],
    'geonames' => [
        'location' => $common,
    ],
    'lookups' => [
        'canned_response' => $common,
    ],
    'notify' => [
        'legal_update' => [
            ...$common,
            'manage_without_task' => 'Manage Without Task',
            'view_without_task' => 'View Without Task',
            'publish' => 'Publish',
            'set_release_date' => 'Set Release Date',
            'update_changes_to_register' => 'Update Changes To Register',
            'update_highlights' => 'Update Highlights',
            'update_summary_of_highlights' => 'Update Summary of Highlights',
            'upload_related_document' => 'Upload Related Document',
        ],
        'notification_action' => [
            ...$common,
        ],
        'notification_handover' => [
            ...$common,
        ],
    ],
    'ontology' => [
        'category' => $common,
        'category_description' => [
            ...$common,
        ],
        'legal_domain' => [
            ...$common,
            'archive' => 'Archive',
            'locations' => $common,
        ],
        'tag' => $common,
    ],
    'payments' => [
        'payment' => [
            ...$common,
            'export' => 'Export to CSV for Transferwise',
        ],
        'payment_request' => [
            ...$common,
        ],
    ],
    'requirements' => [
        'consequence' => $common,
        'summary' => [
            ...$common,
            'create' => 'Create New Summary',
            'draft' => [
                'apply' => 'Apply Draft Summary',
                'create' => 'Request Summary Update',
                'delete' => 'Delete Draft Summary',
                'request' => 'Request Draft Summary',
                'update' => 'Update Draft Summary',
            ],
        ],
    ],
    'storage' => [
        'attachment' => $common,
    ],
    'system' => [
        'processing_job' => $common,
    ],
    'workflows' => [
        'board' => [
            ...$common,
            'archive' => 'Archive',
            'duplicate' => 'Duplicate',
            'set_task_defaults' => 'Set Task Defaults',
        ],
        'create_from_doc' => 'Create workflow from Docs for Update',
        'monitoring_task_config' => [
            ...$common,
        ],
        'note' => [
            'create' => 'Create',
            'delete' => 'Delete others',
            'update' => 'Update others',
            'viewAny' => 'View All',
        ],
        'project' => $common,
        'rating_group' => $common,
        'rating_type' => $common,
        'task' => [
            ...$common,
            'archive' => 'Archive',
            'archive_own_task' => 'Archive Own Task',
            'assign_to_self' => 'Assign To Self',
            'close' => 'Close Task',
            'close_depended_on' => 'Close Depended On Task',
            'close_parent' => 'Close Parent Task',
            'delete_rating' => 'Delete Rating',
            'override_rating' => 'Override Rating',
            'override_status' => 'Override Status',
            'rate' => 'Rate Task',
            're_open' => 'Re Open Completed Task',
            'remove_assignment_from_self' => 'Unassign From Self',
            'reverse_to_in_progress' => 'Reverse To In Progress',
            'set_complexity' => 'Set Complexity',
            'set_pending' => 'Set as Pending',
            'set_todo_when_previous_is_archived' => 'Set as ToDo When Previous Is Archived',
            'set_units' => 'Set Units',
            'view_assignee' => 'View Assignee',
            'view_manager' => 'View Manager',
            'view_pending' => 'View Pending',
        ],
        'task_application' => [
            'create' => 'Apply',
            'delete' => 'Delete',
            'update' => 'Approve/Reject',
            'view' => 'View One',
            'viewAny' => 'View All',
        ],
        'task_check' => [
            ...$common,
            'view_collaborator' => 'View Collaborator',
        ],
        'task_transition' => [
            'view' => 'View One',
            'viewAny' => 'View All',
        ],
        'task_type' => $common,
    ],
];
