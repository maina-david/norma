<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use App\Models\Traits\UsesUid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperSearchPage
 */
class SearchPage extends AbstractModel
{
    use UsesUid;

    /**
     * associated DB table.
     *
     * @var string
     */
    protected $table = 'search_pages';

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'last_crawled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany
     */
    public function versions(): HasMany
    {
        /** @var HasMany */
        return $this->hasMany(SearchPageVersion::class, 'search_page_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(SearchPageVersion::class, 'search_page_id')
            ->latestOfMany();
    }

    public function searchPageMeta(): HasOne
    {
        return $this->hasOne(SearchPageMeta::class, 'search_page_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return $this->url ?? '';
    }
}
