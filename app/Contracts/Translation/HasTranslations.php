<?php

namespace App\Contracts\Translation;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Lookups\Translation[] $translations
 */
interface HasTranslations
{
    /**
     * @return HasMany
     */
    public function translations(): HasMany;
}
