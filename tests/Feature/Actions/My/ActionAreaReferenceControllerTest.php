<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\NormaModule;
use App\Enums\Tasks\TaskStatus;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class ActionAreaReferenceControllerTest extends MyTestCase
{
    public function testListingAndViewing(): void
    {
        /** @var \App\Models\Customer\Norma $norma */
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work']);
        $norma->references()->attach($reference->id);
        $action = ActionArea::factory()->create();
        $action->references()->attach($reference->id);

        $norma->updateSetting('modules.' . NormaModule::actions()->value, false);
        $norma->refresh();

        $this->get(route('my.actions.action-areas.requirements.index'))
            ->assertSessionHas('flash.type', 'error')
            ->assertSessionHas('flash.message', 'Actions has not been enabled.')
            ->assertRedirect(route('my.dashboard'));

        $norma->enableModule(NormaModule::actions());

        $norma->refresh();

        Task::factory()->create([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
            'assigned_to_id' => $user->id,
            'place_id' => $norma->id,
            'task_status' => TaskStatus::inProgress()->value,
        ]);

        Task::factory()->create([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
            'assigned_to_id' => $user->id,
            'place_id' => $norma->id,
            'task_status' => TaskStatus::done()->value,
        ]);

        $this->get(route('my.actions.action-areas.requirements.show', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertSee('my-norma');
    }
}
