<?php

namespace App\Enums\Actions;

enum RiskOfNonCompliance: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case NoAnswer = 4;
}
