<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Arachno\Link;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\UsesUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperTocItem
 */
class TocItem extends AbstractModel
{
    use UsesUid;
    use ApiQueryFilterable;

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
        'deactivated_at' => 'datetime',
    ];

    /** @var string|null Used by TocItemContentExtractor to set the HTML content between two ToC items */
    public ?string $_content = null;

    /*****************************************************************
    / Model Relations
    /*****************************************************************/

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class, 'doc_id');
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * @return BelongsTo
     */
    public function contentResource(): BelongsTo
    {
        return $this->belongsTo(ContentResource::class);
    }

    /*****************************************************************
    / Model Scopes
    /*****************************************************************/

    /**
     * @param Builder     $query
     * @param int         $docId
     * @param string|null $parentId
     *
     * @return Builder
     */
    public function scopeChildrenForDoc(
        Builder $query,
        int $docId,
        ?string $parentId = null
    ): Builder {
        /** @var Builder */
        return $query->where('doc_id', $docId)
            ->when(!$parentId, function ($q) {
                $q->where('level', 1);
            })
            ->when($parentId, function ($q) use ($parentId) {
                $q->where('parent_id', $parentId);
            })
            ->orderBy('position');
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    public function getUidString(): string
    {
        return static::buildUid(
            $this->source_unique_id,
            $this->doc?->uid_hash ?? '',
            $this->label ?: '',
            $this->parent?->uid_hash ?? '',
        );
    }

    /**
     * In the case of ToC items, we want the UID to be unique for the work but not for every instance, so we can
     * match up existing toc items with new ones, if they are the same ones.
     * As the docUid is generated from the source_id and the source_unique_id, it also stays the same
     * when scraping it multiple times.
     * So docs and toc_items are different to other tables' uid in that it doesn't have a unique constraint.
     *
     * @param string|null $sourceUniqueId
     * @param string      $docUid
     * @param string      $label
     * @param string      $parentUid
     *
     * @return string
     */
    public static function buildUid(
        ?string $sourceUniqueId,
        string $docUid,
        string $label,
        string $parentUid
    ): string {
        if ($sourceUniqueId) {
            return sprintf(
                '%s__%s',
                $docUid,
                $sourceUniqueId
            );
        }

        return sprintf(
            '%s__%s__%s',
            $docUid,
            $label,
            $parentUid
        );
    }
}
