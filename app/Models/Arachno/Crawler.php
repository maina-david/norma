<?php

namespace App\Models\Arachno;

use App\Enums\Arachno\CrawlType;
use App\Http\ModelFilters\Arachno\CrawlerFilter;
use App\Models\AbstractModel;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Traits\UsesUid;
use App\Services\Arachno\Crawlers\CrawlerConfigFactory;
use App\Services\Arachno\Crawlers\CrawlerConfigInterface;
use App\Services\Arachno\Frontier\CrawlerManager;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCrawler
 */
class Crawler extends AbstractModel
{
    use UsesUid;
    use HasTitleLikeScope;
    use Filterable;

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'enabled' => 'bool',
        'can_fetch' => 'bool',
    ];

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    /**
     * @return BelongsTo
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * @return HasMany
     */
    public function crawls(): HasMany
    {
        /** @var HasMany */
        return $this->hasMany(Crawl::class)
            ->orderBy('created_at', 'DESC');
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return $this->slug ?: '';
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(CrawlerFilter::class);
    }

    /**
     * @return bool
     */
    public function needsBrowser(): bool
    {
        return (bool) $this->needs_browser;
    }

    /**
     * @param CrawlType|null $crawlType
     *
     * @return Crawl|null
     */
    public function createCrawl(?CrawlType $crawlType = null): ?Crawl
    {
        /** @var Crawl|null */
        return Crawl::create([
            'uid' => hex2bin(sha1($this->uid_hash . '__' . microtime())),
            'crawler_id' => $this->id,
            'crawl_type' => $crawlType->value ?? CrawlType::GENERAL->value,
        ]);
    }

    /**
     * @param CrawlType|null|null $crawlType
     *
     * @return Crawl|null
     */
    public function createAndStartCrawl(?CrawlType $crawlType = null): ?Crawl
    {
        $crawl = $this->createCrawl($crawlType);
        if (is_null($crawl)) {
            return null;
        }

        app(CrawlerManager::class)->startCrawl($crawl);

        return $crawl;
    }

    /**
     * @return CrawlerConfigInterface
     */
    public function getCrawlerConfig(): CrawlerConfigInterface
    {
        return app(CrawlerConfigFactory::class)->createConfigForCrawler($this);
    }

    /**
     * @return array<string,mixed>
     */
    public function getCrawlerSettings(): array
    {
        return $this->getCrawlerConfig()->getCrawlerSettings();
    }

    /**
     * @return array<CrawlType>
     */
    public function getAvailableCrawlTypes(): array
    {
        $config = app(CrawlerConfigFactory::class)->createConfigForCrawler($this);
        $startUrls = $config->getCrawlerSettings()['start_urls'] ?? [];
        $types = [];
        foreach (array_keys($startUrls) as $key) {
            $key = str_replace('type_', '', (string) $key);
            $types[] = CrawlType::from((int) $key);
        }

        return $types;
    }

    /**
     * @return bool
     */
    public function isForUpdates(): bool
    {
        return $this->getCrawlerSettings()['for_update'] ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeIsEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }

    public function scopeHasNewWorksSchedule(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNotNull('schedule_new_works');
    }

    public function scopeHasChangesSchedule(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNotNull('schedule_changes');
    }

    public function scopeHasUpdatesSchedule(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNotNull('schedule_updates');
    }

    public function scopeHasFullCatalogueSchedule(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNotNull('schedule_full_catalogue');
    }
}
