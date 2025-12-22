<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\NormaModule;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class ActionAreaPlannerControllerTest extends MyTestCase
{
    public function testListingAndViewing(): void
    {
        /** @var \App\Models\Customer\Norma $norma */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $norma->references()->attach($reference->id);
        $action = ActionArea::factory()->create();
        $action->references()->attach($reference->id);

        $this->get(route('my.actions.action-areas.controls.index'))
            ->assertSessionHas('flash.type', 'error')
            ->assertSessionHas('flash.message', 'Actions has not been enabled.')
            ->assertRedirect(route('my.dashboard'));

        $norma->enableModule(NormaModule::actions());

        $org->refresh();
        $norma->refresh();

        Task::factory()->create([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
            'assigned_to_id' => $user->id,
            'place_id' => $norma->id,
            'action_area_id' => $action->id,
        ]);

        $this->get(route('my.actions.action-areas.controls.index'))
            ->assertSuccessful()
            ->assertSee('my-norma');

        $this->get(route('my.actions.action-areas.subject.index'))
            ->assertSuccessful()
            ->assertSee('my-norma');

        $this->get(route('my.actions.action-areas.requirements.index'))
            ->assertSuccessful()
            ->assertSee('my-norma');

        $this->get(route('my.actions.action-areas.subject.index', ['statuses' => [1]]))
            ->assertSuccessful()
            ->assertSee('my-norma');

        $this->get(route('my.actions.action-areas.show', ['action' => $action->id]))
            ->assertSuccessful()
            ->assertSee('my-norma');
    }
}
