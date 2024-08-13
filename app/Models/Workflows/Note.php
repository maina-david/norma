<?php

namespace App\Models\Workflows;

use App\Enums\Application\ApplicationType;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperNote
 */
class Note extends AbstractModel
{
    public $table = 'corpus_notes';

    /**
     * @return MorphTo
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'author_id');
    }

    /**
     * Check if the user is the author.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user): bool
    {
        return $this->author_id === $user->id;
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::collaborate()->value => ['view'],
        ];
    }
}
