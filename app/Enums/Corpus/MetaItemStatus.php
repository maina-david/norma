<?php

namespace App\Enums\Corpus;

enum MetaItemStatus: int
{
    case PENDING = 1;
    case COMPLETED = 2;
    case ANY = 3;

    /**
     * Get the classes of colours that identify the state.
     *
     * @return string[]
     */
    public static function stateColours(): array
    {
        return [
            self::PENDING->value => 'text-negative border-negative',
            self::COMPLETED->value => 'text-primary border-primary',
            self::ANY->value => 'text-warning border-warning',
        ];
    }
}
