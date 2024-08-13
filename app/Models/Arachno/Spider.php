<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperSpider
 */
class Spider extends AbstractModel
{
    protected $table = 'corpus_spiders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'slug';

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
