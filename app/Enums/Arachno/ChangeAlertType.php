<?php

namespace App\Enums\Arachno;

use App\Enums\Traits\EnumToValues;

enum ChangeAlertType: int
{
    use EnumToValues;

    case WACHETE = 1;
    case WORK_UPDATE = 2;
}
