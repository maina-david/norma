<?php

namespace App\Models\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Models\AbstractModel;
use App\Models\Corpus\Doc;
use App\Models\Traits\UsesUid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperCrawl
 */
class Crawl extends AbstractModel
{
    use UsesUid;

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
        'cookies' => 'array',
    ];

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    /**
     * @return BelongsTo
     */
    public function crawler(): BelongsTo
    {
        return $this->belongsTo(Crawler::class);
    }

    /**
     * @return HasMany
     */
    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class);
    }

    /**
     * @return HasMany
     */
    public function urlFrontierLinks(): HasMany
    {
        return $this->hasMany(UrlFrontierLink::class);
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return ($this->crawler?->uid_hash ?? '') . '__' . ((string) now());
    }

    /**
     * @return self
     */
    public function startNow(): self
    {
        $this->update(['started_at' => Carbon::now()]);

        return $this;
    }

    /**
     * @return bool
     */
    public function needsBrowser(): bool
    {
        return $this->crawler?->needsBrowser() ?? false;
    }

    /**
     * @return bool
     */
    public function isTypeFullCatalogue(): bool
    {
        return $this->crawl_type === CrawlType::FULL_CATALOGUE->value;
    }

    /**
     * @return bool
     */
    public function isTypeNewCatalogueWorkDiscovery(): bool
    {
        return $this->crawl_type === CrawlType::NEW_CATALOGUE_WORK_DISCOVERY->value;
    }

    /**
     * @return bool
     */
    public function isTypeFetchWorks(): bool
    {
        return $this->crawl_type === CrawlType::FETCH_WORKS->value;
    }

    /**
     * @return bool
     */
    public function isTypeWorkChangesDiscovery(): bool
    {
        return $this->crawl_type === CrawlType::WORK_CHANGES_DISCOVERY->value;
    }

    /**
     * @return bool
     */
    public function isTypeForUpdates(): bool
    {
        return $this->crawl_type === CrawlType::FOR_UPDATES->value;
    }

    /**
     * @return bool
     */
    public function isTypeSearch(): bool
    {
        return $this->crawl_type === CrawlType::SEARCH->value;
    }
}
