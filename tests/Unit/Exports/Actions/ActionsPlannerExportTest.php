<?php

namespace Tests\Unit\Exports\Actions;

use App\Exports\Actions\ActionsPlannerExport;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class ActionsPlannerExportTest extends MyTestCase
{
    public function testBuild(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work']);
        $norma->references()->attach($reference->id);
        $control = Category::factory()->create(['parent_id' => Category::factory(), 'level' => 2]);
        $subject = Category::factory()->create(['level' => 1]);
        $action = ActionArea::factory()->create(['control_category_id' => $control->id, 'subject_category_id' => $subject->id]);
        $action->references()->attach($reference->id);
        $task = Task::factory()->create([
            'action_area_id' => $action->id,
            'taskable_type' => $reference->getMorphClass(),
            'taskable_id' => $reference->id,
            'place_id' => $norma->id,
        ]);

        $progressCallback = function ($progress) {};

        $filters = [];
        $excel = app(ActionsPlannerExport::class)->setDomain('https://my.norma.com/')->forNorma($norma, $org, $user, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();
        $this->assertSame('Parent Topic', $ws->getCell('A1')->getValue());

        $this->assertSame($subject->display_label, $ws->getCell('A2')->getValue());
        $this->assertSame($action->title, $ws->getCell('B2')->getValue());

        $excel = app(ActionsPlannerExport::class)->usingControl()->forOrganisation($org, $user, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();
        $this->assertSame($control->display_label, $ws->getCell('A2')->getValue());
        $this->assertSame($action->title, $ws->getCell('B2')->getValue());
    }
}
