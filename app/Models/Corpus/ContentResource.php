<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Notify\LegalUpdate;
use App\Models\Traits\UsesUid;
use App\Services\Storage\MimeTypeManager;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperContentResource
 */
class ContentResource extends AbstractModel
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
     * @return HasMany
     */
    public function tocItems(): HasMany
    {
        return $this->hasMany(TocItem::class, 'content_resource_id');
    }

    /**
     * @return HasMany
     */
    public function firstForDocs(): HasMany
    {
        return $this->hasMany(Doc::class, 'first_content_resource_id');
    }

    /**
     * @return HasMany
     */
    public function legalUpdates(): HasMany
    {
        return $this->hasMany(LegalUpdate::class, 'content_resource_id');
    }

    /**
     * @return HasMany
     */
    public function docMetasForSummary(): HasMany
    {
        return $this->hasMany(DocMeta::class, 'summary_content_resource_id');
    }

    public function textContentResource(): BelongsTo
    {
        return $this->belongsTo(static::class, 'text_content_resource_id');
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    /**
     * @return string
     */
    public function getUidString(): string
    {
        return $this->content_hash ?? '';
    }

    /**
     * @return bool
     */
    public function isHtml(): bool
    {
        return $this->mime_type
            ? app(MimeTypeManager::class)->isHtml($this->mime_type)
            : false;
    }

    /**
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->mime_type
            ? app(MimeTypeManager::class)->isPdf($this->mime_type)
            : false;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return string|null
     */
    public function getContentFromStorage(): ?string
    {
        return $this->path ? app(ContentResourceStore::class)->get($this->path) : null;
    }
}
