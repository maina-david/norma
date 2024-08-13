<?php

namespace App\Models\Traits;

use App\Models\Lookups\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTranslations
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function englishTranslation(): HasOne
    {
        return $this->hasOne(Translation::class)->ofMany([
            'id' => 'min',
        ], function ($query) {
            $query->where('target_language', 'en');
        });
    }
}
