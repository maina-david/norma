<?php

namespace App\Models\Workflows;

use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Models\AbstractModel;
use App\Models\Collaborators\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array $locations
 * @property array $legal_domains
 *
 * @mixin IdeHelperMonitoringTaskConfig
 */
class MonitoringTaskConfig extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'enabled' => 'bool',
        'locations' => 'array',
        'legal_domains' => 'array',
        'tasks' => 'array',
        'last_run' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }

    /**
     * Check if the task should run.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $shouldRunToday = now()->isDayOfWeek($this->start_day);

        if (!$this->last_run && $shouldRunToday) {
            return true;
        }

        if ($this->frequency === MonitoringTaskFrequency::DAY->value) {
            // Add 5 minutes in case the job took long to run.
            return ((int) now()->addMinutes(5)->diffInDays($this->last_run, true)) >= $this->frequency_quantity;
        }

        if ($this->frequency === MonitoringTaskFrequency::WEEK->value) {
            // Add 5 minutes in case the job took long to run.
            return $shouldRunToday && ((int) now()->addMinutes(5)->diffInWeeks($this->last_run, true)) >= $this->frequency_quantity;
        }

        // else it's a monthly frequency
        // Add 5 minutes in case the job took long to run.
        return $shouldRunToday && ((int) now()->addMinutes(5)->diffInMonths($this->last_run, true)) >= $this->frequency_quantity;
    }
}
