<?php

namespace App\Enums\Workflows;

enum ComputedTaskAction: int
{
    case SUMMARIES = 5;
    case STATEMENTS = 6;
    case SUMMARIES_AND_STATEMENTS = 7;
    case NOT_AID_STATEMENTS_3MIN = 8;
    case NOT_AID_STATEMENTS_4MIN = 9;
}
