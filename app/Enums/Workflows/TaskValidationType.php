<?php

namespace App\Enums\Workflows;

enum TaskValidationType: string
{
    case COMPLETE = 'status';
    case TODO = 'on_todo';
}
