<?php

namespace Tests\Feature\General;

use App\Models\Auth\User;
use Tests\TestCase;

class NonNullableTest extends TestCase
{
    /**
     * @return void
     */
    public function testSettingOfNonNullableFields(): void
    {
        $user = new User();

        $this->assertNull($user->fname);
        $this->assertNull($user->sname);

        $user->save();

        $this->assertEquals('', $user->fname);
        $this->assertEquals('', $user->sname);
    }
}
