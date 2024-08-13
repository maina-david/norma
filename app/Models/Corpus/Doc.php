<?php

namespace App\Models\Corpus;

use App\Http\ModelFilters\Corpus\DocFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Link;
use App\Models\Arachno\Source;
use App\Models\Corpus\Pivots\CategoryDoc;
use App\Models\Corpus\Pivots\DocKeyword;
use App\Models\Corpus\Pivots\DocLegalDomain;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\UpdateCandidate;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Traits\UsesUid;
use App\Traits\HandlesUTF8;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property DocMeta $docMeta
 *
 * @mixin IdeHelperDoc
 */
class Doc extends AbstractModel implements Auditable
{
    use UsesUid;
    use HasTitleLikeScope;
    use Filterable;
    use \OwenIt\Auditing\Auditable;
    use HandlesUTF8;

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
        'delete_if_unused' => 'bool',
        'update_detected_at' => 'datetime',
        'created_at' => 'datetime',
        'for_update' => 'bool',
    ];

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function docMeta(): HasOne
    {
        return $this->hasOne(DocMeta::class, 'doc_id');
    }

    public function startLink(): BelongsTo
    {
        return $this->belongsTo(Link::class, 'start_link_id');
    }

    public function tocItems(): HasMany
    {
        return $this->hasMany(TocItem::class, 'doc_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, (new DocKeyword())->getTable())
            ->using(DocKeyword::class);
    }

    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new DocLegalDomain())->getTable())
            ->using(DocLegalDomain::class);
    }

    public function legalUpdates(): HasMany
    {
        return $this->hasMany(LegalUpdate::class, 'created_from_doc_id');
    }

    public function latestLegalUpdate(): HasOne
    {
        return $this->hasOne(LegalUpdate::class, 'created_from_doc_id')->latestOfMany();
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function firstContentResource(): BelongsTo
    {
        return $this->belongsTo(ContentResource::class, 'first_content_resource_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, (new CategoryDoc())->getTable(), 'doc_id', 'category_id')
            ->using(CategoryDoc::class);
    }

    /**
     * @return HasOne
     */
    public function updateCandidate(): HasOne
    {
        return $this->hasOne(UpdateCandidate::class);
    }

    public function catalogueDoc(): BelongsTo
    {
        return $this->belongsTo(CatalogueDoc::class, 'uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expression(): HasOne
    {
        return $this->hasOne(WorkExpression::class);
    }

    /*****************************************************************
    / Model Scopes
    /*****************************************************************/

    public function scopeForCrawl(Builder $query, int $id): Builder
    {
        return $query->where('spider_crawl_id', $id);
    }

    public function scopeWithHash(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNotNull('version_hash');
    }

    public function scopeWithoutHash(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNull('version_hash');
    }

    public function scopeUpdateDetected(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNotNull('update_detected_at');
    }

    public function scopeUpdateNotDetected(Builder $query): Builder
    {
        /** @var Builder */
        return $query->whereNull('update_detected_at');
    }

    public function scopeForUpdate(Builder $query): Builder
    {
        /** @var Builder */
        return $query->where('for_update', true);
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    public function getUidString(): string
    {
        return ($this->source_id ?? '') . '__' . $this->source_unique_id;
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(DocFilter::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source_unique_id' => $this->source_unique_id,
        ];
    }

    /**
     * Transform the data before performing an audit.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function transformAudit(array $data): array
    {
        $data['old_values'] = $this->arrayToUTF8($data['old_values'] ?? []);
        $data['new_values'] = $this->arrayToUTF8($data['new_values'] ?? []);

        return $data;
    }
}
