<?php

namespace App\Models\Compilation;

use App\Enums\Compilation\ApplicabilityNoteType;
use App\Models\AbstractModel;

/**
 * @mixin IdeHelperApplicabilityNote
 */
class ApplicabilityNote extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'note_type' => ApplicabilityNoteType::class,
    ];
}
