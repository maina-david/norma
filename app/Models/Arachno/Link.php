<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use App\Models\Arachno\Pivots\ContentResourceLink;
use App\Models\Corpus\ContentResource;
use App\Models\Traits\UsesUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperLink
 */
class Link extends AbstractModel
{
    use UsesUid;
    public $timestamps = false;

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /*****************************************************************
    / Model Relations
    /*****************************************************************/

    /**
     * @return BelongsToMany
     */
    public function contentResources(): BelongsToMany
    {
        /** @var BelongsToMany */
        return $this->belongsToMany(ContentResource::class)
            ->using(ContentResourceLink::class);
        //       ->withPivot('created')
        //       ->orderByPivot('created', 'desc');
    }

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return static::buildUid($this->url ?? '');
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function buildUid(string $url): string
    {
        return Str::before($url, '#');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForUrlUid(Builder $builder, string $url): Builder
    {
        $url = Link::buildUid($url);
        $uidHash = Link::hashUid($url);

        return $builder->forUid($uidHash);
    }
}
