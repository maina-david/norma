<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperUpdateEmail
 */
class UpdateEmail extends AbstractModel
{
    /** @var string */
    protected $table = 'update_emails';

    /** @var array<string, string> */
    protected $casts = [
        'charsets' => 'array',
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
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        /** @var BelongsTo */
        return $this->belongsTo(UpdateEmailSender::class);
    }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        /** @var HasMany */
        return $this->hasMany(UpdateEmailAttachment::class);
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
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
        ];
    }
}
