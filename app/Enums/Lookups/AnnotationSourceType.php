<?php

namespace App\Enums\Lookups;

enum AnnotationSourceType: int
{
    case CONTEXT_QUESTION = 1;
    case ASSESSMENT_ITEM = 2;
    case TAG = 3;
    case CDM = 4;
    case LIBRYO_AI = 5;
    case WORDCLOUD = 6;
    case QC_OTHER = 7;
    case MAGI = 8;
    case ACTION_AREA = 9;
}
