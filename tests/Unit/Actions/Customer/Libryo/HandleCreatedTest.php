<?php

namespace Tests\Unit\Actions\Customer\Libryo;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Libryo;
use Tests\TestCase;

class HandleCreatedTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCompilationSettingIsCreated(): void
    {
        $libryo = Libryo::factory()->create();

        $setting = CompilationSetting::find($libryo->id);
        $this->assertNotNull($setting);

        // and test the defaults
        $this->assertSame(true, $setting->use_collections);
        $this->assertSame(true, $setting->use_legal_domains);
        $this->assertSame(true, $setting->use_context_questions);
        $this->assertSame(false, $setting->include_no_legal_domains);
        $this->assertSame(true, $setting->include_no_context_questions);
        $this->assertSame(false, $setting->use_topics);
    }
}
