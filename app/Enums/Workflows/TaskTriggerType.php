<?php

namespace App\Enums\Workflows;

enum TaskTriggerType: int
{
    case ON_DONE = 1;
    case ON_REVIEW = 2;
    case ON_PROGRESS = 3;
    case ON_STATUS_CHANGE = 4;
}
