<?php

namespace App\Http\Controllers\Traits;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;

trait DecodesHashids
{
    protected function decodeHash(string $hash, string $resourceClass): Model
    {
        $id = $this->decodeHashId($hash, $resourceClass);

        /** @var Model */
        $model = $resourceClass::findOrFail($id);

        return $model;
    }

    protected function decodeHashId(string $hash, string $resourceClass): int
    {
        if (is_numeric($hash)) {
            $id = $hash;
        } else {
            $hashIds = new Hashids(config('encrypt.hash_id.' . $resourceClass . '.passphrase'), config('encrypt.hash_id.' . $resourceClass . '.padding'));
            $ids = $hashIds->decode($hash);
            $id = $ids[0] ?? 0;
        }

        return (int) $id;
    }
}
