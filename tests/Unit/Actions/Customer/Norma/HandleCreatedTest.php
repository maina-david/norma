<?php

namespace Tests\Unit\Actions\Customer\Norma;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Norma;
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
        $norma = Norma::factory()->create();

        $setting = CompilationSetting::find($norma->id);
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
