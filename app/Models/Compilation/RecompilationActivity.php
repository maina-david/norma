<?php

namespace App\Models\Compilation;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperRecompilationActivity
 */
class RecompilationActivity extends AbstractModel
{
    protected $table = 'recompilation_activities';

    /** @var array<string, string> */
    protected $casts = [
        'details' => 'json',
    ];
}
