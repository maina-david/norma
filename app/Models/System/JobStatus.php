<?php

namespace App\Models\System;

use App\Enums\System\JobStatusType;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin IdeHelperJobStatus
 */
class JobStatus extends AbstractModel
{
    protected $table = 'job_statuses';

    /** @var array<string> */
    public $dates = ['started_at', 'finished_at', 'created_at', 'updated_at'];

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------*/

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include job statuses that are finished.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeEnded($query)
    {
        return $query->where(function ($q) {
            $q->where('job_status', JobStatusType::failed()->value);
            $q->orWhere('job_status', JobStatusType::finished()->value);
        });
    }
}
