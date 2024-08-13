<?php

namespace Tests\Feature\Api\Internals\My\Ontology;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryType;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class CategoryControllerTest extends MyTestCase
{
    public function testListingControlsAndSubjects(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        CategoryType::factory()->create(['id' => \App\Enums\Ontology\CategoryType::CONTROL->value]);
        CategoryType::factory()->create(['id' => \App\Enums\Ontology\CategoryType::SUBJECT->value]);

        $control = Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::CONTROL->value]);
        $control = Category::factory()->create(['parent_id' => $control->id, 'category_type_id' => \App\Enums\Ontology\CategoryType::CONTROL->value]);
        $subject = Category::factory()->create(['category_type_id' => \App\Enums\Ontology\CategoryType::SUBJECT->value]);

        $action = ActionArea::factory()->create(['control_category_id' => $control->id, 'subject_category_id' => $subject->id]);
        Task::factory()->create(['action_area_id' => $action->id, 'place_id' => $libryo->id]);

        $reference = Reference::factory()->create();
        $reference->libryos()->attach($libryo->id);
        $reference->categories()->attach([$control->id, $subject->id]);

        $this->getJson(route('api.my.categories.controls.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['value' => $control->id],
                ],
            ]);

        $this->getJson(route('api.my.categories.subjects.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['value' => $subject->id],
                ],
            ]);
    }
}
