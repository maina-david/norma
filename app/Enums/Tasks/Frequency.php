<?php

namespace App\Enums\Tasks;

enum Frequency: int
{
    case DAILY = 1;
    case WEEKLY = 2;
    case MONTHLY = 3;
    case YEARLY = 4;
}
