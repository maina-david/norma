<?php

namespace App\Stores\Arachno;

use App\Models\Arachno\Link;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Str;

class LinkStore
{
    use AttachesDetaches;

    public function firstOrCreateFromUri(string $uri): Link
    {
        $url = Str::before($uri, '#');
        $uidHash = Link::buildUid($url);
        $uid = Link::hashForDB($uidHash);
        /** @var Link|null $link */
        $link = Link::forUrlUid($uri)->first();
        if (!$link) {
            /** @var Link */
            $link = Link::create([
                'uid' => $uid,
                'url' => $url,
            ]);
        }

        return $link;
    }
}
