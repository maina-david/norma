<?php

namespace App\Models\Actions;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperActionAreaCompliance
 */
class ActionAreaCompliance extends AbstractModel
{
    use SoftDeletes;

    protected $fillable = [
        'action_area_id',
        'risk_of_non_compliance',
        'date_answered',
        'user_id',
        'next_review',
        'current',
    ];

    protected $casts = [
        'date_answered' => 'datetime',
        'changed_by' => User::class,
        'next_review' => 'datetime',
        'current' => 'boolean',
    ];
}
