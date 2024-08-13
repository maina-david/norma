<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\LibryoModule;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class ActionAreaPlannerControllerTest extends MyTestCase
{
    public function testListingAndViewing(): void
    {
        /** @var \App\Models\Customer\Libryo $libryo */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $reference = Reference::factory()->create();
        $libryo->references()->attach($reference->id);
        $action = ActionArea::factory()->create();
        $action->references()->attach($reference->id);

        $this->get(route('my.actions.action-areas.controls.index'))
            ->assertSessionHas('flash.type', 'error')
            ->assertSessionHas('flash.message', 'Actions has not been enabled.')
            ->assertRedirect(route('my.dashboard'));

        $libryo->enableModule(LibryoModule::actions());

        $org->refresh();
        $libryo->refresh();

        Task::factory()->create([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
            'assigned_to_id' => $user->id,
            'place_id' => $libryo->id,
            'action_area_id' => $action->id,
        ]);

        $this->get(route('my.actions.action-areas.controls.index'))
            ->assertSuccessful()
            ->assertSee('my-libryo');

        $this->get(route('my.actions.action-areas.subject.index'))
            ->assertSuccessful()
            ->assertSee('my-libryo');

        $this->get(route('my.actions.action-areas.requirements.index'))
            ->assertSuccessful()
            ->assertSee('my-libryo');

        $this->get(route('my.actions.action-areas.subject.index', ['statuses' => [1]]))
            ->assertSuccessful()
            ->assertSee('my-libryo');

        $this->get(route('my.actions.action-areas.show', ['action' => $action->id]))
            ->assertSuccessful()
            ->assertSee('my-libryo');
    }
}
