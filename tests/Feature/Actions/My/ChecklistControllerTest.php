<?php

namespace Tests\Feature\Actions\My;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use Tests\Feature\My\MyTestCase;

class ChecklistControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testViewingAllChecklists(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work']);
        $norma->references()->attach($reference->id);
        $control = Category::factory()->create(['parent_id' => Category::factory(), 'level' => 2]);
        $subject = Category::factory()->create(['level' => 1]);
        $action = ActionArea::factory()->create(['control_category_id' => $control->id, 'subject_category_id' => $subject->id]);
        $action->references()->attach($reference->id);

        // Test viewing the checklists page
        $response = $this->get(route('my.actions.checklists.index'))
            ->assertSuccessful()
            ->assertViewIs('inertia.my.layout');

        $response = $this->get(route('api.my.actions.checklist.areas.index'))
            ->assertSuccessful();

        // Test viewing the checklist areas via API
        $response = $this->get(route('api.my.actions.checklist.areas.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'subject_label',
                        'subject_icon',
                        'control_label',
                        'control_icon',
                    ],
                ],
            ]);
        // $content = $response->getContent();

        // Todo: Delete this after done debugging
        /*        fwrite(STDERR, print_r($content, true));

                // Verify the response data
                $data = $response->json('data');

                $this->assertNotEmpty($data);

                foreach ($data as $actionArea) {
                    $this->assertArrayHasKey('id', $actionArea);
                    $this->assertArrayHasKey('title', $actionArea);
                    $this->assertArrayHasKey('subject_label', $actionArea);
                    $this->assertArrayHasKey('subject_icon', $actionArea);
                    $this->assertArrayHasKey('control_label', $actionArea);
                    $this->assertArrayHasKey('control_icon', $actionArea);

                    $this->assertIsInt($actionArea['id']);
                    $this->assertIsString($actionArea['title']);
                    $this->assertIsString($actionArea['subject_label']);
                    $this->assertIsString($actionArea['subject_icon']);
                    $this->assertIsString($actionArea['control_label']);
                    $this->assertNull($actionArea['control_icon']);
                }*/

        $updateData = [
            'riskOfNonCompliance' => 3,
        ];

        $response = $this->put(route('api.my.actions.checklist.update', ['action' => $action->id]), $updateData)
            ->assertSuccessful()
            ->assertJson([
                'message' => 'Checklist updated successfully.',
            ]);
    }
}
