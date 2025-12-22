<?php

namespace Tests\Feature\Dashboard\My;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Livewire\Compilation\ContextQuestion\UnansweredApplicabilityModal;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Work;
use App\Models\Notify\LegalUpdate;
use Livewire\Livewire;
use Tests\Feature\My\MyTestCase;

class DashboardControllerTest extends MyTestCase
{
    public function testDashboard(): void
    {
        /** @var \App\Models\Auth\User $user */
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.dashboard';

        $work = Work::factory()->create();
        $update = LegalUpdate::factory()->for($work)->create();
        $context = ContextQuestion::factory()->create();
        $context->normas()->attach($norma->id, ['answer' => ContextQuestionAnswer::maybe()->value]);
        $user->legalUpdates()->attach($update->id, ['read_status' => 0, 'understood_status' => 0]);

        $response = $this->get(route($routeName));

        $response->assertSuccessful();
        $response->assertSee('Hello, ' . $user->fname);
        $response->assertSee('Days Active in the last 30 days');
        $response->assertSee('Updates Received This Month');
        $response->assertSee('Norma Streams');
        $response->assertSee('Unread updates');
        $response->assertSeeLivewire(UnansweredApplicabilityModal::class);

        $this->assertFalse($user->refresh()->getSetting('context_hide_unanswered_notice', false));
        Livewire::test(UnansweredApplicabilityModal::class, ['pendingCount' => 1])
            ->assertSet('pendingCount', 1)
            ->assertSee('You currently have 1 unanswered Applicability Items')
            ->call('manageSetting', true);

        $this->assertTrue($user->refresh()->getSetting('context_hide_unanswered_notice', false));
    }
}
