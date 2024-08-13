<?php

namespace App\Models\Auth;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperHubspotId
 */
class HubspotId extends AbstractModel
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    public $timestamps = false;
}
