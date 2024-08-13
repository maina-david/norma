<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use Tests\Feature\My\MyTestCase;

class ReferenceLinkedActionAreasControllerTest extends MyTestCase
{
    public function testingLinkedActionAreas(): void
    {
        $this->initUserLibryoOrg();
        $reference = Reference::factory()->create();
        $actionArea = ActionArea::factory()->create();
        $reference->actionAreas()->attach($actionArea->id);

        $this->getJson(route('api.my.references.action-areas.index', ['reference' => $reference]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $actionArea->id,
                        'title' => $actionArea->title,
                        'related_label' => $actionArea->display_label,
                        'subject_label' => $actionArea->subject_label,
                        'control_label' => $actionArea->control_label,
                        'subject_icon' => $actionArea->subject_icon ?? 'hashtag',
                        'control_icon' => $actionArea->control_icon ?? 'hashtag',
                    ],
                ],
            ]);
    }
}
