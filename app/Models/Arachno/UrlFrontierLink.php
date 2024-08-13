<?php

namespace App\Models\Arachno;

use App\Contracts\Arachno\UrlFrontierLinkInterface;
use App\Enums\Arachno\CrawlType;
use App\Http\ModelFilters\Arachno\UrlFrontierLinkFilter;
use App\Models\AbstractModel;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Traits\UsesUid;
use App\Services\Arachno\Parse\DocMetaDto;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperUrlFrontierLink
 */
class UrlFrontierLink extends AbstractModel implements UrlFrontierLinkInterface
{
    use UsesUid;
    use Filterable;
    use MassPrunable;

    public $timestamps = false;

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
        'needs_browser' => 'bool',
    ];

    /** @var DocMetaDto|null */
    public ?DocMetaDto $_metaDto = null;

    public function prunable(): Builder
    {
        return static::whereNotNull('crawled_at')
            ->where('crawled_at', '<=', now()->subMonths(6));
    }

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    public function crawl(): BelongsTo
    {
        return $this->belongsTo(Crawl::class);
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function parentTocItem(): BelongsTo
    {
        return $this->belongsTo(TocItem::class, 'parent_toc_item_id');
    }

    public function catalogueDoc(): BelongsTo
    {
        return $this->belongsTo(CatalogueDoc::class, 'catalogue_doc_id');
    }

    public function refererLink(): BelongsTo
    {
        return $this->belongsTo(static::class, 'referer_id');
    }

    public function contentCache(): BelongsTo
    {
        return $this->belongsTo(ContentCache::class, 'content_cache_id');
    }

    public function frontierMeta(): HasOne
    {
        return $this->hasOne(UrlFrontierMeta::class, 'url_frontier_link_id');
    }

    /*****************************************************************
    / Model Scopes
    /*****************************************************************/

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUncrawled(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNull('crawled_at');
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeCrawlTypeSearch(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->where('crawl_type', CrawlType::SEARCH->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeCrawlTypeNotSearch(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNot('crawl_type', CrawlType::SEARCH->value);
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    public function getUidString(): string
    {
        return ($this->url ?: '') . '__' . $this->crawl_id;
    }

    public function setCrawledNow(): self
    {
        $this->update(['crawled_at' => Carbon::now()]);

        return $this;
    }

    public function needsBrowser(): bool
    {
        return (bool) $this->needs_browser;
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(UrlFrontierLinkFilter::class);
    }

    public function isCrawlTypeSearch(): bool
    {
        return $this->crawl_type === CrawlType::SEARCH->value;
    }

    // public function isImage(): bool
    // {
    //     if (!$this->content_type) {
    //         return false;
    //     }

    //     return app(MimeTypeManager::class)->isImage($this->content_type);
    // }

    // public function isCss(): bool
    // {
    //     if (!$this->content_type) {
    //         return false;
    //     }

    //     return app(MimeTypeManager::class)->isCss($this->content_type);
    // }

    // public function isPdf(): bool
    // {
    //     if (!$this->content_type) {
    //         return false;
    //     }

    //     return app(MimeTypeManager::class)->isPdf($this->content_type);
    // }

    // /**
    //  * @return bool
    //  */
    // public function isWebResource(): bool
    // {
    //     if (!$this->content_type) {
    //         return false;
    //     }

    //     return (bool) ($this->isImage() || $this->isCss());
    // }
}
