<?php

namespace App\Enums\Arachno\Parse;

use App\Enums\Traits\EnumToValues;

enum DomQueryType: string
{
    use EnumToValues;

    case XPATH = 'xpath';
    case CSS = 'css';
    case REGEX = 'regex';
    case TEXT = 'text';
    case NESTED = 'nested';
    case PDF = 'pdf';
    case JSON = 'json';
    case FUNC = 'func';
}
