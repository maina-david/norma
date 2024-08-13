<?php

namespace App\Models\System;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin IdeHelperSystemNotification
 */
class SystemNotification extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'active' => 'boolean',
        'is_permanent' => 'boolean',
        'has_user_action' => 'boolean',
        'modules' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

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
    public function scopeNotExpired(Builder $builder): Builder
    {
        return $builder->where(function ($q) {
            $q->where('expiry_date', '>=', now());
            $q->orWhereNull('expiry_date');
        });
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
            'content' => $this->content,
        ];
    }
}
