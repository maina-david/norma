<?php

namespace App\Models\Collaborators;

use App\Models\AbstractModel;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentRequest;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Workflows\Task;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperTeam
 */
class Team extends AbstractModel implements Auditable
{
    use HasTitleLikeScope;
    use \OwenIt\Auditing\Auditable;

    /**
     * associted DB table.
     *
     * @var string
     */
    protected $table = 'librarian_teams';

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'team_id');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class, 'team_id');
    }

    public function outstandingPaymentRequests(): HasMany
    {
        return $this->paymentRequests()->outstanding();
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TeamRate::class, 'team_id');
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

    public function getRateForTask(Task $task): ?TeamRate
    {
        /** @var TeamRate|null */
        return $this->rates->where('for_task_type_id', $task->task_type_id)->first();
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
        ];
    }
}
