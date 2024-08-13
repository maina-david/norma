<?php

namespace App\Models\Collaborators;

use App\Enums\Collaborators\DocumentArchiveOptions;
use App\Enums\Collaborators\DocumentType;
use App\Models\AbstractModel;
use App\Models\Storage\Collaborate\Attachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperProfileDocument
 */
class ProfileDocument extends AbstractModel
{
    /** @var string */
    protected $table = 'librarian_profile_documents';

    /** @var array<string, string> */
    protected $casts = [
        'type' => DocumentType::class,
        'reason_for_update' => DocumentArchiveOptions::class,
        'active' => 'boolean',
        'expired' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    /**
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    /**
     * @param Builder         $builder
     * @param DocumentType    $type
     * @param string|int|null $subtype
     *
     * @return Builder
     */
    public function scopeOfType(Builder $builder, DocumentType $type, string|int|null $subtype = null): Builder
    {
        return $builder->where(function ($query) use ($type, $subtype) {
            $query->where('type', $type->value)
                ->when($subtype, fn ($builder) => $builder->where('subtype', $subtype));
        });
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('active', true);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeExpired(Builder $builder): Builder
    {
        return $builder->active()->where('expired', true);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeValid(Builder $builder): Builder
    {
        return $builder->active()->where('expired', false);
    }
}
