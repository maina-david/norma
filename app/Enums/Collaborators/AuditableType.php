<?php

namespace App\Enums\Collaborators;

enum AuditableType: int
{
    case CONSEQUENCE = 1;
    case LEGAL_DOMAIN = 2;
    case LEGAL_STATEMENT = 3;
    case LOCATION = 4;
    case SOURCE = 5;
    case SUMMARY = 6;
    case TAG = 7;
    case WORK = 8;
    case WORK_EXPRESSION = 9;
    case REFERENCE = 10;
    case ASSESSMENT_ITEM = 11;
    case CONTEXT_QUESTION = 12;
    case CATEGORY = 13;
}
