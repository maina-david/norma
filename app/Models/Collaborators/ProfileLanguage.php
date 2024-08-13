<?php

namespace App\Models\Collaborators;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperProfileLanguage
 */
class ProfileLanguage extends AbstractModel
{
    /** @var string */
    protected $table = 'librarian_profile_languages';

    /**
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
