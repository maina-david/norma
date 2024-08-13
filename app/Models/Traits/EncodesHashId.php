<?php

namespace App\Models\Traits;

use Hashids\Hashids;

trait EncodesHashId
{
    public function getHashIdAttribute(): string
    {
        $model = get_class($this);
        $hashIds = new Hashids(config('encrypt.hash_id.' . $model . '.passphrase'), config('encrypt.hash_id.' . $model . '.padding'));

        return $hashIds->encode($this->id);
    }
}
