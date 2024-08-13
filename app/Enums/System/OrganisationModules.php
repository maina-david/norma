<?php

namespace App\Enums\System;

use App\Enums\Traits\HasLabels;

enum OrganisationModules: string
{
    use HasLabels;

    case ACTIONS = 'actions';
    case COMMENTS = 'comments';
    case COMPLY = 'comply';
    case DRIVES = 'drives';
    case KEYWORD_SEARCH = 'keyword_search';
    case LIVE_CHAT = 'live_chat';
    case SEARCH_REQUIREMENTS_AND_DRIVES = 'search_requirements_and_drives';
    case TASKS = 'tasks';
    case UPDATE_EMAILS = 'update_emails';
    case SSO = 'sso';

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'customer.organisation.modules.';
    }
}
