<?php

namespace App\Enums\Lookups;

enum ContentMetaSuggestions: int
{
    case ASSESS = 1;
    case CATEGORY = 2;
    case CONTEXT = 3;
    case DOMAIN = 4;
    case ACTION = 5;
}
