<?php

namespace Tests\Unit\Observers\Notify;

use App\Models\Notify\LegalUpdate;
use Tests\TestCase;

class LegalUpdateObserverTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testObserveSaving(): void
    {
        $legalUpdate = LegalUpdate::factory()->create();
        $test = '<p style="text-align: center; font-weight: bold;"><a href="example.com">a Link</a><table></table></p>';

        foreach (['highlights', 'changes_to_register', 'update_report'] as $field) {
            $legalUpdate->update([$field => $test]);
            $legalUpdate->refresh();
            $this->assertStringContainsString('a Link', $legalUpdate[$field]);
            $this->assertStringNotContainsString('<table>', $legalUpdate[$field]);
        }
    }
}
