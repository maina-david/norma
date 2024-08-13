<?php

namespace Tests\Feature\Auth;

use App\Models\Storage\My\Attachment;
use Exception;
use Hashids\Hashids;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Macros\InlineDownload;
use Tests\TestCase;

class UserAvatarControllerTest extends TestCase
{
    /**
     * @throws Exception
     *
     * @return void
     */
    public function testItSendsTheCorrectAvatar(): void
    {
        InlineDownload::apply();
        Storage::fake();

        $file = UploadedFile::fake()->image('passport.jpg');
        $attachment = new Attachment();
        $attachment->saveFile($file);

        $key = (new Hashids(config('encrypt.avatar_url.passphrase')))->encode($attachment->id);

        $this->actingAs($this->mySuperUser());

        $this->get(route('my.user.avatar.download', ['attachment' => $key]))
            ->assertSuccessful()
            ->assertInlineDownload('passport.jpg');
    }
}
