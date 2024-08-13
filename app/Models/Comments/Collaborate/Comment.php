<?php

namespace App\Models\Comments\Collaborate;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperComment
 */
class Comment extends AbstractModel
{
    use ApiQueryFilterable;

    protected $table = 'librarian_comments';

    /**
     * @return BelongsTo
     */
    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'author_id');
    }

    /**
     * Check if the logged in user is the author.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user): bool
    {
        return $user->id === $this->author_id;
    }
}
