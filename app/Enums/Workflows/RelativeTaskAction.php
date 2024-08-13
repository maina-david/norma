<?php

namespace App\Enums\Workflows;

enum RelativeTaskAction: int
{
    case IGNORE = 1;
    case OWN = 2;
    case MULTIPLE_OF = 3;
    case CUSTOM_VALUE = 4;
    case REFERENCES = 10;
}
