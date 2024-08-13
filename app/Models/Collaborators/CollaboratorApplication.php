<?php

namespace App\Models\Collaborators;

use App\Enums\Application\ApplicationType;
use App\Http\ModelFilters\Collaborators\CollaboratorApplicationFilter;
use App\Models\AbstractModel;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperCollaboratorApplication
 */
class CollaboratorApplication extends AbstractModel
{
    use Filterable;

    protected $table = 'librarian_turk_applications';

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'turk_application_id');
    }

    /**
     * @param Builder $builder
     * @param string  $email
     *
     * @return Builder
     */
    public function scopeEmailLike(Builder $builder, string $email): Builder
    {
        return $builder->where('email', 'LIKE', "%{$email}%");
    }

    /**
     * @param Builder $builder
     * @param string  $name
     *
     * @return Builder
     */
    public function scopeNameLike(Builder $builder, string $name): Builder
    {
        return $builder->where(function ($q) use ($name) {
            $q->where('fname', 'LIKE', "%{$name}%")
                ->orWhere('lname', 'LIKE', "%{$name}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(CollaboratorApplicationFilter::class);
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::collaborate()->value => ['create'],
        ];
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
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
        ];
    }
}
