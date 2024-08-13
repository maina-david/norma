<?php

namespace App\Enums\Workflows;

enum CollaborateSessionKey: string
{
    case CURRENT_EXPRESSION = 'ccwe';
    case CURRENT_TASK = 'cct';
    case CURRENT_REFERENCE_FILTERS = 'crf';
    case CURRENT_VISIBLE_META = 'cvm';
    case CURRENT_EXPRESSION_PREVIEW = 'cxp';
}
