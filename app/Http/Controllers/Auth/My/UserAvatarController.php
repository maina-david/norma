<?php

namespace App\Http\Controllers\Auth\My;

use App\Http\Controllers\Controller;
use App\Models\Storage\My\Attachment;
use Hashids\Hashids;
use Illuminate\Http\Response;

class UserAvatarController extends Controller
{
    /**
     * @param string $attachment
     *
     * @return Response
     */
    public function downloadAvatar(string $attachment, string $size = 'small'): Response
    {
        $hashIds = new Hashids(config('encrypt.avatar_url.passphrase'));
        $id = $hashIds->decode($attachment)[0];

        /** @var Attachment */
        $attachment = Attachment::findOrFail($id);

        return $attachment->streamImageFile($size);
    }
}
