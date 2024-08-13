<?php

namespace App\Models\Ontology;

use App\Models\AbstractModel;
use App\Models\Traits\UsesUid;

/**
 * @mixin IdeHelperWorkType
 */
class WorkType extends AbstractModel
{
    use UsesUid;

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    public function getUidString(): string
    {
        return $this->slug ?? '';
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
