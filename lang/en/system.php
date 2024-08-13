<?php

use App\Enums\System\ProcessingJobStatus;
use App\Enums\System\ProcessingJobType;

return [
    'antivirus' => [
        'failed' => 'The uploaded file has failed our antivirus tests. Please select a different file for upload.',
    ],
    'api_log' => [
        'body' => 'Body',
        'created_at' => 'Created At',
        'headers' => 'Headers',
        'id' => 'ID',
        'index_title' => 'API logs',
        'method' => 'Method',
        'query' => 'Query',
        'response_status' => 'Response Status',
        'route' => 'Route',
        'user_id' => 'User Id',
    ],
    'processing_job' => [
        'create_title' => 'Create Processing Job',
        'created_at' => 'Date Created',
        'details' => 'Details',
        'edit_title' => 'Edit Processing Job',
        'index_title' => 'Processing Jobs',
        'job' => 'Job',
        'job_status' => [
            ProcessingJobStatus::failed()->value => 'Failed',
            ProcessingJobStatus::pending()->value => 'Pending',
            ProcessingJobStatus::processing()->value => 'Processing',
            ProcessingJobStatus::complete()->value => 'Complete',
        ],
        'job_type' => [
            ProcessingJobType::referenceParentsPositions()->value => 'Reference Parents Positions',
            ProcessingJobType::fetchSelectors()->value => 'References',
            ProcessingJobType::cacheHighlights()->value => 'Cache Highlights',
            ProcessingJobType::arachnoCapture()->value => 'Capture',
            ProcessingJobType::arachnoRecapture()->value => 'Recapture',
        ],
        'show_title' => 'Processing Job Details',
        'source_id' => 'Source ID',
        'source_unique_id' => 'Source Unique ID',
        'spider_slug' => 'Spider Slug',
        'start_url' => 'Start URL',
        'view_url' => 'View URL',
    ],
    'system_notification' => array_merge([
        'active' => 'Active',
        'alert' => 'Alert',
        'content' => 'Content',
        'create_title' => 'System Notification: Create',
        'edit_title' => 'System Notification: Edit',
        'expiry_date' => 'Expiry Date',
        'has_user_action' => 'Has User Action',
        'id' => 'ID',
        'index_title' => 'System Notifications',
        'is_permanent' => 'Is Permanent',
        'modal' => 'Modal',
        'title' => 'Title',
        'type' => 'Alert Type',
        'user_action_link' => 'User Action Link',
        'user_action_text' => 'User Action Text',
        'yes' => 'Yes',
    ], require ('timestamps.php')),
];
