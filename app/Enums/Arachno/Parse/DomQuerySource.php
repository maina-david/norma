<?php

namespace App\Enums\Arachno\Parse;

use App\Enums\Traits\EnumToValues;

enum DomQuerySource: string
{
    use EnumToValues;

    case URL = 'url';
    case DOM = 'dom';
    case ANCHOR_TEXT = 'anchor_text';
}
